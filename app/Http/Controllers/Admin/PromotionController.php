<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * List published listings ordered by boost_weight for promotion management.
     */
    public function index()
    {
        $listings = Listing::query()
            ->with(['translations', 'user'])
            ->published()
            ->orderByDesc('boost_weight')
            ->orderByDesc('avg_rating')
            ->paginate(30);

        return view('admin.promotions.index', compact('listings'));
    }

    /**
     * Update the boost_weight for a listing to control its prominence in search results.
     *
     * A higher boost_weight surfaces the listing higher in the default sort.
     * Value 1 is the baseline (no boost); higher values increase prominence.
     */
    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'boost_weight' => 'required|integer|min:1|max:1000',
        ]);

        $listing->update(['boost_weight' => $validated['boost_weight']]);

        // Re-index in Meilisearch so the updated boost_weight is reflected immediately
        $listing->searchable();

        return back()->with('success', "Boost weight for [{$listing->slug}] updated to [{$validated['boost_weight']}].");
    }
}
