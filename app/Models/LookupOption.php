<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['type', 'value', 'label', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    // -------------------------------------------------------------------------
    // Scopes & helpers
    // -------------------------------------------------------------------------

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Return options for a given type as [['value'=>..., 'label'=>...], ...]
     * Ready to be embedded in a field schema (e.g. for select/pills).
     */
    public static function optionsFor(string $type): array
    {
        return static::where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['value', 'label'])
            ->map(fn ($o) => ['value' => $o->value, 'label' => $o->label])
            ->toArray();
    }

    /**
     * Return just the values for a given type (used in validation Rule::in).
     */
    public static function valuesFor(string $type): array
    {
        return static::where('type', $type)->orderBy('sort_order')->pluck('value')->toArray();
    }

    /**
     * All distinct types currently stored.
     */
    public static function distinctTypes(): array
    {
        return static::distinct()->orderBy('type')->pluck('type')->toArray();
    }
}
