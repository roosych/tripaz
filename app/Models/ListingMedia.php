<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingMedia extends Model
{
    protected $fillable = [
        'listing_id',
        'path',
        'disk',
        'mime_type',
        'size',
        'collection',
        'sort_order',
        'conversions',
        'custom_properties',
    ];

    protected function casts(): array
    {
        return [
            'conversions'       => 'array',
            'custom_properties' => 'array',
            'size'              => 'integer',
            'sort_order'        => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function url(string $conversion = ''): string
    {
        if ($conversion && isset($this->conversions[$conversion])) {
            return asset('storage/' . $this->conversions[$conversion]);
        }

        // Use external URL from custom_properties when the path is a placeholder (seeded/fake data)
        if (!empty($this->custom_properties['url'])) {
            return $this->custom_properties['url'];
        }

        return asset('storage/' . $this->path);
    }
}
