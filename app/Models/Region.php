<?php

namespace App\Models;

use App\Enums\RegionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Region extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => RegionType::class,
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(ListingLocation::class, 'region_id');
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
