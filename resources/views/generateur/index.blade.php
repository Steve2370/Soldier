@extends('layouts.app')
@section('title', 'Générateur de mot de passe')

@section('content')
    <div style="max-width: 1100px;" x-data="generateur()" x-init="generer()">

        <div class="s-page-head">
            <div>
                <span class="s-tag">// Arsenal</span>
                <h1 style="margin-top: 10px;">Générateur</h1>
                <p class="s-sub">
                    Générez des mots de passe cryptographiquement sûrs via <code class="s-mono" style="background: #0e1a27; padding: 2px 8px; border-radius: 5px; font-size: 0.78rem; color: var(--accent-ultra);">crypto.getRandomValues</code>
                </p>
            </div>
        </div>

        {{-- Résultat --}}
        <div class="card s-hud s-scan" style="padding: 28px; margin-bottom: 20px; background: rgba(7,18,28,0.92); border-color: rgba(79,195,247,0.35);">
            <span class="s-scan-line"></span>
            <div style="position: relative; z-index: 2; display: flex; flex-wrap: wrap; align-items: center; gap: 22px;">
                <div class="s-mono s-gen-output"
                     aria-live="polite"
                     x-text="motDePasse || '···'"
                     x-effect="motDePasse && window.soldierScramble && window.soldierScramble($el, motDePasse, 520)"></div>

                <div style="display: flex; gap: 10px; flex-shrink: 0;">
                    <button @click="generer()" class="btn-secondary" title="Regénérer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'spin': spinning }">
                            <polyline points="23 4 23 10 17 10"/>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Regénérer
                    </button>
                    <button @click="copierMdp()" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="9" y="9" width="13" height="13" rx="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        Copier
                    </button>
                </div>
            </div>

            <div style="position: relative; z-index: 2; margin-top: 22px;">
                <div style="display: flex; gap: 4px; margin-bottom: 10px;">
                    <template x-for="i in 5" :key="i">
                        <div style="flex: 1; height: 6px; border-radius: 3px; transition: background 0.4s ease, box-shadow 0.4s ease;"
                             :style="i <= forceSegments ? 'background: ' + forceColor + '; box-shadow: 0 0 12px ' + forceColor : 'background: #12202e'">
                        </div>
                    </template>
                </div>
                <div class="s-mono" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; align-items: center; font-size: 0.8rem;">
                    <span :style="'color: ' + forceColor" x-text="forceLabel"></span>
                    <span style="color: var(--text-muted);">
                        <span style="font-size: 1rem;" :style="'color: ' + forceColor" x-text="entropie"></span> bits d'entropie
                    </span>
                </div>
            </div>
        </div>

        <div class="s-stagger" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <div class="card s-glow" style="padding: 18px; text-align: center;">
                <div style="font-size: 1.6rem; color: var(--accent-ultra); text-shadow: 0 0 16px rgba(79,195,247,.5);" x-text="entropie"></div>
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">bits d'entropie</div>
            </div>
            <div class="card s-glow" style="padding: 18px; text-align: center;">
                <div style="font-size: 1.6rem; color: var(--accent-ultra); text-shadow: 0 0 16px rgba(79,195,247,.5);" x-text="motDePasse.length"></div>
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">caractères</div>
            </div>
            <div class="card s-glow" style="padding: 18px; text-align: center;">
                <div style="font-size: 1.6rem; color: var(--accent-ultra); text-shadow: 0 0 16px rgba(79,195,247,.5);" x-text="tailleAlphabet"></div>
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">symboles possibles</div>
            </div>
            <div class="card s-glow" style="padding: 18px; text-align: center;">
                <div class="s-neon" style="font-size: 1.05rem; min-height: 1.9rem; display: flex; align-items: center; justify-content: center;" x-text="window.soldierCrackTime ? window.soldierCrackTime(entropie) : '—'"></div>
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">pour le casser (10¹⁰ essais/s)</div>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
            <div style="flex: 999 1 480px; min-width: 0;">
                <div role="tablist" style="display: flex; gap: 4px; padding: 4px; border-radius: 10px; background: rgba(4,10,16,.9); border: 1px solid var(--border); margin-bottom: 16px; width: fit-content; max-width: 100%;">
                    <button
                        role="tab"
                        @click="type = 'password'; generer()"
                        :class="type === 'password' ? 'tab-active' : 'tab'"
                        :aria-selected="type === 'password'"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Mot de passe
                    </button>
                    <button
                        role="tab"
                        @click="type = 'passphrase'; generer()"
                        :class="type === 'passphrase' ? 'tab-active' : 'tab'"
                        :aria-selected="type === 'passphrase'"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Phrase de passe
                    </button>
                </div>

                <div class="card">

                    <div x-show="type === 'password'">

                        <div style="margin-bottom: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label for="gen_longueur" style="margin-bottom: 0;">Longueur</label>
                                <div class="s-value-pill" x-text="longueur"></div>
                            </div>
                            <div class="s-range" @click="longueur = Math.round($event.offsetX / $el.offsetWidth * (64 - 8) + 8); generer()">
                                <div class="s-range-fill" :style="'width: ' + ((longueur - 8) / (64 - 8) * 100) + '%'"></div>
                                <input id="gen_longueur" type="range" min="8" max="64" x-model.number="longueur" @input="generer()" class="s-range-input">
                            </div>
                            <div class="s-mono" style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.7rem; color: var(--text-muted);">
                                <span>8</span><span>64</span>
                            </div>
                        </div>

                        <div>
                            <label style="margin-bottom: 12px;">Inclure</label>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 8px;">

                                @foreach([
                                    ['majuscules', 'Majuscules', 'A–Z'],
                                    ['minuscules', 'Minuscules', 'a–z'],
                                    ['chiffres', 'Chiffres', '0–9'],
                                    ['symboles', 'Symboles', '!@#$%^&*'],
                                    ['similaires', 'Éviter similaires', '0 O l 1'],
                                ] as [$key, $label, $exemple])
                                    <label for="opt_{{ $key }}" class="s-opt" :class="options.{{ $key }} ? 's-opt-on' : ''">
                                        <input
                                            type="checkbox"
                                            id="opt_{{ $key }}"
                                            @change="options.{{ $key }} = $event.target.checked; generer()"
                                            :checked="options.{{ $key }}"
                                            class="s-sr"
                                        >
                                        <span style="display: flex; flex-direction: column; gap: 4px;">
                                            <span style="font-size: 0.8rem; color: var(--text-primary);">{{ $label }}</span>
                                            <span class="s-mono" style="font-size: 0.7rem; color: var(--text-muted);">{{ $exemple }}</span>
                                        </span>
                                        <span class="s-switch" aria-hidden="true"></span>
                                    </label>
                                @endforeach

                            </div>
                        </div>
                    </div>

                    <div x-show="type === 'passphrase'">

                        <div style="margin-bottom: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label for="gen_mots" style="margin-bottom: 0;">Nombre de mots</label>
                                <div class="s-value-pill" x-text="nbMots"></div>
                            </div>
                            <div class="s-range">
                                <div class="s-range-fill" :style="'width: ' + ((nbMots - 5) / (16 - 5) * 100) + '%'"></div>
                                <input id="gen_mots" type="range" min="5" max="16" x-model.number="nbMots" @input="generer()" class="s-range-input">
                            </div>
                            <div class="s-mono" style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.7rem; color: var(--text-muted);">
                                <span>5 mots</span><span>16 mots</span>
                            </div>
                        </div>

                        <div style="margin-bottom: 18px;">
                            <label>Séparateur</label>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                @foreach(['-' => 'Tiret (-)', '_' => 'Underscore (_)', '.' => 'Point (.)', ' ' => 'Espace', '' => 'Aucun'] as $val => $lab)
                                    <button type="button"
                                            @click="separateur = '{{ $val }}'; generer()"
                                            :class="separateur === '{{ $val }}' ? 'sep-btn-active' : 'sep-btn'"
                                    >{{ $lab }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div style="background: rgba(79,195,247,0.08); border: 1px solid rgba(79,195,247,0.3); border-radius: 10px; padding: 14px 16px;">
                            <p style="font-size: 0.78rem; color: var(--text-secondary); margin: 0; line-height: 1.6;">
                                Une passphrase de <strong style="color: var(--accent-ultra); font-weight: 400;" x-text="nbMots + ' mots'"></strong> depuis notre dictionnaire de <span x-text="dictionnaireTaille"></span> mots =
                                <strong style="color: var(--accent-ultra); font-weight: 400;" x-text="entropie + ' bits'"></strong> d’entropie théorique. Conservez au moins 12 mots pour environ 80 bits.
                                Plus mémorisable qu'un mot de passe aléatoire.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card" style="flex: 1 1 300px; min-width: 0;">
                <h3 style="font-size: 0.9rem; font-weight: 400; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Comprendre l'entropie
                </h3>
                <div style="display: flex; flex-direction: column;">
                    @foreach([
                        ['< 40', 'Très faible', '#ff5a64', 'Craquable en secondes'],
                        ['40–60', 'Faible', '#ff8a50', 'Craquable en heures'],
                        ['60–80', 'Moyen', '#f2b544', 'Craquable en années'],
                        ['80–100', 'Fort', '#4fc3f7', 'Résistant à long terme'],
                        ['> 100', 'Très fort', '#3df5c0', 'Cryptographiquement sûr'],
                    ] as [$bits, $label, $color, $desc])
                        <div style="display: flex; align-items: center; gap: 12px; padding: 11px 0; border-top: 1px solid #12202e;">
                            <span style="width: 9px; height: 9px; border-radius: 50%; background: {{ $color }}; box-shadow: 0 0 8px {{ $color }}; flex-shrink: 0;"></span>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.78rem; color: {{ $color }};">{{ $label }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px;">{{ $desc }}</div>
                            </div>
                            <span class="s-mono" style="font-size: 0.72rem; color: var(--text-muted);">{{ $bits }} bits</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <style>
        .s-gen-output { flex: 1 1 380px; min-width: 0; font-size: clamp(1.15rem, 2.3vw, 1.9rem); letter-spacing: .04em; line-height: 1.4; word-break: break-all; color: var(--text-primary); min-height: 2.6rem; text-shadow: 0 0 14px rgba(79,195,247,.25); }
        .tab, .tab-active {
            display: inline-flex; align-items: center; gap: 8px;
            min-height: 42px; padding: 0 16px; border-radius: 7px;
            font-size: 0.78rem;
            cursor: pointer; border: 0;
            font-family: var(--font-display);
            transition: all 0.25s;
        }
        .tab { background: transparent; color: var(--text-secondary); }
        .tab:hover { color: #fff; }
        .tab-active { background: rgba(45,159,212,.25); color: #fff; box-shadow: 0 0 18px rgba(79,195,247,.3), inset 0 0 0 1px rgba(79,195,247,.5); }

        .s-value-pill { min-width: 52px; text-align: center; background: rgba(61,245,192,.08); border: 1px solid rgba(61,245,192,.4); border-radius: 8px; padding: 5px 12px; font-size: 1rem; color: var(--neon); text-shadow: 0 0 10px rgba(61,245,192,.7); }
        .s-range { position: relative; height: 8px; background: #12202e; border-radius: 4px; cursor: pointer; }
        .s-range-fill { position: absolute; left: 0; top: 0; height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--accent), var(--neon)); box-shadow: 0 0 12px rgba(79,195,247,.6); transition: width 0.1s; }
        .s-range-fill::after { content: ""; position: absolute; right: -9px; top: 50%; width: 18px; height: 18px; margin-top: -9px; border-radius: 50%; background: #fff; box-shadow: 0 0 0 4px rgba(61,245,192,.35), 0 0 16px rgba(61,245,192,.9); }
        .s-range-input { position: absolute; inset: -10px 0; opacity: 0; cursor: pointer; width: 100%; }
        .s-range-input:focus-visible + * , .s-range:focus-within { outline: 2px solid var(--accent-bright); outline-offset: 6px; border-radius: 4px; }

        .s-opt { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 58px; padding: 0 14px; border-radius: 10px; border: 1px solid #1a2b3c; background: rgba(4,10,16,.7); cursor: pointer; margin: 0; transition: all .25s; }
        .s-opt:hover { border-color: var(--accent); }
        .s-opt:focus-within { outline: 2px solid var(--accent-bright); outline-offset: 2px; }
        .s-opt-on { border-color: rgba(61,245,192,.5); box-shadow: 0 0 18px rgba(61,245,192,.12); }
        .s-switch { position: relative; width: 40px; height: 22px; border-radius: 999px; background: #16283a; transition: background .25s; flex-shrink: 0; }
        .s-switch::after { content: ""; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #6e8aa3; transition: all .25s cubic-bezier(.4,1.6,.6,1); }
        .s-opt-on .s-switch { background: rgba(61,245,192,.35); }
        .s-opt-on .s-switch::after { left: 21px; background: var(--neon); box-shadow: 0 0 10px var(--neon); }

        .sep-btn, .sep-btn-active {
            min-height: 38px; padding: 0 14px; border-radius: 8px;
            font-size: 0.75rem; cursor: pointer;
            border: 1px solid; transition: all 0.2s;
            font-family: var(--font-mono);
        }
        .sep-btn { background: rgba(4,10,16,.7); color: var(--text-secondary); border-color: #22364a; }
        .sep-btn:hover { border-color: var(--accent-bright); color: #fff; }
        .sep-btn-active { background: rgba(45,159,212,.2); color: #fff; border-color: var(--accent-bright); box-shadow: 0 0 14px rgba(79,195,247,.3); }

        .spin { animation: spin 0.5s ease; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

    {{-- Behavior is supplied once by the tested TypeScript module. --}}
@endsection
