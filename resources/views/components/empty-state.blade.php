{{--
    Empty State Component
    Props:
      $icon        — HugeIcons class without base prefix (default: 'ph-tray')
      $title       — heading text
      $message     — descriptive paragraph text
      $actionLabel — optional CTA button text
      $actionUrl   — optional CTA button URL
--}}
@props([
    'icon'        => 'ph-tray',
    'title'       => 'Nothing here yet',
    'message'     => '',
    'actionLabel' => null,
    'actionUrl'   => null,
])

<div class="text-center py-5 px-3">
    <div class="mb-3">
        <i class="ph {{ $icon }} display-3 text-muted opacity-50"></i>
    </div>
    <h5 class="fw-semibold text-secondary mb-2">{{ $title }}</h5>
    @if($message)
        <p class="text-muted mb-3" style="max-width: 360px; margin-inline: auto; font-size: 0.9rem;">
            {{ $message }}
        </p>
    @endif
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm px-4">
            {{ $actionLabel }}
        </a>
    @endif
</div>
