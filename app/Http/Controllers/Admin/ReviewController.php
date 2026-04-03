<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * List all reviews with optional pending-only filter for moderation.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Review::class);

        $pendingOnly = $request->boolean('pending');

        $reviews = Review::query()
            ->with(['listing.translations', 'user'])
            ->when($pendingOnly, fn($q) => $q->where('is_approved', false))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'pendingOnly'));
    }

    /**
     * Approve a review, making it visible on the public listing.
     */
    public function approve(Review $review)
    {
        $this->authorize('approve', $review);

        $review->update(['is_approved' => true]);

        return back()->with('success', 'Review approved and is now publicly visible.');
    }

    /**
     * Reject (soft-delete via flag) a review, hiding it from public view.
     */
    public function reject(Review $review)
    {
        $this->authorize('reject', $review);

        $review->update(['is_approved' => false]);

        return back()->with('success', 'Review has been rejected and hidden from public view.');
    }
}
