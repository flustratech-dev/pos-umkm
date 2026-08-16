<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - {{ $store->name }}</title>
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
        .meta-grid {
            width: 100%;
            margin-bottom: 16px;
        }
        .meta-grid td {
            padding: 4px 0;
            vertical-align: top;
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
                    <div class="title" style="margin: 0; font-size: 15px;">LAPORAN PENJUALAN & BAGI HASIL STAND</div>
                    <div class="subtitle" style="font-weight: bold; color: #0f1419; font-size: 11px;">{{ $store->name }} ({{ $store->booth_number ?: 'Stand Tenant' }}) &bull; {{ $activeEvent?->name ?: 'Bazar UMKM' }}</div>
                    <div class="subtitle">Pemilik: {{ $user->name }} ({{ $user->phone ?: '-' }}) &bull; JADISATU Event System</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="kpi-cards">
        <tr>
            <td width="33%" style="padding-right: 6px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Omzet Gross</div>
                    <div class="kpi-value" style="color: #0f1419;">Rp {{ number_format($stats['total_gross'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="33%" style="padding: 0 3px;">
                <div class="kpi-card" style="background-color: #e8f5fd; border-color: #bde2f9;">
                    <div class="kpi-title" style="color: #1d9bf0;">Hak Warung (75%)</div>
                    <div class="kpi-value">Rp {{ number_format($stats['owner_share'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="33%" style="padding-left: 6px;">
                <div class="kpi-card">
                    <div class="kpi-title">Transaksi Paid</div>
                    <div class="kpi-value" style="color: #0f1419;">{{ $stats['paid_count'] }} Tx ({{ $stats['cash_count'] }} Cash / {{ $stats['qris_count'] }} QRIS)</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="18%">Invoice</th>
                <th width="18%">Waktu</th>
                <th width="10%">Metode</th>
                <th width="20%" class="text-right">Total Belanja</th>
                <th width="20%" class="text-right">Porsi 75% Bersih</th>
                <th width="14%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td class="bold">{{ $tx->invoice_code }}</td>
                    <td>{{ $tx->paid_at ? $tx->paid_at->format('d/m/Y H:i') : $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td style="text-transform: uppercase;">{{ $tx->payment_method }}</td>
                    <td class="text-right bold">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right bold" style="color: #1d9bf0;">
                        @if($tx->status === 'paid')
                            Rp {{ number_format($tx->revenueSplit?->owner_share ?: ($tx->total_amount * 0.75), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <span style="font-weight: bold; font-size: 9px; text-transform: uppercase;">
                            {{ $tx->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #536471;">Belum ada riwayat transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis pada: {{ now()->format('d/m/Y H:i:s') }} | JADISATU Event System
    </div>
</body>
</html>
