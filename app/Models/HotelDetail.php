<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelDetail extends Model
{
    /**
     * listing_id is both PK and FK — no auto-increment.
     */
    public $incrementing = false;

    protected $primaryKey = 'listing_id';

    protected $fillable = [
        'listing_id',
        'stars',
        'total_rooms',
        'check_in_time',
        'check_out_time',
        'policies',
    ];

    protected function casts(): array
    {
        return [
            'stars'          => 'integer',
            'total_rooms'    => 'integer',
            'policies'       => 'array',
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
