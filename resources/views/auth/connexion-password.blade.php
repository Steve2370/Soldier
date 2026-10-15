@extends('layouts.public')
@section('title', 'Connexion Mot de passe')

@section('content')
    <div class="s-auth">
        <x-soldier.auth-panel :emblem="false" kicker="" title="UNE CLÉ POUR ENTRER" neon="UNE POUR OUVRIR">
            <div class="s-unlock" aria-hidden="true">
                <span class="s-pulse"></span>
                <svg viewBox="0 0 64 64"><path class="s-shackle" d="M20 30V20a12 12 0 0 1 24 0v10"/><rect x="12" y="30" width="40" height="28" rx="5"/><circle cx="32" cy="42" r="4"/><path d="M32 46v5"/></svg>
            </div>
            <ul class="s-terminal" style="max-width: 380px; width: 100%;" aria-hidden="true">
                <li><span class="pr">&gt;</span> identité vérifiée <span class="ok">[ok]</span></li>
                <li><span class="pr">&gt;</span> argon2id · dérivation de la clé <span class="ok">[en attente]</span></li>
                <li><span class="pr">&gt;</span> coffre scellé<span class="s-caret"></span></li>
            </ul>
        </x-soldier.auth-panel>

        <div class="s-auth-main">
            <div class="s-auth-box s-orbit-border" x-data="{ showPass: false, showMaster: false }">
                <ol class="s-steps" aria-label="Étapes de connexion">
                    <li class="s-step is-done"><span class="s-step-num"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></span>Email</li>
                    <li class="s-step-line is-done" aria-hidden="true"></li>
                    <li class="s-step is-current"><span class="s-step-num">2</span>Déverrouillage</li>
                </ol>

                <div>
                    <h1 style="font-size: 1.65rem; font-weight: 400; color: var(--text-primary); margin-bottom: 10px;">Déverrouiller le coffre</h1>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Entrez vos deux mots de passe pour continuer</p>
                </div>

                <div style="background: rgba(4,10,16,0.9); border: 1px solid #22364a; border-radius: 999px; padding: 6px 6px 6px 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2" style="flex-shrink: 0;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span class="s-mono" style="font-size: 0.85rem; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ session('login_email') }}</span>
                    </div>
                    <a href="{{ route('connexion') }}" style="font-size: 0.75rem; color: var(--accent-bright); text-decoration: none; padding: 8px 12px;">Modifier</a>
                </div>

                <form method="POST" action="{{ route('connexion.password.post') }}">
                    @csrf

                    <div style="margin-bottom: 18px;">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative;">
                            <input
                                :type="showPass ? 'text' : 'password'"
                                id="password" name="password"
                                class="input @error('password') input-error @enderror"
                                placeholder="••••••••"
                                autocomplete="current-password" autofocus
                                style="padding-right: 50px;"
                            >
                            <button type="button" @click="showPass = !showPass" class="eye-btn" aria-label="Afficher ou masquer le mot de passe">
                                <svg x-show="!showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="s-keybox" style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <label for="master_password" style="margin-bottom: 0; color: var(--neon-soft);">Master Password</label>
                            <span class="s-tag" style="font-size: 10px; color: var(--neon-soft);">Déverrouille le coffre</span>
                        </div>
                        <div style="position: relative;">
                            <input
                                :type="showMaster ? 'text' : 'password'"
                                id="master_password" name="master_password"
                                class="input @error('master_password') input-error @enderror"
                                placeholder="••••••••"
                                autocomplete="off"
                                style="padding-right: 50px; border-color: rgba(61,245,192,0.35);"
                            >
                            <button type="button" @click="showMaster = !showMaster" class="eye-btn" aria-label="Afficher ou masquer le master password">
                                <svg x-show="!showMaster" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showMaster" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('master_password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px; line-height: 1.6;">
                            Le master password sert à déverrouiller votre coffre. Il n'est pas conservé en clair après la requête.
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; min-height: 32px;">
                        <input type="checkbox" id="remember" name="remember" style="width: 17px; height: 17px; accent-color: var(--accent); cursor: pointer;">
                        <label for="remember" style="margin: 0; font-size: 0.82rem; cursor: pointer; color: var(--text-secondary);">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; min-height: 50px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        Déverrouiller le coffre
                    </button>
                </form>

                <p style="text-align: center; font-size: 0.8rem;"><a href="{{ route('connexion') }}" style="color: var(--text-muted); text-decoration: none;">← Retour</a></p>
            </div>
        </div>
    </div>
@endsection
