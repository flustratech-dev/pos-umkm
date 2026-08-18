<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Fee Lisensi Developer</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1d9bf0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0f1419;
            margin: 0 0 4px 0;
        }
        .subtitle {
            font-size: 11px;
            color: #536471;
            margin: 0;
        }
        .kpi-cards {
            width: 100%;
            margin-bottom: 20px;
        }
        .kpi-card {
            background-color: #f7f9f9;
            border: 1px solid #eff3f4;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }
        .kpi-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #536471;
            font-weight: bold;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #1d9bf0;
            margin-top: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data-table th {
            background-color: #f7f9f9;
            color: #0f1419;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 1px solid #cfd9de;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eff3f4;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #536471;
        }
    </style>
</head>
    @php
        $logoPath = public_path('images/logo_jadisatu.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
    @endphp
    <div class="header">
        <table style="width: 100%; border: none; margin-bottom: 4px;">
            <tr>
                @if($logoBase64)
                    <td style="width: 55px; border: none; text-align: left; vertical-align: middle; padding: 0;">
                        <img src="{{ $logoBase64 }}" style="height: 48px; width: auto; object-fit: contain;">
                    </td>
                @endif
                <td style="border: none; text-align: {{ $logoBase64 ? 'left' : 'center' }}; vertical-align: middle; padding: 0 0 0 10px;">
                    <div class="title" style="margin: 0; font-size: 15px;">AUDIT PLATFORM & BREAKDOWN PEMBAGIAN HASIL</div>
                    <div class="subtitle" style="font-weight: bold; color: #0f1419; font-size: 11px;">Platform Lisensi JADISATU Multi-Event</div>
                    <div class="subtitle">Pembagian: 75% Warung &bull; 25% EO &bull; Fee Dev 10% dari 25% (= 2.5%) &bull; JADISATU Event System</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="kpi-cards">
        <tr>
            <td width="20%" style="padding-right: 4px;">
                <div class="kpi-card" style="background-color: #e8f5fd; border-color: #bde2f9;">
                    <div class="kpi-title" style="color: #1d9bf0;">Fee Developer (10% dari 25%)</div>
                    <div class="kpi-value">Rp {{ number_format($platformStats['total_superadmin_fee'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Gross Volume Platform</div>
                    <div class="kpi-value" style="color: #0f1419;">Rp {{ number_format($platformStats['total_platform_gross'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Hak Warung (75%)</div>
                    <div class="kpi-value" style="color: #1d9bf0;">Rp {{ number_format($platformStats['total_platform_gross'] * 0.75, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Potongan EO (25%)</div>
                    <div class="kpi-value" style="color: #0f1419;">Rp {{ number_format($platformStats['total_platform_gross'] * 0.25, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding-left: 4px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Transaksi Paid</div>
                    <div class="kpi-value" style="color: #0f1419;">{{ $platformStats['paid_count'] }} Transaksi</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="13%">Invoice</th>
                <th width="13%">Waktu Paid</th>
                <th width="13%">Event</th>
                <th width="13%">Stand Tenant</th>
                <th width="12%" class="text-right">Gross Transaksi</th>
                <th width="12%" class="text-right">Warung (75%)</th>
                <th width="12%" class="text-right">Potongan EO (25%)</th>
                <th width="12%" class="text-right" style="color: #1d9bf0;">Fee Dev (10%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paidTransactions as $tx)
                <tr>
                    <td class="bold">{{ $tx->invoice_code }}</td>
                    <td>{{ $tx->paid_at ? $tx->paid_at->format('d/m/Y H:i') : $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $tx->store?->event?->name ?: '-' }}</td>
                    <td class="bold">{{ $tx->store?->name ?: '-' }}</td>
                    <td class="text-right bold">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right bold" style="color: #1d9bf0;">Rp {{ number_format($tx->revenueSplit?->owner_share ?: ($tx->total_amount * 0.75), 0, ',', '.') }}</td>
                    <td class="text-right bold">Rp {{ number_format($tx->revenueSplit?->admin_gross_share ?: ($tx->total_amount * 0.25), 0, ',', '.') }}</td>
                    <td class="text-right bold" style="color: #1d9bf0;">Rp {{ number_format($tx->revenueSplit?->superadmin_share ?: ($tx->total_amount * 0.025), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #536471;">Belum ada transaksi paid terdaftar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis pada: {{ now()->format('d/m/Y H:i:s') }} | JADISATU Multi-Event System
    </div>
</body>
</html>
