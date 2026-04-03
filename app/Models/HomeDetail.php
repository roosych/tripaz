<?php

namespace App\Models;

use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeDetail extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'listing_id';

    protected $fillable = [
        'listing_id',
        'property_type',
        'bedrooms',
        'bathrooms',
        'max_guests',
        'total_area',
        'floor',
        'house_rules',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'bedrooms'      => 'integer',
            'bathrooms'     => 'integer',
            'max_guests'    => 'integer',
            'total_area'    => 'decimal:2',
            'floor'         => 'integer',
            'house_rules'   => 'array',
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
