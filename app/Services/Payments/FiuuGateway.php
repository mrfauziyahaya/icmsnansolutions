<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fiuu (formerly Razer Merchant Services) — Hosted Payment Page integration.
 *
 * Spec: "[official API] Fiuu API Spec for Merchant" v13.93.
 *
 * Flow: redirect the buyer (GET, with a signed vcode) to Fiuu's hosted page.
 * Fiuu then POSTs the result back to our return/notification/callback URL, each
 * carrying an skey we verify. A separate q_by_oid requery is used to reconcile.
 *
 *   vcode (request)   = md5( amount . merchantID . orderid . verify_key [. currency] )
 *   skey  (response)  = md5( paydate . domain . md5(tranID.orderid.status.domain.amount.currency) . appcode . secret_key )
 *   skey  (q_by_oid)  = md5( oID . domain . verify_key . amount )
 */
class FiuuGateway implements PaymentGateway, SiteAwareGateway
{
    use Concerns\ResolvesSiteCredentials;

    protected function gatewayKey(): string
    {
        return 'fiuu';
    }

    public function isConfigured(): bool
    {
        return filled($this->cfg('merchant_id'))
            && filled($this->cfg('verify_key'))
            && filled($this->cfg('secret_key'));
    }

    private function paymentHost(): string
    {
        return $this->cfg('sandbox')
            ? 'https://sandbox-payment.fiuu.com'
            : 'https://pay.fiuu.com';
    }

    private function apiHost(): string
    {
        return $this->cfg('sandbox')
            ? 'https://sandbox-api.fiuu.com'
            : 'https://api.fiuu.com';
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('Fiuu is not configured.');
        }

        $merchantId = $this->cfg('merchant_id');
        $verifyKey  = $this->cfg('verify_key');
        $amount     = number_format((float) $payment->amount, 2, '.', '');
        $orderId    = $payment->reference;
        $currency   = $payment->currency;

        // vcode ties amount + order + merchant together so the hosted page can
        // detect tampering. Currency is appended only if the merchant profile
        // has "extended format for Verify Payment" enabled.
        $vcodeSeed = $amount . $merchantId . $orderId . $verifyKey;
        if ($this->cfg('vcode_with_currency')) {
            $vcodeSeed .= $currency;
        }
        $vcode = md5($vcodeSeed);

        $webhook = route('pay.webhook', ['gateway' => 'fiuu']);

        $params = [
            'amount'      => $amount,
            'orderid'     => $orderId,
            'bill_name'   => $payment->payer_name,
            'bill_email'  => $payment->payer_email,
            'bill_mobile' => $payment->payer_phone,
            'bill_desc'   => 'Pembayaran ' . $orderId,
            'currency'    => $currency,
            'country'     => 'MY',
            'vcode'       => $vcode,
            // Both the browser return and the server-to-server notification/
            // callback come back to our single webhook (see verifyCallback +
            // the controller's Fiuu ACK handling).
            'returnurl'   => $webhook,
            'callbackurl' => $webhook,
        ];

        $url = $this->paymentHost() . '/RMS/pay/' . $merchantId . '/?' . http_build_query($params);

        $payment->update(['checkout_url' => $url]);

        return $url;
    }

    public function verifyCallback(Request $request): array
    {
        $tranID   = (string) $request->input('tranID');
        $orderid  = (string) $request->input('orderid');
        $status   = (string) $request->input('status');
        $domain   = (string) $request->input('domain');
        $amount   = (string) $request->input('amount');
        $currency = (string) $request->input('currency');
        $appcode  = (string) $request->input('appcode');
        $paydate  = (string) $request->input('paydate');
        $skey     = (string) $request->input('skey');

        $secretKey = $this->cfg('secret_key');

        // Recompute the skey exactly as Fiuu documents it. A mismatch means the
        // payload was forged or altered — refuse it.
        $key0 = md5($tranID . $orderid . $status . $domain . $amount . $currency);
        $key1 = md5($paydate . $domain . $key0 . $appcode . $secretKey);

        if (! hash_equals($key1, $skey)) {
            Log::warning('Fiuu skey verification failed.', ['orderid' => $orderid, 'ip' => $request->ip()]);
            throw new GatewayException('Fiuu skey verification failed.');
        }

        return [
            'reference'         => $orderid,
            'gateway_reference' => $tranID ?: null,
            'status'            => $this->mapStatus($status),
            'reason'            => $this->mapStatus($status) === 'paid'
                ? null
                : trim('Fiuu status ' . $status . ' ' . (string) $request->input('error_desc')),
        ];
    }

    public function getStatus(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('Fiuu is not configured.');
        }

        $merchantId = $this->cfg('merchant_id');
        $verifyKey  = $this->cfg('verify_key');
        $amount     = number_format((float) $payment->amount, 2, '.', '');
        $orderId    = $payment->reference;

        // Query-by-order-ID requery. skey here has a different field order than
        // the payment-response skey (per spec).
        $skey = md5($orderId . $merchantId . $verifyKey . $amount);

        $response = Http::asForm()->post($this->apiHost() . '/RMS/query/q_by_oid.php', [
            'amount' => $amount,
            'oID'    => $orderId,
            'domain' => $merchantId,
            'skey'   => $skey,
            'type'   => 2,   // JSON
        ]);

        if (! $response->successful()) {
            throw new GatewayException('Fiuu getStatus failed: HTTP ' . $response->status());
        }

        $data     = $response->json();
        $statCode = is_array($data) ? ($data['StatCode'] ?? null) : null;

        // No StatCode → transaction not found yet (buyer never completed) → still pending.
        if (! $statCode) {
            return ['status' => 'pending', 'reason' => null];
        }

        $status = $this->mapStatus((string) $statCode);

        return [
            'status' => $status,
            'reason' => $status === 'failed' ? ('Fiuu StatCode ' . $statCode) : null,
        ];
    }

    /**
     * Fiuu status codes: 00 = success, 11 = failed, 22 = pending (FPX-B2B/M2E).
     */
    private function mapStatus(string $code): string
    {
        return match ($code) {
            '00'    => 'paid',
            '22'    => 'pending',
            default => 'failed',
        };
    }
}
