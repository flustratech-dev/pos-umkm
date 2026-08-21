<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Rentang tanggal laporan.
 *
 * Batasnya selalu satu hari penuh menurut waktu setempat (00:00:00 sampai
 * 23:59:59), jadi transaksi paling pagi dan paling malam pada tanggal yang
 * dipilih ikut terhitung.
 */
class ReportPeriod
{
    public function __construct(
        public readonly ?Carbon $from = null,
        public readonly ?Carbon $to = null,
    ) {}

    /**
     * Baca rentang dari query string (?from=YYYY-MM-DD&to=YYYY-MM-DD).
     * Tanggal yang tidak valid diabaikan, sehingga laporan jatuh ke "semua periode".
     */
    public static function fromRequest(Request $request): self
    {
        $from = self::parse($request->query('from'));
        $to = self::parse($request->query('to'));

        // Kalau hanya satu sisi yang diisi, anggap laporan satu hari itu saja.
        if ($from && !$to) {
            $to = $from->copy();
        }
        if ($to && !$from) {
            $from = $to->copy();
        }

        // Tanggal terbalik dirapikan, bukan ditolak.
        if ($from && $to && $from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return new self(
            $from?->startOfDay(),
            $to?->endOfDay(),
        );
    }

    protected static function parse(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', substr(trim($value), 0, 10));
        } catch (\Throwable) {
            return null;
        }
    }

    public function isFiltered(): bool
    {
        return $this->from !== null && $this->to !== null;
    }

    public function isSingleDay(): bool
    {
        return $this->isFiltered() && $this->from->isSameDay($this->to);
    }

    /**
     * Saring transaksi berdasarkan waktu transaksi dibuat.
     */
    public function apply(Builder $query, string $column = 'created_at'): Builder
    {
        if (!$this->isFiltered()) {
            return $query;
        }

        return $query->whereBetween($column, [$this->from, $this->to]);
    }

    /**
     * Label untuk judul laporan dan nama berkas.
     */
    public function label(): string
    {
        if (!$this->isFiltered()) {
            return 'Semua Periode';
        }

        if ($this->isSingleDay()) {
            return $this->from->translatedFormat('d F Y');
        }

        return $this->from->translatedFormat('d F Y') . ' - ' . $this->to->translatedFormat('d F Y');
    }

    public function fileLabel(): string
    {
        if (!$this->isFiltered()) {
            return 'Semua_Periode';
        }

        if ($this->isSingleDay()) {
            return $this->from->format('Ymd');
        }

        return $this->from->format('Ymd') . '-' . $this->to->format('Ymd');
    }

    /**
     * Nilai untuk mengisi kembali input tanggal di halaman.
     */
    public function toArray(): array
    {
        return [
            'from' => $this->from?->format('Y-m-d'),
            'to' => $this->to?->format('Y-m-d'),
            'label' => $this->label(),
        ];
    }
}
