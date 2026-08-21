<?php

namespace App\Services;

use App\Models\Transaction;
use App\Support\ReportPeriod;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menyusun laporan transaksi jadi berkas Excel atau CSV.
 * Versi PDF tetap memakai template Blade yang sudah ada.
 */
class ReportExportService
{
    public const FORMATS = ['pdf', 'xlsx', 'csv'];

    /**
     * Judul kolom, dipakai sama persis oleh Excel maupun CSV.
     */
    protected function headings(bool $withStore): array
    {
        $headings = ['Waktu', 'Kode Invoice'];

        if ($withStore) {
            $headings[] = 'Warung';
            $headings[] = 'Kode Tenda';
        }

        return array_merge($headings, [
            'Kasir',
            'Metode',
            'Status',
            'Item',
            'Total (Rp)',
            'Porsi Warung (Rp)',
            'Porsi EO (Rp)',
            'Fee Platform (Rp)',
            'Bukti QRIS',
            'Catatan',
        ]);
    }

    /**
     * Satu transaksi menjadi satu baris.
     */
    protected function row(Transaction $transaction, bool $withStore): array
    {
        $waktu = $transaction->paid_at ?: $transaction->created_at;

        $items = $transaction->items
            ->map(fn ($item) => "{$item->qty}x {$item->title}")
            ->implode(', ');

        $row = [
            $waktu?->format('d/m/Y H:i'),
            $transaction->invoice_code,
        ];

        if ($withStore) {
            $row[] = $transaction->store?->name ?? '-';
            $row[] = $transaction->store?->booth_number ?? '-';
        }

        $split = $transaction->revenueSplit;

        return array_merge($row, [
            $transaction->cashier?->name ?? '-',
            strtoupper($transaction->payment_method),
            $this->statusLabel($transaction->status),
            $items,
            // Dibulatkan ke rupiah penuh: nilai berdesimal seperti 474774.4999
            // dibaca Excel berlokal Indonesia sebagai pemisah ribuan.
            (int) round((float) $transaction->total_amount),
            $split ? (int) round((float) $split->owner_share) : 0,
            $split ? (int) round((float) $split->admin_gross_share) : 0,
            $split ? (int) round((float) $split->superadmin_share) : 0,
            $this->proofLabel($transaction),
            $transaction->proof_failure_reason
                ?? $transaction->cancellation_reason
                ?? $transaction->rejection_reason
                ?? '',
        ]);
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'pending' => 'Menunggu Konfirmasi Tunai',
            'pending_verification' => 'Menunggu Verifikasi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    protected function proofLabel(Transaction $transaction): string
    {
        if ($transaction->payment_method !== 'qris') {
            return '-';
        }

        if ($transaction->paymentProof) {
            return 'Ada';
        }

        return $transaction->proof_failure_reason ? 'Tanpa Bukti' : '-';
    }

    /**
     * Ringkasan yang diletakkan di atas tabel.
     */
    protected function summaryRows(Collection $transactions, ReportPeriod $period, string $title): array
    {
        $paid = $transactions->where('status', 'paid');

        return [
            [$title],
            ['Periode', $period->label()],
            ['Diunduh', now()->format('d/m/Y H:i')],
            ['Jumlah Transaksi', $transactions->count()],
            ['Transaksi Lunas', $paid->count()],
            ['Total Penjualan Lunas (Rp)', (int) round((float) $paid->sum('total_amount'))],
            [],
        ];
    }

    public function csv(Collection $transactions, ReportPeriod $period, string $title, bool $withStore = true): StreamedResponse
    {
        $fileName = $this->fileName($title, $period, 'csv');

        return response()->streamDownload(function () use ($transactions, $period, $title, $withStore) {
            $handle = fopen('php://output', 'w');

            // BOM supaya Excel membaca huruf beraksen dan rupiah dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($this->summaryRows($transactions, $period, $title) as $row) {
                fputcsv($handle, $row, ';');
            }

            fputcsv($handle, $this->headings($withStore), ';');

            foreach ($transactions as $transaction) {
                fputcsv($handle, $this->row($transaction, $withStore), ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function xlsx(Collection $transactions, ReportPeriod $period, string $title, bool $withStore = true): StreamedResponse
    {
        $fileName = $this->fileName($title, $period, 'xlsx');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');

        $baris = 1;
        foreach ($this->summaryRows($transactions, $period, $title) as $row) {
            if ($row !== []) {
                $sheet->fromArray($row, null, 'A' . $baris);
            }
            $baris++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headings = $this->headings($withStore);
        $barisJudul = $baris;
        $sheet->fromArray($headings, null, 'A' . $barisJudul);

        $kolomTerakhir = chr(ord('A') + count($headings) - 1);
        $sheet->getStyle("A{$barisJudul}:{$kolomTerakhir}{$barisJudul}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D9BF0']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $baris++;
        foreach ($transactions as $transaction) {
            $sheet->fromArray($this->row($transaction, $withStore), null, 'A' . $baris);
            $baris++;
        }

        // Kolom rupiah diformat ribuan supaya enak dibaca.
        $kolomRupiah = $withStore ? ['I', 'J', 'K', 'L'] : ['G', 'H', 'I', 'J'];
        foreach ($kolomRupiah as $kolom) {
            $sheet->getStyle("{$kolom}{$barisJudul}:{$kolom}{$baris}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        foreach (range('A', $kolomTerakhir) as $kolom) {
            $sheet->getColumnDimension($kolom)->setAutoSize(true);
        }

        $sheet->freezePane('A' . ($barisJudul + 1));

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function fileName(string $title, ReportPeriod $period, string $extension): string
    {
        $judul = preg_replace('/[^A-Za-z0-9]+/', '_', $title);
        $judul = trim($judul, '_');

        return "{$judul}_{$period->fileLabel()}.{$extension}";
    }
}
