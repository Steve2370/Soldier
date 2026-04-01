import { showToast } from '../toast'

const DICEWARE = [
    'abri','acier','acte','adieu','agile','aigle','aimer','ainsi','ajout','aléa',
    'algue','allez','allié','alpin','ancre','angle','animer','anode','apnée','appel',
    'arbre','arche','ardeur','argent','armée','arôme','arrêt','artère','asile','aspect',
    'astre','atome','atout','aube','avenir','avoir','axe','azote','badge','balcon',
    'balle','banque','barque','bassin','bâton','bazar','béton','bijou','bilan','bogue',
    'boîte','bombe','bonne','bord','botte','boule','brique','brume','bulle','câble',
    'calme','canal','capot','cargo','carte','caser','cause','caverne','cendre','cercle',
    'champ','chaos','charme','charte','chasse','cheval','chiffe','chose','cible','ciment',
    'cirque','clair','classe','clef','clone','cobalt','coffre','coiffe','colère','combat',
    'comète','copie','corde','corps','cortex','coude','coulis','couple','crayon','crédit',
]

export function generateur() {
    return {
        type: 'password' as 'password' | 'passphrase',
        motDePasse: '' as string,
        longueur: 20 as number,
        nbMots: 5 as number,
        separateur: '-' as string,
        spinning: false as boolean,
        entropie: 0 as number,
        tailleAlphabet: 0 as number,
        forceSegments: 0 as number,
        forceLabel: '' as string,
        forceColor: 'var(--border-bright)' as string,

        options: {
            majuscules: true,
            minuscules: true,
            chiffres: true,
            symboles: true,
            similaires: true,
        },

        generer(): void {
            this.spinning = true
            setTimeout(() => { this.spinning = false }, 400)

            if (this.type === 'password') {
                this.genererPassword()
            } else {
                this.genererPassphrase()
            }
            this.calculerEntropie()
        },

        genererPassword(): void {
            let chars = ''
            if (this.options.minuscules) chars += 'abcdefghijklmnopqrstuvwxyz'
            if (this.options.majuscules) chars += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            if (this.options.chiffres) chars += '0123456789'
            if (this.options.symboles) chars += '!@#$%^&*()-_=+[]{}|;:,.<>?'
            if (this.options.similaires) chars = chars.replace(/[0O1lI]/g, '')
            if (!chars) { this.motDePasse = ''; return }

            const array = new Uint8Array(this.longueur)
            crypto.getRandomValues(array)
            this.motDePasse = Array.from(array, b => chars[b % chars.length]).join('')
        },

        genererPassphrase(): void {
            const array = new Uint32Array(this.nbMots)
            crypto.getRandomValues(array)
            this.motDePasse = Array.from(array, n => DICEWARE[n % DICEWARE.length]).join(this.separateur)
        },

        calculerEntropie(): void {
            if (this.type === 'passphrase') {
                this.entropie = Math.round(this.nbMots * Math.log2(7776))
                this.tailleAlphabet = 7776
            } else {
                let alphabet = 0
                const mdp = this.motDePasse
                if (/[a-z]/.test(mdp)) alphabet += 26
                if (/[A-Z]/.test(mdp)) alphabet += 26
                if (/[0-9]/.test(mdp)) alphabet += 10
                if (/[^a-zA-Z0-9]/.test(mdp)) alphabet += 32
                this.tailleAlphabet = alphabet
                this.entropie = alphabet > 0 ? Math.round(mdp.length * Math.log2(alphabet)) : 0
            }

            if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = 'var(--border-bright)' }
            else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ef4444' }
            else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#f97316' }
            else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f59e0b' }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = 'var(--accent)' }
            else { this.forceSegments = 5; this.forceLabel = 'Très fort';   this.forceColor = 'var(--accent-bright)' }
        },

        async copierMdp(): Promise<void> {
            if (!this.motDePasse) return
            try {
                await navigator.clipboard.writeText(this.motDePasse)
                showToast('success', 'Copié !', 'Effacé du presse-papier dans 30 secondes.')
                setTimeout(() => navigator.clipboard.writeText(''), 30000)
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },
    }
}
