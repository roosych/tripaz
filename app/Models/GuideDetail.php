<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideDetail extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'listing_id';

    protected $fillable = [
        'listing_id',
        'languages',
        'experience_years',
        'certifications',
        'bio_extra',
    ];

    protected function casts(): array
    {
        return [
            'languages'       => 'array',
            'experience_years' => 'integer',
            'certifications'  => 'array',
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
