@props(['value' => 0])

<div {{ $attributes->merge(['class' => 'ui-progress']) }}>
    <div class="ui-progress-bar" style="width: {{ min((float) $value, 100) }}%"></div>
</div>
