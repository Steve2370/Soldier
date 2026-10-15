@extends('layouts.public')
@section('title', 'État du forfait Famille — Soldier')

@section('content')
    <div class="s-centered" style="position: relative; min-height: calc(100vh - 66px); display: flex; align-items: center; justify-content: center; padding: 40px 16px; overflow: hidden;">
        <x-soldier.backdrop :locks="5" opacity="0.18" />

        <div class="s-stagger" style="position: relative; max-width: 500px; width: 100%; text-align: center;">

            <div class="succes-seal {{ $active ? 'is-active' : 'is-pending' }}" aria-hidden="true">
                <span class="succes-ring"></span>
                <span class="succes-ring succes-ring-2"></span>
                @if($active)
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                @else
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @endif
            </div>

            <span class="s-tag" style="color: {{ $active ? 'var(--neon)' : 'var(--warning)' }};">{{ $active ? '// Accès confirmé' : '// En attente de Stripe' }}</span>
            <h1 style="font-size: clamp(1.6rem, 4vw, 2rem); font-weight: 400; margin: 14px 0 12px;">
                {{ $active ? 'Votre accès Famille est actif' : 'Confirmation du paiement en attente' }}
            </h1>
            <p style="font-size: 0.9375rem; color: var(--text-secondary); line-height: 1.75; margin-bottom: 32px;">
                @if($active)
                    Votre accès Famille est actif. Vous pouvez inviter jusqu’à 5 membres de votre famille.
                @else
                    Nous n’avons pas encore reçu la confirmation de Stripe. Aucun accès payé n’est activé pour le moment. Actualisez cette page dans quelques instants. Si l’attente persiste, contactez le support sans refaire de paiement.
                @endif
            </p>

            <div class="card s-hud" style="padding: 22px 24px; margin-bottom: 32px; text-align: left;">
                <p class="s-tag" style="color: var(--text-muted); margin-bottom: 8px;">{{ $active ? 'Inclus dans votre forfait' : 'Inclus après confirmation' }}</p>
                @foreach(['Jusqu\'à 6 membres', 'Coffres familiaux partagés', 'Tableau de bord familial', 'Support prioritaire'] as $feature)
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid rgba(79,195,247,0.06);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $active ? 'var(--neon)' : 'var(--text-muted)' }}" stroke-width="2.5" style="flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
                        <span style="font-size: 0.875rem; color: var(--text-secondary);">{{ $feature }}</span>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('dashboard') }}" class="btn-primary" style="text-decoration: none; padding: 14px 36px;">
                Accéder à mon coffre →
            </a>

        </div>
    </div>

    <style>
        .succes-seal { position: relative; width: 88px; height: 88px; margin: 0 auto 24px; border-radius: 50%; display: grid; place-items: center; background: rgba(4,10,16,.9); }
        .succes-seal.is-active { border: 1px solid rgba(61,245,192,.55); box-shadow: 0 0 40px rgba(61,245,192,.3); }
        .succes-seal.is-pending { border: 1px solid rgba(242,181,68,.55); box-shadow: 0 0 40px rgba(242,181,68,.2); }
        .succes-ring { position: absolute; inset: -1px; border-radius: 50%; border: 1px solid currentColor; animation: s-ripple 2.6s ease-out infinite; }
        .succes-ring-2 { animation-delay: 1.3s; }
        .succes-seal.is-active .succes-ring { color: rgba(61,245,192,.55); }
        .succes-seal.is-pending .succes-ring { color: rgba(242,181,68,.5); }
    </style>
@endsection
