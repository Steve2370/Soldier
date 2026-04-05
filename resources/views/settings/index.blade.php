@extends('layouts.app')
@section('title', 'Paramètres')

@section('content')
    <div x-data="settings('{{ session('tab', 'securite') }}', {{ $errors->has('master_password') ? 'true' : 'false' }})">

        <div style="margin-bottom: 28px;">
            <h1 style="font-size: 1.625rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">Paramètres</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Gérez la sécurité et les préférences de votre compte</p>
        </div>

        <div style="display: flex; gap: 6px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 0;">
            @foreach(['securite' => 'Sécurité', 'compte' => 'Compte'] as $id => $label)
                <button
                    @click="onglet = '{{ $id }}'
                    :class="onglet === '{{ $id }}' ? 'onglet-active' : 'onglet'"
                    style="font-family: 'Audiowide', sans-serif;"
                >{{ $label }}</button>
            @endforeach
        </div>

        <div x-show="onglet === 'securite'" x-transition>

            <div class="card" style="border-color: var(--border-bright); margin-bottom: 14px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; gap: 14px; align-items: flex-start; flex: 1;">
                        <div style="width: 42px; height: 42px; background: {{ $mfaEmail?->actif ? 'var(--accent-dim)' : 'var(--bg-elevated)' }}; border: 1px solid {{ $mfaEmail?->actif ? 'var(--border-bright)' : 'var(--border)' }}; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="{{ $mfaEmail?->actif ? 'var(--accent-bright)' : 'var(--text-muted)' }}" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">MFA par email</span>
                                @if($mfaEmail?->actif)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-warning">Inactif</span>
                                @endif
                            </div>
                            <p style="font-size: 0.8125rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                                Reçois un code à 6 chiffres par email à chaque connexion. Activé par défaut.
                            </p>
                        </div>
                    </div>

                    @if(!$mfaEmail?->actif)
                        <form method="POST" action="{{ route('settings.mfa.email.activer') }}" style="flex-shrink: 0;">
                            @csrf
                            <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.8125rem;">Activer</button>
                        </form>
                    @else
                        <button @click="showDesactiverEmail = true" class="btn-secondary" style="padding: 8px 16px; font-size: 0.8125rem; flex-shrink: 0;">Désactiver</button>
                    @endif
                </div>

                <div x-show="showDesactiverEmail" x-transition style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                    <form method="POST" action="{{ route('settings.mfa.email.desactiver') }}">
                        @csrf
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin-bottom: 12px;">
                            Confirmez avec votre master password pour désactiver le MFA.
                        </p>
                        <div style="display: flex; gap: 8px; align-items: flex-start;">
                            <div style="flex: 1;">
                                <input type="password" name="master_password"
                                       class="input @error('master_password') input-error @enderror"
                                       placeholder="Master password" autocomplete="off">
                                @error('master_password') <p class="error-msg" style="margin-top: 4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-danger" style="padding: 10px 14px; white-space: nowrap;">Confirmer</button>
                            <button type="button" @click="showDesactiverEmail = false" class="btn-secondary" style="padding: 10px 14px;">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card" style="border-color: var(--border-bright); margin-bottom: 14px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; gap: 14px; align-items: flex-start; flex: 1;">
                        <div style="width: 42px; height: 42px; background: {{ $mfaTotp?->actif ? 'var(--accent-dim)' : 'var(--bg-elevated)' }}; border: 1px solid {{ $mfaTotp?->actif ? 'var(--border-bright)' : 'var(--border)' }}; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="{{ $mfaTotp?->actif ? 'var(--accent-bright)' : 'var(--text-muted)' }}" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">Authenticator (TOTP)</span>
                                @if($mfaTotp?->actif)
                                    <span class="badge badge-success">Actif</span>
                                @endif
                            </div>
                            <p style="font-size: 0.8125rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                                Google Authenticator, Authy, 1Password. Code qui change toutes les 30 secondes.
                            </p>
                        </div>
                    </div>
                    @if(!$mfaTotp?->actif)
                        <button @click="configurerTotp('{{ route('settings.totp.configurer') }}')" class="btn-secondary" style="padding: 8px 16px; font-size: 0.8125rem; flex-shrink: 0;">Configurer</button>
                    @else
                        <form method="POST" action="{{ route('settings.totp.desactiver') }}">
                            @csrf
                            <button type="submit" class="btn-secondary" style="padding: 8px 16px; font-size: 0.8125rem; flex-shrink: 0; color: var(--danger); border-color: rgba(239,68,68,0.3);">
                                Désactiver
                            </button>
                        </form>
                    @endif
                </div>

                <div x-show="showTotpSetup" x-transition style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--border);">
                    <div style="display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: start;">
                        <div>
                            <div style="width: 160px; height: 160px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid var(--border-bright);">
                                <img x-show="totpQrUrl" :src="totpQrUrl" style="width: 150px; height: 150px;">
                                <div x-show="!totpQrUrl" style="color: var(--text-muted); font-size: 0.75rem; text-align: center; padding: 10px;">Chargement...</div>
                            </div>
                            <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 8px; text-align: center; max-width: 160px;">Scannez avec votre app authenticator</p>
                        </div>

                        <div>
                            <div style="margin-bottom: 14px;">
                                <label style="margin-bottom: 6px;">Clé secrète (saisie manuelle)</label>
                                <div style="display: flex; gap: 6px;">
                                    <code style="background: var(--bg-base); border: 1px solid var(--border-bright); border-radius: 8px; padding: 8px 12px; font-size: 0.8rem; color: var(--accent-bright); flex: 1; word-break: break-all;" x-text="totpSecret"></code>
                                    <button @click="copierTotp()" class="btn-secondary" style="padding: 8px 10px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.totp.valider') }}">
                                @csrf
                                <label>Code de vérification</label>
                                <div style="display: flex; gap: 8px; margin-top: 6px;">
                                    <input type="text" name="code" maxlength="6"
                                           class="input" placeholder="000000"
                                           style="font-family: 'DM Mono', monospace; letter-spacing: 0.3em; font-size: 1.1rem; text-align: center; max-width: 140px;">
                                    <button type="submit" class="btn-primary" style="padding: 10px 16px; font-size: 0.8375rem;">Vérifier</button>
                                    <button type="button" @click="showTotpSetup = false" class="btn-secondary" style="padding: 10px 12px;">✕</button>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Entrez le code affiché dans votre app pour confirmer la configuration.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="border-color: var(--border-bright); margin-bottom: 14px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; gap: 14px; align-items: flex-start; flex: 1;">
                        <div style="width: 42px; height: 42px; background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">Passkeys</span>
                                @if($passkeys->isNotEmpty())
                                    <span class="badge badge-success">{{ $passkeys->count() }} actif{{ $passkeys->count() > 1 ? "s" : "" }}</span>
                                @else
                                    <span class="badge badge-info">Optionnel</span>
                                @endif
                            </div>
                            <p style="font-size: 0.8125rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                                Touch ID, Face ID, clé USB YubiKey. Connexion sans mot de passe, résistant au phishing.
                            </p>
                        </div>
                    </div>
                    <button onclick="inscrirePasskey()" class="btn-primary" style="padding: 8px 16px; font-size: 0.8125rem; flex-shrink: 0;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Ajouter
                    </button>
                </div>

                @if($passkeys->isNotEmpty())
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); display: flex; flex-direction: column; gap: 8px;">
                        @foreach($passkeys as $passkey)
                            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: var(--bg-elevated); border-radius: 10px; border: 1px solid var(--border);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><circle cx="12" cy="16" r="1" fill="var(--accent-bright)"/></svg>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">{{ $passkey->nom }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Dernière utilisation : {{ $passkey->derniere_utilisation?->diffForHumans() ?? "Jamais" }}</div>
                                </div>
                                <form method="POST" action="{{ route("passkeys.supprimer", $passkey->id) }}">
                                    @csrf @method("DELETE")
                                    <button type="submit" style="background: none; border: none; color: var(--danger); cursor: pointer; padding: 4px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card" style="border-color: var(--border-bright); margin-top: 20px;">
                <h3 style="font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Changer le Master Password
                </h3>
                <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;">
                    Seule la KEK sera re-chiffrée. Vos coffres et mots de passe <strong style="color: var(--text-secondary);">ne sont pas re-chiffrés</strong> — l'opération est instantanée.
                </p>

                <form method="POST" action="{{ route('settings.mot-de-passe') }}" x-data="{ show1: false, show2: false, show3: false }">
                    @csrf

                    <div style="margin-bottom: 14px;">
                        <label>Ancien Master Password</label>
                        <div style="position: relative;">
                            <input :type="show1 ? 'text' : 'password'" name="ancien_master_password"
                                   class="input @error('ancien_master_password') input-error @enderror"
                                   placeholder="••••••••" autocomplete="off" style="padding-right: 44px;">
                            <button type="button" @click="show1 = !show1" class="eye-btn">
                                <svg x-show="!show1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show1"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('ancien_master_password') <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label>Nouveau Master Password</label>
                        <div style="position: relative;">
                            <input :type="show2 ? 'text' : 'password'" name="nouveau_master_password"
                                   class="input" placeholder="••••••••" autocomplete="new-password" style="padding-right: 44px;">
                            <button type="button" @click="show2 = !show2" class="eye-btn">
                                <svg x-show="!show2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show2"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label>Confirmer le nouveau Master Password</label>
                        <div style="position: relative;">
                            <input :type="show3 ? 'text' : 'password'" name="nouveau_master_password_confirmation"
                                   class="input" placeholder="••••••••" autocomplete="new-password" style="padding-right: 44px;">
                            <button type="button" @click="show3 = !show3" class="eye-btn">
                                <svg x-show="!show3" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show3"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Changer le Master Password
                    </button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-bottom: 14px;">
            <h3 style="font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Photo de profil
            </h3>

            <div style="display: flex; align-items: center; gap: 20px;" x-data="avatarUpload()">

                <div style="position: relative; flex-shrink: 0;">
                    <div style="width: 88px; height: 88px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border-bright); display: flex; align-items: center; justify-content: center; background: var(--accent); font-weight: 800; font-size: 1.25rem; color: #fff; flex-shrink: 0;">
                        @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div x-show="uploading" style="position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </div>
                </div>

                <div style="flex: 1;">
                    <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 12px; line-height: 1.5;">
                        JPG, PNG, Webp — max 2 Mo. Visible dans la sidebar et les partages.
                    </p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <form method="POST" action="{{ route('settings.avatar') }}" enctype="multipart/form-data" @submit="uploading = true">
                            @csrf
                            <label style="cursor: pointer; margin: 0;">
                                <input type="file" name="avatar" accept="image/*" style="display: none;"
                                       @change="previewFile($event); $el.closest('form').submit()">
                                <span class="btn-secondary" style="font-size: 0.8375rem; padding: 8px 14px; display: inline-flex; align-items: center; gap: 7px; cursor: pointer;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Choisir une photo
                        </span>
                            </label>
                        </form>

                        @if(auth()->user()->avatar)
                            <form method="POST" action="{{ route('settings.avatar.supprimer') }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-secondary" style="font-size: 0.8375rem; padding: 8px 14px; color: var(--danger); border-color: rgba(239,68,68,0.3);">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        </style>

        <div x-show="onglet === 'compte'" x-transition>

            <div class="card" style="border-color: var(--border-bright); margin-bottom: 14px;">
                <h3 style="font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); margin-bottom: 18px;">Informations du compte</h3>

                <div style="display: flex; align-items: center; gap: 14px; padding: 14px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border-bright); display: flex; align-items: center; justify-content: center; background: var(--accent); font-weight: 800; font-size: 1.25rem; color: #fff; flex-shrink: 0;">
                        @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ auth()->user()->name }}</div>
                        <div style="font-size: 0.8125rem; color: var(--text-muted);">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 14px;">Changer le mot de passe de connexion</h4>
                <form method="POST" action="{{ route('settings.mot-de-passe.compte') }}" x-data="{ s1:false, s2:false }">
                    @csrf
                    <div style="margin-bottom: 14px;">
                        <label>Mot de passe actuel</label>
                        <div style="position: relative;">
                            <input :type="s1 ? 'text' : 'password'" name="mot_de_passe_actuel"
                                   class="input @error('mot_de_passe_actuel') input-error @enderror"
                                   placeholder="••••••••" style="padding-right: 44px;">
                            <button type="button" @click="s1=!s1" class="eye-btn"><svg x-show="!s1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><svg x-show="s1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button>
                        </div>
                        @error('mot_de_passe_actuel') <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p> @enderror
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label>Nouveau mot de passe</label>
                        <div style="position: relative;">
                            <input :type="s2 ? 'text' : 'password'" name="nouveau_mot_de_passe"
                                   class="input" x-model="nouveauMdp" placeholder="••••••••" autocomplete="new-password" style="padding-right: 44px;">
                            <button type="button" @click="s2=!s2" class="eye-btn">...</button>
                        </div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label>Confirmer le nouveau mot de passe</label>
                        <div style="position: relative;">
                            <input :type="s2 ? 'text' : 'password'" name="nouveau_mot_de_passe_confirmation"
                                   class="input" placeholder="••••••••" autocomplete="new-password" style="padding-right: 44px;">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="font-size: 0.875rem;">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .onglet, .onglet-active {
            padding: 10px 18px;
            font-size: 0.875rem; font-weight: 600;
            cursor: pointer; border: none; background: none;
            font-family: 'DM Sans', sans-serif;
            border-bottom: 2px solid transparent;
            color: var(--text-muted);
            transition: all 0.15s;
            margin-bottom: -1px;
        }
        .onglet:hover    { color: var(--text-primary); }
        .onglet-active   { color: var(--accent-bright); border-bottom-color: var(--accent-bright); }

        .eye-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: var(--text-muted); cursor: pointer;
            padding: 2px; transition: color 0.15s;
        }
        .eye-btn:hover { color: var(--accent-bright); }
        .input-error { border-color: #ef4444 !important; }
    </style>

@endsection
