@extends('layouts.public')
@section('title', 'Vérification MFA')

@section('content')
    <div class="s-centered">
        <x-soldier.backdrop :locks="5" />

        <div class="card s-orbit-border s-boot" style="width: 100%; max-width: 470px; padding: 38px; border-radius: 16px; border-color: transparent; display: flex; flex-direction: column; gap: 24px;">
            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px;">
                <div class="s-emblem" style="width: 86px; height: 86px; margin: 6px 0;" aria-hidden="true">
                    <span class="s-pulse"></span>
                    <span class="s-ring-b"></span>
                    <span class="s-ring"></span>
                    <span class="s-radar"></span>
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--accent-ultra)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="position: relative; filter: drop-shadow(0 0 8px rgba(79,195,247,.9));"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <h1 style="font-size: 1.65rem; font-weight: 400; color: var(--text-primary);">
                    Vérification
                </h1>
                <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.6;">
                    @if(session('auth.mfa_type') === 'totp')
                        Entrez le code affiché dans votre application authenticator
                    @else
                        Entrez le code à 6 chiffres envoyé à votre adresse email
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('mfa.verify.post') }}">
                @csrf

                <div style="margin-bottom: 22px;">
                    <label for="mfa_code" style="text-align: center; display: block; margin-bottom: 12px;">
                        Code de vérification
                    </label>

                    <input
                        type="text"
                        name="code"
                        id="mfa_code"
                        class="input @error('code') input-error @enderror"
                        maxlength="14"
                        placeholder="000000"
                        autocomplete="one-time-code"
                        autofocus
                        style="text-align: center; font-size: 1.6rem; letter-spacing: 0.3em; padding: 16px; color: var(--neon); text-shadow: 0 0 10px rgba(61,245,192,.6);"
                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '').slice(0,14)"
                    >
                    <p style="font-size: .75rem; color: var(--text-muted); text-align: center; margin-top: 10px;">Vous pouvez aussi utiliser un code de récupération.</p>
                    @error('code')
                    <p class="error-msg" style="justify-content: center;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                @if(session('auth.mfa_type') === 'totp')
                    <div style="display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 10px; background: rgba(4,10,16,.9); border: 1px solid var(--border); margin-bottom: 20px;">
                        <svg viewBox="0 0 36 36" aria-hidden="true" style="width: 36px; height: 36px; transform: rotate(-90deg); flex-shrink: 0;">
                            <circle cx="18" cy="18" r="15" fill="none" stroke="#16283a" stroke-width="3"/>
                            <circle data-totp-ring cx="18" cy="18" r="15" fill="none" stroke="var(--neon)" stroke-width="3" stroke-linecap="round" style="transition: stroke-dashoffset 1s linear; filter: drop-shadow(0 0 4px rgba(61,245,192,.9));"/>
                        </svg>
                        <p class="s-mono" style="font-size: 12px; color: var(--text-secondary);">Nouveau code dans <span data-totp-left style="color: var(--neon);">30</span> s</p>
                    </div>
                @endif

                <button type="submit" class="btn-primary" style="width: 100%; min-height: 50px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Vérifier
                </button>

                <div class="divider"></div>

                @if(session('auth.mfa_type') !== 'totp')
                    <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                        Vous n'avez pas reçu le code ?
                        <button type="submit" form="mfa-resend" style="color: var(--accent-bright); background: none; border: 0; cursor: pointer; font-family: inherit; font-size: inherit; padding: 6px;">Renvoyer →</button>
                    </p>
                @endif

                <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                    <a href="{{ route('connexion') }}" style="color: var(--text-muted); text-decoration: none;">
                        ← Retour à la connexion
                    </a>
                </p>
            </form>
            <form id="mfa-resend" method="POST" action="{{ route('mfa.resend') }}">@csrf</form>
        </div>

        <p class="s-mono" style="font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.12em;">
            AES-256-GCM · ARGON2ID · SESSION SÉCURISÉE
        </p>
    </div>
@endsection
