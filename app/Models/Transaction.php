<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code',
        'store_id',
        'cashier_id',
        'total_amount',
        'payment_method',
        'amount_paid',
        'change_due',
        'status',
        'paid_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'refund_ack_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_due' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refund_ack_confirmed' => 'boolean',
        ];
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }

    public function scopeForActiveEvent($query)
    {
        $activeEvent = Event::getActive();
        if (!$activeEvent) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('store', function ($q) use ($activeEvent) {
            $q->where('event_id', $activeEvent->id);
        });
    }

    // Relations
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function paymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class);
    }

    public function revenueSplit(): HasOne
    {
        return $this->hasOne(RevenueSplit::class);
    }
}
