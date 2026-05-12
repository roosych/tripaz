<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Owner;
use App\Http\Controllers\Public\ListingController as PublicListingController;
use App\Http\Controllers\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Locale is set globally via App\Http\Middleware\SetLocale (registered in bootstrap/app.php)

// =============================================================================
// Area 1: Public routes — no auth required
// =============================================================================

// Canonical catalogue URL — filters and pagination live here
Route::get('/', [PublicListingController::class, 'index'])->name('listings.index');

Route::prefix('listings')->name('listings.')->group(function () {
    // /listings redirects permanently to / so bookmark/share URLs keep working
    Route::get('/', fn () => redirect()->route('listings.index', request()->query(), 301))->name('index.redirect');
    Route::get('/{slug}', [PublicListingController::class, 'show'])->name('show');
});

Route::get('/api/listings/search', function (Request $request) {
    $q = trim($request->input('q', ''));

    if (strlen($q) < 2) {
        return response()->json([]);
    }

    $locale = app()->getLocale();

    $results = \App\Models\Listing::query()
        ->published()
        ->whereHas('translations', function ($query) use ($q, $locale) {
            $query->where('locale', $locale)
                  ->where('title', 'like', '%' . $q . '%');
        })
        ->with('translations')
        ->limit(10)
        ->get()
        ->map(function ($listing) use ($locale) {
            return [
                'title' => $listing->translation($locale)?->title ?? $listing->slug,
                'slug'  => $listing->slug,
                'type'  => $listing->type->value,
            ];
        });

    return response()->json($results);
})->name('api.listings.search');

// =============================================================================
// Auth routes
// =============================================================================

