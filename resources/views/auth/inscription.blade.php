@extends('layouts.public')
@section('title', 'Inscription')

@section('content')
    <div style="min-height: calc(100vh - 88px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%; max-width: 440px;" x-data="inscriptionForm()">

            <div style="text-align: center; margin-bottom: 40px;">
                <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <img src="{{ asset('assets/Soldier-Logo.png') }}"
                         alt="Logo"
                         style="width: 120px; height: auto;">
                </div>
                <h1 style="font-family: 'Audiowide', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; letter-spacing: -0.02em;">Créer votre compte</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Votre coffre chiffré en 30 secondes</p>
            </div>

            <div class="card">
                <form method="POST" action="{{ route('inscription.post') }}">
                    @csrf

                    <div style="margin-bottom: 16px;">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" name="name"
                               class="input @error('name') input-error @enderror"
                               value="{{ old('name') }}"
                               placeholder="Livaï Ackerman"
                               autocomplete="name" autofocus>
                        @error('name')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email"
                               class="input @error('email') input-error @enderror"
                               value="{{ old('email') }}"
                               placeholder="sergent@soldier.com"
                               autocomplete="email">
                        @error('email')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative;">
                            <input :type="showPass ? 'text' : 'password'" id="password" name="password"
                                   class="input @error('password') input-error @enderror"
                                   placeholder="••••••••" autocomplete="new-password"
                                   style="padding-right: 44px;">
                            <button type="button" @click="showPass = !showPass" class="eye-btn">
                                <svg x-show="!showPass" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPass" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="margin-bottom: 22px;">
                        <label for="password_confirmation">Confirmer le mot de passe</label>
                        <div style="position: relative;">
                            <input :type="showPassConfirm ? 'text' : 'password'"
                                   id="password_confirmation" name="password_confirmation"
                                   class="input" placeholder="••••••••"
                                   autocomplete="new-password" style="padding-right: 44px;">
                            <button type="button" @click="showPassConfirm = !showPassConfirm" class="eye-btn">
                                <svg x-show="!showPassConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPassConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
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
                            Chiffre votre coffre
                        </span>
                        </div>
                        <div style="position: relative;">
                            <input
                                :type="showMaster ? 'text' : 'password'"
                                id="master_password" name="master_password"
                                class="input @error('master_password') input-error @enderror"
                                placeholder="••••••••" autocomplete="off"
                                style="padding-right: 44px;"
                                x-model="masterPassword"
                                @input="calculerEntropie()"
                            >
                            <button type="button" @click="showMaster = !showMaster" class="eye-btn">
                                <svg x-show="!showMaster" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showMaster" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('master_password')
                        <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="masterPassword.length > 0" style="margin-bottom: 16px;" x-transition>
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
                        <p x-show="conseil" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 5px;" x-text="conseil"></p>
                    </div>

                    <div style="background: rgba(33,126,170,0.07); border: 1px solid rgba(45,159,212,0.2); border-radius: 9px; padding: 12px 14px; margin-bottom: 22px; display: flex; gap: 10px; align-items: flex-start;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2" style="flex-shrink:0; margin-top:2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <p style="font-size: 0.78rem; color: var(--text-secondary); margin: 0; line-height: 1.55;">
                            Le master password <strong style="color: var(--text-primary);">n'est jamais stocké</strong> sur nos serveurs. Si vous le perdez, vos données sont <strong style="color: var(--text-primary);">irrécupérables</strong>.
                        </p>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 13px;" onclick="this.disabled=true; this.closest('form').submit();">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Créer mon compte
                    </button>
                </form>

                <div class="divider"></div>

                <p style="text-align: center; font-size: 0.875rem; color: var(--text-secondary);">
                    Déjà un compte ?
                    <a href="{{ route('connexion') }}" style="color: var(--accent-bright); font-weight: 600; text-decoration: none;">Se connecter →</a>
                </p>
            </div>

            <p style="text-align: center; margin-top: 22px; font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.04em;">
                AES-256-GCM · Argon2id · RSA-4096 · Zero-knowledge
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            function inscriptionForm() {
                return {
                    masterPassword: '',
                    showPass: false,
                    showPassConfirm: false,
                    showMaster: false,
                    entropie: 0,
                    forceSegments: 0,
                    forceLabel: '',
                    forceColor: 'rgba(33,126,170,0.4)',
                    conseil: '',

                    calculerEntropie() {
                        const mdp = this.masterPassword;
                        let alphabet = 0;
                        if (/[a-z]/.test(mdp)) alphabet += 26;
                        if (/[A-Z]/.test(mdp)) alphabet += 26;
                        if (/[0-9]/.test(mdp)) alphabet += 10;
                        if (/[^a-zA-Z0-9]/.test(mdp)) alphabet += 32;
                        this.entropie = alphabet > 0 ? Math.round(mdp.length * Math.log2(alphabet)) : 0;

                        if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = 'rgba(33,126,170,0.2)'; this.conseil = ''; }
                        else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ef4444'; this.conseil = 'Ajoutez des majuscules, chiffres et symboles.'; }
                        else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#f97316'; this.conseil = 'Allongez votre master password.'; }
                        else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f59e0b'; this.conseil = 'Bon début, visez plus de 80 bits.'; }
                        else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = 'var(--accent-bright)'; this.conseil = 'Quelques caractères de plus pour "Très fort".'; }
                        else { this.forceSegments = 5; this.forceLabel = 'Très fort ✓'; this.forceColor = '#2d9fd4'; this.conseil = 'Excellent ! Cryptographiquement solide.'; }
                    }
                }
            }
        </script>
    @endpush
@endsection
