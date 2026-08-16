<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'owner_share',
        'admin_gross_share',
        'superadmin_share',
        'admin_net_share',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'owner_share' => 'decimal:2',
            'admin_gross_share' => 'decimal:2',
            'superadmin_share' => 'decimal:2',
            'admin_net_share' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
