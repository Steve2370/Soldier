import { showToast } from '../toast'
import { copySecret } from '../security/clipboard'

export function dashboard() {
    return {
        recherche: '' as string,
        filtreActif: 'Tous' as string,
        showModal: false,
        favoris: {} as Record<number, boolean>,

        filtrerElement(label: string, url: string, favori: boolean, id?: number): boolean {
            if (id !== undefined) favori = this.favoris[id] ?? favori
            if (this.filtreActif === 'Favoris' && !favori) return false
            if (!this.recherche) return true
            const q = this.recherche.toLowerCase()
            return label.toLowerCase().includes(q) || url.toLowerCase().includes(q)
        },

        async copierMdp(mdp: string): Promise<void> {
            if (!mdp) {
                showToast('warning', 'Aucun mot de passe', "Ce service n'a pas de mot de passe enregistré.")
                return
            }
            try {
                await copySecret(mdp)
                showToast('success', 'Copié !', 'Effacement tenté après 30 s si le navigateur le permet et si le contenu est inchangé.')
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },

        async toggleFavori(id: number, btn: HTMLElement): Promise<void> {
            try {
                const res = await fetch(`/services/${id}/favori`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content,
                        'Accept': 'application/json',
                    },
                })
                if (!res.ok) throw new Error('Favorite update rejected')
                const data = await res.json()
                if (typeof data.favori !== 'boolean') throw new Error('Invalid favorite response')
                this.favoris[id] = data.favori
                btn.classList.toggle('favori-active', data.favori)
                btn.title = data.favori ? 'Retirer des favoris' : 'Ajouter aux favoris'
                btn.querySelector('svg')?.setAttribute('fill', data.favori ? 'currentColor' : 'none')
                showToast('success', data.favori ? 'Ajouté aux favoris' : 'Retiré des favoris')
            } catch {
                showToast('error', 'Erreur', 'Impossible de mettre à jour le favori.')
            }
        },

        confirmerSuppression(event: Event, label: string): void {
            const form = event.target as HTMLFormElement

            document.getElementById('modal-suppression')?.remove()

            const modal = document.createElement('div')
            modal.id = 'modal-suppression'
            modal.style.cssText = [
                'position:fixed',
                'top:0',
                'left:0',
                'width:100%',
                'height:100%',
                'background:rgba(2,5,10,0.82);backdrop-filter:blur(6px)',
                'z-index:999999',
                'display:flex',
                'align-items:center',
                'justify-content:center',
                'font-family:Audiowide,sans-serif',
            ].join(';')

            modal.innerHTML = `
                <div style="background:rgba(6,14,22,0.97);border:1px solid rgba(255,90,100,0.45);border-radius:16px;box-shadow:0 0 50px rgba(255,90,100,0.18),0 24px 60px rgba(0,0,0,0.6);padding:28px;max-width:400px;width:calc(100% - 40px);">
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                        <div style="width:42px;height:42px;border-radius:11px;background:rgba(255,90,100,0.1);border:1px solid rgba(255,90,100,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff5a64" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </div>
                        <div>
                            <div style="color:#eef3f8;font-size:1rem;margin-bottom:3px;">Supprimer le service ?</div>
                            <div class="modal-service-label" style="font-size:0.8rem;color:#8fa2b8;"></div>
                        </div>
                    </div>
                    <p style="font-size:0.875rem;color:#c3cedb;margin-bottom:24px;line-height:1.65;">
                        Le service sera restaurable depuis la corbeille pendant 30 jours.
                    </p>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button id="modal-annuler" style="background:rgba(79,195,247,0.06);color:#c3cedb;border:1px solid #2a4258;border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;transition:all 0.15s;">
                            Annuler
                        </button>
                        <button id="modal-supprimer" style="background:rgba(255,90,100,0.12);color:#ff5a64;border:1px solid rgba(255,90,100,0.35);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;transition:all 0.15s;">
                            Supprimer
                        </button>
                    </div>
                </div>`

            document.body.appendChild(modal)

            const labelElement = modal.querySelector('.modal-service-label') as HTMLElement
            labelElement.textContent = `« ${label} »`

            const annulerBtn = document.getElementById('modal-annuler') as HTMLButtonElement
            const supprimerBtn = document.getElementById('modal-supprimer') as HTMLButtonElement

            annulerBtn.addEventListener('mouseover', () => { annulerBtn.style.background = 'rgba(79,195,247,0.14)' })
            annulerBtn.addEventListener('mouseout',  () => { annulerBtn.style.background = 'rgba(79,195,247,0.06)' })
            supprimerBtn.addEventListener('mouseover', () => { supprimerBtn.style.background = 'rgba(255,90,100,0.25)' })
            supprimerBtn.addEventListener('mouseout',  () => { supprimerBtn.style.background = 'rgba(255,90,100,0.12)' })

            const close = () => modal.remove()
            annulerBtn.addEventListener('click', close)
            modal.addEventListener('click', (e) => { if (e.target === modal) close() })
            supprimerBtn.addEventListener('click', () => {
                close()
                form.submit()
            })

            modal.style.opacity = '0'
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { modal.style.opacity = '1'; modal.style.transition = 'opacity 0.2s ease' })
            })
        },
    }
}
