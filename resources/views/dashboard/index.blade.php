@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div x-data="dashboard()">

        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 30px;">
            <div>
                <h1 style="font-size: 1.625rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
                    Mes services
                </h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">
                    <span style="color: var(--accent-bright); font-weight: 600;">{{ collect($services)->sum(fn($s) => count($s['elements'])) }}</span>
                    entrées · chiffrées AES-256-GCM
                </p>
            </div>
            <a href="{{ route('services.creer') }}" class="btn-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouveau service
            </a>
        </div>

        <div style="display: flex; gap: 12px; margin-bottom: 28px; align-items: center;">
            <div style="position: relative; flex: 1; max-width: 380px;">
                <svg style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="input" placeholder="Rechercher..." x-model="recherche" style="padding-left: 40px;">
            </div>
            <div style="display: flex; gap: 6px;">
                @foreach(['Tous', 'Favoris', 'Récents'] as $filtre)
                    <button
                        @click="filtreActif = '{{ $filtre }}'"
                        :class="filtreActif === '{{ $filtre }}' ? 'filtre-btn-active' : 'filtre-btn'"
                    >{{ $filtre }}</button>
                @endforeach
            </div>
        </div>

        @php $totalElements = collect($services)->sum(fn($s) => count($s['elements'])); @endphp

        @if($totalElements > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 12px;">
                @foreach($services as $groupe)
                    @foreach($groupe['elements'] as $element)
                        <div
                            class="service-card"
                            x-show="filtrerElement('{{ strtolower($element['label']) }}', '{{ strtolower($element['url'] ?? '') }}', {{ $element['favori'] ? 'true' : 'false' }})"
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
                                        {{ strtoupper(substr($element['label'], 0, 1)) }}
                                    </div>
                                </div>

                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $element['label'] }}
                                    </div>
                                    @if($element['url'])
                                        <div style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                                            {{ parse_url($element['url'], PHP_URL_HOST) ?? $element['url'] }}
                                        </div>
                                    @endif
                                </div>

                                <button
                                    @click="toggleFavori({{ $element['id'] }}, $el)"
                                    class="favori-btn {{ $element['favori'] ? 'favori-active' : '' }}"
                                    title="{{ $element['favori'] ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $element['favori'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </button>
                            </div>

                            <div style="background: rgba(107,127,138,0.5); border: 1px solid rgba(33,126,170,0.2); border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span style="font-size: 0.8125rem; color: var(--text-secondary); flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $element['donnees']['identifiant'] ?? '—' }}
                            </span>
                            </div>

                            <div style="display: flex; gap: 6px;">
                                <button
                                    @click="copierMdp('{{ addslashes($element['donnees']['mot_de_passe'] ?? '') }}')"
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
                                    <form method="POST" action="{{ route('services.supprimer', $element['id']) }}" @submit.prevent="confirmerSuppression">
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
            <div style="text-align: center; padding: 100px 20px;">
                <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <img src="{{ asset('assets/Soldier-Logo.png') }}"
                         alt="Logo"
                         style="width: 90px; height: auto;">
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">Coffre vide</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 28px; max-width: 300px; margin-left: auto; margin-right: auto;">
                    Commencez par ajouter votre premier service. Tout sera chiffré localement.
                </p>
                <a href="{{ route('services.creer') }}" class="btn-primary">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter un service
                </a>
            </div>
        @endif

    </div>

    <div x-data x-show="$store.modalSuppression.show" x-transition
         style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 99999; display: flex; align-items: center; justify-content: center;"
         @click.self="$store.modalSuppression.show = false">
        <div style="background: var(--bg-surface); border: 1px solid rgba(239,68,68,0.3); border-radius: 16px; padding: 28px; max-width: 380px; width: calc(100% - 40px);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <div>
                    <div style="font-weight: 700; color: var(--text-primary); font-size: 1rem;">Supprimer le service ?</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;" x-text="'« ' + $store.modalSuppression.label + ' »'"></div>
                </div>
            </div>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 22px; line-height: 1.6;">
                Cette action est irréversible. Le service sera déplacé dans la corbeille.
            </p>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button @click="$store.modalSuppression.show = false" class="btn-secondary">Annuler</button>
                <button @click="$store.modalSuppression.executer()" class="btn-danger">Supprimer</button>
            </div>
        </div>
    </div>

    <style>
        .service-card {
            background: var(--bg-surface);
            border: 1px solid rgba(33,126,170,0.25);
            border-radius: 12px;
            padding: 16px;
            transition: border-color 0.2s, transform 0.2s;
            cursor: default;
        }
        .service-card:hover {
            border-color: rgba(33,126,170,0.5);
            transform: translateY(-2px);
        }

        .favicon-wrapper {
            width: 44px; height: 44px;
            border-radius: 11px;
            background: rgba(107,127,138,0.6);
            border: 1px solid rgba(33,126,170,0.3);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
        }
        .favicon-fallback {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.125rem;
            color: var(--accent-bright);
            background: rgba(33,126,170,0.15);
        }

        .favori-btn {
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); padding: 4px;
            border-radius: 6px; transition: all 0.15s; flex-shrink: 0;
        }
        .favori-btn:hover { color: #f59e0b; }
        .favori-active { color: #f59e0b !important; }

        .action-chip {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            background: rgba(107,127,138,0.5);
            border: 1px solid rgba(33,126,170,0.25);
            border-radius: 8px; padding: 7px 10px;
            font-size: 0.8rem; color: var(--text-secondary);
            cursor: pointer; transition: all 0.15s;
            text-decoration: none;
            font-family: 'Audiowide', sans-serif;
            font-weight: 500;
        }
        .action-chip:hover {
            background: rgba(33,126,170,0.15);
            border-color: var(--accent-bright);
            color: var(--accent-bright);
        }
        .action-chip-danger:hover {
            background: rgba(239,68,68,0.1) !important;
            border-color: rgba(239,68,68,0.4) !important;
            color: #ef4444 !important;
        }

        .filtre-btn, .filtre-btn-active {
            padding: 7px 14px; border-radius: 8px;
            font-size: 0.8125rem; font-weight: 500;
            cursor: pointer; transition: all 0.15s;
            font-family: 'Audiowide', sans-serif;
        }
        .filtre-btn {
            background: rgba(107,127,138,0.5);
            border: 1px solid rgba(33,126,170,0.25);
            color: var(--text-secondary);
        }
        .filtre-btn:hover { border-color: var(--accent-bright); color: var(--text-primary); }
        .filtre-btn-active {
            background: rgba(33,126,170,0.2);
            color: var(--accent-bright);
            border: 1px solid rgba(33,126,170,0.4);
        }

        .btn-danger {
            background: rgba(239,68,68,0.1);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 9px; padding: 10px 20px;
            font-size: 0.875rem; cursor: pointer; transition: all 0.15s;
            display: inline-flex; align-items: center; gap: 8px;
            font-family: 'Audiowide', sans-serif;
        }
        .btn-danger:hover { background: rgba(239,68,68,0.2); border-color: #ef4444; }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('modalSuppression', {
                    show: false,
                    label: '',
                    form: null,
                    confirmer(form, label) {
                        this.form = form;
                        this.label = label;
                        this.show = true;
                    },
                    executer() {
                        if (this.form) this.form.submit();
                        this.show = false;
                    }
                });
            });

            function dashboard() {
                return {
                    recherche: '',
                    filtreActif: 'Tous',
                    showModal: false,
                    labelASupprimer: '',
                    formASupprimer: null,

                    filtrerElement(label, url, favori) {
                        if (this.filtreActif === 'Favoris' && !favori) return false;
                        if (!this.recherche) return true;
                        const q = this.recherche.toLowerCase();
                        return label.includes(q) || url.includes(q);
                    },

                    async copierMdp(mdp) {
                        if (!mdp) { showToast('warning', 'Aucun mot de passe', 'Ce service n\'a pas de mot de passe enregistré.'); return; }
                        try {
                            await navigator.clipboard.writeText(mdp);
                            showToast('success', 'Copié !', 'Le mot de passe sera effacé dans 30 secondes.');
                            setTimeout(() => navigator.clipboard.writeText(''), 30000);
                        } catch { showToast('error', 'Erreur', 'Impossible d\'accéder au presse-papier.'); }
                    },

                    async toggleFavori(id, btn) {
                        try {
                            const res = await fetch(`/services/${id}/favori`, {
                                method: 'PATCH',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            btn.classList.toggle('favori-active', data.favori);
                            btn.querySelector('svg').setAttribute('fill', data.favori ? 'currentColor' : 'none');
                            showToast('success', data.favori ? 'Ajouté aux favoris' : 'Retiré des favoris', '');
                        } catch { showToast('error', 'Erreur', 'Impossible de mettre à jour le favori.'); }
                    },

                    confirmerSuppression(event, label) {
                        this.formASupprimer = event.target;

                        const existing = document.getElementById('modal-suppression');
                        if (existing) existing.remove();

                        const modal = document.createElement('div');
                        modal.id = 'modal-suppression';
                        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999999;display:flex;align-items:center;justify-content:center;';
                        modal.innerHTML = `
        <div style="background:#202020;border:1px solid rgba(239,68,68,0.3);border-radius:16px;padding:28px;max-width:380px;width:calc(100% - 40px);position:relative;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <div>
                    <div style="font-weight:700;color:#fff;font-size:1rem;">Supprimer le service ?</div>
                    <div style="font-size:0.8rem;color:#808080;margin-top:2px;">« ${label} »</div>
                </div>
            </div>
            <p style="font-size:0.875rem;color:#e0e0e0;margin-bottom:22px;line-height:1.6;">Cette action est irréversible. Le service sera déplacé dans la corbeille.</p>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button id="modal-annuler" style="background:#404040;color:#e0e0e0;border:1px solid rgba(255,255,255,0.12);border-radius:9px;padding:10px 20px;font-size:0.875rem;cursor:pointer;font-family:Audiowide,sans-serif;">Annuler</button>
                <button id="modal-supprimer" style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.3);border-radius:9px;padding:10px 20px;font-size:0.875rem;cursor:pointer;font-family:Audiowide,sans-serif;">Supprimer</button>
            </div>
        </div>
    `;
                        document.body.appendChild(modal);
                        document.getElementById('modal-annuler').onclick = () => modal.remove();
                        modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
                        document.getElementById('modal-supprimer').onclick = () => {
                            modal.remove();
                            this.formASupprimer.submit();
                        };
                    },

                    executerSuppression() {
                    },
                }
            }
        </script>
    @endpush

    <div style="margin-top: 48px; padding: 20px 24px; background: linear-gradient(135deg, rgba(33,126,170,0.06), rgba(45,159,212,0.04)); border: 1px solid rgba(33,126,170,0.15); border-radius: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 40px; height: 40px; background: rgba(33,126,170,0.1); border: 1px solid rgba(45,159,212,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <div>
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary); margin-bottom: 2px;">Soldier est gratuit</div>
                <div style="font-size: 0.78rem; color: var(--text-muted);">Si vous aimez le projet, vous pouvez m'encourager</div>
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
