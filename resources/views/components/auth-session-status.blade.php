@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800']) }}>
        {{ $status }}
    </div>
@endif
