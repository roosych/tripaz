<?php

namespace App\Services;

use App\Enums\ListingPaymentStatus;
use App\Enums\ListingPaymentType;
use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\ListingPayment;
use App\Models\User;
use App\Notifications\PaymentApproved;
use App\Notifications\PaymentRejected;
use DomainException;
use Illuminate\Support\Facades\DB;

class ListingPublishService
{
    public function __construct(
        private readonly SystemSettingsService $settings,
    ) {}

    /**
     * Count how many of a host's listings count toward the free limit.
     * Only active (Published) listings occupy a slot.
     */
    public function countPublishedListings(User $host): int
    {
        return Listing::withoutTrashed()
            ->where('user_id', $host->id)
            ->where('status', ListingStatus::Published->value)
            ->count();
    }

    /**
     * Returns true if the host still has a free slot available.
     */
    public function canPublishFree(User $host): bool
    {
        $limit = $this->settings->getInt('free_published_listings_limit');
        return $this->countPublishedListings($host) < $limit;
    }

    /**
     * Resolves the correct post-approval status for a listing.
     * Called by ModerationService::approve() via publishListing().
     *
     * If host has a free slot → Published.
     * If host is at or above limit → AwaitingPayment.
     */
    public function resolvePostApprovalStatus(User $host): ListingStatus
    {
        return $this->canPublishFree($host)
            ? ListingStatus::Published
            : ListingStatus::AwaitingPayment;
    }

    /**
     * The single entry point for publishing a listing after moderation approval.
     * ModerationService::approve() delegates here instead of setting status directly.
     *
     * Wrapped in a transaction with User::lockForUpdate() to prevent TOCTOU races
     * where two concurrent admin approvals could bypass the free-listing limit for
     * the same host.
     */
    public function publishListing(Listing $listing, User $actor): Listing
    {
        return DB::transaction(function () use ($listing) {
            // Lock the host row so concurrent approvals for the same host serialize here
            $host   = User::lockForUpdate()->findOrFail($listing->user_id);
            $status = $this->resolvePostApprovalStatus($host);

            $listing->status           = $status;
            $listing->payment_required = ($status === ListingStatus::AwaitingPayment);
            $listing->save();

            if ($status === ListingStatus::Published) {
                $listing->searchable();
            } else {
                $listing->unsearchable();
            }

            return $listing;
        });
    }

    /**
     * Host creates a payment request for an awaiting_payment listing.
     * Price is locked at the current setting value — never recalculated after creation.
     *
     * Wrapped in a transaction with a SELECT FOR UPDATE guard to prevent duplicate
     * pending payments for the same listing (TOCTOU duplicate guard).
     */
    public function createPaymentRequest(Listing $listing, User $host): ListingPayment
    {
        if ($listing->status !== ListingStatus::AwaitingPayment) {
            throw new DomainException('Payment requests can only be created for listings awaiting payment.');
        }

        if ($listing->user_id !== $host->id) {
            throw new DomainException('Host does not own this listing.');
        }

        return DB::transaction(function () use ($listing, $host) {
            // Lock any existing pending row to prevent concurrent duplicates
            $existing = ListingPayment::where('listing_id', $listing->id)
                ->where('status', ListingPaymentStatus::Pending)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new DomainException('A pending payment request already exists for this listing.');
            }

            return ListingPayment::create([
                'listing_id'   => $listing->id,
                'user_id'      => $host->id,
                'amount'       => $this->settings->getFloat('price_per_extra_listing'),
                'currency'     => $this->settings->getString('listing_payment_currency'),
                'payment_type' => ListingPaymentType::Publish,
                'status'       => ListingPaymentStatus::Pending,
            ]);
        });
    }

    /**
     * Admin approves a payment → transitions the listing to Published.
     *
     * Guards:
     *  - SELECT FOR UPDATE on payment row (prevents concurrent approvals)
     *  - SELECT FOR UPDATE on listing row (prevents concurrent status mutations)
     *  - listing->status must be awaiting_payment before publishing
     *  - Idempotency key is independent of the admin actor (any admin can retry safely)
     */
    public function approvePayment(ListingPayment $payment, User $admin): Listing
    {
        return DB::transaction(function () use ($payment, $admin) {
            // Lock the payment row to prevent concurrent approvals
            $payment = ListingPayment::lockForUpdate()->findOrFail($payment->id);

            if ($payment->status !== ListingPaymentStatus::Pending) {
                throw new DomainException('This payment has already been processed.');
            }

            // Idempotency key is payment-scoped only — independent of which admin acts
            $idempotencyKey = hash('sha256', 'approve:' . $payment->id);
            if (ListingPayment::where('idempotency_key', $idempotencyKey)->exists()) {
                throw new DomainException('This payment approval has already been processed (duplicate request).');
            }

            // Lock the listing row to serialize concurrent approval/withdraw races
            $listing = Listing::lockForUpdate()->findOrFail($payment->listing_id);

            // Guard: listing must still be awaiting payment when we approve
            if ($listing->status !== ListingStatus::AwaitingPayment) {
                throw new DomainException(
                    "Cannot approve payment — listing is no longer awaiting payment (current status: {$listing->status->value})."
                );
            }

            $payment->update([
                'status'          => ListingPaymentStatus::Approved,
                'reviewed_by'     => $admin->id,
                'reviewed_at'     => now(),
                'idempotency_key' => $idempotencyKey,
            ]);

            $listing->status           = ListingStatus::Published;
            $listing->payment_required = false;
            $listing->save();

            $listing->searchable();

            // Notify the host (queued)
            $listing->user->notify(new PaymentApproved($listing));

            return $listing;
        });
    }

    /**
     * Admin rejects a payment — listing stays in awaiting_payment.
     * The host may submit a new payment row after rejection.
     */
    public function rejectPayment(ListingPayment $payment, User $admin, string $reason = ''): ListingPayment
    {
        if ($payment->status !== ListingPaymentStatus::Pending) {
            throw new DomainException('Only pending payments can be rejected.');
        }

        $payment->update([
            'status'      => ListingPaymentStatus::Rejected,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $reason,
        ]);

        $payment->refresh();

        // Notify the host (queued)
        $payment->listing->user->notify(new PaymentRejected($payment->listing, $reason));

        return $payment;
    }

    /**
     * Host withdraws an awaiting_payment listing back to draft.
     * Cancels any pending payment rows and clears the payment_required flag.
     */
    public function withdrawToDraft(Listing $listing, User $host): Listing
    {
        if ($listing->status !== ListingStatus::AwaitingPayment) {
            throw new DomainException('Only listings awaiting payment can be withdrawn to draft.');
        }

        if ($listing->user_id !== $host->id) {
            throw new DomainException('Host does not own this listing.');
        }

        return DB::transaction(function () use ($listing) {
            // Cancel any pending payment requests — preserve rows for audit trail
            $listing->payments()
                ->where('status', ListingPaymentStatus::Pending)
                ->update([
                    'status'       => ListingPaymentStatus::Cancelled,
                    'cancelled_at' => now(),
                ]);

            $listing->status           = ListingStatus::Draft;
            $listing->payment_required = false;
            $listing->save();

            $listing->unsearchable();

            return $listing;
        });
    }

    /**
     * Admin or owner unpublishes a listing (published → draft).
     */
    public function unpublish(Listing $listing, User $actor): Listing
    {
        $listing->status           = ListingStatus::Draft;
        $listing->payment_required = false;
        $listing->save();

        $listing->unsearchable();

        return $listing;
    }
}
