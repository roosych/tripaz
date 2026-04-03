{{--
    Star Rating Component
    Props:
      $rating      — float 0–5
      $reviewCount — int (optional)
      $size        — 'sm' | 'md' (default md)
--}}
@props([
    'rating'      => 0,
    'reviewCount' => null,
    'size'        => 'md',
])

@php
    $rating      = (float) $rating;
    $fullStars   = (int) floor($rating);
    $halfStar    = ($rating - $fullStars) >= 0.5;
    $emptyStars  = 5 - $fullStars - ($halfStar ? 1 : 0);
    $iconClass   = $size === 'sm' ? 'fs-6' : 'fs-5';
@endphp

<span class="star-rating d-inline-flex align-items-center gap-1">
    <span class="d-inline-flex">
        @for($i = 0; $i < $fullStars; $i++)
            <i class="ph ph-star {{ $iconClass }}"></i>
        @endfor
        @if($halfStar)
            <i class="ph ph-star-half {{ $iconClass }}"></i>
        @endif
        @for($i = 0; $i < $emptyStars; $i++)
            <i class="ph ph-star {{ $iconClass }}"></i>
        @endfor
    </span>
    @if($reviewCount !== null)
        <small class="text-muted ms-1">
            <strong>{{ number_format($rating, 1) }}</strong>
            ({{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }})
        </small>
    @endif
</span>
