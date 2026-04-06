@extends('layouts.app')
@section('title', 'Partage')

@section('content')
    <div x-data="partage({{ $errors->any() ? 'true' : 'false' }}, '{{ old('permission', 'lecture') }}')">

    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px;">
            <div>
                <h1 style="font-size: 1.625rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">Partage</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Zero-knowledge — le serveur ne voit jamais vos clés</p>
            </div>
            <button @click="showFormulaire = !showFormulaire" class="btn-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Partager un coffre
            </button>
        </div>

        <div x-show="showFormulaire" x-transition style="margin-bottom: 24px;">
            <div class="card" style="border-color: var(--accent); box-shadow: 0 0 0 1px rgba(3,166,60,0.15);">
                <h3 style="font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    Partager un coffre
                </h3>

                <form method="POST" action="{{ route('partage.envoyer') }}">
                    @csrf

                    <div>
                        <label for="coffre_id">Coffre à partager <span style="color: var(--danger);">*</span></label>
                        <select id="coffre_id" name="coffre_id"
                                class="input @error('coffre_id') input-error @enderror"
                                style="cursor: pointer;"
                                @change="coffreSelectionne = $event.target.value"
                        >
                            <option value="">Sélectionner un coffre...</option>
                            @foreach($coffres as $coffre)
                                <option value="{{ $coffre->id }}" {{ old('coffre_id') == $coffre->id ? 'selected' : '' }}>
                                    {{ $coffre->nom }} ({{ $coffre->elements_count }} entrée{{ $coffre->elements_count > 1 ? 's' : '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('coffre_id') <p class="error-msg">...</p> @enderror

                        @foreach($coffres as $coffre)
                            <div x-show="coffreSelectionne == '{{ $coffre->id }}'"
                                 x-transition
                                 style="margin-top: 10px; background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.2); border-radius: 10px; padding: 10px; display: flex; flex-direction: column; gap: 6px;">
                                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 2px;">
                                    Éléments inclus dans ce partage
                                </div>
                                @foreach($coffre->elements as $el)
                                    <div x-data="{ checked: true }"
                                         @click="checked = !checked"
                                         :style="checked
             ? 'display:flex; align-items:center; gap:10px; padding:6px 8px; background:var(--bg-card); border-radius:7px; border:1px solid rgba(33,126,170,0.3); cursor:pointer; transition: all 0.15s;'
             : 'display:flex; align-items:center; gap:10px; padding:6px 8px; background:var(--bg-card); border-radius:7px; border:1px solid rgba(33,126,170,0.08); cursor:pointer; opacity:0.45; transition: all 0.15s;'">
                                        <div style="width:28px; height:28px; border-radius:7px; background:var(--bg-elevated); border:1px solid rgba(33,126,170,0.2); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                                            @if($el->favicon_url)
                                                <img src="{{ $el->favicon_url }}" style="width:18px; height:18px; object-fit:contain;"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            @endif
                                            <div style="{{ $el->favicon_url ? 'display:none;' : '' }} width:100%; height:100%; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:var(--accent-bright);">
                                                {{ strtoupper(substr($el->label, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-size:0.8125rem; font-weight:600; color:var(--text-primary);">{{ $el->label }}</div>
                                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ ucfirst($el->type) }}</div>
                                        </div>
                                        <div :style="checked
                                            ? 'width:36px; height:20px; border-radius:10px; background:var(--accent); position:relative; flex-shrink:0; transition:background 0.2s;'
                                            : 'width:36px; height:20px; border-radius:10px; background:rgba(255,255,255,0.1); position:relative; flex-shrink:0; transition:background 0.2s;'">
                                            <div :style="checked
                                                ? 'position:absolute; top:3px; left:18px; width:14px; height:14px; border-radius:50%; background:#fff; transition:left 0.2s;'
                                                : 'position:absolute; top:3px; left:3px; width:14px; height:14px; border-radius:50%; background:#fff; transition:left 0.2s;'">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label>Permission</label>
                        <div style="display: flex; gap: 16px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="permission" value="lecture"
                                       {{ old('permission', 'lecture') === 'lecture' ? 'checked' : '' }}
                                       style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--accent);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary);">Lecture</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="permission" value="ecriture"
                                       {{ old('permission', 'lecture') === 'ecriture' ? 'checked' : '' }}
                                       style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--accent);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary);">Écriture</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label for="email">Email du destinataire <span style="color: var(--danger);">*</span></label>
                        <div style="position: relative;">
                            <svg style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <input type="email" id="email" name="email"
                                   class="input @error('email') input-error @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="collegue@exemple.com"
                                   style="padding-left: 38px;">
                        </div>
                        @error('email') <p class="error-msg"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</p> @enderror

                        <div style="background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 9px; padding: 10px 14px; margin-top: 10px; display: flex; gap: 8px; align-items: flex-start;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <p style="font-size: 0.78rem; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                                La Data Key du coffre sera chiffrée avec la clé publique RSA du destinataire. <strong style="color: var(--text-primary);">Le serveur ne voit jamais vos données en clair.</strong>
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
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
                <h2 style="font-size: 1rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
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
                                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">{{ $invitation->coffre->nom }}</div>
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
            <h2 style="font-size: 1rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Partagés par moi ({{ $partagesEnvoyes->where('statut', 'accepte')->count() }})
            </h2>

            @forelse($partagesEnvoyes->where('statut', 'accepte') as $share)
                <div class="card" style="border-color: var(--border-bright); padding: 14px 16px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 0.875rem; color: var(--accent-bright); flex-shrink: 0;">
                            {{ strtoupper(substr($share->destinataire->name, 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">{{ $share->destinataire->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $share->coffre->nom }} · {{ ucfirst($share->permission) }} · Depuis {{ $share->accepte_le?->diffForHumans() }}</div>
                        </div>
                        <span class="badge {{ $share->permission === 'ecriture' ? 'badge-warning' : 'badge-info' }}">
                    {{ ucfirst($share->permission) }}
                </span>
                        <form method="POST" action="{{ route('partage.revoquer', $share->id) }}" @submit.prevent="confirmerRevocation($event, '{{ addslashes($share->destinataire->name) }}')">
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
            <h2 style="font-size: 1rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                Partagés avec moi ({{ $partagesRecus->count() }})
            </h2>

            @forelse($partagesRecus as $share)
                <div class="card" style="border-color: var(--border-bright); padding: 14px 16px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 0.875rem; color: var(--text-secondary); flex-shrink: 0;">
                            {{ strtoupper(substr($share->proprietaire->name, 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">{{ $share->coffre->nom }}</div>
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

        <div x-show="showModal" x-transition style="position: fixed; inset: 0; background: rgba(1,15,26,0.85); z-index: 50; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);" @click.self="showModal = false">
            <div class="card" style="max-width: 380px; width: calc(100% - 40px); border-color: rgba(239,68,68,0.3);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px;">Révoquer l'accès</h3>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 20px; line-height: 1.5;">
                    <strong style="color: var(--text-primary);" x-text="nomARevoquer"></strong> n'aura plus accès à ce coffre immédiatement.
                </p>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button @click="showModal = false" class="btn-secondary">Annuler</button>
                    <button @click="executerRevocation()" class="btn-danger">Révoquer</button>
                </div>
            </div>
        </div>

    </div>

    <style>
        .input-error { border-color: #ef4444 !important; }
        select.input option { background: var(--bg-elevated); color: var(--text-primary); }
    </style>

@endsection
