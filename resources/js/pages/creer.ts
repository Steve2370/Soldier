import { randomPassword } from '../security/random'

export function creerService(initial: { type?: string, label?: string, url?: string } = {}) {
    return {
        type: initial.type ?? 'login',
        label: initial.label ?? '',
        url: initial.url ?? '',
        motDePasse: '' as string,
        showMdp: false as boolean,
        faviconUrl: '' as string,
        entropie: 0 as number,
        forceSegments: 0 as number,
        forceLabel: '' as string,
        forceColor: 'rgba(33,126,170,0.4)' as string,

        options: {
            majuscules: true,
            minuscules: true,
            chiffres: true,
            symboles: true,
            similaires: true,
        },

        get nomPlaceholder(): string {
            const p: Record<string, string> = {
                login: 'Ex : GitHub, Netflix...',
                carte: 'Ex : Visa Desjardins',
                note: 'Ex : Codes de récupération',
                identite: 'Ex : Passeport Canada',
                cles: 'Ex : Serveur prod',
                autre: 'Ex : Token API',
            }
            return p[this.type] || 'Nom'
        },

        toggleMdp(): void {
            this.showMdp = !this.showMdp
            const input = document.getElementById('mdp_visuel') as HTMLInputElement
            if (input) {
                input.type = this.showMdp ? 'text' : 'password'
            }
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
            const input = document.getElementById('mdp_visuel') as HTMLInputElement
            if (input) input.type = 'text'
            this.calculerForce()
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
            else { this.forceSegments = 5; this.forceLabel = 'Très fort ✓'; this.forceColor = '#3df5c0' }
        },
    }
}
