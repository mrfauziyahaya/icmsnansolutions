<?php

namespace App\Services;

use App\Models\Client;
use App\Models\WhatsAppNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $phoneNumberId;
    private string $accessToken;
    private string $adminNumber;
    private string $apiUrl;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken   = config('services.whatsapp.access_token');
        $this->adminNumber   = config('services.whatsapp.admin_number');
        $this->apiUrl        = 'https://graph.facebook.com/v19.0/' . $this->phoneNumberId . '/messages';
    }

    // ── Policy lifecycle notifications ─────────────────────────────────────

    public function notifyPolicyCreated(Client $client): void
    {
        $params = $this->policyParams($client);
        $this->sendTemplate($client, 'policy_created', 'nan_policy_created', $params);
        $this->sendAdminAlert($client->name, 'Polisi Baru Dicipta');
    }

    public function notifyPolicyUpdated(Client $client): void
    {
        $params = $this->policyParams($client);
        $this->sendTemplate($client, 'policy_updated', 'nan_policy_updated', $params);
        $this->sendAdminAlert($client->name, 'Polisi Dikemaskini');
    }

    public function notifyPolicyRenewed(Client $client): void
    {
        $params = $this->policyParams($client);
        $this->sendTemplate($client, 'policy_renewed', 'nan_policy_renew', $params);
        $this->sendAdminAlert($client->name, 'Polisi Diperbaharui');
    }

    // ── Expiry reminders ────────────────────────────────────────────────────

    public function sendExpiryReminder(Client $client, string $type): void
    {
        $days   = $type === 'expiry_30d' ? '30' : '14';
        $expiry = $client->expiry_date?->format('d/m/Y') ?? '-';

        $params = [
            $client->name,
            $days,
            $client->plate,
            $client->insurance_company,
            $expiry,
        ];

        $this->sendTemplate($client, $type, 'nan_expiry_reminder', $params);
        $this->sendAdminAlert($client->name, "Peringatan Tamat Tempoh {$days} Hari");
    }

    // ── Core send helpers ───────────────────────────────────────────────────

    private function policyParams(Client $client): array
    {
        // Templates have a static link button — only 4 body variables needed
        return [
            $client->name,
            $client->plate,
            $client->insurance_company,
            $client->expiry_date?->format('d/m/Y') ?? '-',
        ];
    }

    private function sendTemplate(Client $client, string $type, string $templateName, array $params): void
    {
        $phone  = $this->normalizePhone($client->phone);
        $status = 'sent';
        $error  = null;

        if ($phone) {
            $result = $this->callTemplate($phone, $templateName, $params);
            if (!$result['success']) {
                $status = 'failed';
                $error  = $result['error'];
            }
        } else {
            $status = 'failed';
            $error  = 'No valid phone number on record.';
        }

        WhatsAppNotification::create([
            'client_id'       => $client->client_id,
            'type'            => $type,
            'recipient_phone' => $phone ?? $client->phone,
            'message'         => $templateName . ': ' . implode(' | ', $params),
            'status'          => $status,
            'error'           => $error,
            'sent_at'         => now(),
        ]);
    }

    private function callTemplate(string $phone, string $templateName, array $params): array
    {
        if (!$this->phoneNumberId || !$this->accessToken) {
            Log::warning('WhatsApp credentials not configured.');
            return ['success' => false, 'error' => 'WhatsApp credentials not configured.'];
        }

        $components = [];

        if (!empty($params)) {
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $params),
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'template',
                    'template'          => [
                        'name'       => $templateName,
                        'language'   => ['code' => 'ms'],
                        'components' => $components,
                    ],
                ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            $err = $response->json('error.message', 'Unknown error');
            Log::error("WhatsApp template '{$templateName}' failed to {$phone}: {$err}");
            return ['success' => false, 'error' => $err];

        } catch (\Throwable $e) {
            Log::error("WhatsApp exception: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendAdminAlert(string $clientName, string $typeLabel): void
    {
        $this->callTemplate($this->adminNumber, 'nan_admin_alert', [$clientName, $typeLabel]);
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $phone = preg_replace('/\D/', '', $phone);
        // Malaysian numbers: 01x → 601x
        if (str_starts_with($phone, '0')) {
            $phone = '6' . $phone;
        }
        return $phone;
    }
}
