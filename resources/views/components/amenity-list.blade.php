{{--
    Amenity List Component
    Props:
      $amenities  — Collection of Amenity models
      $max        — int|null  Max to show before "+N more" (null = show all)
      $locale     — string
--}}
@props([
    'amenities' => collect(),
    'max'       => null,
    'locale'    => 'az',
])

@php
    $total   = $amenities->count();
    $visible = $max ? $amenities->take($max) : $amenities;
    $extra   = $max ? max(0, $total - $max) : 0;
@endphp

@if($total > 0)
    <div class="d-flex flex-wrap gap-2 align-items-center">
        @foreach($visible as $amenity)
            <span class="badge bg-light text-dark border fw-normal py-1 px-2" title="{{ $amenity->name($locale) }}">
                @if($amenity->icon)
                    <i class="{{ $amenity->icon }} me-1"></i>
                @endif
                {{ $amenity->name($locale) }}
            </span>
        @endforeach
        @if($extra > 0)
            <span class="text-muted small">+{{ $extra }} more</span>
        @endif
    </div>
@endif
