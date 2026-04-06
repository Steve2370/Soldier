import { showToast } from '../toast'

export function partage(hasErrors: boolean, initialPermission: string) {
    return {
        showFormulaire: hasErrors as boolean,
        permission: initialPermission as string,
        coffreSelectionne: '' as string,

        confirmerRevocation(event: Event, nom: string): void {
            const form = event.target as HTMLFormElement

            document.getElementById('modal-revocation')?.remove()

            const modal = document.createElement('div')
            modal.id = 'modal-revocation'
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
                    <h3 style="font-size:1rem;font-weight:700;color:#ffffff;margin-bottom:10px;">Révoquer l'accès</h3>
                    <p style="color:#e0e0e0;font-size:0.875rem;margin-bottom:22px;line-height:1.6;">
                        <strong style="color:#ffffff;">${nom}</strong> n'aura plus accès à ce coffre immédiatement.
                    </p>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button id="rev-annuler" style="background:#404040;color:#e0e0e0;border:1px solid rgba(255,255,255,0.12);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;">
                            Annuler
                        </button>
                        <button id="rev-confirmer" style="background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.35);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;">
                            Révoquer
                        </button>
                    </div>
                </div>
            `

            document.body.appendChild(modal)

            const close = () => modal.remove()
            document.getElementById('rev-annuler')?.addEventListener('click', close)
            modal.addEventListener('click', (e) => { if (e.target === modal) close() })
            document.getElementById('rev-confirmer')?.addEventListener('click', () => {
                close()
                showToast('warning', 'Accès révoqué', `${nom} n'a plus accès à ce coffre.`)
                form.submit()
            })

            modal.style.opacity = '0'
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { modal.style.opacity = '1'; modal.style.transition = 'opacity 0.2s ease' })
            })
        },
    }
}
