import { showToast } from '../toast'
import { copySecret } from '../security/clipboard'

export function afficherService() {
    return {
        showMdp: false as boolean,
        showModal: false,

        async copier(texte: string, label: string): Promise<void> {
            if (!texte) return
            try {
                await copySecret(texte)
                showToast('success', `${label} copié`, 'Effacement tenté après 30 s si le navigateur le permet et si le contenu est inchangé.')
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },

        confirmerSuppression(event: Event): void {
            const form = event.target as HTMLFormElement

            document.getElementById('modal-suppression-afficher')?.remove()

            const modal = document.createElement('div')
            modal.id = 'modal-suppression-afficher'
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
                    <h3 style="font-size:1rem;color:#eef3f8;margin-bottom:10px;">Confirmer la suppression</h3>
                    <p style="color:#c3cedb;font-size:0.875rem;margin-bottom:22px;line-height:1.6;">
                        Ce service sera placé dans la corbeille pendant 30 jours.
                    </p>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button id="aff-annuler" style="background:rgba(79,195,247,0.06);color:#c3cedb;border:1px solid #2a4258;border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;">
                            Annuler
                        </button>
                        <button id="aff-supprimer" style="background:rgba(255,90,100,0.12);color:#ff5a64;border:1px solid rgba(255,90,100,0.35);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;">
                            Supprimer
                        </button>
                    </div>
                </div>
            `

            document.body.appendChild(modal)

            const close = () => modal.remove()
            document.getElementById('aff-annuler')?.addEventListener('click', close)
            modal.addEventListener('click', (e) => { if (e.target === modal) close() })
            document.getElementById('aff-supprimer')?.addEventListener('click', () => {
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
