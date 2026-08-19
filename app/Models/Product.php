<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Daftar kategori produk beserta ikonnya.
     * Tambah kategori baru cukup di sini, seluruh filter & form ikut menyesuaikan.
     */
    public const CATEGORIES = [
        'Makanan' => '🍱',
        'Minuman' => '🧋',
        'Snack' => '🍟',
        'Merchandise' => '🛍️',
    ];

    public const DEFAULT_CATEGORY = 'Makanan';

    protected $fillable = [
        'store_id',
        'title',
        'price',
        'is_negotiable',
        'min_price',
        'max_price',
        'category',
        'description',
        'photo',
        'stock_badge',
        'is_active',
    ];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Batas harga yang boleh dipakai kasir untuk produk tawar-menawar.
     * Produk harga pas hanya boleh dijual pada harga acuannya.
     *
     * @return array{0: float, 1: float}
     */
    public function priceRange(): array
    {
        if (!$this->is_negotiable) {
            return [(float) $this->price, (float) $this->price];
        }

        $min = $this->min_price !== null ? (float) $this->min_price : 0.0;
        $max = $this->max_price !== null ? (float) $this->max_price : (float) $this->price;

        return [$min, $max];
    }

    /**
     * Harga acuan yang dicoret di struk saat harga deal lebih rendah.
     */
    public function listPrice(): float
    {
        [, $max] = $this->priceRange();

        return $this->is_negotiable ? $max : (float) $this->price;
    }

    public function acceptsPrice(float $price): bool
    {
        [$min, $max] = $this->priceRange();

        return $price >= $min && $price <= $max;
    }

    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo) {
            return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80';
        }

        if (str_starts_with($this->photo, 'http') || str_starts_with($this->photo, 'data:')) {
            return $this->photo;
        }

        if (str_starts_with($this->photo, '/')) {
            return $this->photo;
        }

        return '/storage/' . $this->photo;
    }

    // Relations
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
