<?php

namespace App\Models;

use App\Enums\ListingPaymentStatus;
use App\Enums\ListingPaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ListingPayment extends Model
{
    protected $fillable = [
        'ulid',
        'listing_id',
        'user_id',
        'amount',
        'currency',
        'payment_type',
        'status',
        'payment_method',
        'proof_file_path',
        'notes',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'cancelled_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'status'       => ListingPaymentStatus::class,
            'payment_type' => ListingPaymentType::class,
            'amount'       => 'decimal:2',
            'reviewed_at'  => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ListingPayment $payment): void {
            if (empty($payment->ulid)) {
                $payment->ulid = (string) Str::ulid();
            }
        });
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
