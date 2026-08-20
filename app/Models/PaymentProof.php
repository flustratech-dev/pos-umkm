<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'proof_path',
        'uploaded_at',
    ];

    protected $appends = ['proof_url'];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function getProofUrlAttribute(): string
    {
        if ($this->proof_path && str_starts_with($this->proof_path, 'http')) {
            return $this->proof_path;
        }

        if ($this->proof_path && Storage::disk('public')->exists($this->proof_path)) {
            return asset('storage/' . $this->proof_path);
        }

        return asset($this->proof_path);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
