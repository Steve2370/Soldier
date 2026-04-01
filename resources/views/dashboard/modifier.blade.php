@extends('layouts.app')
@section('title', 'Modifier — ' . $donnees['label'])

@section('content')
    <div x-data="modifierService('{{ addslashes($donnees['label']) }}', '{{ addslashes($donnees['url'] ?? '') }}', '{{ $donnees['favicon_url'] ?? '' }}')">

    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 28px;">
            <a href="{{ route('services.afficher', $element->id) }}" class="btn-secondary" style="padding: 9px 12px; flex-shrink: 0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 3px;">Modifier le service</h1>
                <p style="color: var(--text-muted); font-size: 0.8375rem; margin: 0;">Les modifications seront rechiffrées avec un nouvel IV</p>
            </div>
        </div>

        <div class="card">
            p<form method="POST" action="{{ route('services.mettreAJour', $element->id) }}">
                @csrf @method('PUT')
                <input type="hidden" name="type" value="{{ $donnees['type'] }}">
                <div style="background: rgba(107,127,138,0.5); border: 1px solid rgba(33,126,170,0.3); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px;">
                    <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(107,127,138,0.6); border: 1px solid rgba(33,126,170,0.3); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <img x-show="faviconUrl" :src="faviconUrl" style="width: 28px; height: 28px; object-fit: contain;" x-on:error="faviconUrl = ''">
                        <div x-show="!faviconUrl" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.25rem; color: var(--accent-bright);" x-text="labelInitiale"></div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="label || 'Nom du service'"></div>
                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;" x-text="urlAffichee || 'URL du service'"></div>
                    </div>
                    <span style="display: inline-flex; align-items: center; background: rgba(33,126,170,0.15); border: 1px solid rgba(33,126,170,0.3); border-radius: 20px; padding: 3px 10px; font-size: 0.72rem; font-weight: 700; color: var(--accent-bright); flex-shrink: 0;">Modification</span>
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="label">Nom du service <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="label" name="label"
                           class="input @error('label') input-error @enderror"
                           value="{{ old('label', $donnees['label']) }}"
                           x-model="label" @input="mettreAJourFavicon()"
                           placeholder="Ex : GitHub, Netflix..." autocomplete="off">
                    @error('label')<p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>@enderror
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="url">URL du service</label>
                    <div style="position: relative;">
                        <svg style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <input type="url" id="url" name="url"
                               class="input @error('url') input-error @enderror"
                               value="{{ old('url', $donnees['url']) }}"
                               x-model="url" @input="mettreAJourFavicon()"
                               placeholder="https://github.com" style="padding-left: 38px;">
                    </div>
                    @error('url')<p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>@enderror
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="identifiant">Identifiant / Email</label>
                    <div style="position: relative;">
                        <svg style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="identifiant" name="identifiant"
                               class="input @error('identifiant') input-error @enderror"
                               value="{{ old('identifiant', $donnees['donnees']['identifiant'] ?? '') }}"
                               placeholder="leon@exemple.com" autocomplete="off" style="padding-left: 38px;">
                    </div>
                    @error('identifiant')<p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>@enderror
                </div>

                <div style="margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="margin-bottom: 0;">Mot de passe</label>
                        <button type="button" @click="genererMdp()"
                                style="font-size: 0.775rem; color: var(--accent-bright); background: rgba(33,126,170,0.12); border: 1px solid rgba(33,126,170,0.3); padding: 3px 10px; border-radius: 20px; cursor: pointer; font-weight: 600; font-family: 'Audiowide', sans-serif; transition: all 0.15s;">
                            Générer
                        </button>
                    </div>
                    <div style="position: relative;">
                        <svg style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="mot_de_passe" name="mot_de_passe"
                               class="input @error('mot_de_passe') input-error @enderror"
                               @input="motDePasse = $event.target.value; calculerForce()"
                               placeholder="Laisser vide pour conserver l'actuel"
                               autocomplete="new-password"
                               style="padding-left: 38px; padding-right: 80px;">
                        <div style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); display: flex; gap: 4px;">
                            <button type="button" onclick="var i=document.getElementById('mot_de_passe');i.type=i.type==='password'?'text':'password';" class="icon-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button type="button" @click="copier(motDePasse)" class="icon-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                    <div x-show="motDePasse.length > 0" style="margin-top: 8px;" x-transition>
                        <div style="display: flex; gap: 3px; height: 4px; margin-bottom: 5px;">
                            <template x-for="i in 5" :key="i">
                                <div style="flex: 1; border-radius: 3px; transition: background 0.3s;" :style="'background: ' + (i <= forceSegments ? forceColor : 'rgba(33,126,170,0.15)')"></div>
                            </template>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.72rem;">
                            <span style="color: var(--text-muted);">Entropie : <span :style="'color:' + forceColor" x-text="entropie + ' bits'"></span></span>
                            <span :style="'color:' + forceColor + '; font-weight: 700;'" x-text="forceLabel"></span>
                        </div>
                    </div>
                    <p x-show="motDePasse.length === 0" style="font-size: 0.76rem; color: var(--text-muted); margin-top: 6px;">
                        💡 Laisser vide pour conserver l'actuel
                    </p>
                    @error('mot_de_passe')<p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>@enderror
                </div>

                <div style="margin-bottom: 28px;">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"
                              class="input @error('notes') input-error @enderror"
                              placeholder="Informations complémentaires..."
                              rows="3" style="resize: vertical; line-height: 1.5;">{{ old('notes', $donnees['donnees']['notes'] ?? '') }}</textarea>
                    @error('notes')<p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p>@enderror
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid rgba(33,126,170,0.2);">
                    <a href="{{ route('services.afficher', $element->id) }}" class="btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Annuler
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Sauvegarder les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .icon-btn { width: 30px; height: 30px; background: rgba(107,127,138,0.5); border: 1px solid rgba(33,126,170,0.25); border-radius: 7px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; flex-shrink: 0; }
        .icon-btn:hover { color: var(--accent-bright); border-color: var(--accent-bright); background: rgba(33,126,170,0.15); }
    </style>

@endsection
