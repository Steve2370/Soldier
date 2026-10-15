import { showToast } from '../toast'
import { randomPassword } from '../security/random'
import { copySecret } from '../security/clipboard'

interface ModifierState {
    label: string
    url: string
    motDePasse: string
    showMdp: boolean
    faviconUrl: string
    entropie: number
    forceSegments: number
    forceLabel: string
    forceColor: string
    labelInitiale: string
    urlAffichee: string
    mettreAJourFavicon(): void
    genererMdp(): void
    calculerForce(): void
    copier(texte: string): Promise<void>
}

export function modifierService(initialLabel: string, initialUrl: string, initialFavicon: string): ModifierState {
    return {
        label: initialLabel,
        url: initialUrl,
        motDePasse: '',
        showMdp: false,
        faviconUrl: initialFavicon,
        entropie: 0,
        forceSegments: 0,
        forceLabel: '',
        forceColor: 'rgba(33,126,170,0.4)',

        get labelInitiale(): string {
            return this.label ? this.label.charAt(0).toUpperCase() : '?'
        },

        get urlAffichee(): string {
            if (!this.url) return ''
            try { return new URL(this.url).hostname } catch { return this.url }
        },

        mettreAJourFavicon(): void {
            // Passe par notre propre proxy (/favicon) plutôt que d'appeler Google
            // directement depuis le navigateur : sinon, chaque frappe révélerait
            // à Google le domaine en cours de saisie (voir M3 de l'audit sécurité).
            if (this.url) {
                try {
                    const domain = new URL(this.url).hostname
                    this.faviconUrl = `/favicon?domain=${encodeURIComponent(domain)}`
                    return
                } catch {}
            }
            if (this.label && this.label.length > 1) {
                const nom = this.label.toLowerCase().replace(/\s+/g, '')
                this.faviconUrl = `/favicon?domain=${encodeURIComponent(nom + '.com')}`
            } else {
                this.faviconUrl = ''
            }
        },

        genererMdp(): void {
            const chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*()-_=+'
            this.motDePasse = randomPassword(chars, 20)
            this.showMdp = true
            this.calculerForce()
            const input = document.getElementById('mot_de_passe') as HTMLInputElement
            if (input) {
                input.type = 'text'
                input.value = this.motDePasse
            }
        },

        calculerForce(): void {
            const mdp = this.motDePasse
            let alphabet = 0
            if (/[a-z]/.test(mdp)) alphabet += 26
            if (/[A-Z]/.test(mdp)) alphabet += 26
            if (/[0-9]/.test(mdp)) alphabet += 10
            if (/[^a-zA-Z0-9]/.test(mdp)) alphabet += 32
            this.entropie = alphabet > 0 ? Math.round(mdp.length * Math.log2(alphabet)) : 0

            if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = '#16283a' }
            else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ff5a64' }
            else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#ff8a50' }
            else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f2b544' }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = '#4fc3f7' }
            else { this.forceSegments = 5; this.forceLabel = 'Très fort ✓';  this.forceColor = '#3df5c0' }
        },

        async copier(texte: string): Promise<void> {
            if (!texte) return
            try {
                await copySecret(texte)
                showToast('success', 'Copié !', 'Effacement tenté après 30 s si le navigateur le permet et si le contenu est inchangé.')
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },
    }
}
