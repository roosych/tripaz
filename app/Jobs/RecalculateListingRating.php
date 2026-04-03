<?php

namespace App\Jobs;

use App\Models\Listing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecalculateListingRating implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    public function __construct(
        public readonly int $listingId,
    ) {}

    /**
     * Recalculate avg_rating and review_count from the reviews table.
     *
     * NOTE: The reviews table is handled by a future Booking/Review module.
     * This job reads from `reviews` (listing_id, rating) when that module
     * is available. The listing is updated atomically.
     */
    public function handle(): void
    {
        $listing = Listing::withTrashed()->findOrFail($this->listingId);

        $stats = DB::table('reviews')
            ->where('listing_id', $this->listingId)
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        $listing->update([
            'avg_rating'   => $stats->average ? round((float) $stats->average, 2) : null,
            'review_count' => (int) $stats->total,
        ]);

        // Re-index in Meilisearch with fresh rating data
        if ($listing->shouldBeSearchable()) {
            $listing->searchable();
        }
    }
}
