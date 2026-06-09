<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }

        .page { padding: 40px 50px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 30px; }
        .header-logo { display: table-cell; vertical-align: middle; width: 120px; }
        .header-logo img { max-width: 110px; max-height: 70px; }
        .header-company { display: table-cell; vertical-align: middle; padding-left: 16px; }
        .header-company h1 { font-size: 20px; font-weight: bold; color: #ea580c; }
        .header-company p { font-size: 11px; color: #555; margin-top: 2px; line-height: 1.5; }
        .header-invoice { display: table-cell; vertical-align: top; text-align: right; width: 180px; }
        .header-invoice .label { font-size: 22px; font-weight: bold; color: #ea580c; letter-spacing: 1px; }
        .header-invoice .inv-number { font-size: 13px; font-weight: bold; color: #1a1a1a; margin-top: 4px; }
        .header-invoice .inv-date { font-size: 11px; color: #555; margin-top: 2px; }

        /* Divider */
        .divider { border-top: 2px solid #ea580c; margin: 0 0 24px 0; }

        /* Type badge */
        .type-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; background: #fff7ed; color: #ea580c; border: 1px solid #fdba74; margin-bottom: 22px; }

        /* Billed to */
        .billed-section { display: table; width: 100%; margin-bottom: 28px; }
        .billed-to { display: table-cell; width: 55%; vertical-align: top; }
        .billed-to h3 { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 6px; }
        .billed-to .client-name { font-size: 14px; font-weight: bold; color: #1a1a1a; }
        .billed-to p { font-size: 11px; color: #444; line-height: 1.6; margin-top: 2px; }

        /* Policy info */
        .policy-box { display: table-cell; width: 45%; vertical-align: top; }
        .policy-box table { width: 100%; border-collapse: collapse; }
        .policy-box td { font-size: 11px; padding: 3px 6px; }
        .policy-box td:first-child { color: #888; }
        .policy-box td:last-child { font-weight: bold; text-align: right; }

        /* Line items */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table thead th { background: #ea580c; color: #fff; font-size: 11px; font-weight: bold; padding: 9px 12px; text-align: left; }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody tr:nth-child(even) { background: #fff7ed; }
        .items-table tbody td { padding: 9px 12px; font-size: 12px; border-bottom: 1px solid #f0f0f0; }
        .items-table tbody td:last-child { text-align: right; font-weight: 600; }
        .items-table tfoot td { padding: 8px 12px; font-size: 12px; }
        .items-table tfoot .subtotal td { border-top: 1px solid #e5e5e5; color: #555; }
        .items-table tfoot .total-row td { border-top: 2px solid #ea580c; font-size: 14px; font-weight: bold; color: #ea580c; padding-top: 10px; }

        /* Footer */
        .footer { margin-top: 40px; border-top: 1px solid #e5e5e5; padding-top: 14px; font-size: 10px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div class="header-logo">
            @if($setting->logo_path && file_exists(storage_path('app/public/' . $setting->logo_path)))
                <img src="{{ storage_path('app/public/' . $setting->logo_path) }}">
            @endif
        </div>
        <div class="header-company">
            <h1>{{ strtoupper($setting->company_name ?? 'NAN SOLUTIONS') }}</h1>
            @if($setting->address1)
                <p>
                    {{ $setting->address1 }}@if($setting->address2), {{ $setting->address2 }}@endif<br>
                    @if($setting->postcode || $setting->city){{ $setting->postcode }} {{ $setting->city }}@endif
                    @if($setting->state), {{ $setting->state }}@endif<br>
                    @if($setting->phone)Tel: {{ $setting->phone }}@endif
                    @if($setting->email) &nbsp;|&nbsp; {{ $setting->email }}@endif
                </p>
            @endif
        </div>
        <div class="header-invoice">
            <div class="label">INVOICE</div>
            <div class="inv-number">{{ $invoice->invoice_number }}</div>
            <div class="inv-date">Date: {{ $invoice->issued_at->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Type badge -->
    <div class="type-badge">
        @if($invoice->type === 'new_policy') New Policy
        @elseif($invoice->type === 'renewal') Renewal
        @else Policy Update
        @endif
    </div>

    <!-- Billed to + Policy info -->
    <div class="billed-section">
        <div class="billed-to">
            <h3>Billed To</h3>
            <div class="client-name">{{ $client->name }}</div>
            @if($client->mykad_companyno)<p>IC / Reg No: {{ $client->mykad_companyno }}</p>@endif
            @if($client->phone)<p>Tel: {{ $client->phone }}</p>@endif
            @if($client->address1)
                <p>
                    {{ $client->address1 }}@if($client->address2), {{ $client->address2 }}@endif<br>
                    {{ $client->postcode }} {{ $client->city }}, {{ $client->state }}
                </p>
            @endif
        </div>
        <div class="policy-box">
            <table>
                <tr><td>Vehicle</td><td>{{ $client->plate }} – {{ $client->vehicle_model }}</td></tr>
                <tr><td>Category</td><td>{{ $client->category }}</td></tr>
                <tr><td>Insurer</td><td>{{ $client->insurance_company }}</td></tr>
                <tr><td>Inception</td><td>{{ $client->inception_date->format('d/m/Y') }}</td></tr>
                <tr><td>Expiry</td><td>{{ $client->expiry_date?->format('d/m/Y') ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Line items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right">Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Nett Premium</td>
                <td>{{ number_format($invoice->nett_premium, 2) }}</td>
            </tr>
            <tr>
                <td>Service Tax (8%)</td>
                <td>{{ number_format($invoice->nett_premium * 0.08, 2) }}</td>
            </tr>
            <tr>
                <td>Stamp Duty</td>
                <td>10.00</td>
            </tr>
            @if($invoice->road_tax_price > 0)
            <tr>
                <td>Road Tax</td>
                <td>{{ number_format($invoice->road_tax_price, 2) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="subtotal">
                <td colspan="2"></td>
            </tr>
            <tr class="total-row">
                <td>TOTAL PAYABLE</td>
                <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        This is a computer-generated invoice. No signature is required. &nbsp;|&nbsp; {{ $setting->company_name ?? 'NAN Solutions' }}
    </div>

</div>
</body>
</html>
