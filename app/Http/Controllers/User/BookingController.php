<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class BookingController extends Controller
{
    /**
     * List all bookings made by the authenticated user.
     *
     * TODO: implement once the Booking model and bookings table are created.
     */
    public function index()
    {
        // Booking model does not exist yet — return empty collection as placeholder.
        $bookings = new Collection();

        return view('dashboard.bookings.index', compact('bookings'));
    }
}
