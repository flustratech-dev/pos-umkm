<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'start_date',
        'end_date',
        'location',
        'is_active',
        'is_testing_mode',
        'qris_image',
        'qris_payload',
        'created_by',
    ];

    protected $appends = ['qris_image_url'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_testing_mode' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    // Relations
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function getQrisImageUrlAttribute(): ?string
    {
        if ($this->qris_image) {
            if (Str::startsWith($this->qris_image, ['http://', 'https://'])) {
                return $this->qris_image;
            }
            return asset('storage/' . $this->qris_image);
        }
        return null;
    }
}
