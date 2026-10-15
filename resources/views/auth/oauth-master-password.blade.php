@extends('layouts.public')
@section('title', 'Déverrouiller votre coffre')

@section('content')
    <div class="s-centered">
        <x-soldier.backdrop :locks="5" />

        <div class="card s-orbit-border s-orbit-neon s-boot" style="width: 100%; max-width: 480px; padding: 38px; border-radius: 16px; border-color: transparent; display: flex; flex-direction: column; gap: 22px;"
             x-data="{ showMaster: false, showConfirm: false, masterPassword: '', confirm: '', entropie: 0, forceSegments: 0, forceLabel: '', forceColor: '#16283a',
        calculerEntropie() {
            const mdp = this.masterPassword;
            let alphabet = 0;
            if (/[a-z]/.test(mdp)) alphabet += 26;
            if (/[A-Z]/.test(mdp)) alphabet += 26;
            if (/[0-9]/.test(mdp)) alphabet += 10;
            if (/[^a-zA-Z0-9]/.test(mdp)) alphabet += 32;
            this.entropie = alphabet > 0 ? Math.round(mdp.length * Math.log2(alphabet)) : 0;
            if (this.entropie === 0)      { this.forceSegments = 0; this.forceLabel = '';            this.forceColor = '#16283a'; }
            else if (this.entropie < 40)  { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ff5a64'; }
            else if (this.entropie < 60)  { this.forceSegments = 2; this.forceLabel = 'Faible';      this.forceColor = '#ff8a50'; }
            else if (this.entropie < 80)  { this.forceSegments = 3; this.forceLabel = 'Moyen';       this.forceColor = '#f2b544'; }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort';        this.forceColor = '#4fc3f7'; }
            else                          { this.forceSegments = 5; this.forceLabel = 'Très fort ✓'; this.forceColor = '#3df5c0'; }
        }
    }">

            <div style="display: flex; align-items: center; gap: 14px;" aria-hidden="true">
                <span style="width: 44px; height: 44px; border-radius: 12px; background: #0e1823; border: 1px solid #24394e; display: grid; place-items: center; overflow: hidden; flex-shrink: 0;">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span style="font-size: 0.85rem; color: #8fd8ff;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    @endif
                </span>
                <span class="s-step-line" style="height: 2px;"></span>
                <span class="s-logo-ring"><img src="{{ asset('assets/Soldier-Logo.png') }}" alt="" style="width: 44px; height: 44px; border-radius: 50%;"></span>
            </div>

            <div>
                @if(session('oauth_new_user'))
                    <h1 style="font-size: 1.55rem; font-weight: 400; color: var(--text-primary); margin-bottom: 10px;">
                        Créer votre Master Password
                    </h1>
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.6;">
                        Bienvenue <strong class="s-neon" style="font-weight: 400;">{{ auth()->user()->name }}</strong> !<br>
                        Choisissez un master password pour chiffrer votre coffre.
                    </p>
                @else
                    <h1 style="font-size: 1.55rem; font-weight: 400; color: var(--text-primary); margin-bottom: 10px;">
                        Déverrouiller le coffre
                    </h1>
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.6;">
                        Connecté en tant que <strong class="s-mono" style="color: var(--text-primary); font-weight: 400;">{{ auth()->user()->email }}</strong><br>
                        Entrez votre master password pour accéder à votre coffre.
                    </p>
                @endif
            </div>

            <form method="POST" action="{{ route('oauth.master-password.post') }}">
                @csrf

                <div class="s-keybox" style="margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <label for="oauth_master_password" style="margin-bottom: 0; color: var(--neon-soft);">Master Password</label>
                        <span class="s-tag" style="font-size: 10px; color: var(--neon-soft);">
                            @if(session('oauth_new_user')) Chiffre votre coffre @else Déverrouille le coffre @endif
                        </span>
                    </div>
                    <div style="position: relative;">
                        <input :type="showMaster ? 'text' : 'password'"
                               id="oauth_master_password"
                               name="master_password"
                               class="input @error('master_password') input-error @enderror"
                               placeholder="••••••••"
                               autocomplete="off"
                               autofocus
                               x-model="masterPassword"
                               @input="calculerEntropie()"
                               style="padding-right: 48px; border-color: rgba(61,245,192,0.35);">
                        <button type="button" @click="showMaster = !showMaster" class="eye-btn" aria-label="Afficher ou masquer le master password">
                            <svg x-show="!showMaster" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showMaster" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('master_password')
                    <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                    @enderror

                    {{-- Barre de force — seulement pour nouveau user --}}
                    @if(session('oauth_new_user'))
                        <div x-show="masterPassword.length > 0" style="margin-top: 12px;" x-transition>
                            <div style="display: flex; gap: 4px; height: 5px; margin-bottom: 8px;">
                                <template x-for="i in 5" :key="i">
                                    <div style="flex: 1; border-radius: 3px; transition: background 0.35s, box-shadow 0.35s;"
                                         :style="i <= forceSegments ? 'background: ' + forceColor + '; box-shadow: 0 0 10px ' + forceColor : 'background: #16283a'">
                                    </div>
                                </template>
                            </div>
                            <div class="s-mono" style="display: flex; justify-content: space-between; font-size: 0.72rem;">
                                <span style="color: var(--text-muted);">Entropie : <span :style="'color:' + forceColor" x-text="entropie + ' bits'"></span></span>
                                <span :style="'color:' + forceColor" x-text="forceLabel"></span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Confirmation — seulement pour nouveau user --}}
                @if(session('oauth_new_user'))
                    <div style="margin-bottom: 18px;">
                        <label for="oauth_master_password_confirmation">Confirmer le Master Password</label>
                        <div style="position: relative;">
                            <input :type="showConfirm ? 'text' : 'password'"
                                   id="oauth_master_password_confirmation"
                                   name="master_password_confirmation"
                                   class="input"
                                   placeholder="••••••••"
                                   autocomplete="off"
                                   style="padding-right: 48px;">
                            <button type="button" @click="showConfirm = !showConfirm" class="eye-btn" aria-label="Afficher ou masquer la confirmation">
                                <svg x-show="!showConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                <div style="display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 9px; background: rgba(4,10,16,0.9); border: 1px solid var(--border); margin-bottom: 20px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2" style="flex-shrink:0; margin-top:2px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0; line-height: 1.6;">
                        Le master password sert à déverrouiller votre coffre. Il n'est pas conservé en clair après la requête.
                    </p>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; min-height: 50px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    @if(session('oauth_new_user')) Créer mon coffre @else Déverrouiller @endif
                </button>
            </form>

            <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                <a href="{{ route('deconnexion') }}" style="color: var(--text-muted); text-decoration: none;"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    ← Changer de compte
                </a>
            </p>
            <form id="logout-form" method="POST" action="{{ route('deconnexion') }}" style="display:none;">@csrf</form>
        </div>

        <p class="s-mono" style="font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.12em;">
            AES-256-GCM · ARGON2ID · SESSION SÉCURISÉE
        </p>
    </div>
@endsection