// Guest-only: registration and login forms
Route::middleware('guest')->group(function () {
    // Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout — authenticated users only
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Email verification — notice is public, verify link requires auth
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware('auth');

// =============================================================================
// Area 2: Owner Dashboard — auth + role:host
// =============================================================================

Route::prefix('owner')
    ->middleware(['auth', 'role:host'])
    ->name('owner.')
    ->group(function () {

        Route::get('/', fn () => redirect()->route('owner.dashboard'));

        // Owner Dashboard — overview + stats
        Route::get('dashboard', [Owner\DashboardController::class, 'index'])->name('dashboard');

        // Listings — full CRUD scoped to the authenticated owner
        Route::get('listings', [Owner\ListingController::class, 'index'])->name('listings.index');
        Route::get('listings/create', [Owner\ListingController::class, 'create'])->name('listings.create');
        Route::post('listings', [Owner\ListingController::class, 'store'])->name('listings.store');
        Route::get('listings/{slug}', [Owner\ListingController::class, 'show'])->name('listings.show');
        Route::get('listings/{slug}/edit', [Owner\ListingController::class, 'edit'])->name('listings.edit');
        Route::put('listings/{slug}', [Owner\ListingController::class, 'update'])->name('listings.update');
        Route::delete('listings/{slug}', [Owner\ListingController::class, 'destroy'])->name('listings.destroy');
        Route::patch('listings/{slug}/submit', [Owner\ListingController::class, 'submit'])->name('listings.submit');
        Route::patch('listings/{slug}/toggle-status', [Owner\ListingController::class, 'toggleStatus'])->name('listings.toggle-status');

        Route::get('api/listing-type-data', [Owner\ListingController::class, 'listingTypeData'])->name('api.listing-type-data');

        // Payment routes for awaiting_payment listings
        Route::get('listings/{listing}/payment', [Owner\ListingPaymentController::class, 'show'])->name('listings.payment.show');
        Route::post('listings/{listing}/payment', [Owner\ListingPaymentController::class, 'store'])
            ->middleware('throttle:5,1')  // max 5 proof uploads per minute per host
            ->name('listings.payment.store');
        Route::post('listings/{listing}/withdraw', [Owner\ListingPaymentController::class, 'withdraw'])->name('listings.payment.withdraw');

        // Media management for own listings
        Route::post('listings/{slug}/media', [Owner\MediaController::class, 'store'])->name('listings.media.store');
        Route::delete('listings/{slug}/media/{media}', [Owner\MediaController::class, 'destroy'])->name('listings.media.destroy');
        Route::patch('listings/{slug}/media/{media}/cover', [Owner\MediaController::class, 'setCover'])->name('listings.media.cover');
        Route::post('listings/{slug}/media/youtube', [Owner\MediaController::class, 'storeYoutube'])->name('listings.media.youtube');

        // Reviews on own listings
        Route::get('reviews', [Owner\ReviewController::class, 'index'])->name('reviews.index');

        // Bookings for own listings (stub until Booking model exists)
        Route::get('bookings', [Owner\BookingController::class, 'index'])->name('bookings.index');
    });

// =============================================================================
// Area 3: User Dashboard — auth + role:user
// =============================================================================

Route::prefix('dashboard')
    ->middleware(['auth', 'role:user'])
    ->name('dashboard.')
    ->group(function () {

        Route::get('/', fn () => redirect()->route('dashboard.profile.show'));

        // Profile
        Route::get('profile', [User\ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [User\ProfileController::class, 'update'])->name('profile.update');

        // Reviews
        Route::get('reviews', [User\ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews', [User\ReviewController::class, 'store'])->name('reviews.store');

        // Bookings (stub until Booking model exists)
        Route::get('bookings', [User\BookingController::class, 'index'])->name('bookings.index');
    });

// =============================================================================
// Favorites — auth only (any role: user, host, admin)
// =============================================================================

Route::prefix('dashboard')
    ->middleware('auth')
    ->name('dashboard.')
    ->group(function () {
        Route::get('favorites', [User\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('favorites/{listing}', [User\FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('favorites/{listing}', [User\FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });

// =============================================================================
// Area 4: Admin Panel — auth + role:admin
// =============================================================================

Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Listings moderation + admin-managed CRUD
        Route::get('listings', [Admin\ListingController::class, 'index'])->name('listings.index');
        Route::get('listings/create', [Admin\ListingController::class, 'create'])->name('listings.create');
        Route::post('listings', [Admin\ListingController::class, 'store'])->name('listings.store');
        Route::get('listings/{listing}/edit', [Admin\ListingController::class, 'edit'])->name('listings.edit');
        Route::put('listings/{listing}', [Admin\ListingController::class, 'update'])->name('listings.update');
        Route::patch('listings/{listing}/approve', [Admin\ListingController::class, 'approve'])->name('listings.approve');
        Route::patch('listings/{listing}/reject', [Admin\ListingController::class, 'reject'])->name('listings.reject');
        Route::patch('listings/{listing}/suspend', [Admin\ListingController::class, 'suspend'])->name('listings.suspend');
        Route::patch('listings/{listing}/reinstate', [Admin\ListingController::class, 'reinstate'])->name('listings.reinstate');
        Route::patch('listings/{listing}/set-status', [Admin\ListingController::class, 'setStatus'])->name('listings.set-status');

        // Admin listing media management
        Route::post('listings/{listing}/media', [Admin\MediaController::class, 'store'])->name('listings.media.store');
        Route::delete('listings/{listing}/media/{media}', [Admin\MediaController::class, 'destroy'])->name('listings.media.destroy');
        Route::patch('listings/{listing}/media/{media}/cover', [Admin\MediaController::class, 'setCover'])->name('listings.media.cover');
        Route::post('listings/{listing}/media/youtube', [Admin\MediaController::class, 'storeYoutube'])->name('listings.media.youtube');

        // Review moderation
        Route::get('reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}/approve', [Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('reviews/{review}/reject', [Admin\ReviewController::class, 'reject'])->name('reviews.reject');

        // User management
        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/role', [Admin\UserController::class, 'updateRole'])->name('users.role');

        // Listing payments management
        Route::get('listing-payments', [Admin\ListingPaymentController::class, 'index'])->name('listing-payments.index');
        Route::get('listing-payments/{payment}', [Admin\ListingPaymentController::class, 'show'])->name('listing-payments.show');
        Route::patch('listing-payments/{payment}/approve', [Admin\ListingPaymentController::class, 'approve'])->name('listing-payments.approve');
        Route::patch('listing-payments/{payment}/reject', [Admin\ListingPaymentController::class, 'reject'])->name('listing-payments.reject');

        // Promotions / boost_weight management
        Route::get('promotions', [Admin\PromotionController::class, 'index'])->name('promotions.index');
        Route::patch('promotions/{listing}', [Admin\PromotionController::class, 'update'])->name('promotions.update');

        // AJAX helper — returns filtered categories, amenities, and field schema for a listing type
        Route::get('api/listing-type-data', [Admin\ListingController::class, 'listingTypeData'])->name('api.listing-type-data');

        // Categories management
        Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [Admin\CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [Admin\CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

        // Amenities management
        Route::get('lookup-options', [Admin\LookupOptionController::class, 'index'])->name('lookup-options.index');
        Route::post('lookup-options', [Admin\LookupOptionController::class, 'store'])->name('lookup-options.store');
        Route::post('lookup-options/reorder', [Admin\LookupOptionController::class, 'reorder'])->name('lookup-options.reorder');
        Route::put('lookup-options/{lookupOption}', [Admin\LookupOptionController::class, 'update'])->name('lookup-options.update');
        Route::delete('lookup-options/{lookupOption}', [Admin\LookupOptionController::class, 'destroy'])->name('lookup-options.destroy');

        Route::get('amenities', [Admin\AmenityController::class, 'index'])->name('amenities.index');
        Route::get('amenities/create', [Admin\AmenityController::class, 'create'])->name('amenities.create');
        Route::post('amenities', [Admin\AmenityController::class, 'store'])->name('amenities.store');
        Route::post('amenities/reorder', [Admin\AmenityController::class, 'reorder'])->name('amenities.reorder');
        Route::get('amenities/{amenity}/edit', [Admin\AmenityController::class, 'edit'])->name('amenities.edit');
        Route::put('amenities/{amenity}', [Admin\AmenityController::class, 'update'])->name('amenities.update');
        Route::delete('amenities/{amenity}', [Admin\AmenityController::class, 'destroy'])->name('amenities.destroy');

        // Amenity Groups
        Route::get('amenity-groups', [Admin\AmenityGroupController::class, 'index'])->name('amenity-groups.index');
        Route::get('amenity-groups/create', [Admin\AmenityGroupController::class, 'create'])->name('amenity-groups.create');
        Route::post('amenity-groups', [Admin\AmenityGroupController::class, 'store'])->name('amenity-groups.store');
        Route::post('amenity-groups/reorder', [Admin\AmenityGroupController::class, 'reorder'])->name('amenity-groups.reorder');
        Route::get('amenity-groups/{amenityGroup}/edit', [Admin\AmenityGroupController::class, 'edit'])->name('amenity-groups.edit');
        Route::put('amenity-groups/{amenityGroup}', [Admin\AmenityGroupController::class, 'update'])->name('amenity-groups.update');
        Route::delete('amenity-groups/{amenityGroup}', [Admin\AmenityGroupController::class, 'destroy'])->name('amenity-groups.destroy');

        // Regions management
        Route::get('regions', [Admin\RegionController::class, 'index'])->name('regions.index');
        Route::get('regions/create', [Admin\RegionController::class, 'create'])->name('regions.create');
        Route::post('regions', [Admin\RegionController::class, 'store'])->name('regions.store');
        Route::get('regions/{region}/edit', [Admin\RegionController::class, 'edit'])->name('regions.edit');
        Route::put('regions/{region}', [Admin\RegionController::class, 'update'])->name('regions.update');
        Route::delete('regions/{region}', [Admin\RegionController::class, 'destroy'])->name('regions.destroy');

        // Booking configuration per listing type
        Route::get('booking-config', [Admin\BookingConfigController::class, 'index'])->name('booking-config.index');
        Route::patch('booking-config/{type}', [Admin\BookingConfigController::class, 'update'])->name('booking-config.update');

        // System settings management
        Route::get('system-settings', [Admin\SystemSettingController::class, 'index'])->name('system-settings.index');
        Route::patch('system-settings/{setting}', [Admin\SystemSettingController::class, 'update'])->name('system-settings.update');
    });
