@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php
        $tousElements = collect($services)->flatMap(fn ($groupe) => $groupe['elements']);
        $totalElements = $tousElements->count();
        $motsDePasse = $tousElements
            ->filter(fn ($e) => ($e['type'] ?? '') === 'login' && ! empty($e['donnees']['mot_de_passe'] ?? null))
            ->map(fn ($e) => (string) $e['donnees']['mot_de_passe'])
            ->values();
        $classes = fn (string $p) => (int) preg_match('/[a-z]/', $p) + (int) preg_match('/[A-Z]/', $p) + (int) preg_match('/[0-9]/', $p) + (int) preg_match('/[^a-zA-Z0-9]/', $p);
        $nbFaibles = $motsDePasse->filter(fn ($p) => mb_strlen($p) < 12 || $classes($p) < 3)->count();
        $nbReutilises = $motsDePasse->countBy()->filter(fn ($n) => $n > 1)->sum();
        $nbSains = max(0, $motsDePasse->count() - $motsDePasse->filter(fn ($p) => mb_strlen($p) < 12 || $classes($p) < 3 || $motsDePasse->countBy()[$p] > 1)->count());
        $score = $motsDePasse->count() > 0 ? (int) round(100 * $nbSains / $motsDePasse->count()) : 100;
        $nbTypes = $tousElements->pluck('type')->unique()->count();
    @endphp

    <div x-data="dashboard()">

        @if(session('security_alert'))
            <div x-data="{ show: true }" x-show="show" x-transition role="alert"
                 style="margin-bottom: 20px; background: rgba(255,90,100,0.08); border: 1px solid rgba(255,90,100,0.4); border-radius: 12px; padding: 14px 18px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 0 24px rgba(255,90,100,.12);">
                <svg style="flex-shrink:0; margin-top:2px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff5a64" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div style="flex:1;">
                    <div style="font-size:0.85rem; color:#ff9aa0; margin-bottom:4px;">Alerte de sécurité</div>
                    <div style="font-size:0.78rem; color:var(--text-secondary); line-height:1.6;">Un ou plusieurs éléments de votre coffre n'ont pas pu être déchiffrés. Cela peut indiquer une tentative de manipulation de vos données. Contactez <a href="mailto:support@soldierkey.com" style="color:#ff9aa0;">support@soldierkey.com</a>.</div>
                </div>
                <button @click="show = false" aria-label="Fermer l'alerte" style="background:none; border:none; cursor:pointer; color:var(--text-muted); flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        @endif

        <div class="s-page-head">
            <div>
                <span class="s-tag">// Coffre personnel</span>
                <h1 style="margin-top: 10px;">Mes services</h1>
                <p class="s-sub s-mono">
                    <span data-countup style="color: var(--text-primary);">{{ $totalElements }}</span>
                    entrées · <span class="s-neon">chiffrées AES-256-GCM</span>
                </p>
            </div>
            <a href="{{ route('services.creer') }}" class="btn-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouveau service
            </a>
        </div>

        @if($totalElements > 0)
            {{-- Santé du coffre, calculée localement à partir des éléments déjà déchiffrés pour cette page --}}
            <div class="s-stagger" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 28px;">
                <div class="card s-hud s-scan s-glow" style="padding: 20px; display: flex; align-items: center; gap: 18px;">
                    <span class="s-scan-line"></span>
                    <svg viewBox="0 0 84 84" role="img" aria-label="Score de sécurité {{ $score }} sur 100" style="width: 78px; height: 78px; flex-shrink: 0; transform: rotate(-90deg);">
                        <circle cx="42" cy="42" r="36" fill="none" stroke="#12202e" stroke-width="6"/>
                        <circle class="s-gauge-arc" data-gauge="{{ $score }}" cx="42" cy="42" r="36" fill="none" stroke="{{ $score >= 80 ? '#3df5c0' : ($score >= 50 ? '#f2b544' : '#ff5a64') }}" stroke-width="6" stroke-linecap="round"/>
                    </svg>
                    <div>
                        <p class="s-tag" style="color: var(--text-muted);">Score de sécurité</p>
                        <p style="font-size: 1.9rem; margin-top: 6px;"><span class="{{ $score >= 80 ? 's-neon' : '' }}" data-countup style="{{ $score < 80 ? 'color:' . ($score >= 50 ? '#f5c870' : '#ff9aa0') : '' }}">{{ $score }}</span><span style="font-size: 0.85rem; color: var(--text-muted);">/100</span></p>
                    </div>
                </div>
                <div class="card s-glow" style="padding: 20px; display: flex; flex-direction: column; gap: 8px;">
                    <p class="s-tag" style="color: var(--text-muted);">Entrées</p>
                    <p style="font-size: 1.9rem;" data-countup>{{ $totalElements }}</p>
                    <p style="font-size: 0.72rem; color: var(--text-muted);">{{ $nbTypes }} {{ $nbTypes > 1 ? "types d'éléments" : "type d'élément" }}</p>
                </div>
                <div class="card s-glow" style="padding: 20px; display: flex; flex-direction: column; gap: 8px;">
                    <p class="s-tag" style="color: var(--text-muted);">Mots de passe faibles</p>
                    <p style="font-size: 1.9rem; {{ $nbFaibles > 0 ? 'color:#f5c870; text-shadow: 0 0 18px rgba(242,181,68,.5);' : 'color: var(--neon);' }}" data-countup>{{ $nbFaibles }}</p>
                    <p style="font-size: 0.72rem; color: var(--text-muted);">Moins de 12 caractères ou peu variés</p>
                </div>
                <div class="card s-glow" style="padding: 20px; display: flex; flex-direction: column; gap: 8px;">
                    <p class="s-tag" style="color: var(--text-muted);">Réutilisés</p>
                    <p style="font-size: 1.9rem; {{ $nbReutilises > 0 ? 'color:#f5c870; text-shadow: 0 0 18px rgba(242,181,68,.5);' : 'color: var(--neon);' }}" data-countup>{{ $nbReutilises }}</p>
                    <a href="{{ route('generateur') }}" style="font-size: 0.72rem; text-decoration: none;">Générer des remplaçants →</a>
                </div>
            </div>
        @endif

        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; align-items: center;">
            <div style="position: relative; flex: 1 1 280px; max-width: 460px;">
                <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="input" placeholder="Rechercher dans le coffre..." aria-label="Rechercher dans le coffre" x-model="recherche" style="padding-left: 42px; min-height: 46px;">
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                @foreach(['Tous', 'Favoris', 'Récents'] as $filtre)
                    <button
                        @click="filtreActif = '{{ $filtre }}'"
                        :class="filtreActif === '{{ $filtre }}' ? 'filtre-btn-active' : 'filtre-btn'"
                    >{{ $filtre }}</button>
                @endforeach
            </div>
        </div>

        @if($totalElements > 0)
            <div class="s-stagger" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 14px;">
                @foreach($services as $groupe)
                    @foreach($groupe['elements'] as $element)
                        <div
                            class="service-card"
                            x-show="filtrerElement(@js(strtolower($element['label'])), @js(strtolower($element['url'] ?? '')), {{ $element['favori'] ? 'true' : 'false' }}, {{ $element['id'] }})"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                        >
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                                <div class="favicon-wrapper">
                                    @if($element['favicon_url'])
                                        <img
                                            src="{{ $element['favicon_url'] }}"
                                            alt="{{ $element['label'] }}"
                                            style="width: 26px; height: 26px; object-fit: contain;"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                    @endif
                                        <div class="favicon-fallback" style="{{ $element['favicon_url'] ? 'display:none;' : '' }}">
                                            @php
                                                $typeIcons = [
                                                    'login' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
                                                    'carte' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                                                    'note' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                                                    'identite' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                                                    'cles' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>',
                                                    'autre' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4"/></svg>',
                                                ];
                                            @endphp
                                            @if($element['type'] === 'login' && !$element['favicon_url'])
                                                {{ strtoupper(substr($element['label'], 0, 1)) }}
                                            @else
                                                {!! $typeIcons[$element['type']] ?? $typeIcons['autre'] !!}
                                            @endif
                                        </div>
                                </div>

                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.9rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $element['label'] }}
                                    </div>
                                    @if($element['url'])
                                        <div class="s-mono" style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 3px;">
                                            {{ parse_url($element['url'], PHP_URL_HOST) ?? $element['url'] }}
                                        </div>
                                    @endif
                                </div>

                                @if($groupe['partage'] ?? false)
                                    <span class="s-chip s-chip-b" title="Partagé avec vous">Partagé</span>
                                @endif

                                <button
                                    @click="toggleFavori({{ $element['id'] }}, $el)"
                                    class="favori-btn {{ $element['favori'] ? 'favori-active' : '' }}"
                                    title="{{ $element['favori'] ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $element['favori'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </button>
                            </div>

                            <div class="service-ident">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span class="s-mono" style="font-size: 0.78rem; color: var(--text-secondary); flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $element['donnees']['identifiant'] ?? '—' }}
                            </span>
                                <span class="s-mono" aria-hidden="true" style="font-size: 0.72rem; color: #4e6a82; letter-spacing: .18em;">••••••</span>
                            </div>

                            <div style="display: flex; gap: 6px;">
                                <button
                                    @click="copierMdp(@js($element['donnees']['mot_de_passe'] ?? ''))"
                                    class="action-chip"
                                    style="flex: 1;"
                                    title="Copier le mot de passe"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    Copier
                                </button>
                                <a href="{{ route('services.afficher', $element['id']) }}" class="action-chip" title="Voir">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @if(!($groupe['partage'] ?? false) || ($groupe['permission'] ?? '') === 'ecriture')
                                    <a href="{{ route('services.modifier', $element['id']) }}" class="action-chip" title="Modifier">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                @endif
                                @if(!($groupe['partage'] ?? false))
                                    <form method="POST" action="{{ route('services.supprimer', $element['id']) }}" @submit.prevent="confirmerSuppression($event, @js($element['label']))">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-chip action-chip-danger" title="Supprimer">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>

        @else
            <div class="card s-hud" style="text-align: center; padding: 72px 20px; display: flex; flex-direction: column; align-items: center; gap: 18px;">
                <x-soldier.emblem :size="120" />
                <h2 style="font-size: 1.3rem; font-weight: 400; color: var(--text-primary); margin-top: 10px;">Coffre vide</h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 340px; line-height: 1.6;">
                    Commencez par ajouter votre premier service. Les données seront chiffrées avant leur stockage.
                </p>
                <a href="{{ route('services.creer') }}" class="btn-primary">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter un service
                </a>
            </div>
        @endif

    </div>

    {{-- The confirmation dialog is owned by the page component. --}}

    <style>
        .service-card {
            position: relative;
            background: rgba(8,15,23,0.86);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            transition: border-color 0.3s, transform 0.3s, box-shadow 0.3s;
            cursor: default;
            overflow: hidden;
        }
        .service-card::before {
            content: ""; position: absolute; left: 0; right: 0; height: 60px; top: -60px;
            background: linear-gradient(to bottom, transparent, rgba(79,195,247,.08), rgba(79,195,247,.35) 98%, transparent);
            pointer-events: none; opacity: 0;
        }
        .service-card:hover {
            border-color: rgba(79,195,247,0.55);
            transform: translateY(-3px);
            box-shadow: 0 0 0 1px rgba(79,195,247,.15), 0 16px 44px rgba(79,195,247,.14);
        }
        .service-card:hover::before { opacity: 1; animation: s-scan 1.6s linear infinite; }

        .service-ident {
            background: rgba(4,10,16,0.9); border: 1px solid #1a2b3c; border-radius: 9px;
            padding: 9px 12px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;
        }

        .favicon-wrapper {
            width: 44px; height: 44px;
            border-radius: 11px;
            background: #0e1a27;
            border: 1px solid rgba(79,195,247,0.3);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
            transition: box-shadow .3s;
        }
        .service-card:hover .favicon-wrapper { box-shadow: 0 0 18px rgba(79,195,247,.45); }
        .favicon-fallback {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            color: var(--accent-ultra);
            background: rgba(79,195,247,0.12);
        }

        .favori-btn {
            width: 36px; height: 36px; display: grid; place-items: center;
            background: none; border: none; cursor: pointer;
            color: var(--text-muted);
            border-radius: 8px; transition: all 0.2s; flex-shrink: 0;
        }
        .favori-btn:hover { color: #f5c870; filter: drop-shadow(0 0 6px rgba(242,181,68,.8)); }
        .favori-active { color: #f5c870 !important; filter: drop-shadow(0 0 6px rgba(242,181,68,.7)); }

        .action-chip {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            min-height: 38px; min-width: 38px;
            background: rgba(8,16,26,0.8);
            border: 1px solid #22364a;
            border-radius: 8px; padding: 0 12px;
            font-size: 0.75rem; color: var(--text-secondary);
            cursor: pointer; transition: all 0.2s;
            text-decoration: none;
            font-family: var(--font-display);
        }
        .action-chip:hover {
            background: rgba(79,195,247,0.12);
            border-color: var(--accent-bright);
            color: var(--accent-ultra);
            box-shadow: 0 0 14px rgba(79,195,247,.3);
        }
        .action-chip-danger:hover {
            background: rgba(255,90,100,0.1) !important;
            border-color: rgba(255,90,100,0.5) !important;
            color: #ff9aa0 !important;
            box-shadow: 0 0 14px rgba(255,90,100,.25) !important;
        }

        .filtre-btn, .filtre-btn-active {
            min-height: 40px; padding: 0 16px; border-radius: 999px;
            font-size: 0.75rem;
            cursor: pointer; transition: all 0.2s;
            font-family: var(--font-display);
        }
        .filtre-btn {
            background: transparent;
            border: 1px solid #22364a;
            color: var(--text-secondary);
        }
        .filtre-btn:hover { border-color: var(--accent-bright); color: #fff; box-shadow: 0 0 14px rgba(79,195,247,.25); }
        .filtre-btn-active {
            background: var(--accent-bright);
            color: #02121b;
            border: 1px solid var(--accent-bright);
            box-shadow: 0 0 18px rgba(79,195,247,.45);
        }
    </style>

    {{-- Behavior is supplied once by the tested TypeScript module. --}}

    <div class="s-stagger" style="margin-top: 32px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
        @if(!auth()->user()->hasFamilyPlan())
            <div class="card s-hud s-glow" style="padding: 22px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; background: rgba(9,22,34,0.9); border-color: rgba(79,195,247,0.35);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 46px; height: 46px; background: rgba(79,195,247,0.12); border: 1px solid rgba(79,195,247,0.4); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 20px rgba(79,195,247,.3);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-ultra)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.88rem; color: var(--text-primary); margin-bottom: 4px;">Plan Famille disponible</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Partagez Soldier avec jusqu'à 6 membres pour 3,99$/mois</div>
                    </div>
                </div>
                <a href="{{ route('pricing') }}" class="btn-primary" style="min-height: 40px; font-size: 0.75rem;">
                    Découvrir →
                </a>
            </div>
        @else
            <div class="card s-glow" style="padding: 22px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; border-color: rgba(61,245,192,0.35); background: rgba(6,20,15,0.85);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 46px; height: 46px; background: rgba(61,245,192,0.1); border: 1px solid rgba(61,245,192,0.4); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 20px rgba(61,245,192,.25);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.88rem; color: var(--neon-soft); margin-bottom: 4px;">Plan Famille actif</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Gérez votre groupe famille et invitez vos membres</div>
                    </div>
                </div>
                <a href="{{ route('famille.index') }}" class="btn-secondary" style="min-height: 40px; font-size: 0.75rem; border-color: rgba(61,245,192,0.4); color: var(--neon-soft);">
                    Gérer le groupe →
                </a>
            </div>
        @endif

        <div class="card s-glow" style="padding: 22px 24px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 46px; height: 46px; background: rgba(242,181,68,0.1); border: 1px solid rgba(242,181,68,0.35); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f5c870" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <div>
                <div style="font-size: 0.88rem; color: var(--text-primary); margin-bottom: 4px;">Soldier est gratuit</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Si vous aimez le projet, vous pouvez m'encourager</div>
            </div>
        </div>
    </div>

@endsection

@if(isset($toast))
    <script>
        import { showToast } from "../../js/toast.ts";

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                showToast('{{ $toast['type'] }}', '{{ $toast['titre'] }}', '{{ $toast['message'] }}');
            }, 400);
        });
    </script>
@endif
