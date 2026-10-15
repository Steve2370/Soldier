@extends('layouts.app')
@section('title', 'Partage')

@section('content')
    @php
        $canShareFamily = auth()->user()->hasFamilyPlan() && $familyMembers->isNotEmpty();
        $initialMode = $canShareFamily && in_array(old('partage_groupe'), ['1', 1, true], true) ? '1' : '0';
        $initialPermission = old('permission') === 'ecriture' ? 'ecriture' : 'lecture';
        $hasPreviousSelection = session()->hasOldInput('coffre_id');
        $selectedElementIds = collect(is_array(old('element_ids')) ? old('element_ids') : [])
            ->filter(fn ($id) => is_scalar($id))->map(fn ($id) => (string) $id)->all();
    @endphp
    <div x-data="partage({{ $errors->any() ? 'true' : 'false' }}, @js($initialPermission))">

        <div class="s-page-head">
            <div>
                <span class="s-tag">// Transmission chiffrée</span>
                <h1 style="margin-top: 10px;">Partage</h1>
                <p class="s-sub">Partage chiffré avec enveloppe RSA-4096</p>
            </div>
            <button @click="showFormulaire = !showFormulaire" class="btn-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Partager un coffre
            </button>
        </div>

        {{-- Fonctionnement du partage, purement illustratif --}}
        <div class="card s-hud" aria-label="Fonctionnement du partage" style="padding: 24px 28px; margin-bottom: 22px; background: rgba(7,18,28,0.92);">
            <div class="s-pipeline">
                <div class="s-pipe-node"><span class="s-node s-node-1"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 8.2-8.2M17 6l2 2M15 8l2 2"/></svg></span><span class="s-mono">clé du coffre</span></div>
                <span class="s-pipe" aria-hidden="true"><span class="s-packet"></span></span>
                <div class="s-pipe-node"><span class="s-node s-node-2"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span><span class="s-mono">enveloppe RSA-4096</span></div>
                <span class="s-pipe" aria-hidden="true"><span class="s-packet" style="animation-delay: .9s;"></span></span>
                <div class="s-pipe-node"><span class="s-node s-node-3"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg></span><span class="s-mono">destinataire</span></div>
            </div>
        </div>

        <div x-show="showFormulaire" x-transition style="margin-bottom: 24px;">
            <div class="card s-orbit-border" style="border-color: transparent;">
                <h3 style="font-size: 0.9375rem;  color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    Partager un coffre
                </h3>

                <form method="POST" action="{{ route('partage.envoyer') }}">
                    @csrf

                    <div style="margin-bottom:18px;">
                        <label for="coffre_id">Coffre à partager <span style="color: var(--danger);">*</span></label>
                        <select id="coffre_id" name="coffre_id" required
                                class="input @error('coffre_id') input-error @enderror"
                                style="cursor: pointer;"
                                @change="coffreSelectionne = $event.target.value">
                            <option value="">Sélectionner un coffre...</option>
                            @foreach($coffres as $coffre)
                                <option value="{{ $coffre->id }}" {{ old('coffre_id') == $coffre->id ? 'selected' : '' }}>
                                    {{ $coffre->nom }} ({{ $coffre->elements_count }} entrée{{ $coffre->elements_count > 1 ? 's' : '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('coffre_id') <p class="error-msg" role="alert">{{ $message }}</p> @enderror

                        @foreach($coffres as $coffre)
                            <div x-show="coffreSelectionne == '{{ $coffre->id }}'" x-transition
                                 style="margin-top:10px; background:var(--bg-elevated); border:1px solid rgba(79,195,247,0.2); border-radius:10px; padding:10px; display:flex; flex-direction:column; gap:6px;">
                                <div style="font-size:0.72rem;  color:var(--text-muted); letter-spacing:0.08em; text-transform:uppercase; margin-bottom:2px;">
                                    Éléments inclus dans ce partage
                                </div>
                                @foreach($coffre->elements as $el)
                                    <label class="share-element-choice">
                                        <input type="checkbox" name="element_ids[]" value="{{ $el->id }}"
                                               @checked(! $hasPreviousSelection || in_array((string) $el->id, $selectedElementIds, true))
                                               disabled :disabled="coffreSelectionne !== '{{ $coffre->id }}'"
                                               style="width:16px; height:16px; accent-color:var(--neon); flex-shrink:0;">
                                        <div style="width:28px; height:28px; border-radius:7px; background:var(--bg-elevated); border:1px solid rgba(79,195,247,0.2); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                                            @if($el->favicon_url)
                                                <img src="{{ $el->favicon_url }}" style="width:18px; height:18px; object-fit:contain;">
                                            @else
                                                <span style="font-size:0.7rem;  color:var(--accent-bright);">{{ strtoupper(substr($el->label, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-size:0.8125rem;  color:var(--text-primary);">{{ $el->label }}</div>
                                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ ucfirst($el->type) }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                        @if($errors->has('element_ids') || $errors->has('element_ids.*'))
                            <p class="error-msg" role="alert" data-share-selection-error>{{ $errors->first('element_ids') ?: $errors->first('element_ids.*') }}</p>
                        @endif
                    </div>

                    <div style="margin-bottom:18px;" x-data="{ modePartage: @js($initialMode) }">

                        <label style="font-size:0.8rem;  color:var(--text-muted); display:block; margin-bottom:10px;">Partager avec</label>
                        <div style="display:flex; gap:8px; margin-bottom:16px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="radio" name="partage_groupe" value="0" x-model="modePartage" @checked($initialMode === '0')
                                       style="width:18px; height:18px; accent-color:var(--neon); flex-shrink:0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span style="font-size:0.875rem; font-weight:500; color:var(--text-primary);">Un utilisateur</span>
                            </label>

                        @if($canShareFamily)
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                    <input type="radio" name="partage_groupe" value="1" x-model="modePartage" @checked($initialMode === '1')
                                           style="width:18px; height:18px; accent-color:var(--neon); flex-shrink:0;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <span style="font-size:0.875rem; font-weight:500; color:var(--text-primary);">Groupe famille</span>
                                    <span class="s-chip s-chip-ok">{{ $familyGroup->members->where('role','member')->count() }} membres</span>
                                </label>
                            @endif
                        </div>

                        @error('partage_groupe') <p class="error-msg" role="alert">{{ $message }}</p> @enderror
                        <div x-show="modePartage==='0'" x-transition>
                            <label for="email" style="font-size:0.8rem;  color:var(--text-muted); display:block; margin-bottom:6px;">
                                Email du destinataire <span style="color:var(--danger);">*</span>
                            </label>
                            <div style="position:relative;">
                                <svg style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <input type="email" id="email" name="email"
                                       :disabled="modePartage !== '0'" :required="modePartage === '0'"
                                       class="input @error('email') input-error @enderror"
                                       value="{{ is_string(old('email')) ? old('email') : '' }}"
                                       placeholder="destinataire@soldier.com"
                                       style="padding-left:38px;">
                            </div>
                            @error('email') <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p> @enderror
                        </div>

                        @if($canShareFamily)
                            <div x-show="modePartage==='1'" x-transition>
                                <div style="background:rgba(45,159,212,0.05); border:1px solid rgba(45,159,212,0.2); border-radius:10px; padding:14px;">
                                    <div style="font-size:0.78rem; color:#2d9fd4;  margin-bottom:10px;">
                                        Membres du groupe (les accès existants sont conservés) :
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:12px;">
                                        @foreach($familyGroup->members->where('role','member') as $membre)
                                            <div style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:var(--bg-card); border-radius:8px;">
                                                <div style="width:32px; height:32px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:0.8rem;  color:#fff; overflow:hidden; flex-shrink:0;">
                                                    @if($membre->user->avatar)
                                                        <img src="{{ Storage::url($membre->user->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                                                    @elseif($membre->user->avatar_url)
                                                        <img src="{{ $membre->user->avatar_url }}" style="width:100%; height:100%; object-fit:cover;">
                                                    @else
                                                        {{ strtoupper(substr($membre->user->name, 0, 1)) }}
                                                    @endif
                                                </div>
                                                <div style="flex:1;">
                                                    <div style="font-size:0.85rem;  color:var(--text-primary);">{{ $membre->user->name }}</div>
                                                    <div style="font-size:0.72rem; color:var(--text-muted);">{{ $membre->user->email }}</div>
                                                </div>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div style="background:rgba(61,245,192,0.05); border:1px solid rgba(61,245,192,0.2); border-radius:8px; padding:10px 12px; display:flex; align-items:center; gap:8px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        <p style="font-size:0.75rem; color:var(--neon); margin:0; ">
                                            Une invitation sera créée pour chaque membre sans accès existant à ce coffre.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div style="margin-bottom:18px;">
                        <label>Permission</label>
                        <div style="display:flex; gap:16px; margin-top:8px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="radio" name="permission" value="lecture"
                                       @checked($initialPermission === 'lecture')
                                       style="width:18px; height:18px; cursor:pointer; accent-color:var(--neon);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span style="font-size:0.875rem; font-weight:500; color:var(--text-primary);">Lecture</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="radio" name="permission" value="ecriture"
                                       @checked($initialPermission === 'ecriture')
                                       style="width:18px; height:18px; cursor:pointer; accent-color:var(--neon);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span style="font-size:0.875rem; font-weight:500; color:var(--text-primary);">Écriture</span>
                            </label>
                        </div>
                        @error('permission') <p class="error-msg" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div style="background:var(--accent-dim); border:1px solid var(--border-bright); border-radius:9px; padding:10px 14px; margin-bottom:18px; display:flex; gap:8px; align-items:flex-start;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <p style="font-size:0.78rem; color:var(--text-secondary); margin:0; line-height:1.5;">
                            La Data Key du coffre sera chiffrée avec la clé publique RSA du destinataire. Le serveur conserve les enveloppes chiffrées et orchestre le déverrouillage web.
                        </p>
                    </div>

                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <button type="button" @click="showFormulaire = false" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Envoyer l'invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($invitationsEnAttente->isNotEmpty())
            <div style="margin-bottom: 28px;">
                <h2 style="font-size: 1rem;  color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Invitations en attente ({{ $invitationsEnAttente->count() }})
                </h2>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($invitationsEnAttente as $invitation)
                        <div class="card" style="border-color: rgba(245,158,11,0.2); padding: 14px 16px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; background: rgba(245,158,11,0.1); border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style=" font-size: 0.875rem; color: var(--text-primary);">{{ $invitation->coffre->nom }}</div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">→ {{ $invitation->email_destinataire }} · {{ ucfirst($invitation->permission) }} · Expire {{ $invitation->expire_le->diffForHumans() }}</div>
                                </div>
                                <form method="POST" action="{{ route('partage.annuler', $invitation->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 0.78rem;">Annuler</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div style="margin-bottom: 28px;">
            <h2 style="font-size: 1rem;  color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Partagés par moi ({{ $partagesEnvoyes->where('statut', 'accepte')->count() }})
            </h2>

            @forelse($partagesEnvoyes->where('statut', 'accepte') as $share)
                <div class="card" style="border-color: var(--border-bright); padding: 14px 16px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; color: var(--accent-bright); flex-shrink: 0;">
                            {{ strtoupper(substr($share->destinataire->name, 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style=" font-size: 0.875rem; color: var(--text-primary);">{{ $share->destinataire->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $share->coffre->nom }} · {{ ucfirst($share->permission) }} · Depuis {{ $share->accepte_le?->diffForHumans() }}</div>
                        </div>
                        <span class="badge {{ $share->permission === 'ecriture' ? 'badge-warning' : 'badge-info' }}">
                    {{ ucfirst($share->permission) }}
                </span>
                        <form method="POST" action="{{ route('partage.revoquer', $share->id) }}" @submit.prevent="confirmerRevocation($event, @js($share->destinataire->name))">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger" style="padding: 6px 12px; font-size: 0.78rem;">Révoquer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; background: var(--bg-surface); border: 1px dashed var(--border); border-radius: 12px;">
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Aucun coffre partagé pour le moment.</p>
                </div>
            @endforelse
        </div>

        <div>
            <h2 style="font-size: 1rem;  color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                Partagés avec moi ({{ $partagesRecus->count() }})
            </h2>

            @forelse($partagesRecus as $share)
                <div class="card" style="border-color: var(--border-bright); padding: 14px 16px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; color: var(--text-secondary); flex-shrink: 0;">
                            {{ strtoupper(substr($share->proprietaire->name, 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style=" font-size: 0.875rem; color: var(--text-primary);">{{ $share->coffre->nom }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Partagé par {{ $share->proprietaire->name }} · {{ ucfirst($share->permission) }}</div>
                        </div>
                        <span class="badge badge-success">Accès actif</span>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; background: var(--bg-surface); border: 1px dashed var(--border); border-radius: 12px;">
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Personne n'a partagé de coffre avec vous.</p>
                </div>
            @endforelse
        </div>

    </div>

    <style>
        .s-pipeline { display: flex; align-items: center; gap: 14px; }
        .s-pipe-node { display: flex; flex-direction: column; align-items: center; gap: 10px; font-size: 0.68rem; color: var(--text-muted); text-align: center; flex-shrink: 0; }
        .s-node { width: 70px; height: 70px; border-radius: 18px; display: grid; place-items: center; background: #06101a; border: 1px solid #23415a; color: var(--accent-ultra); }
        .s-node-1 { animation: s-lit 3.6s infinite; } .s-node-2 { animation: s-lit 3.6s 1.2s infinite; } .s-node-3 { animation: s-lit 3.6s 2.4s infinite; }
        @keyframes s-lit { 0%, 100% { box-shadow: none; border-color: #23415a; } 10%, 30% { box-shadow: 0 0 34px rgba(61,245,192,.5); border-color: var(--neon); color: var(--neon); } }
        .s-pipe { position: relative; flex: 1; min-width: 30px; height: 2px; background: linear-gradient(90deg, rgba(79,195,247,.15), rgba(79,195,247,.5), rgba(79,195,247,.15)); }
        .s-packet { position: absolute; top: -5px; width: 44px; height: 12px; border-radius: 6px; background: radial-gradient(closest-side, #fff, var(--neon) 40%, transparent); animation: s-packet 1.8s cubic-bezier(.6,0,.4,1) infinite; }
        @media (max-width: 560px) { .s-node { width: 52px; height: 52px; } .s-pipe-node .s-mono { display: none; } }
        .input-error { border-color: #ff5a64 !important; }
        select.input option { background: var(--bg-elevated); color: var(--text-primary); }
        .share-element-choice { display:flex; align-items:center; gap:12px; min-height:52px; padding:8px 12px; background:var(--bg-card); border-radius:10px; border:1px solid var(--border); cursor:pointer; transition: box-shadow .2s, background .2s; }
        .share-element-choice:hover { border-color: var(--accent); }
        .share-element-choice:has(input:checked) { border-color:var(--accent); background: rgba(45,159,212,.08); box-shadow: 0 0 16px rgba(79,195,247,.18); }
        .share-element-choice:focus-within { outline:2px solid var(--accent); outline-offset:2px; }
    </style>

@endsection
