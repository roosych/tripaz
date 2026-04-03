<?php

namespace App\Models;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'listing_type',
        'parent_id',
        'name',
        'slug',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'listing_type' => ListingType::class,
            'sort_order'   => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'listing_categories');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForType(Builder $query, string|ListingType $type): Builder
    {
        $value = $type instanceof ListingType ? $type->value : $type;

        return $query->where(function (Builder $q) use ($value) {
            $q->whereNull('listing_type')->orWhere('listing_type', $value);
        });
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name->az');
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
