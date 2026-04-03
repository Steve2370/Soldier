@extends('layouts.public')
@section('title', 'Déverrouiller votre coffre')

@section('content')
    <div style="min-height: calc(100vh - 88px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%; max-width: 420px;" x-data="{ showMaster: false, showConfirm: false, masterPassword: '', confirm: '', entropie: 0, forceSegments: 0, forceLabel: '', forceColor: 'rgba(33,126,170,0.4)',
        calculerEntropie() {
            const mdp = this.masterPassword;
            let alphabet = 0;
            if (/[a-z]/.test(mdp)) alphabet += 26;
            if (/[A-Z]/.test(mdp)) alphabet += 26;
            if (/[0-9]/.test(mdp)) alphabet += 10;
            if (/[^a-zA-Z0-9]/.test(mdp)) alphabet += 32;
            this.entropie = alphabet > 0 ? Math.round(mdp.length * Math.log2(alphabet)) : 0;
            if (this.entropie === 0)      { this.forceSegments = 0; this.forceLabel = '';            this.forceColor = 'rgba(33,126,170,0.2)'; }
            else if (this.entropie < 40)  { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ef4444'; }
            else if (this.entropie < 60)  { this.forceSegments = 2; this.forceLabel = 'Faible';      this.forceColor = '#f97316'; }
            else if (this.entropie < 80)  { this.forceSegments = 3; this.forceLabel = 'Moyen';       this.forceColor = '#f59e0b'; }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort';        this.forceColor = 'var(--accent-bright)'; }
            else                          { this.forceSegments = 5; this.forceLabel = 'Très fort ✓'; this.forceColor = '#2d9fd4'; }
        }
    }">

            <div style="text-align: center; margin-bottom: 36px;">
                <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Logo" style="width: 100px; height: auto;">
                </div>

                @if(session('oauth_new_user'))
                    <h1 style="font-family: 'Audiowide', sans-serif; font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">
                        Créer votre Master Password
                    </h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
                        Bienvenue <strong style="color: var(--text-primary);">{{ auth()->user()->name }}</strong> !<br>
                        Choisissez un master password pour chiffrer votre coffre.
                    </p>
                @else
                    <h1 style="font-family: 'Audiowide', sans-serif; font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">
                        Déverrouiller le coffre
                    </h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
                        Connecté en tant que <strong style="color: var(--text-primary);">{{ auth()->user()->email }}</strong><br>
                        Entrez votre master password pour accéder à votre coffre.
                    </p>
                @endif
            </div>

            <div class="card">
                <form method="POST" action="{{ route('oauth.master-password.post') }}">
                    @csrf

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px;">
                            <label style="margin-bottom: 0;">Master Password</label>
                            <span style="font-size: 0.7rem; color: var(--accent-bright); background: rgba(33,126,170,0.12); padding: 2px 9px; border-radius: 20px; border: 1px solid rgba(45,159,212,0.25);">
                            @if(session('oauth_new_user')) Chiffre votre coffre @else Déverrouille le coffre @endif
                        </span>
                        </div>
                        <div style="position: relative;">
                            <input :type="showMaster ? 'text' : 'password'"
                                   name="master_password"
                                   class="input @error('master_password') input-error @enderror"
                                   placeholder="••••••••"
                                   autocomplete="off"
                                   autofocus
                                   x-model="masterPassword"
                                   @input="calculerEntropie()"
                                   style="padding-right: 46px;">
                            <button type="button" @click="showMaster = !showMaster" class="eye-btn">
                                <svg x-show="!showMaster" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showMaster" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('master_password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Barre de force — seulement pour nouveau user --}}
                    @if(session('oauth_new_user'))
                        <div x-show="masterPassword.length > 0" style="margin-bottom: 18px;" x-transition>
                            <div style="display: flex; gap: 3px; height: 4px; margin-bottom: 6px;">
                                <template x-for="i in 5" :key="i">
                                    <div style="flex: 1; border-radius: 3px; transition: background 0.3s;"
                                         :style="'background: ' + (i <= forceSegments ? forceColor : 'rgba(33,126,170,0.15)')">
                                    </div>
                                </template>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.72rem;">
                                <span style="color: var(--text-muted);">Entropie : <span :style="'color:' + forceColor" x-text="entropie + ' bits'"></span></span>
                                <span :style="'color:' + forceColor + '; font-weight: 700;'" x-text="forceLabel"></span>
                            </div>
                        </div>

                        {{-- Confirmation — seulement pour nouveau user --}}
                        <div style="margin-bottom: 18px;">
                            <label style="margin-bottom: 7px;">Confirmer le Master Password</label>
                            <div style="position: relative;">
                                <input :type="showConfirm ? 'text' : 'password'"
                                       name="master_password_confirmation"
                                       class="input"
                                       placeholder="••••••••"
                                       autocomplete="off"
                                       style="padding-right: 46px;">
                                <button type="button" @click="showConfirm = !showConfirm" class="eye-btn">
                                    <svg x-show="!showConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    <div style="background: rgba(33,126,170,0.08); border: 1px solid rgba(45,159,212,0.18); border-radius: 9px; padding: 11px 14px; margin-bottom: 22px; display: flex; gap: 9px; align-items: flex-start;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2" style="flex-shrink:0; margin-top:2px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <p style="font-size: 0.78rem; color: var(--text-secondary); margin: 0; line-height: 1.55;">
                            Le master password <strong style="color: var(--text-primary);">n'est jamais envoyé</strong> à nos serveurs. Il chiffre localement votre coffre.
                        </p>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 13px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        @if(session('oauth_new_user')) Créer mon coffre @else Déverrouiller @endif
                    </button>
                </form>

                <div class="divider"></div>

                <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                    <a href="{{ route('deconnexion') }}" style="color: var(--text-muted); text-decoration: none;"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        ← Changer de compte
                    </a>
                </p>
                <form id="logout-form" method="POST" action="{{ route('deconnexion') }}" style="display:none;">@csrf</form>
            </div>

            <p style="text-align: center; margin-top: 22px; font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.04em;">
                AES-256-GCM · Argon2id · Zero-knowledge
            </p>
        </div>
    </div>
@endsection
