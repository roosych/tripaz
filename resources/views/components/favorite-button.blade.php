@props([
    'listingId',
    'isFavorited' => false,
    'size'        => 'sm',
])

@php
    $storeUrl   = route('dashboard.favorites.store',   $listingId);
    $destroyUrl = route('dashboard.favorites.destroy', $listingId);
    $loginUrl   = route('login');
    $authed     = auth()->check();

    $btnSize  = $size === 'lg' ? '44px' : '32px';
    $iconSize = $size === 'lg' ? 'fs-5'  : '';
@endphp

<div
    class="favorite-btn-wrap"
    data-listing-id="{{ $listingId }}"
    data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
    data-store-url="{{ $storeUrl }}"
    data-destroy-url="{{ $destroyUrl }}"
    data-login-url="{{ $loginUrl }}"
    data-authed="{{ $authed ? 'true' : 'false' }}"
>
    <button
        type="button"
        class="btn rounded-circle p-1 lh-1 border-0 shadow-sm favorite-btn btn-light"
        title="{{ $isFavorited ? __('listings.remove_favorite') : __('listings.add_favorite') }}"
        style="width:{{ $btnSize }};height:{{ $btnSize }}"
    >
        <i class="ph {{ $isFavorited ? 'ph-heart-fill' : 'ph-heart' }} text-danger {{ $iconSize }}"></i>
    </button>
</div>
