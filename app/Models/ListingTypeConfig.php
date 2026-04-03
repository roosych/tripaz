<?php

namespace App\Models;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Model;

class ListingTypeConfig extends Model
{
    protected $fillable = [
        'type',
        'booking_enabled',
    ];

    protected function casts(): array
    {
        return [
            'type'            => ListingType::class,
            'booking_enabled' => 'boolean',
        ];
    }

    /**
     * Finds or creates the config record for the given ListingType enum value.
     * The `type` column stores the string value of the enum (e.g. 'hotel').
     */
    public static function forType(ListingType $type): self
    {
        return static::firstOrCreate(
            ['type' => $type->value],
            ['booking_enabled' => false],
        );
    }
}
