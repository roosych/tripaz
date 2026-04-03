<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * List all approved reviews for listings owned by the authenticated user.
     */
    public function index()
    {
        $listingIds = Auth::user()->listings()->pluck('id');

        $reviews = Review::whereIn('listing_id', $listingIds)
            ->with(['listing.translations', 'user'])
            ->where('is_approved', true)
            ->latest()
            ->paginate(20);

        return view('owner.reviews.index', compact('reviews'));
    }
}
