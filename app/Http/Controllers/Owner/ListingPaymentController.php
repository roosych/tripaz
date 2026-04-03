<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\ListingPublishService;
use App\Services\SystemSettingsService;
use App\Enums\ListingStatus;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingPaymentController extends Controller
{
    public function __construct(
        private ListingPublishService $publishService,
        private SystemSettingsService $settings,
    ) {}

    public function show(Listing $listing)
    {
        abort_if($listing->user_id !== Auth::id(), 403);
        abort_if($listing->status !== ListingStatus::AwaitingPayment, 404);

        $listing->load(['translations', 'payments' => fn ($q) => $q->latest()]);

        return view('owner.listings.payment', [
            'listing'       => $listing,
            'price'         => $this->settings->getFloat('price_per_extra_listing'),
            'currency'      => $this->settings->getString('listing_payment_currency'),
            'latestPayment' => $listing->payments->first(),
        ]);
    }

    public function store(Request $request, Listing $listing)
    {
        abort_if($listing->user_id !== Auth::id(), 403);
        abort_if($listing->status !== ListingStatus::AwaitingPayment, 404);

        $data = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'proof_file'     => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // Guard: reject early before file upload if a pending payment already exists
        if ($listing->payments()->where('status', 'pending')->exists()) {
            return back()->with('error', 'A pending payment request already exists for this listing.');
        }

        $path = $request->file('proof_file')->store('payment-proofs', 'public');

        try {
            $payment = $this->publishService->createPaymentRequest($listing, Auth::user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $payment->update([
            'payment_method'  => $data['payment_method'],
            'notes'           => $data['notes'] ?? null,
            'proof_file_path' => $path,
        ]);

        return redirect()->route('owner.listings.payment.show', $listing)
            ->with('success', 'Payment proof submitted. We will review it shortly.');
    }

    /**
     * Host withdraws an awaiting_payment listing back to draft,
     * cancelling any open pending payment rows.
     */
    public function withdraw(Listing $listing)
    {
        abort_if($listing->user_id !== Auth::id(), 403);
        abort_if($listing->status !== ListingStatus::AwaitingPayment, 422);

        try {
            $this->publishService->withdrawToDraft($listing, Auth::user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('owner.listings.index')
            ->with('success', 'Listing withdrawn to draft. You can edit and resubmit it for review.');
    }
}
