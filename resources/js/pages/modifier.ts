import { showToast } from '../toast'

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
            if (this.url) {
                try {
                    const domain = new URL(this.url).hostname
                    this.faviconUrl = `https://www.google.com/s2/favicons?domain=${domain}&sz=128`
                    return
                } catch {}
            }
            if (this.label && this.label.length > 1) {
                const nom = this.label.toLowerCase().replace(/\s+/g, '')
                this.faviconUrl = `https://www.google.com/s2/favicons?domain=${nom}.com&sz=128`
            } else {
                this.faviconUrl = ''
            }
        },

        genererMdp(): void {
            const chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*()-_=+'
            const array = new Uint8Array(20)
            crypto.getRandomValues(array)
            this.motDePasse = Array.from(array, b => chars[b % chars.length]).join('')
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

            if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = 'rgba(33,126,170,0.2)' }
            else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ef4444' }
            else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#f97316' }
            else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f59e0b' }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = 'var(--accent-bright)' }
            else { this.forceSegments = 5; this.forceLabel = 'Très fort ✓';  this.forceColor = '#2d9fd4' }
        },

        async copier(texte: string): Promise<void> {
            if (!texte) return
            try {
                await navigator.clipboard.writeText(texte)
                showToast('success', 'Copié !', 'Effacé dans 30 secondes.')
                setTimeout(() => navigator.clipboard.writeText(''), 30000)
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },
    }
}
