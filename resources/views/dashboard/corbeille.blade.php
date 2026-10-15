@extends('layouts.app')
@section('title', 'Corbeille')
@section('content')
    <div style="max-width: 1000px;">
        <div class="s-page-head">
            <div>
                <span class="s-tag">// Zone de rétention</span>
                <h1 style="margin-top: 10px;">Corbeille</h1>
                <p class="s-sub" style="max-width: 620px; line-height: 1.6;">Vos éléments restent restaurables pendant 30 jours. Ils sont ensuite supprimés définitivement par la purge planifiée.</p>
            </div>
            <span class="s-chip s-chip-ok"><span class="s-dot" aria-hidden="true"></span>Purge planifiée</span>
        </div>

        @if($elements->count() > 0)
            <div class="card" style="padding: 0;">
                <div class="s-mono" style="display: flex; justify-content: space-between; padding: 14px 22px; font-size: 11px; letter-spacing: .14em; color: #6e8aa3;">
                    <span>ÉLÉMENT</span><span>PURGE DANS</span>
                </div>
                <div class="s-stagger">
                    @foreach($elements as $element)
                        @php
                            $joursRestants = max(0, 30 - (int) floor($element->deleted_at->diffInDays(now(), true)));
                            $couleurPurge = $joursRestants <= 3 ? '#ff5a64' : ($joursRestants <= 10 ? '#f2b544' : '#4fc3f7');
                        @endphp
                        <div class="s-trash-row">
                            <span class="s-trash-tile" aria-hidden="true">{{ strtoupper(mb_substr($element->label, 0, 1)) }}</span>
                            <div style="flex: 1 1 200px; min-width: 0;">
                                <h2 style="font-size: 0.92rem; font-weight: 400; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $element->label }}</h2>
                                <p class="s-mono" style="color: var(--text-muted); font-size: 0.72rem; margin-top: 6px;">Supprimé {{ $element->deleted_at->diffForHumans() }}</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <span class="s-mono" style="font-size: 0.8rem; color: {{ $couleurPurge }};">{{ $joursRestants }} j</span>
                                <span class="s-purge-bar" aria-hidden="true"><span style="width: {{ round($joursRestants / 30 * 100) }}%; background: {{ $couleurPurge }}; box-shadow: 0 0 8px {{ $couleurPurge }};"></span></span>
                            </div>
                            <form method="POST" action="{{ route('corbeille.restaurer', $element->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-secondary s-restore-btn">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5"/></svg>
                                    Restaurer
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="card" style="display: flex; flex-direction: column; align-items: center; gap: 18px; padding: 60px 24px; text-align: center;">
                <div class="s-trash-radar" aria-hidden="true">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="1.8" stroke-linecap="round" style="position: relative;"><path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/></svg>
                </div>
                <p style="font-size: 1rem;">Votre corbeille est vide.</p>
                <a href="{{ route('dashboard') }}" style="font-size: 0.8rem; text-decoration: none;">Retour à mes services</a>
            </div>
        @endif

        <div style="margin-top: 18px;">{{ $elements->links() }}</div>
    </div>

    <style>
        .s-trash-row { display: flex; flex-wrap: wrap; align-items: center; gap: 18px; padding: 18px 22px; border-top: 1px solid #12202e; transition: background .2s; }
        .s-trash-row:hover { background: linear-gradient(90deg, rgba(255,90,100,.06), transparent 70%); }
        .s-trash-tile { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; font-size: 15px; flex-shrink: 0; background: #0e1a27; color: #8fa2b8; border: 1px solid #22364a; }
        .s-purge-bar { display: block; width: 160px; max-width: 100%; height: 4px; border-radius: 2px; background: #12202e; overflow: hidden; }
        .s-purge-bar > span { display: block; height: 100%; border-radius: 2px; transform-origin: left; animation: s-growx 1s cubic-bezier(.2,.8,.2,1) backwards; }
        @keyframes s-growx { from { transform: scaleX(0); } to { transform: scaleX(1); } }
        .s-restore-btn:hover { border-color: var(--neon) !important; box-shadow: 0 0 20px rgba(61,245,192,.3) !important; color: var(--neon-soft) !important; }
        .s-trash-radar { position: relative; width: 110px; height: 110px; border-radius: 50%; border: 1px solid rgba(61,245,192,.35); display: grid; place-items: center; overflow: hidden; }
        .s-trash-radar::before { content: ""; position: absolute; inset: 0; border-radius: 50%; background: conic-gradient(from 0deg, rgba(61,245,192,.4), transparent 80deg); animation: s-spin 3s linear infinite; }
    </style>
@endsection
