import { showToast } from '../toast'

export function dashboard() {
    return {
        recherche: '' as string,
        filtreActif: 'Tous' as string,

        filtrerElement(label: string, url: string, favori: boolean): boolean {
            if (this.filtreActif === 'Favoris' && !favori) return false
            if (!this.recherche) return true
            const q = this.recherche.toLowerCase()
            return label.includes(q) || url.includes(q)
        },

        async copierMdp(mdp: string): Promise<void> {
            if (!mdp) {
                showToast('warning', 'Aucun mot de passe', "Ce service n'a pas de mot de passe enregistré.")
                return
            }
            try {
                await navigator.clipboard.writeText(mdp)
                showToast('success', 'Copié !', 'Le mot de passe sera effacé dans 30 secondes.')
                setTimeout(() => navigator.clipboard.writeText(''), 30000)
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
                const data = await res.json()
                btn.classList.toggle('favori-active', data.favori)
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
                'background:rgba(0,0,0,0.75)',
                'z-index:999999',
                'display:flex',
                'align-items:center',
                'justify-content:center',
                'font-family:Audiowide,sans-serif',
            ].join(';')

            modal.innerHTML = `
                <div style="background:#202020;border:1px solid rgba(239,68,68,0.35);border-radius:16px;padding:28px;max-width:400px;width:calc(100% - 40px);">
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                        <div style="width:42px;height:42px;border-radius:11px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#ffffff;font-size:1rem;margin-bottom:3px;">Supprimer le service ?</div>
                            <div style="font-size:0.8rem;color:#808080;">« ${label} »</div>
                        </div>
                    </div>
                    <p style="font-size:0.875rem;color:#e0e0e0;margin-bottom:24px;line-height:1.65;">
                        Cette action est irréversible. Le service sera déplacé dans la corbeille.
                    </p>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button id="modal-annuler" style="background:#404040;color:#e0e0e0;border:1px solid rgba(255,255,255,0.12);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;transition:all 0.15s;">
                            Annuler
                        </button>
                        <button id="modal-supprimer" style="background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.35);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;transition:all 0.15s;">
                            Supprimer
                        </button>
                    </div>
                </div>`

            document.body.appendChild(modal)

            const annulerBtn = document.getElementById('modal-annuler') as HTMLButtonElement
            const supprimerBtn = document.getElementById('modal-supprimer') as HTMLButtonElement

            annulerBtn.addEventListener('mouseover', () => { annulerBtn.style.background = '#505050' })
            annulerBtn.addEventListener('mouseout',  () => { annulerBtn.style.background = '#404040' })
            supprimerBtn.addEventListener('mouseover', () => { supprimerBtn.style.background = 'rgba(239,68,68,0.25)' })
            supprimerBtn.addEventListener('mouseout',  () => { supprimerBtn.style.background = 'rgba(239,68,68,0.12)' })

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
