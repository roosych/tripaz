<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── Listing counts by status ──────────────────────────────────────
        $listingCountsByStatus = $user->listings()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalListings     = $listingCountsByStatus->sum();
        $publishedCount    = (int) ($listingCountsByStatus[ListingStatus::Published->value]    ?? 0);
        $pendingCount      = (int) ($listingCountsByStatus[ListingStatus::PendingReview->value] ?? 0);
        $draftCount        = (int) ($listingCountsByStatus[ListingStatus::Draft->value]        ?? 0);
        $rejectedCount     = (int) ($listingCountsByStatus[ListingStatus::Rejected->value]     ?? 0);

        // ── Review stats ──────────────────────────────────────────────────
        $listingIds = $user->listings()->pluck('id');

        $reviewStats = Review::whereIn('listing_id', $listingIds)
            ->selectRaw('count(*) as total, avg(rating) as avg_rating')
            ->first();

        $totalReviews  = (int) ($reviewStats->total ?? 0);
        $averageRating = $reviewStats->avg_rating ? round((float) $reviewStats->avg_rating, 1) : null;

        // ── Recent listings (last 5) ───────────────────────────────────────
        $recentListings = $user->listings()
            ->with('translations')
            ->latest()
            ->limit(5)
            ->get();

        // ── Recent reviews (last 5) ────────────────────────────────────────
        $recentReviews = Review::whereIn('listing_id', $listingIds)
            ->with(['listing.translations', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        return view('owner.dashboard', compact(
            'totalListings',
            'publishedCount',
            'pendingCount',
            'draftCount',
            'rejectedCount',
            'totalReviews',
            'averageRating',
            'recentListings',
            'recentReviews',
        ));
    }
}
