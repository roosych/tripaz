<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingPayment;
use App\Services\ListingPublishService;
use App\Enums\ListingPaymentStatus;
use Illuminate\Http\Request;

class ListingPaymentController extends Controller
{
    public function __construct(
        private ListingPublishService $publishService,
    ) {}

    public function index(Request $request)
    {
        $query = ListingPayment::with(['listing.translations', 'user', 'reviewer'])
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->whereHas('listing', fn ($q) => $q->where('type', $type));
        }

        if ($host = $request->input('host')) {
            $query->whereHas('user', function ($q) use ($host) {
                $q->where('name', 'like', '%' . $host . '%')
                  ->orWhere('email', 'like', '%' . $host . '%');
            });
        }

        $payments     = $query->paginate(20)->withQueryString();
        $pendingCount = ListingPayment::where('status', ListingPaymentStatus::Pending)->count();

        return view('admin.listing-payments.index', compact('payments', 'pendingCount'));
    }

    public function show(ListingPayment $payment)
    {
        $payment->load(['listing.translations', 'listing.user', 'user', 'reviewer']);

        return view('admin.listing-payments.show', compact('payment'));
    }

    public function approve(ListingPayment $payment)
    {
        try {
            $this->publishService->approvePayment($payment, auth()->user());

            return redirect()->route('admin.listing-payments.index')
                ->with('success', 'Payment approved. Listing is now published.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, ListingPayment $payment)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->publishService->rejectPayment($payment, auth()->user(), $data['reason']);

        return redirect()->route('admin.listing-payments.index')
            ->with('success', 'Payment rejected. Host has been notified.');
    }
}
