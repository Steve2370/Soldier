@props(['fixed' => false, 'video' => true, 'floor' => true, 'locks' => 6, 'opacity' => null])

@php
    $lockSlots = [
        ['left' => 6,  'size' => 16, 'dur' => 11, 'delay' => 0,   'green' => false],
        ['left' => 18, 'size' => 12, 'dur' => 14, 'delay' => 3,   'green' => false],
        ['left' => 29, 'size' => 22, 'dur' => 12, 'delay' => 6,   'green' => true],
        ['left' => 47, 'size' => 10, 'dur' => 12, 'delay' => 9,   'green' => false],
        ['left' => 58, 'size' => 14, 'dur' => 14, 'delay' => 5,   'green' => true],
        ['left' => 69, 'size' => 16, 'dur' => 13, 'delay' => 2,   'green' => false],
        ['left' => 78, 'size' => 22, 'dur' => 15, 'delay' => 7,   'green' => true],
        ['left' => 87, 'size' => 12, 'dur' => 10, 'delay' => 4,   'green' => false],
        ['left' => 94, 'size' => 18, 'dur' => 17, 'delay' => 0.5, 'green' => false],
    ];
@endphp

<div {{ $attributes->class(['s-backdrop', 's-backdrop-fixed' => $fixed]) }} aria-hidden="true">
    @if($video)
        <video autoplay muted loop playsinline preload="metadata" @if($opacity) style="opacity: {{ $opacity }};" @endif>
            <source src="{{ asset('assets/cyber.mp4') }}" type="video/mp4">
        </video>
    @endif
    <div class="s-vignette"></div>
    @if($floor)
        <div class="s-floor"></div>
    @endif
    @foreach(array_slice($lockSlots, 0, (int) $locks) as $l)
        <span class="s-lock {{ $l['green'] ? 's-lock-green' : '' }}" style="left: {{ $l['left'] }}%; width: {{ $l['size'] }}px; height: {{ $l['size'] }}px; animation-duration: {{ $l['dur'] }}s; animation-delay: {{ $l['delay'] }}s;"><svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
    @endforeach
</div>
