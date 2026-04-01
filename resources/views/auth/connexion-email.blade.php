@extends('layouts.public')
@section('title', 'Connexion')

@section('content')
    <div style="min-height: calc(100vh - 88px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%; max-width: 420px;">
            <div style="text-align: center; margin-bottom: 40px;">
                <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <img src="{{ asset('assets/Soldier-Logo.png') }}"
                         alt="Logo"
                         style="width: 120px; height: auto;">
                </div>
                <h1 style="font-family: 'Audiowide', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; letter-spacing: -0.02em;">Bon retour Soldat</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Entrez votre adresse email pour continuer</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 32px; justify-content: center;">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-bright)); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; box-shadow: 0 0 16px rgba(45,159,212,0.3);">1</div>
                <div style="flex: 1; max-width: 56px; height: 1px; background: rgba(33,126,170,0.2);"></div>
                <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(22,37,52,0.8); border: 1px solid rgba(33,126,170,0.25); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: var(--text-muted);">2</div>
            </div>

            <div class="card">
                <form method="POST" action="{{ route('connexion.post') }}">
                    @csrf
                    <div style="margin-bottom: 22px;">
                        <label for="email">Adresse email</label>
                        <input
                            type="email" id="email" name="email"
                            class="input @error('email') input-error @enderror"
                            value="{{ old('email') }}"
                            placeholder="sergent@soldier.com"
                            autocomplete="email" autofocus
                        >
                        @error('email')
                        <p class="error-msg">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 13px;">
                        Continuer →
                    </button>
                </form>

                <div class="divider"></div>

                <p style="text-align: center; font-size: 0.875rem; color: var(--text-secondary);">
                    Pas encore de compte ?
                    <a href="{{ route('inscription') }}" style="color: var(--accent-bright); font-weight: 600; text-decoration: none;">Créer un compte →</a>
                </p>
            </div>

            <p style="text-align: center; margin-top: 22px; font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.04em;">
                AES-256-GCM · Argon2id · Zero-knowledge
            </p>
        </div>
    </div>
@endsection
