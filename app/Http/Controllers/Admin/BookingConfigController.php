<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Models\ListingTypeConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingConfigController extends Controller
{
    /**
     * Display the booking enable/disable configuration for every listing type.
     * Ensures a config record exists for each of the 6 ListingType enum cases.
     */
    public function index(): View
    {
        // Guarantee all 6 type config rows exist before rendering
        foreach (ListingType::cases() as $type) {
            ListingTypeConfig::forType($type);
        }

        $configs = ListingTypeConfig::orderBy('type')->get();

        return view('admin.booking-config.index', compact('configs'));
    }

    /**
     * Update the booking_enabled flag for the given listing type.
     *
     * @param  string  $type  The string value of the ListingType enum (e.g. 'hotel')
     */
    public function update(Request $request, string $type): RedirectResponse
    {
        $listingType = ListingType::tryFrom($type);

        if ($listingType === null) {
            abort(404, "Unknown listing type: {$type}");
        }

        $validated = $request->validate([
            'booking_enabled' => ['required', 'boolean'],
        ]);

        $config = ListingTypeConfig::forType($listingType);
        $config->update(['booking_enabled' => $validated['booking_enabled']]);

        $label  = $listingType->label();
        $status = $validated['booking_enabled'] ? 'enabled' : 'disabled';

        return redirect()
            ->route('admin.booking-config.index')
            ->with('success', "Booking for {$label} has been {$status}.");
    }
}
