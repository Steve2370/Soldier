{{-- Orbite décorative : les membres du groupe gravitent autour du coffre commun (6 places). --}}
@php
    $places = 6;
    $liste = collect($membres)->values();
    $positions = [];
    for ($i = 0; $i < $places; $i++) {
        $angle = deg2rad(-90 + $i * (360 / $places));
        $positions[] = ['left' => round(50 + 50 * cos($angle), 2), 'top' => round(50 + 50 * sin($angle), 2)];
    }
@endphp

<div class="card s-hud" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 28px; padding: 28px; margin-bottom: 20px; background: rgba(7,18,28,0.92);" aria-hidden="true">
    <div class="s-orbit">
        <span class="s-orbit-ring"></span>
        <div class="s-orbit-core">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="1.8" stroke-linecap="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        </div>
        <div class="s-orbit-rot">
            @foreach($positions as $i => $pos)
                @php $m = $liste->get($i); @endphp
                <span class="s-sat {{ $m ? 's-sat-on' : 's-sat-off' }}" style="left: {{ $pos['left'] }}%; top: {{ $pos['top'] }}%;">
                    @if($m)
                        @if($m->user->avatar)
                            <img src="{{ Storage::url($m->user->avatar) }}" alt="">
                        @elseif($m->user->avatar_url)
                            <img src="{{ $m->user->avatar_url }}" alt="">
                        @else
                            {{ strtoupper(mb_substr($m->user->name, 0, 1)) }}
                        @endif
                    @else
                        <span class="s-sat-plus">+</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>
    <div style="flex: 1 1 220px; max-width: 320px; display: flex; flex-direction: column; gap: 10px;">
        <p class="s-tag" style="color: var(--text-muted);">Effectif</p>
        <p style="font-size: 1.8rem;"><span class="s-neon">{{ $liste->count() }}</span> <span style="font-size: 0.9rem; color: var(--text-muted);">/ {{ $places }} membres</span></p>
        <div style="display: grid; grid-template-columns: repeat({{ $places }}, minmax(0, 1fr)); gap: 6px;">
            @for($i = 0; $i < $places; $i++)
                <span style="height: 6px; border-radius: 3px; {{ $i < $liste->count() ? 'background: var(--neon); box-shadow: 0 0 10px rgba(61,245,192,.8);' : 'background: #12202e;' }}"></span>
            @endfor
        </div>
    </div>
</div>

<style>
    .s-orbit { position: relative; width: 240px; height: 240px; max-width: 100%; flex-shrink: 0; }
    .s-orbit-ring { position: absolute; inset: 0; border-radius: 50%; border: 1px dashed rgba(79,195,247,.35); }
    .s-orbit-rot { position: absolute; inset: 0; animation: s-spin 40s linear infinite; }
    .s-orbit-core { position: absolute; left: 50%; top: 50%; width: 92px; height: 92px; margin: -46px 0 0 -46px; border-radius: 22px; background: rgba(9,22,34,.95); border: 1px solid rgba(61,245,192,.5); display: grid; place-items: center; box-shadow: 0 0 40px rgba(61,245,192,.25); }
    .s-orbit-core::after { content: ""; position: absolute; inset: -10px; border-radius: 28px; border: 1px solid rgba(61,245,192,.4); animation: s-ripple 2.8s ease-out infinite; }
    .s-sat { position: absolute; width: 46px; height: 46px; margin: -23px 0 0 -23px; border-radius: 50%; display: grid; place-items: center; font-size: 13px; overflow: hidden; animation: s-spinr 40s linear infinite; }
    .s-sat img { width: 100%; height: 100%; object-fit: cover; }
    .s-sat-on { background: #12263a; color: #8fd8ff; border: 1px solid rgba(79,195,247,.6); box-shadow: 0 0 16px rgba(79,195,247,.45); }
    .s-sat-off { background: rgba(4,10,16,.9); color: #6e8aa3; border: 1px dashed #2a4258; }
    .s-sat-plus { animation: s-breathe 2s infinite; }
</style>
