{{--
    Status Badge Component
    Props:
      $status — string: draft | pending_review | published | rejected | suspended | awaiting_payment
--}}
@props(['status'])

@php
    $map = [
        'draft'            => ['class' => 'bg-secondary',         'label' => 'Draft',            'style' => ''],
        'pending_review'   => ['class' => 'bg-warning text-dark', 'label' => 'Pending Review',   'style' => ''],
        'published'        => ['class' => 'bg-success',           'label' => 'Published',        'style' => ''],
        'rejected'         => ['class' => 'bg-danger',            'label' => 'Rejected',         'style' => ''],
        'suspended'        => ['class' => 'bg-dark',              'label' => 'Suspended',        'style' => ''],
        'awaiting_payment' => ['class' => '',                     'label' => 'Awaiting Payment', 'style' => 'background-color:#fd7e14!important;color:#fff'],
    ];

    $config = $map[$status] ?? ['class' => 'bg-secondary', 'label' => ucfirst(str_replace('_', ' ', $status)), 'style' => ''];
@endphp

<span class="badge {{ $config['class'] }} rounded-pill px-2 py-1"
      style="font-size:0.72rem; font-weight:600; letter-spacing:0.3px{{ $config['style'] ? ';' . $config['style'] : '' }}">
    {{ $config['label'] }}
</span>
