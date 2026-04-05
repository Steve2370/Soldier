@extends('layouts.public')
@section('title', 'Connexion Mot de passe')

@section('content')
    <div style="min-height: calc(100vh - 88px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%; max-width: 420px;" x-data="{ showPass: false, showMaster: false }">

            <div style="text-align: center; margin-bottom: 40px;">
                <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <img src="{{ asset('assets/Soldier-Logo.png') }}"
                         alt="Logo"
                         style="width: 120px; height: auto;">
                </div>
                <h1 style="font-family: 'Audiowide', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; letter-spacing: -0.02em;">Déverrouiller le coffre</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Entrez vos deux mots de passe pour continuer</p>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 32px; justify-content: center;">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(33,126,170,0.12); border: 1px solid var(--accent); display: flex; align-items: center; justify-content: center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div style="flex: 1; max-width: 56px; height: 1px; background: var(--accent);"></div>
                <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-bright)); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; box-shadow: 0 0 16px rgba(45,159,212,0.3);">2</div>
            </div>

            <div class="card">

                <div style="background: rgba(15,30,42,0.6); border: 1px solid rgba(33,126,170,0.2); border-radius: 10px; padding: 12px 16px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span style="font-size: 0.875rem; color: var(--text-primary);">{{ session('login_email') }}</span>
                    </div>
                    <a href="{{ route('connexion') }}" style="font-size: 0.8rem; color: var(--accent-bright); text-decoration: none; font-weight: 600;">Modifier</a>
                </div>

                <form method="POST" action="{{ route('connexion.password.post') }}">
                    @csrf

                    <div style="margin-bottom: 16px;">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative;">
                            <input
                                :type="showPass ? 'text' : 'password'"
                                id="password" name="password"
                                class="input @error('password') input-error @enderror"
                                placeholder="••••••••"
                                autocomplete="current-password" autofocus
                                style="padding-right: 46px;"
                            >
                            <button type="button" @click="showPass = !showPass" class="eye-btn">
                                <svg x-show="!showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                        <div style="flex: 1; height: 1px; background: rgba(33,126,170,0.2);"></div>
                        <span style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; letter-spacing: 0.04em;">Clé de chiffrement</span>
                        <div style="flex: 1; height: 1px; background: rgba(33,126,170,0.2);"></div>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px;">
                            <label style="margin-bottom: 0;">Master Password</label>
                            <span style="font-size: 0.7rem; color: var(--accent-bright); background: rgba(33,126,170,0.12); padding: 2px 9px; border-radius: 20px; border: 1px solid rgba(45,159,212,0.25);">
                            Déverrouille le coffre
                        </span>
                        </div>
                        <div style="position: relative;">
                            <input
                                :type="showMaster ? 'text' : 'password'"
                                id="master_password" name="master_password"
                                class="input @error('master_password') input-error @enderror"
                                placeholder="••••••••"
                                autocomplete="off"
                                style="padding-right: 46px;"
                            >
                            <button type="button" @click="showMaster = !showMaster" class="eye-btn">
                                <svg x-show="!showMaster" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showMaster" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('master_password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="background: rgba(33,126,170,0.08); border: 1px solid rgba(45,159,212,0.18); border-radius: 9px; padding: 11px 14px; margin-bottom: 20px; display: flex; gap: 9px; align-items: flex-start;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2" style="flex-shrink:0; margin-top:2px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <p style="font-size: 0.78rem; color: var(--text-secondary); margin: 0; line-height: 1.55;">
                            Le master password <strong style="color: var(--text-primary);">déchiffre localement</strong> votre coffre. Il n'est jamais envoyé à nos serveurs.
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 22px;">
                        <input type="checkbox" id="remember" name="remember" style="width: 15px; height: 15px; accent-color: var(--accent); cursor: pointer;">
                        <label for="remember" style="margin: 0; font-size: 0.875rem; cursor: pointer; color: var(--text-secondary); font-weight: 400;">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 13px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        Déverrouiller le coffre
                    </button>
                </form>
            </div>

            <p style="text-align: center; margin-top: 22px; font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.04em;">
                AES-256-GCM · Argon2id · Zero-knowledge
            </p>
        </div>
    </div>
@endsection
