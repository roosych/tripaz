<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        Favorite::firstOrCreate([
            'user_id'    => $request->user()->id,
            'listing_id' => $listing->id,
        ]);

        return back()->with('success', 'Added to favorites.');
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        Favorite::where('user_id', $request->user()->id)
                ->where('listing_id', $listing->id)
                ->delete();

        return back()->with('success', 'Removed from favorites.');
    }

    public function index(Request $request): View
    {
        $favorites = $request->user()
            ->favoriteListings()
            ->with(['translations', 'location.region', 'media', 'amenities'])
            ->paginate(20);

        return view('dashboard.favorites.index', compact('favorites'));
    }
}
