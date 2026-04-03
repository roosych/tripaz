<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $listingCounts = Listing::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'listings_total'     => Listing::count(),
            'listings_pending'   => (int) $listingCounts->get('pending_review', 0),
            'listings_published' => (int) $listingCounts->get('published', 0),
            'listings_draft'     => (int) $listingCounts->get('draft', 0),
            'listings_rejected'  => (int) $listingCounts->get('rejected', 0),
            'listings_suspended' => (int) $listingCounts->get('suspended', 0),
            'users_total'        => User::count(),
            'hosts_total'        => User::role('host')->count(),
            'reviews_total'      => Review::count(),
            'reviews_pending'    => Review::where('is_approved', false)->count(),
        ];

        $recentListings = Listing::with(['translations', 'user'])
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentListings'));
    }
}
