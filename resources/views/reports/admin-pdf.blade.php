<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan EO - {{ $activeEvent?->name ?: 'Bazar UMKM' }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1d9bf0;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #0f1419;
            margin: 0 0 3px 0;
        }
        .subtitle {
            font-size: 10px;
            color: #536471;
            margin: 0;
        }
        .kpi-cards {
            width: 100%;
            margin-bottom: 14px;
        }
        .kpi-card {
            background-color: #f7f9f9;
            border: 1px solid #eff3f4;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
        }
        .kpi-title {
            font-size: 8px;
            text-transform: uppercase;
            color: #536471;
            font-weight: bold;
        }
        .kpi-value {
            font-size: 12px;
            font-weight: bold;
            color: #1d9bf0;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table th {
            background-color: #f7f9f9;
            color: #0f1419;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #cfd9de;
            padding: 5px 6px;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #eff3f4;
            font-size: 9px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
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
                    <div class="title" style="margin: 0; font-size: 15px;">REKAPITULASI LAPORAN KEUANGAN PANITIA EO</div>
                    <div class="subtitle" style="font-weight: bold; color: #0f1419; font-size: 11px;">{{ $activeEvent?->name ?: 'Bazar UMKM' }} &bull; {{ $activeEvent?->location ?: 'Venue Event' }}</div>
                    <div class="subtitle">Periode: {{ $activeEvent?->start_date?->format('d/m/Y') }} s/d {{ $activeEvent?->end_date?->format('d/m/Y') }} &bull; JADISATU Event System</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="kpi-cards">
        <tr>
            <td width="33.3%" style="padding-right: 4px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Gross Omzet</div>
                    <div class="kpi-value" style="color: #0f1419;">Rp {{ number_format($stats['total_gross'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="33.3%" style="padding: 0 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">Porsi Warung (75%)</div>
                    <div class="kpi-value" style="color: #1d9bf0;">Rp {{ number_format($stats['owner_total'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="33.3%" style="padding-left: 4px;">
                <div class="kpi-card" style="background-color: #e8f5fd; border-color: #bde2f9;">
                    <div class="kpi-title" style="color: #1d9bf0;">Bagian EO (25%)</div>
                    <div class="kpi-value">Rp {{ number_format($stats['admin_gross'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Invoice</th>
                <th width="14%">Waktu</th>
                <th width="18%">Stand Warung</th>
                <th width="8%">Metode</th>
                <th width="15%" class="text-right">Omzet Kotor</th>
                <th width="15%" class="text-right">Bersih (75%)</th>
                <th width="15%" class="text-right">Bagian EO (25%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td class="bold">{{ $tx->invoice_code }}</td>
                    <td>{{ $tx->paid_at ? $tx->paid_at->format('d/m/Y H:i') : $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="bold">{{ $tx->store?->name ?: '-' }}</td>
                    <td style="text-transform: uppercase;">{{ $tx->payment_method }}</td>
                    <td class="text-right bold">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right bold" style="color: #1d9bf0;">
                        @if($tx->status === 'paid')
                            Rp {{ number_format($tx->revenueSplit?->owner_share ?: ($tx->total_amount * 0.75), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right bold">
                        @if($tx->status === 'paid')
                            Rp {{ number_format($tx->revenueSplit?->admin_gross_share ?: ($tx->total_amount * 0.25), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <span style="font-weight: bold; font-size: 8px; text-transform: uppercase;">
                            {{ $tx->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #536471;">Belum ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 25px; page-break-inside: avoid;">
        <tr>
            <td style="width: 50%; border: none; text-align: center; font-size: 9px; vertical-align: top;">
                <div>Dibuat & Divalidasi Oleh:</div>
                <div style="font-weight: bold; margin-top: 2px;">Admin Event Organizer</div>
                <div style="height: 45px;"></div>
                <div>( __________________________ )</div>
                <div style="font-size: 8px; color: #536471; margin-top: 2px;">Panitia Pelaksana EO</div>
            </td>
            <td style="width: 50%; border: none; text-align: center; font-size: 9px; vertical-align: top;">
                <div>Mengetahui & Menyetujui:</div>
                <div style="font-weight: bold; margin-top: 2px;">Penanggung Jawab / Ketua EO</div>
                <div style="height: 45px;"></div>
                <div>( __________________________ )</div>
                <div style="font-size: 8px; color: #536471; margin-top: 2px;">Event Organizer Lead</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis pada: {{ now()->format('d/m/Y H:i:s') }} | JADISATU Event System
    </div>
</body>
</html>
