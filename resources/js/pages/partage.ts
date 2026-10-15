import { showToast } from '../toast'

export function partage(hasErrors: boolean, initialPermission: string) {
    return {
        showFormulaire: hasErrors as boolean,
        permission: initialPermission as string,
        coffreSelectionne: (document.getElementById('coffre_id') as HTMLSelectElement)?.value || '' as string,

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
                'background:rgba(2,5,10,0.82);backdrop-filter:blur(6px)',
                'z-index:999999',
                'display:flex',
                'align-items:center',
                'justify-content:center',
                'font-family:Audiowide,sans-serif',
            ].join(';')

            modal.innerHTML = `
                <div style="background:rgba(6,14,22,0.97);border:1px solid rgba(255,90,100,0.45);border-radius:16px;box-shadow:0 0 50px rgba(255,90,100,0.18),0 24px 60px rgba(0,0,0,0.6);padding:28px;max-width:400px;width:calc(100% - 40px);">
                    <h3 style="font-size:1rem;color:#eef3f8;margin-bottom:10px;">Révoquer l'accès</h3>
                    <p style="color:#c3cedb;font-size:0.875rem;margin-bottom:22px;line-height:1.6;">
                        <strong class="revocation-name" style="color:#eef3f8;"></strong> n'aura plus accès à ce coffre immédiatement.
                    </p>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button id="rev-annuler" style="background:rgba(79,195,247,0.06);color:#c3cedb;border:1px solid #2a4258;border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;">
                            Annuler
                        </button>
                        <button id="rev-confirmer" style="background:rgba(255,90,100,0.12);color:#ff5a64;border:1px solid rgba(255,90,100,0.35);border-radius:9px;padding:10px 20px;font-size:0.85rem;cursor:pointer;font-family:Audiowide,sans-serif;">
                            Révoquer
                        </button>
                    </div>
                </div>
            `

            document.body.appendChild(modal)
            const nameElement = modal.querySelector('.revocation-name') as HTMLElement
            nameElement.textContent = nom

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
