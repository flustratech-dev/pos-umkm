<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'owner_id',
        'name',
        'booth_number',
        'access_uuid',
        'category',
        'is_active',
        'use_dynamic_qris',
    ];

    protected $appends = ['unique_code'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'use_dynamic_qris' => 'boolean',
        ];
    }

    /**
     * Kode unik yang ditambahkan ke nominal QRIS supaya pembayaran tiap stand
     * bisa dibedakan. Diambil dari angka pada kode tenda (mis. "019" -> 19,
     * "Stand 01" -> 1) agar nominalnya bisa dicocokkan langsung dengan tenda.
     * Kalau kode tenda tidak memuat angka, dipakai id stand sebagai cadangan.
     */
    public function getUniqueCodeAttribute(): int
    {
        $digits = preg_replace('/\D/', '', (string) $this->booth_number);

        if ($digits === '' || (int) $digits === 0) {
            return (int) $this->id;
        }

        return (int) $digits;
    }

    // Relations
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'store_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function helpdeskTickets(): HasMany
    {
        return $this->hasMany(HelpdeskTicket::class);
    }
}
