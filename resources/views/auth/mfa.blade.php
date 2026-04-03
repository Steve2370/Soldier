@extends('layouts.public')
@section('title', 'Vérification MFA')

@section('content')
    <div style="min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%; max-width: 420px;">

            <div style="text-align: center; margin-bottom: 36px;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: var(--accent); border-radius: 16px; margin-bottom: 20px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">
                    Vérification
                </h1>
                <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
                    @if(session('mfa_type') === 'totp')
                        Entrez le code affiché dans votre application authenticator
                    @else
                        Entrez le code à 6 chiffres envoyé à votre adresse email
                    @endif
                </p>
            </div>

            <div class="card">
                <form method="POST" action="{{ route('mfa.verify.post') }}">
                    @csrf

                    <div style="margin-bottom: 24px;">
                        <label style="text-align: center; display: block; margin-bottom: 12px;">
                            Code de vérification
                        </label>

                        {{-- Input code 6 chiffres --}}
                        <input
                            type="text"
                            name="code"
                            id="mfa_code"
                            class="input @error('code') input-error @enderror"
                            maxlength="6"
                            placeholder="000000"
                            autocomplete="one-time-code"
                            autofocus
                            style="text-align: center; font-size: 2rem; font-weight: 700; letter-spacing: 0.5em; padding: 16px; font-family: 'Courier New', monospace;"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,6)"
                        >
                        @error('code')
                        <p class="error-msg" style="justify-content: center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Vérifier
                    </button>

                    <div class="divider"></div>

                    @if(session('mfa_type') !== 'totp')
                        <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                            Vous n'avez pas reçu le code ?
                            <a href="{{ route('mfa.verify') }}" style="color: var(--accent-bright); text-decoration: none; font-weight: 600;">Renvoyer →</a>
                        </p>
                    @endif

                    <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                        <a href="{{ route('connexion') }}" style="color: var(--text-muted); text-decoration: none;">
                            ← Retour à la connexion
                        </a>
                    </p>
                </form>
            </div>

            <p style="text-align: center; margin-top: 20px; font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.04em;">
                AES-256-GCM · Argon2id · Zero-knowledge
            </p>
        </div>
    </div>
@endsection
