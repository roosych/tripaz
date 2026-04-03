<?php

namespace App\Models;

use App\Enums\PriceRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantDetail extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'listing_id';

    protected $fillable = [
        'listing_id',
        'cuisine_types',
        'price_range',
        'has_outdoor',
        'has_delivery',
        'has_takeaway',
        'opening_hours',
        'menu_url',
    ];

    protected function casts(): array
    {
        return [
            'cuisine_types'    => 'array',
            'price_range'      => PriceRange::class,
            'has_outdoor'      => 'boolean',
            'has_delivery'     => 'boolean',
            'has_takeaway'     => 'boolean',
            'opening_hours'    => 'array',
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
