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

    protected $fillable = [
        'store_id',
        'title',
        'price',
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
            'is_active' => 'boolean',
        ];
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
