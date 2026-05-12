@extends('layouts.public')
@section('title', 'Abonnement activé — Soldier')

@section('content')
    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:40px 24px;">
        <div style="max-width:480px; width:100%; text-align:center;">

            <div style="width:72px; height:72px; background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.3); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>

            <h1 style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:12px; letter-spacing:-0.02em;">
                Bienvenue dans Soldier Famille !
            </h1>
            <p style="font-size:0.9375rem; color:var(--text-secondary); line-height:1.75; margin-bottom:36px;">
                Votre abonnement est actif. Vous pouvez maintenant inviter jusqu'à 5 membres de votre famille et profiter de toutes les fonctionnalités.
            </p>

            <div style="background:rgba(22,37,52,0.7); border:1px solid rgba(33,126,170,0.2); border-radius:16px; padding:24px; margin-bottom:32px; text-align:left;">
                @foreach(['Jusqu\'à 6 membres', 'Coffres familiaux partagés', 'Tableau de bord familial', 'Support prioritaire'] as $feature)
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.04);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                        <span style="font-size:0.875rem; color:var(--text-secondary);">{{ $feature }}</span>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('dashboard') }}" style="display:inline-block; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border-radius:12px; padding:14px 36px; font-size:0.9rem; font-weight:700; text-decoration:none;">
                Accéder à mon coffre →
            </a>

        </div>
    </div>
@endsection
