<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'title',
        'price',
        'original_price',
        'qty',
        'subtotal',
    ];

    protected $appends = ['is_negotiated'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'qty' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Baris ini terjual di bawah harga acuan karena hasil tawar-menawar.
     */
    public function getIsNegotiatedAttribute(): bool
    {
        return $this->original_price !== null
            && (float) $this->original_price > (float) $this->price;
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
