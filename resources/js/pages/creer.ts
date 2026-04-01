
export function creerService() {
    return {
        type: 'login' as string,
        label: '' as string,
        url: '' as string,
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

            if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = 'rgba(33,126,170,0.2)' }
            else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ef4444' }
            else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#f97316' }
            else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f59e0b' }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = 'var(--accent-bright)' }
            else { this.forceSegments = 5; this.forceLabel = 'Très fort ✓'; this.forceColor = '#2d9fd4' }
        },
    }
}
