<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class AmenityGroup extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'sort_order', 'listing_types'];

    protected function casts(): array
    {
        return [
            'listing_types' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function amenities(): HasMany
    {
        return $this->hasMany(Amenity::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function name(string $locale = 'az'): string
    {
        return $this->getTranslation('name', $locale, false)
            ?: $this->getTranslation('name', 'az', false)
            ?: '';
    }
}
