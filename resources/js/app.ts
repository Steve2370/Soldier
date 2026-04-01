import Alpine from 'alpinejs'
// @ts-ignore
import { showToast } from './toast'
// @ts-ignore
import { dashboard } from './pages/dashboard'
// @ts-ignore
import { afficherService } from './pages/afficher'
// @ts-ignore
import { modifierService } from './pages/modifier'
// @ts-ignore
import { generateur } from './pages/generateur'
// @ts-ignore
import { settings, avatarUpload } from './pages/settings'
// @ts-ignore
import { partage } from './pages/partage'
import { creerService } from './pages/creer'

declare global {
    interface Window {
        Alpine: typeof Alpine
        showToast: typeof showToast
    }
}

window.Alpine = Alpine
window.showToast = showToast

Alpine.data('dashboard', dashboard)
Alpine.data('afficherService', afficherService)
Alpine.data('generateur', generateur)
Alpine.data('creerService', creerService)

window.modifierService = modifierService
window.settings = settings
window.avatarUpload = avatarUpload
window.partage = partage

declare global {
    interface Window {
        modifierService: typeof modifierService
        settings: typeof settings
        avatarUpload: typeof avatarUpload
        partage: typeof partage
    }
}

window.addEventListener('toast', (e: Event) => {
    const detail = (e as CustomEvent).detail
    if (detail?.titre) {
        showToast(detail.type || 'info', detail.titre, detail.message || '', detail.duration || 5000)
    }
})

Alpine.start()
