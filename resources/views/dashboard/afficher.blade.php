@extends('layouts.app')
@section('title', $donnees['label'])

@section('content')
    <div style="max-width: 680px;" x-data="afficherService()">

        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 28px;">
            <a href="{{ route('dashboard') }}" class="btn-secondary" style="padding: 9px 12px; flex-shrink: 0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div style="flex: 1; min-width: 0;">
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $donnees['label'] }}
                </h1>
                <p style="color: var(--text-muted); font-size: 0.8375rem; margin: 0;">
                    Dernière modification : {{ \Carbon\Carbon::parse($element->updated_at)->diffForHumans() }}
                </p>
            </div>
            <a href="{{ route('services.modifier', $element->id) }}" class="btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Modifier
            </a>
        </div>

        <div class="card" style="margin-bottom: 16px;">

            <div style="display: flex; align-items: center; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid rgba(33,126,170,0.2); margin-bottom: 20px;">
                @php
                    $icones = ['login' => '🔑', 'carte' => '💳', 'note' => '📝', 'identite' => '👤', 'cles' => '🔐', 'autre' => '📦'];
                @endphp
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.3); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                    @if($element->favicon_url)
                        <img src="{{ $element->favicon_url }}" alt="{{ $element->label }}"
                             style="width: 34px; height: 34px; object-fit: contain;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center; font-size:1.5rem;">
                            {{ $icones[$donnees['type']] ?? '📦' }}
                        </div>
                    @else
                        <div style="font-size: 1.5rem;">{{ $icones[$donnees['type']] ?? '📦' }}</div>
                    @endif
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 800; font-size: 1.125rem; color: var(--text-primary); margin-bottom: 4px;">{{ $donnees['label'] }}</div>
                    @if(!empty($donnees['url']))
                        <a href="{{ $donnees['url'] }}" target="_blank" rel="noopener noreferrer"
                           style="font-size: 0.8125rem; color: var(--accent); text-decoration: none; display: inline-flex; align-items: center; gap: 5px;"
                           onmouseover="this.style.color='var(--accent-bright)'" onmouseout="this.style.color='var(--accent)'">
                            {{ parse_url($donnees['url'], PHP_URL_HOST) }}
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    @endif
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                    <span style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25); border-radius: 20px; padding: 3px 10px; font-size: 0.72rem; font-weight: 700; color: #22c55e;">Chiffré</span>
                    @php $labels = ['login' => 'Mot de passe', 'carte' => 'Carte', 'note' => 'Note', 'identite' => 'Identité', 'cles' => 'Clé SSH', 'autre' => 'Autre']; @endphp
                    <span style="background: rgba(33,126,170,0.15); border: 1px solid rgba(33,126,170,0.3); border-radius: 20px; padding: 3px 10px; font-size: 0.72rem; font-weight: 700; color: var(--accent-bright);">{{ $labels[$donnees['type']] ?? 'Autre' }}</span>
                </div>
            </div>

            @if($donnees['type'] === 'login')
                @if(!empty($donnees['donnees']['identifiant']))
                    <div class="champ-row">
                        <div class="champ-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Identifiant</div>
                        <div class="champ-value">
                            <span style="font-family: Audiowide,sans-serif;">{{ $donnees['donnees']['identifiant'] }}</span>
                            <button @click="copier('{{ addslashes($donnees['donnees']['identifiant']) }}', 'Identifiant')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                        </div>
                    </div>
                @endif
                @if(!empty($donnees['donnees']['mot_de_passe']))
                    <div class="champ-row">
                        <div class="champ-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Mot de passe</div>
                        <div class="champ-value">
                            <span style="font-family: monospace; letter-spacing: 0.08em;" x-text="showMdp ? '{{ addslashes($donnees['donnees']['mot_de_passe']) }}' : '••••••••••••'"></span>
                            <div style="display: flex; gap: 4px;">
                                <button @click="showMdp = !showMdp" class="copy-btn">
                                    <svg x-show="!showMdp" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showMdp" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                                <button @click="copier('{{ addslashes($donnees['donnees']['mot_de_passe']) }}', 'Mot de passe')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                            </div>
                        </div>
                    </div>
                @endif

            @elseif($donnees['type'] === 'carte')
                @foreach([['numero','Numéro'],['titulaire','Titulaire'],['expiration','Expiration'],['cvv','CVV'],['code_pin','Code PIN']] as [$champ, $label])
                    @if(!empty($donnees['donnees'][$champ]))
                        <div class="champ-row">
                            <div class="champ-label">{{ $label }}</div>
                            <div class="champ-value">
                                <span style="font-family: monospace;" @if(in_array($champ, ['cvv','code_pin'])) x-text="showMdp ? '{{ addslashes($donnees['donnees'][$champ]) }}' : '•••'" @else {{ $donnees['donnees'][$champ] }} @endif></span>
                                <button @click="copier('{{ addslashes($donnees['donnees'][$champ]) }}', '{{ $label }}')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!empty($donnees['donnees']['cvv']) || !empty($donnees['donnees']['code_pin']))
                    <div style="padding: 8px 0;">
                        <button @click="showMdp = !showMdp" class="btn-secondary" style="font-size: 0.8rem; padding: 6px 14px;">
                            <span x-text="showMdp ? '🙈 Masquer CVV/PIN' : '👁 Afficher CVV/PIN'"></span>
                        </button>
                    </div>
                @endif

            @elseif($donnees['type'] === 'note')
                @if(!empty($donnees['donnees']['contenu']))
                    <div style="background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.2); border-radius: 9px; padding: 16px; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.7; white-space: pre-wrap;">{{ $donnees['donnees']['contenu'] }}</div>
                @endif

            @elseif($donnees['type'] === 'identite')
                @foreach([['prenom','Prénom'],['nom','Nom'],['email','Email'],['telephone','Téléphone'],['passeport','Passeport'],['adresse','Adresse']] as [$champ, $label])
                    @if(!empty($donnees['donnees'][$champ]))
                        <div class="champ-row">
                            <div class="champ-label">{{ $label }}</div>
                            <div class="champ-value">
                                <span>{{ $donnees['donnees'][$champ] }}</span>
                                <button @click="copier('{{ addslashes($donnees['donnees'][$champ]) }}', '{{ $label }}')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                            </div>
                        </div>
                    @endif
                @endforeach

            @elseif($donnees['type'] === 'cles')
                @if(!empty($donnees['donnees']['serveur']))
                    <div class="champ-row">
                        <div class="champ-label">Serveur</div>
                        <div class="champ-value">
                            <span style="font-family: monospace;">{{ $donnees['donnees']['serveur'] }}:{{ $donnees['donnees']['port'] ?? 22 }}</span>
                            <button @click="copier('{{ $donnees['donnees']['serveur'] }}', 'Serveur')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                        </div>
                    </div>
                @endif
                @if(!empty($donnees['donnees']['username']))
                    <div class="champ-row">
                        <div class="champ-label">Username</div>
                        <div class="champ-value">
                            <span style="font-family: monospace;">{{ $donnees['donnees']['username'] }}</span>
                            <button @click="copier('{{ $donnees['donnees']['username'] }}', 'Username')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                        </div>
                    </div>
                @endif
                @if(!empty($donnees['donnees']['cle_privee']))
                    <div class="champ-row" style="align-items: flex-start;">
                        <div class="champ-label" style="padding-top: 4px;">Clé privée</div>
                        <div style="flex: 1; background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.2); border-radius: 9px; padding: 10px 12px;">
                            <pre style="font-size: 0.72rem; color: var(--text-muted); white-space: pre-wrap; word-break: break-all; margin: 0;" x-text="showMdp ? `{{ addslashes($donnees['donnees']['cle_privee']) }}` : '-----BEGIN OPENSSH PRIVATE KEY-----\n[masquée]\n-----END OPENSSH PRIVATE KEY-----'"></pre>
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <button @click="showMdp = !showMdp" class="btn-secondary" style="font-size: 0.75rem; padding: 5px 12px;" x-text="showMdp ? 'Masquer' : 'Afficher'"></button>
                                <button @click="copier(`{{ addslashes($donnees['donnees']['cle_privee']) }}`, 'Clé privée')" class="btn-secondary" style="font-size: 0.75rem; padding: 5px 12px;">Copier</button>
                            </div>
                        </div>
                    </div>
                @endif
                @if(!empty($donnees['donnees']['mot_de_passe']))
                    <div class="champ-row">
                        <div class="champ-label">Passphrase</div>
                        <div class="champ-value">
                            <span style="font-family: monospace;" x-text="showMdp ? '{{ addslashes($donnees['donnees']['mot_de_passe']) }}' : '••••••••'"></span>
                            <button @click="copier('{{ addslashes($donnees['donnees']['mot_de_passe']) }}', 'Passphrase')" class="copy-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                        </div>
                    </div>
                @endif

            @else
                @if(!empty($donnees['donnees']['contenu']))
                    <div style="background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.2); border-radius: 9px; padding: 14px; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">{{ $donnees['donnees']['contenu'] }}</div>
                @endif
            @endif

            @if(!empty($donnees['donnees']['notes']))
                <div class="champ-row" style="align-items: flex-start;">
                    <div class="champ-label" style="padding-top: 2px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Notes</div>
                    <div style="flex: 1; background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.2); border-radius: 9px; padding: 12px 14px; font-size: 0.875rem; color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">{{ $donnees['donnees']['notes'] }}</div>
                </div>
            @endif
        </div>

        <div class="card" style="border-color: rgba(239,68,68,0.25); background: rgba(239,68,68,0.04);">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: #ef4444; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Zone de danger
            </h3>
            <p style="font-size: 0.8375rem; color: var(--text-muted); margin-bottom: 14px;">La suppression place ce service dans la corbeille pendant 30 jours.</p>
            <form method="POST" action="{{ route('services.supprimer', $element->id) }}" @submit.prevent="confirmerSuppression($event)">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    Supprimer
                </button>
            </form>
        </div>

    </div>

    <div x-show="showModal" x-transition style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 50; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);" @click.self="showModal = false">
        <div class="card" style="max-width: 380px; width: calc(100% - 40px); border-color: rgba(239,68,68,0.3);">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px;">Confirmer la suppression</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 20px; line-height: 1.5;">« <strong style="color: var(--text-primary);">{{ $donnees['label'] }}</strong> » sera placé dans la corbeille.</p>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button @click="showModal = false" class="btn-secondary">Annuler</button>
                <button @click="executerSuppression()" class="btn-danger">Supprimer</button>
            </div>
        </div>
    </div>

    <style>
        .champ-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(33,126,170,0.15); }
        .champ-row:last-child { border-bottom: none; padding-bottom: 0; }
        .champ-row:first-child { padding-top: 0; }
        .champ-label { display: flex; align-items: center; gap: 7px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); width: 120px; flex-shrink: 0; }
        .champ-value { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--bg-elevated); border: 1px solid rgba(33,126,170,0.2); border-radius: 9px; padding: 9px 12px; min-width: 0; }
        .copy-btn { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; border-radius: 6px; display: flex; align-items: center; transition: all 0.15s; flex-shrink: 0; }
        .copy-btn:hover { color: var(--accent-bright); background: rgba(33,126,170,0.15); }
        .btn-danger { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); border-radius: 9px; padding: 10px 20px; font-size: 0.875rem; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 8px; font-family: 'Audiowide', sans-serif; }
        .btn-danger:hover { background: rgba(239,68,68,0.2); border-color: #ef4444; }
    </style>

    @push('scripts')
        <script>
            function afficherService() {
                return {
                    showMdp: false,
                    showModal: false,
                    formASupprimer: null,

                    async copier(texte, label) {
                        if (!texte) return;
                        try {
                            await navigator.clipboard.writeText(texte);
                            showToast('success', label + ' copié', 'Effacé dans 30 secondes.');
                            setTimeout(() => navigator.clipboard.writeText(''), 30000);
                        } catch { showToast('error', 'Erreur', 'Impossible d\'accéder au presse-papier.'); }
                    },

                    confirmerSuppression(event) { this.formASupprimer = event.target; this.showModal = true; },
                    executerSuppression() { if (this.formASupprimer) this.formASupprimer.submit(); this.showModal = false; }
                }
            }
        </script>
    @endpush
@endsection
