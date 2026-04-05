import Alpine from 'alpinejs'
import { showToast } from './toast'
import { dashboard } from './pages/dashboard'
import { afficherService } from './pages/afficher'
import { modifierService } from './pages/modifier'
import { generateur } from './pages/generateur'
import { settings, avatarUpload } from './pages/settings'
import { partage } from './pages/partage'
import { creerService } from './pages/creer'
import { inscrirePasskey, connecterAvecPasskey } from './pages/passkey'

declare global {
    interface Window {
        Alpine: typeof Alpine
        showToast: typeof showToast
        modifierService: typeof modifierService
        settings: typeof settings
        avatarUpload: typeof avatarUpload
        partage: typeof partage
        inscrirePasskey: typeof inscrirePasskey
        connecterAvecPasskey: typeof connecterAvecPasskey
    }
}

window.Alpine = Alpine
window.showToast = showToast
window.modifierService = modifierService
window.settings = settings
window.avatarUpload = avatarUpload
window.partage = partage
window.inscrirePasskey = inscrirePasskey
window.connecterAvecPasskey = connecterAvecPasskey

Alpine.data('dashboard', dashboard)
Alpine.data('afficherService', afficherService)
Alpine.data('generateur', generateur)
Alpine.data('creerService', creerService)

window.addEventListener('toast', (e: Event) => {
    const detail = (e as CustomEvent).detail
    if (detail?.titre) {
        showToast(detail.type || 'info', detail.titre, detail.message || '', detail.duration || 5000)
    }
})

Alpine.start()
