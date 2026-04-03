<?php

namespace App\Models;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Amenity extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'listing_type',
        'name',
        'icon',
        'amenity_group_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'listing_type' => ListingType::class,
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function amenityGroup(): BelongsTo
    {
        return $this->belongsTo(AmenityGroup::class);
    }

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'listing_amenities');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Virtual "group" attribute — returns the group name string (az locale).
     * Used for backward-compatible Collection::groupBy('group') calls.
     */
    public function getGroupAttribute(): ?string
    {
        return $this->amenityGroup?->getTranslation('name', 'az', false) ?: null;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForType(Builder $query, string|ListingType $type): Builder
    {
        $value = $type instanceof ListingType ? $type->value : $type;

        return $query
            ->where(function (Builder $q) use ($value) {
                $q->whereNull('listing_type')->orWhere('listing_type', $value);
            })
            ->where(function (Builder $q) use ($value) {
                // Include amenity if: it has no group, OR group is universal (listing_types is null),
                // OR group's listing_types contains this type.
                $q->whereNull('amenity_group_id')
                    ->orWhereHas('amenityGroup', function (Builder $gq) use ($value) {
                        $gq->whereNull('listing_types')
                            ->orWhereJsonContains('listing_types', $value);
                    });
            });
    }

    public function scopeByGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('amenity_group_id', $groupId);
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
