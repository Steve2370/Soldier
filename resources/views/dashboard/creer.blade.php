@extends('layouts.app')
@section('title', 'Nouveau service')

@section('content')
    <div style="max-width: 1100px;" x-data="creerService(@js(['type' => old('type', 'login'), 'label' => old('label', ''), 'url' => old('url', '')]))">

        <a href="{{ route('dashboard') }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 40px; font-size: 0.8rem; color: var(--text-muted); text-decoration: none; margin-bottom: 14px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Mes services
        </a>

        <div class="s-page-head">
            <div>
                <span class="s-tag">// Nouvel élément</span>
                <h1 style="margin-top: 10px;">Nouveau service</h1>
                <p class="s-sub">Chiffré avec AES-256-GCM avant sauvegarde</p>
            </div>
            <span class="s-chip s-chip-ok"><span class="s-dot" aria-hidden="true"></span>Chiffrement actif</span>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
        <div class="card s-hud" style="flex: 999 1 560px; min-width: 0;">
            <form method="POST" action="{{ route('services.stocker') }}">
                @csrf

                @if(isset($coffres) && $coffres->count() > 1)
                    <div style="margin-bottom:20px;" x-data="{ coffreChoisi: '{{ $coffres->first()?->id }}' }">
                        <label>Enregistrer dans</label>
                        <div style="display:flex; gap:16px; margin-top:8px;">
                            @foreach($coffres as $coffre)
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;" @click="coffreChoisi = '{{ $coffre->id }}'">
                                    <input type="radio" name="coffre_id" value="{{ $coffre->id }}"
                                           {{ $loop->first ? 'checked' : '' }}
                                           x-model="coffreChoisi"
                                           style="display:none;">
                                    <div :style="coffreChoisi === '{{ $coffre->id }}' ? 'width:18px; height:18px; border-radius:50%; border:2px solid var(--accent); background:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;' : 'width:18px; height:18px; border-radius:50%; border:2px solid rgba(255,255,255,0.2); background:transparent; display:flex; align-items:center; justify-content:center; flex-shrink:0;'">
                                        <div x-show="coffreChoisi === '{{ $coffre->id }}'" style="width:8px; height:8px; border-radius:50%; background:#fff;"></div>
                                    </div>
                                    @if($coffre->id === $familyCoffreId)
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    @else
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    @endif
                                    <span style="font-size:0.875rem; font-weight:500; color:var(--text-primary);">{{ $coffre->nom }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <input type="hidden" name="coffre_id" value="{{ $coffres->first()?->id }}">
                @endif

                <div style="margin-bottom: 24px;">
                    <label>Type d'élément</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 8px;">

                        <button type="button" @click="type = 'login'" :class="type === 'login' ? 'type-btn type-btn-active' : 'type-btn'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Mot de passe</span>
                        </button>

                        <button type="button" @click="type = 'carte'" :class="type === 'carte' ? 'type-btn type-btn-active' : 'type-btn'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            <span>Carte</span>
                        </button>

                        <button type="button" @click="type = 'note'" :class="type === 'note' ? 'type-btn type-btn-active' : 'type-btn'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span>Note sécurisée</span>
                        </button>

                        <button type="button" @click="type = 'identite'" :class="type === 'identite' ? 'type-btn type-btn-active' : 'type-btn'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Pièce d'identité</span>
                        </button>

                        <button type="button" @click="type = 'cles'" :class="type === 'cles' ? 'type-btn type-btn-active' : 'type-btn'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                            <span>Clé SSH</span>
                        </button>

                        <button type="button" @click="type = 'autre'" :class="type === 'autre' ? 'type-btn type-btn-active' : 'type-btn'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                            <span>Autre</span>
                        </button>

                    </div>
                    <input type="hidden" name="type" :value="type">
                </div>

                <div style="margin-bottom: 18px;">
                    <label>Nom <span style="color: var(--danger);">*</span></label>
                    <div style="display: flex; gap: 10px; align-items: center;">

                        <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--bg-elevated); border: 1px solid rgba(79,195,247,0.3); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                            <img
                                x-show="faviconUrl"
                                :src="faviconUrl"
                                style="width: 28px; height: 28px; object-fit: contain;"
                                x-on:error="faviconUrl = ''"
                            >
                            <div x-show="!faviconUrl" style="color: var(--text-muted);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                        </div>

                        <input type="text" name="label"
                               class="input @error('label') input-error @enderror"
                               value="{{ old('label') }}"
                               :placeholder="nomPlaceholder"
                               x-model="label"
                               @input="mettreAJourFavicon()"
                               autocomplete="off"
                               style="flex: 1;">
                    </div>
                    @error('label')<p class="error-msg" style="margin-top: 5px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>@enderror
                </div>

                <fieldset x-show="type === 'login'" :disabled="type !== 'login'" x-transition style="border:0; padding:0; margin:0; min-width:0;">
                    <div style="margin-bottom: 18px;">
                        <label>URL du service</label>
                        <div style="position: relative;">
                            <svg style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            <input type="url" name="url" class="input @error('url') input-error @enderror" value="{{ old('url') }}" placeholder="https://github.com" x-model="url" @input="mettreAJourFavicon()" style="padding-left: 38px;">
                        </div>
                        @error('url')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Identifiant / Email</label>
                        <input type="text" name="identifiant" class="input" value="{{ old('identifiant') }}" placeholder="sergent@soldier.com" autocomplete="off">
                    </div>
                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="margin-bottom: 0;">Mot de passe</label>
                            <button type="button" @click="genererMdp()" class="s-gen-btn"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/></svg>Générer</button>
                        </div>
                        <div style="position: relative;">
                            <input type="password" id="mdp_visuel" name="mot_de_passe" class="input @error('mot_de_passe') input-error @enderror"
                                   x-model="motDePasse"
                                   @input="motDePasse = $event.target.value; calculerForce()"
                                   placeholder="••••••••" autocomplete="off" style="padding-right: 44px;">

                            <button type="button" @click="toggleMdp()" class="eye-btn">
                                <svg x-show="!showMdp" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showMdp" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        <div x-show="motDePasse.length > 0" style="margin-top: 8px;" x-transition>
                            <div style="display: flex; gap: 3px; height: 4px; margin-bottom: 5px;">
                                <template x-for="i in 5" :key="i">
                                    <div style="flex: 1; border-radius: 3px; transition: background 0.35s, box-shadow 0.35s;" :style="i <= forceSegments ? 'background: ' + forceColor + '; box-shadow: 0 0 10px ' + forceColor : 'background: #16283a'"></div>
                                </template>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.72rem;">
                                <span style="color: var(--text-muted);">Entropie : <span :style="'color:' + forceColor" x-text="entropie + ' bits'"></span></span>
                                <span :style="'color:' + forceColor + '; font-weight: 700;'" x-text="forceLabel"></span>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset x-show="type === 'carte'" :disabled="type !== 'carte'" x-transition style="border:0; padding:0; margin:0; min-width:0;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                        <div>
                            <label>Numéro de carte</label>
                            <input type="text" name="numero" class="input" value="{{ old('numero') }}" placeholder="•••• •••• •••• ••••" maxlength="25">
                        </div>
                        <div>
                            <label>Titulaire</label>
                            <input type="text" name="titulaire" class="input" value="{{ old('titulaire') }}" placeholder="LEON BELTRAN">
                        </div>
                        <div>
                            <label>Expiration</label>
                            <input type="text" name="expiration" class="input" value="{{ old('expiration') }}" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div>
                            <label>CVV</label>
                            <input type="text" name="cvv" class="input" value="{{ old('cvv') }}" placeholder="•••" maxlength="4">
                        </div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Code PIN</label>
                        <input type="text" name="code_pin" class="input" value="{{ old('code_pin') }}" placeholder="••••">
                    </div>
                </fieldset>

                <fieldset x-show="type === 'note'" :disabled="type !== 'note'" x-transition style="border:0; padding:0; margin:0; min-width:0;">
                    <div style="margin-bottom: 18px;">
                        <label>Contenu <span style="color: var(--danger);">*</span></label>
                        <textarea name="contenu" class="input" rows="6" placeholder="Votre note sécurisée..." style="resize: vertical; line-height: 1.6;">{{ old('contenu') }}</textarea>
                    </div>
                </fieldset>

                <fieldset x-show="type === 'identite'" :disabled="type !== 'identite'" x-transition style="border:0; padding:0; margin:0; min-width:0;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                        <div>
                            <label>Prénom</label>
                            <input type="text" name="prenom" class="input" value="{{ old('prenom') }}" placeholder="Leon">
                        </div>
                        <div>
                            <label>Nom</label>
                            <input type="text" name="nom" class="input" value="{{ old('nom') }}" placeholder="Beltran">
                        </div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Email</label>
                        <input type="email" name="email" class="input" value="{{ old('email') }}" placeholder="sergent@soldier.com">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                        <div>
                            <label>Téléphone</label>
                            <input type="text" name="telephone" class="input" value="{{ old('telephone') }}" placeholder="+1 514 555 0123">
                        </div>
                        <div>
                            <label>N° Passeport / Pièce</label>
                            <input type="text" name="passeport" class="input" value="{{ old('passeport') }}" placeholder="AB123456">
                        </div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Adresse</label>
                        <textarea name="adresse" class="input" rows="2" placeholder="123 rue Principale, Montréal, QC" style="resize: vertical;">{{ old('adresse') }}</textarea>
                    </div>
                </fieldset>

                <fieldset x-show="type === 'cles'" :disabled="type !== 'cles'" x-transition style="border:0; padding:0; margin:0; min-width:0;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 18px;">
                        <div>
                            <label>Serveur</label>
                            <input type="text" name="serveur" class="input" value="{{ old('serveur') }}" placeholder="ssh.monserveur.com">
                        </div>
                        <div>
                            <label>Port</label>
                            <input type="number" name="port" class="input" value="{{ old('port', 22) }}" placeholder="22" min="1" max="65535">
                        </div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Username</label>
                        <input type="text" name="username" class="input" value="{{ old('username') }}" placeholder="root">
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Clé privée</label>
                        <textarea name="cle_privee" class="input" rows="5" placeholder="-----BEGIN OPENSSH PRIVATE KEY-----&#10;...&#10;-----END OPENSSH PRIVATE KEY-----" style="resize: vertical; font-family: 'Courier New', monospace; font-size: 0.8rem;">{{ old('cle_privee') }}</textarea>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <label>Mot de passe / Passphrase</label>
                        <div style="position: relative;">
                            <input :type="showMdp ? 'text' : 'password'" name="passphrase_ssh" class="input" placeholder="••••••••" autocomplete="off" style="padding-right: 44px;">
                            <button type="button" @click="showMdp = !showMdp" class="eye-btn">
                                <svg x-show="!showMdp" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showMdp" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                </fieldset>

                <fieldset x-show="type === 'autre'" :disabled="type !== 'autre'" x-transition style="border:0; padding:0; margin:0; min-width:0;">
                    <div style="margin-bottom: 18px;">
                        <label>Contenu</label>
                        <textarea name="contenu" class="input" rows="4" placeholder="Valeur, token, données diverses..." style="resize: vertical; line-height: 1.6;">{{ old('contenu') }}</textarea>
                    </div>
                </fieldset>

                <div x-show="type !== 'note'" style="margin-bottom: 24px;">
                    <label>Notes</label>
                    <textarea name="notes" class="input" rows="3" placeholder="Informations complémentaires..." style="resize: vertical; line-height: 1.5;">{{ old('notes') }}</textarea>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding-top: 18px; border-top: 1px solid #12202e;">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Annuler
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>

        @include('dashboard.partials.stockage-chiffre')
        </div>
    </div>

    @include('dashboard.partials.form-styles')

    {{-- Behavior is supplied once by the tested TypeScript module. --}}
@endsection
