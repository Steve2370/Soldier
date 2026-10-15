@extends('layouts.app')
@section('title', $donnees['label'])

@section('content')
    <div style="max-width: 1100px;" x-data="afficherService()">

        @php
            $svgIcones = [
                'login' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
                'carte' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                'note' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
                'identite' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                'cles' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>',
                'autre' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>',
            ];
            $svgFallback = $svgIcones[$donnees['type']] ?? $svgIcones['autre'];
            $labels = ['login' => 'Mot de passe', 'carte' => 'Carte', 'note' => 'Note', 'identite' => 'Identité', 'cles' => 'Clé SSH', 'autre' => 'Autre'];
        @endphp

        <a href="{{ route('dashboard') }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 40px; font-size: 0.8rem; color: var(--text-muted); text-decoration: none; margin-bottom: 14px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Mes services
        </a>

        <div class="s-page-head" style="align-items: center;">
            <div style="display: flex; align-items: center; gap: 20px; min-width: 0;">
                <div class="s-big-tile">
                    @if($element->favicon_url)
                        <img src="{{ $element->favicon_url }}" alt="{{ $element->label }}"
                             style="width: 38px; height: 38px; object-fit: contain;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">
                            {!! $svgFallback !!}
                        </div>
                    @else
                        {!! $svgFallback !!}
                    @endif
                </div>
                <div style="min-width: 0;">
                    <h1 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $donnees['label'] }}</h1>
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 10px;">
                        @if(!empty($donnees['url']))
                            <a href="{{ $donnees['url'] }}" target="_blank" rel="noopener noreferrer" class="s-mono"
                               style="font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                {{ parse_url($donnees['url'], PHP_URL_HOST) }}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        @endif
                        <span class="s-chip s-chip-b">{{ $labels[$donnees['type']] ?? 'Autre' }}</span>
                        <span class="s-chip s-chip-ok">Chiffré</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('services.modifier', $element->id) }}" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Modifier
            </a>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
            <div style="flex: 999 1 520px; min-width: 0; display: flex; flex-direction: column; gap: 18px;">
                <div class="card s-hud s-scan">
                    <span class="s-scan-line"></span>

                    @if($donnees['type'] === 'login')
                        @if(!empty($donnees['donnees']['identifiant']))
                            <div class="champ-row">
                                <div class="champ-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Identifiant</div>
                                <div class="champ-value">
                                    <span class="s-mono">{{ $donnees['donnees']['identifiant'] }}</span>
                                    <button @click="copier(@js($donnees['donnees']['identifiant']), 'Identifiant')" class="copy-btn" aria-label="Copier l'identifiant"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                </div>
                            </div>
                        @endif
                        @if(!empty($donnees['donnees']['mot_de_passe']))
                            <div class="champ-row">
                                <div class="champ-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Mot de passe</div>
                                <div class="champ-value">
                                    <span class="s-mono" style="letter-spacing: 0.08em;" x-ref="mdp" :class="showMdp ? 's-decrypting' : ''" x-text="showMdp ? @js($donnees['donnees']['mot_de_passe']) : '••••••••••••'"></span>
                                    <div style="display: flex; gap: 4px;">
                                        <button @click="showMdp = !showMdp; showMdp && $nextTick(() => window.soldierScramble($refs.mdp, $refs.mdp.textContent, 650))" class="copy-btn" aria-label="Afficher ou masquer le mot de passe">
                                            <svg x-show="!showMdp" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg x-show="showMdp" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                        <button @click="copier(@js($donnees['donnees']['mot_de_passe']), 'Mot de passe')" class="copy-btn" aria-label="Copier le mot de passe"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1 2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
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
                                        <span class="s-mono" @if(in_array($champ, ['cvv','code_pin'])) :class="showMdp ? 's-decrypting' : ''" x-text="showMdp ? @js($donnees['donnees'][$champ]) : '•••'" @else>{{ $donnees['donnees'][$champ] }}@endif></span>
                                        <button @click="copier(@js($donnees['donnees'][$champ]), @js($label))" class="copy-btn" aria-label="Copier"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2 2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if(!empty($donnees['donnees']['cvv']) || !empty($donnees['donnees']['code_pin']))
                            <div style="padding: 12px 0 0;">
                                <button @click="showMdp = !showMdp" class="btn-secondary" style="font-size: 0.75rem; min-height: 38px;">
                                    <span x-text="showMdp ? 'Masquer CVV/PIN' : 'Afficher CVV/PIN'"></span>
                                </button>
                            </div>
                        @endif

                    @elseif($donnees['type'] === 'note')
                        @if(!empty($donnees['donnees']['contenu']))
                            <div class="s-block">{{ $donnees['donnees']['contenu'] }}</div>
                        @endif

                    @elseif($donnees['type'] === 'identite')
                        @foreach([['prenom','Prénom'],['nom','Nom'],['email','Email'],['telephone','Téléphone'],['passeport','Passeport'],['adresse','Adresse']] as [$champ, $label])
                            @if(!empty($donnees['donnees'][$champ]))
                                <div class="champ-row">
                                    <div class="champ-label">{{ $label }}</div>
                                    <div class="champ-value">
                                        <span>{{ $donnees['donnees'][$champ] }}</span>
                                        <button @click="copier(@js($donnees['donnees'][$champ]), @js($label))" class="copy-btn" aria-label="Copier"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                    @elseif($donnees['type'] === 'cles')
                        @if(!empty($donnees['donnees']['serveur']))
                            <div class="champ-row">
                                <div class="champ-label">Serveur</div>
                                <div class="champ-value">
                                    <span class="s-mono">{{ $donnees['donnees']['serveur'] }}:{{ $donnees['donnees']['port'] ?? 22 }}</span>
                                    <button @click="copier(@js($donnees['donnees']['serveur']), 'Serveur')" class="copy-btn" aria-label="Copier le serveur"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2 2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                </div>
                            </div>
                        @endif
                        @if(!empty($donnees['donnees']['username']))
                            <div class="champ-row">
                                <div class="champ-label">Username</div>
                                <div class="champ-value">
                                    <span class="s-mono">{{ $donnees['donnees']['username'] }}</span>
                                    <button @click="copier(@js($donnees['donnees']['username']), 'Username')" class="copy-btn" aria-label="Copier le username"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2 2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                </div>
                            </div>
                        @endif
                        @if(!empty($donnees['donnees']['cle_privee']))
                            <div class="champ-row" style="align-items: flex-start;">
                                <div class="champ-label" style="padding-top: 4px;">Clé privée</div>
                                <div class="s-block" style="flex: 1; padding: 12px;">
                                    <pre class="s-mono" style="font-size: 0.72rem; color: var(--text-muted); white-space: pre-wrap; word-break: break-all; margin: 0;" :class="showMdp ? 's-decrypting' : ''" x-text="showMdp ? @js($donnees['donnees']['cle_privee']) : '-----BEGIN OPENSSH PRIVATE KEY-----\n[masquée]\n-----END OPENSSH PRIVATE KEY-----'"></pre>
                                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                                        <button @click="showMdp = !showMdp" class="btn-secondary" style="font-size: 0.72rem; min-height: 36px; padding: 0 12px;" x-text="showMdp ? 'Masquer' : 'Afficher'"></button>
                                        <button @click="copier(@js($donnees['donnees']['cle_privee']), 'Clé privée')" class="btn-secondary" style="font-size: 0.72rem; min-height: 36px; padding: 0 12px;">Copier</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(!empty($donnees['donnees']['mot_de_passe']))
                            <div class="champ-row">
                                <div class="champ-label">Passphrase</div>
                                <div class="champ-value">
                                    <span class="s-mono" :class="showMdp ? 's-decrypting' : ''" x-text="showMdp ? @js($donnees['donnees']['mot_de_passe']) : '••••••••'"></span>
                                    <button @click="copier(@js($donnees['donnees']['mot_de_passe']), 'Passphrase')" class="copy-btn" aria-label="Copier la passphrase"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2 2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                </div>
                            </div>
                        @endif

                    @else
                        @if(!empty($donnees['donnees']['contenu']))
                            <div class="s-block">{{ $donnees['donnees']['contenu'] }}</div>
                        @endif
                    @endif

                    @if(!empty($donnees['donnees']['notes']))
                        <div class="champ-row" style="align-items: flex-start;">
                            <div class="champ-label" style="padding-top: 2px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Notes</div>
                            <div class="s-block" style="flex: 1; padding: 12px 14px; font-size: 0.85rem;">{{ $donnees['donnees']['notes'] }}</div>
                        </div>
                    @endif
                </div>

                <div class="card" style="border-color: rgba(255,90,100,0.3); background: rgba(24,8,11,0.75); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                    <div style="flex: 1 1 280px;">
                        <h3 style="font-size: 0.9rem; font-weight: 400; color: #ff9aa0; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            Zone de danger
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">Ce service sera placé dans la corbeille et restera restaurable pendant 30 jours.</p>
                    </div>
                    <form method="POST" action="{{ route('services.supprimer', $element->id) }}" @submit.prevent="confirmerSuppression($event)">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>

            <aside style="flex: 1 1 280px; min-width: 0; display: flex; flex-direction: column; gap: 18px;">
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <svg viewBox="0 0 24 24" aria-hidden="true" style="width: 34px; height: 34px; fill: none; stroke: var(--neon); stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; filter: drop-shadow(0 0 8px rgba(61,245,192,.8));"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path class="s-check" d="m8.5 12 2.5 2.5 4.5-5"/></svg>
                        <div>
                            <p style="font-size: 0.85rem;">Intégrité vérifiée</p>
                            <p class="s-mono" style="font-size: 0.7rem; color: var(--neon-soft); margin-top: 4px;">déchiffré avec succès</p>
                        </div>
                    </div>
                    <ul class="s-mono" style="list-style: none; font-size: 0.72rem;">
                        <li class="s-seal-row"><span style="color: var(--text-muted);">algorithme</span><span>AES-256-GCM</span></li>
                        <li class="s-seal-row"><span style="color: var(--text-muted);">dérivation</span><span>Argon2id</span></li>
                        <li class="s-seal-row"><span style="color: var(--text-muted);">type</span><span>{{ $labels[$donnees['type']] ?? 'Autre' }}</span></li>
                        <li class="s-seal-row"><span style="color: var(--text-muted);">modifié</span><span>{{ \Carbon\Carbon::parse($element->updated_at)->diffForHumans() }}</span></li>
                    </ul>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.6; padding: 0 4px;">
                    Dernière modification : {{ \Carbon\Carbon::parse($element->updated_at)->diffForHumans() }}
                </p>
            </aside>
        </div>

    </div>

    {{-- The confirmation dialog is owned by the page component. --}}

    <style>
        .s-big-tile { position: relative; width: 70px; height: 70px; border-radius: 18px; background: #0e1a27; border: 1px solid rgba(79,195,247,0.45); display: flex; align-items: center; justify-content: center; overflow: visible; flex-shrink: 0; color: var(--accent-ultra); box-shadow: 0 0 30px rgba(79,195,247,.35); }
        .s-big-tile::after { content: ""; position: absolute; inset: -6px; border-radius: 22px; border: 1px solid rgba(79,195,247,.5); animation: s-tile-pulse 2.6s ease-out infinite; pointer-events: none; }
        @keyframes s-tile-pulse { 0% { transform: scale(1); opacity: .8; } 100% { transform: scale(1.25); opacity: 0; } }
        .champ-row { position: relative; z-index: 2; display: flex; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid #12202e; }
        .champ-row:last-child { border-bottom: none; padding-bottom: 0; }
        .champ-row:first-of-type { padding-top: 0; }
        .champ-label { display: flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: .14em; text-transform: uppercase; color: #6e8aa3; width: 130px; flex-shrink: 0; }
        .champ-value { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: rgba(4,10,16,0.9); border: 1px solid #1a2b3c; border-radius: 9px; padding: 8px 8px 8px 14px; min-width: 0; font-size: 0.88rem; transition: border-color .2s, box-shadow .2s; }
        .champ-value:hover { border-color: rgba(79,195,247,.45); box-shadow: 0 0 16px rgba(79,195,247,.12); }
        .s-block { position: relative; z-index: 2; background: rgba(4,10,16,0.9); border: 1px solid #1a2b3c; border-radius: 9px; padding: 16px; font-size: 0.88rem; color: var(--text-secondary); line-height: 1.7; white-space: pre-wrap; }
        .copy-btn { width: 36px; height: 36px; background: none; border: 1px solid transparent; color: var(--text-muted); cursor: pointer; border-radius: 8px; display: grid; place-items: center; transition: all 0.2s; flex-shrink: 0; }
        .copy-btn:hover { color: #fff; background: #0e1a27; border-color: var(--accent); box-shadow: 0 0 14px rgba(79,195,247,.35); }
        .s-seal-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-top: 1px solid #12202e; }
        .s-check { stroke-dasharray: 24; stroke-dashoffset: 24; animation: s-draw .8s .4s ease-out forwards; }
        @keyframes s-draw { to { stroke-dashoffset: 0; } }
        @media (max-width: 640px) { .champ-row { flex-direction: column; align-items: stretch; gap: 8px; } .champ-label { width: auto; } }
    </style>

    {{-- Behavior is supplied once by the tested TypeScript module. --}}
@endsection
