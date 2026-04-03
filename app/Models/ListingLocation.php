<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingLocation extends Model
{
    protected $fillable = [
        'listing_id',
        'region_id',
        'latitude',
        'longitude',
        'postal_code',
        'place_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
