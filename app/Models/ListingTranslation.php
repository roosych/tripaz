<?php

namespace App\Models;

use App\Enums\Locale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingTranslation extends Model
{
    protected $fillable = [
        'listing_id',
        'locale',
        'title',
        'description',
        'address',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'locale' => Locale::class,
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
