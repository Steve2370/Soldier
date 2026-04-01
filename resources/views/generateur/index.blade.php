@extends('layouts.app')
@section('title', 'Générateur de mot de passe')

@section('content')
    <div style="max-width: 680px;" x-data="generateur()" x-init="generer()">

        <div style="margin-bottom: 28px;">
            <h1 style="font-size: 1.625rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">Générateur</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Générez des mots de passe cryptographiquement sûrs via <code style="background: var(--bg-elevated); padding: 1px 6px; border-radius: 4px; font-size: 0.8rem; color: var(--accent-bright);">crypto.getRandomValues()</code>
            </p>
        </div>

        <div class="card" style="border-color: var(--border-bright); margin-bottom: 16px;">

            <div style="background: var(--bg-base); border: 1px solid var(--border-bright); border-radius: 11px; padding: 20px 18px; margin-bottom: 16px; position: relative; overflow: hidden;">

                <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--accent), var(--accent-bright), transparent);"></div>
                <div style="font-family: 'DM Mono', 'Courier New', monospace; font-size: 1.2rem; font-weight: 500; color: var(--text-primary); word-break: break-all; line-height: 1.6; letter-spacing: 0.04em; min-height: 36px;" x-text="motDePasse || '···'"></div>
                <div style="display: flex; align-items: center; gap: 16px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border);">

                    <div style="flex: 1;">
                        <div style="display: flex; gap: 3px; margin-bottom: 6px;">
                            <template x-for="i in 5" :key="i">
                                <div style="flex: 1; height: 5px; border-radius: 3px; transition: background 0.4s ease;"
                                     :style="'background: ' + (i <= forceSegments ? forceColor : 'var(--bg-elevated)')">
                                </div>
                            </template>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.8rem;" :style="'color: ' + forceColor" x-text="forceLabel"></span>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                            <span style="font-weight: 600;" :style="'color: ' + forceColor" x-text="entropie"></span> bits d'entropie
                        </span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; flex-shrink: 0;">
                        <button @click="generer()" class="btn-secondary" style="padding: 9px 14px; font-size: 0.8375rem;" title="Regénérer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'spin': spinning }">
                                <polyline points="23 4 23 10 17 10"/>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                            </svg>
                            Regénérer
                        </button>
                        <button @click="copierMdp()" class="btn-primary" style="padding: 9px 16px; font-size: 0.8375rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <rect x="9" y="9" width="13" height="13" rx="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            Copier
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                <div style="background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; padding: 12px 14px; text-align: center;">
                    <div style="font-family: 'Syne', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--accent-bright);" x-text="entropie"></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">bits d'entropie</div>
                </div>
                <div style="background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; padding: 12px 14px; text-align: center;">
                    <div style="font-family: 'Syne', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--accent-bright);" x-text="motDePasse.length"></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">caractères</div>
                </div>
                <div style="background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; padding: 12px 14px; text-align: center;">
                    <div style="font-family: 'Syne', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--accent-bright);" x-text="tailleAlphabet"></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">symboles possibles</div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 6px; margin-bottom: 16px;">
            <button
                @click="type = 'password'; generer()"
                :class="type === 'password' ? 'tab-active' : 'tab'"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Mot de passe
            </button>
            <button
                @click="type = 'passphrase'; generer()"
                :class="type === 'passphrase' ? 'tab-active' : 'tab'"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Phrase de passe
            </button>
        </div>

        <div class="card" style="border-color: var(--border-bright);">

            <div x-show="type === 'password'">

                <div style="margin-bottom: 22px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label style="margin-bottom: 0;">Longueur</label>
                        <div style="background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 8px; padding: 4px 12px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem; color: var(--accent-bright);" x-text="longueur"></div>
                    </div>
                    <div style="position: relative; height: 6px; background: var(--bg-elevated); border-radius: 3px; cursor: pointer;" @click="longueur = Math.round($event.offsetX / $el.offsetWidth * (64 - 8) + 8); generer()">
                        <div style="position: absolute; left: 0; top: 0; height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--accent), var(--accent-bright)); transition: width 0.1s;"
                             :style="'width: ' + ((longueur - 8) / (64 - 8) * 100) + '%'"></div>
                        <input type="range" min="8" max="64" x-model.number="longueur" @input="generer()"
                               style="position: absolute; inset: -8px 0; opacity: 0; cursor: pointer; width: 100%;">
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span style="font-size: 0.72rem; color: var(--text-muted);">8</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted);">64</span>
                    </div>
                </div>

                <div style="margin-bottom: 6px;">
                    <label style="margin-bottom: 10px;">Inclure</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">

                        @foreach([
                            ['majuscules', 'Majuscules', 'A–Z'],
                            ['minuscules', 'Minuscules', 'a–z'],
                            ['chiffres', 'Chiffres', '0–9'],
                            ['symboles', 'Symboles', '!@#$%^&*'],
                            ['similaires', 'Éviter similaires', '0 O l 1'],
                        ] as [$key, $label, $exemple])
                            <div style="display: flex; align-items: center; gap: 12px; padding: 6px 0;">
                                <input
                                    type="checkbox"
                                    id="opt_{{ $key }}"
                                    @change="options.{{ $key }} = $event.target.checked; generer()"
                                    :checked="options.{{ $key }}"
                                    style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--accent); flex-shrink: 0;"
                                >
                                <label for="opt_{{ $key }}" style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                    <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary);">{{ $label }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">{{ $exemple }}</span>
                                </label>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>

            <div x-show="type === 'passphrase'">

                <div style="margin-bottom: 22px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label style="margin-bottom: 0;">Nombre de mots</label>
                        <div style="background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 8px; padding: 4px 12px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem; color: var(--accent-bright);" x-text="nbMots"></div>
                    </div>
                    <div style="position: relative; height: 6px; background: var(--bg-elevated); border-radius: 3px;">
                        <div style="position: absolute; left: 0; top: 0; height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--accent), var(--accent-bright)); transition: width 0.1s;"
                             :style="'width: ' + ((nbMots - 3) / (10 - 3) * 100) + '%'"></div>
                        <input type="range" min="3" max="10" x-model.number="nbMots" @input="generer()"
                               style="position: absolute; inset: -8px 0; opacity: 0; cursor: pointer; width: 100%;">
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span style="font-size: 0.72rem; color: var(--text-muted);">3 mots</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted);">10 mots</span>
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

                <div style="background: var(--accent-dim); border: 1px solid var(--border-bright); border-radius: 9px; padding: 12px 14px;">
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                        💡 Une passphrase de <strong style="color: var(--accent-bright);" x-text="nbMots + ' mots'"></strong> depuis un dictionnaire de 7776 mots (diceware) =
                        <strong style="color: var(--accent-bright);" x-text="Math.round(nbMots * Math.log2(7776)) + ' bits'"></strong> d'entropie.
                        Plus mémorisable qu'un mot de passe aléatoire.
                    </p>
                </div>
            </div>

        </div>

        <div class="card" style="border-color: var(--border); margin-top: 16px;">
            <h3 style="font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Comprendre l'entropie
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px;">
                @foreach([
                    ['< 40', 'Très faible', '#ef4444', 'Craquable en secondes'],
                    ['40–60', 'Faible', '#f97316', 'Craquable en heures'],
                    ['60–80', 'Moyen', '#f59e0b', 'Craquable en années'],
                    ['80–100', 'Fort', '#03A63C', 'Résistant à long terme'],
                    ['> 100', 'Très fort', '#04D939', 'Cryptographiquement sûr'],
                ] as [$bits, $label, $color, $desc])
                    <div style="background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; padding: 10px 12px;">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: {{ $color }};"></div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $color }};">{{ $label }}</span>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $bits }} bits</div>
                        <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 2px; font-style: italic;">{{ $desc }}</div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <style>
        .tab, .tab-active {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 16px; border-radius: 9px;
            font-size: 0.8375rem; font-weight: 600;
            cursor: pointer; border: 1px solid;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.15s;
        }
        .tab { background: var(--bg-elevated); color: var(--text-secondary); border-color: var(--border); }
        .tab:hover { border-color: var(--border-bright); color: var(--text-primary); }
        .tab-active { background: var(--accent-dim); color: var(--accent-bright); border-color: var(--border-bright); }

        .sep-btn, .sep-btn-active {
            padding: 6px 12px; border-radius: 8px;
            font-size: 0.8rem; cursor: pointer;
            border: 1px solid; transition: all 0.15s;
            font-family: 'DM Mono', monospace;
        }
        .sep-btn { background: var(--bg-elevated); color: var(--text-secondary); border-color: var(--border); }
        .sep-btn:hover { border-color: var(--border-bright); color: var(--text-primary); }
        .sep-btn-active { background: var(--accent-dim); color: var(--accent-bright); border-color: var(--border-bright); }

        .spin { animation: spin 0.5s ease; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

    @push('scripts')
        <script>
            const DICEWARE = [
                'abri','acier','acte','adieu','agile','aigle','aimer','ainsi','ajout','aléa',
                'algue','allez','allié','alpin','ancre','angle','animer','anode','apnée','appel',
                'arbre','arche','ardeur','argent','armée','arôme','arrêt','artère','asile','aspect',
                'astre','atome','atout','aube','avenir','avoir','axe','azote','badge','balcon',
                'balle','banque','barque','bassin','bâton','bazar','béton','bijou','bilan','bogue',
                'boîte','bombe','bonne','bord','botte','boule','brique','brume','bulle','câble',
                'calme','canal','capot','cargo','carte','caser','cause','caverne','cendre','cercle',
                'champ','chaos','charme','charte','chasse','cheval','chiffe','chose','cible','ciment',
                'cirque','clair','classe','clef','clone','cobalt','coffre','coiffe','colère','combat',
                'comète','copie','corde','corps','cortex','coude','coulis','couple','crayon','crédit',
                'crête','croix','crypte','cube','cycle','dague','dalle','débit','décor','delta',
                'dense','désir','destin','dette','diode','disque','dôme','droit','duvet','ébène',
                'écart','éclat','écran','effet','effort','égide','élite','embrun','empire','encre',
                'engin','entier','envol','épée','époque','erreur','escale','étage','éther','exil',
                'explo','fable','facette','fagot','fanal','farce','fatal','fenêtre','ferme','fiche',
                'flair','flèche','flore','flotte','fluide','foehn','fonte','force','forge','forum',
                'fossé','foudre','foyer','franc','frein','front','fugue','funeste','fusion','galet',
                'garde','gazelle','geste','givre','glace','globe','gloire','golem','gorge','grain',
                'grille','grotte','groupe','guide','guilde','halte','havre','héros','hivernal','humeur',
            ];

            function generateur() {
                return {
                    type: 'password',
                    motDePasse: '',
                    longueur: 20,
                    nbMots: 5,
                    separateur: '-',
                    spinning: false,
                    entropie: 0,
                    tailleAlphabet: 0,
                    forceSegments: 0,
                    forceLabel: '',
                    forceColor: 'var(--border-bright)',

                    options: {
                        majuscules: true,
                        minuscules: true,
                        chiffres: true,
                        symboles: true,
                        similaires: true,
                    },

                    generer() {
                        this.spinning = true;
                        setTimeout(() => this.spinning = false, 400);

                        if (this.type === 'password') {
                            this.genererPassword();
                        } else {
                            this.genererPassphrase();
                        }
                        this.calculerEntropie();
                    },

                    genererPassword() {
                        let chars = '';
                        if (this.options.minuscules) chars += 'abcdefghijklmnopqrstuvwxyz';
                        if (this.options.majuscules) chars += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        if (this.options.chiffres) chars += '0123456789';
                        if (this.options.symboles) chars += '!@#$%^&*()-_=+[]{}|;:,.<>?';

                        if (this.options.similaires) {
                            chars = chars.replace(/[0O1lI]/g, '');
                        }

                        if (!chars) { this.motDePasse = ''; return; }

                        const array = new Uint8Array(this.longueur);
                        crypto.getRandomValues(array);
                        this.motDePasse = Array.from(array, b => chars[b % chars.length]).join('');
                    },

                    genererPassphrase() {
                        const array = new Uint32Array(this.nbMots);
                        crypto.getRandomValues(array);
                        const mots = Array.from(array, n => DICEWARE[n % DICEWARE.length]);
                        this.motDePasse = mots.join(this.separateur);
                    },

                    calculerEntropie() {
                        if (this.type === 'passphrase') {
                            this.entropie = Math.round(this.nbMots * Math.log2(7776));
                            this.tailleAlphabet = 7776;
                        } else {
                            let alphabet = 0;
                            const mdp = this.motDePasse;
                            if (/[a-z]/.test(mdp)) alphabet += 26;
                            if (/[A-Z]/.test(mdp)) alphabet += 26;
                            if (/[0-9]/.test(mdp)) alphabet += 10;
                            if (/[^a-zA-Z0-9]/.test(mdp)) alphabet += 32;
                            this.tailleAlphabet = alphabet;
                            this.entropie = alphabet > 0 ? Math.round(mdp.length * Math.log2(alphabet)) : 0;
                        }

                        if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = 'var(--border-bright)'; }
                        else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ef4444'; }
                        else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#f97316'; }
                        else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f59e0b'; }
                        else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = 'var(--accent)'; }
                        else { this.forceSegments = 5; this.forceLabel = 'Très fort'; this.forceColor = 'var(--accent-bright)'; }
                    },

                    async copierMdp() {
                        if (!this.motDePasse) return;
                        await navigator.clipboard.writeText(this.motDePasse);
                        showToast('success', 'Copié !', 'Le mot de passe sera effacé du presse-papier dans 30 secondes.');
                        setTimeout(() => navigator.clipboard.writeText(''), 30000);
                    }
                }
            }
        </script>
    @endpush
@endsection
