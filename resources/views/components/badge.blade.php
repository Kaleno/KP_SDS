@props(['tone' => 'ok'])

@php
$toneClass = match ($tone) {
    'warn' => 'ui-badge-warn',
    'info' => 'ui-badge-info',
    'danger' => 'ui-badge-danger',
    'muted' => 'ui-badge-muted',
    default => 'ui-badge-ok',
};
@endphp

<span {{ $attributes->merge(['class' => $toneClass]) }}>{{ $slot }}</span>
