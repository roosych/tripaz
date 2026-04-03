<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourDetail extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'listing_id';

    protected $fillable = [
        'listing_id',
        'duration_hours',
        'max_participants',
        'meeting_point',
        'includes',
        'excludes',
        'itinerary',
    ];

    protected function casts(): array
    {
        return [
            'duration_hours'   => 'decimal:1',
            'max_participants' => 'integer',
            'includes'         => 'array',
            'excludes'         => 'array',
            'itinerary'        => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
