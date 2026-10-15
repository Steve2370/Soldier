@extends('layouts.public')
@section('title', 'Vérifiez votre email')

@section('content')
    <div class="s-centered">
        <x-soldier.backdrop :locks="5" />

        <div class="card s-orbit-border s-boot" style="width: 100%; max-width: 500px; padding: 42px 38px; border-radius: 16px; border-color: transparent; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 22px;">
            <div class="s-mail" aria-hidden="true">
                <span class="s-mail-wave"></span>
                <span class="s-mail-wave" style="animation-delay: 1.2s;"></span>
                <span class="s-mail-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
            </div>

            <div>
                <h1 style="font-size: 1.55rem; font-weight: 400; color: var(--text-primary); margin-bottom: 12px;">Vérifiez votre email</h1>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.7;">
                    Un lien de vérification a été envoyé à <strong class="s-mono" style="color: var(--text-primary); font-weight: 400;">{{ auth()->user()->email }}</strong>.<br>
                    Cliquez sur le lien dans l'email pour activer votre compte.
                </p>
            </div>

            <div style="width: 100%; display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary" style="width: 100%; min-height: 50px;">
                        Renvoyer l'email de vérification
                    </button>
                </form>

                <form method="POST" action="{{ route('deconnexion') }}">
                    @csrf
                    <button type="submit" class="btn-secondary" style="width: 100%; min-height: 46px;">
                        Se déconnecter
                    </button>
                </form>
            </div>

            <p style="font-size: 0.75rem; color: var(--text-muted);">
                Vérifiez vos spams si vous ne trouvez pas l'email.
            </p>
        </div>
    </div>
@endsection
