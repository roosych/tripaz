<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * List all reviews written by the authenticated user.
     */
    public function index()
    {
        $reviews = Auth::user()
            ->reviews()
            ->with(['listing.translations'])
            ->latest()
            ->paginate(20);

        return view('dashboard.reviews.index', compact('reviews'));
    }

    /**
     * Store a new review for a published listing.
     *
     * A user may only leave one review per listing.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Review::class);

        $validated = $request->validate([
            'listing_id' => 'required|integer|exists:listings,id',
            'rating'     => 'required|integer|between:1,5',
            'comment'    => 'nullable|string|max:2000',
        ]);

        $listing = Listing::published()->findOrFail($validated['listing_id']);

        // Prevent duplicate reviews from the same user on the same listing
        $alreadyReviewed = Review::where('listing_id', $listing->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyReviewed) {
            return back()->withErrors(['listing_id' => 'You have already reviewed this listing.']);
        }

        Review::create([
            'listing_id'  => $listing->id,
            'user_id'     => Auth::id(),
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'] ?? null,
            'is_approved' => false, // reviews require admin approval
        ]);

        return back()->with('success', 'Your review has been submitted and is awaiting approval.');
    }
}
