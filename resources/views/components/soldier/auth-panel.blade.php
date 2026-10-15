@props(['kicker' => null, 'title' => null, 'neon' => null, 'emblem' => true])

<aside class="s-auth-panel">
    <x-soldier.backdrop :locks="5" />

    @if($emblem)
        <x-soldier.emblem :size="140" />
    @endif

    @if($kicker)
        <span class="s-tag">{{ $kicker }}</span>
    @endif

    @if($title || $neon)
        <h2 style="display: flex; flex-direction: column; gap: 10px; font-weight: 400; line-height: 1.15;">
            @if($title)
                <span style="font-size: clamp(15px, 1.3vw, 18px); letter-spacing: .28em; color: #dde7f1;">{{ $title }}</span>
            @endif
            @if($neon)
                <span class="s-neon s-neon-flicker" data-scramble data-scramble-loop="12" style="font-size: clamp(30px, 3.4vw, 48px);">{{ $neon }}</span>
            @endif
        </h2>
    @endif

    {{ $slot }}

    <p class="s-mono" style="font-size: 12px; letter-spacing: .14em; color: #8fb4cc;">ARGON2ID <span style="color: var(--accent);">◆</span> AES-256-GCM <span style="color: var(--accent);">◆</span> RSA-4096</p>
</aside>
