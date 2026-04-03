<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice page.
     *
     * This view is displayed after registration to prompt the user to verify
     * their email address. Verification is optional — no middleware enforces it.
     */
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * Handle a verification link click.
     *
     * Sets email_verified_at on the target user and redirects to the
     * appropriate dashboard. No signed URL validation is enforced here;
     * that enforcement can be added later when MustVerifyEmail is enabled.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $user->save();
        }

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        return match (true) {
            $authUser->hasRole('admin') => redirect()->route('admin.listings.index'),
            $authUser->hasRole('host')  => redirect()->route('owner.listings.index'),
            default                     => redirect()->route('dashboard.profile.show'),
        };
    }
}
