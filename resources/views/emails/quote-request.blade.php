@php
    $ehailing = $quote->ehailing ? 'Ya' : 'Tidak';
    $tambahan = $quote->perlindungan_tambahan ?? [];
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; font-size: 14px; line-height: 1.6; }
        h2 { background: #ea580c; color: #fff; padding: 10px 14px; margin: 24px 0 0; font-size: 15px; letter-spacing: .5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        td { padding: 8px 14px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        td.label { width: 45%; font-weight: bold; color: #374151; }
        ul { margin: 4px 0; padding-left: 20px; }
        .wrap { max-width: 640px; margin: 0 auto; border: 1px solid #e5e7eb; }
        .head { background: #111827; color: #fff; padding: 16px; text-align: center; font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">SEBUT HARGA CUKAI KENDERAAN &mdash; {{ $quote->no_plate }}</div>

        <h2>MAKLUMAT PEMILIK KENDERAAN</h2>
        <table>
            <tr><td class="label">1. Nama Pemilik Kenderaan</td><td>{{ $quote->nama_pemilik }}</td></tr>
            <tr><td class="label">2. No IC Pemilik</td><td>{{ $quote->no_ic }}</td></tr>
            <tr><td class="label">3. Poskod</td><td>{{ $quote->poskod }}</td></tr>
            <tr><td class="label">4. No Plate Kenderaan</td><td>{{ $quote->no_plate }}</td></tr>
        </table>

        <h2>MAKLUMAT KENDERAAN</h2>
        <table>
            <tr><td class="label">Adakah Kenderaan Anda Digunakan Untuk E-Hailing?</td><td>{{ $ehailing }}</td></tr>
            @if($quote->ehailing)
                <tr><td class="label">Digunakan Untuk</td><td>{{ $quote->ehailing_usage ?? '-' }}</td></tr>
            @else
                <tr><td class="label">Kenderaan Baru Tukar Milik</td><td>{{ $quote->tukar_milik ? 'Ya' : 'Tidak' }}</td></tr>
            @endif
            <tr><td class="label">Nombor Whatsapp</td><td>{{ $quote->whatsapp }}</td></tr>
        </table>

        <h2>PERLINDUNGAN</h2>
        <table>
            <tr><td class="label">Jenis Perlindungan</td><td>{{ $quote->jenis_perlindungan }}</td></tr>
        </table>

        @if(!empty($tambahan))
            <h2>PERLINDUNGAN TAMBAHAN</h2>
            <table>
                <tr>
                    <td colspan="2">
                        <ul>
                            @foreach($tambahan as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @if($quote->jumlah_perlindungan_cermin)
                    <tr>
                        <td class="label">Jumlah Perlindungan Cermin Diperlukan</td>
                        <td>{{ number_format($quote->jumlah_perlindungan_cermin, 2) }}</td>
                    </tr>
                @endif
            </table>
        @endif

        <h2>JENIS PEMBAYARAN</h2>
        <table>
            <tr><td class="label">Pilihan Jenis Pembayaran</td><td>{{ $quote->jenis_pembayaran }}</td></tr>
        </table>
    </div>
</body>
</html>
