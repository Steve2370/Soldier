@props(['size' => 150, 'pulse' => true])

@php $img = (int) round($size * 0.79); @endphp

<div {{ $attributes->class(['s-emblem']) }} style="width: {{ $size }}px; height: {{ $size }}px;" aria-hidden="true">
    @if($pulse)
        <span class="s-pulse"></span>
        <span class="s-pulse s-pulse-2"></span>
    @endif
    <span class="s-ring-c"></span>
    <span class="s-ring-b"></span>
    <span class="s-ring"></span>
    <span class="s-radar"></span>
    <img src="{{ asset('assets/Soldier-Logo.png') }}" alt="" style="width: {{ $img }}px; height: {{ $img }}px;">
</div>
