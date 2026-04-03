<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class BookingController extends Controller
{
    /**
     * List bookings for the authenticated owner's listings.
     *
     * TODO: implement once the Booking model and bookings table are created.
     */
    public function index()
    {
        // Booking model does not exist yet — return empty collection as placeholder.
        $bookings = new Collection();

        return view('owner.bookings.index', compact('bookings'));
    }
}
