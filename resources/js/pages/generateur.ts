import { showToast } from '../toast'
import { randomIndices, randomPassword } from '../security/random'
import { copySecret } from '../security/clipboard'

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
        nbMots: 12 as number,
        dictionnaireTaille: DICEWARE.length,
        separateur: '-' as string,
        spinning: false as boolean,
        entropie: 0 as number,
        tailleAlphabet: 0 as number,
        forceSegments: 0 as number,
        forceLabel: '' as string,
        forceColor: '#16283a' as string,

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
            this.tailleAlphabet = chars.length
            this.motDePasse = randomPassword(chars, this.longueur)
        },

        genererPassphrase(): void {
            this.motDePasse = randomIndices(DICEWARE.length, this.nbMots).map(i => DICEWARE[i]).join(this.separateur)
        },

        calculerEntropie(): void {
            if (this.type === 'passphrase') {
                this.entropie = Math.round(this.nbMots * Math.log2(DICEWARE.length))
                this.tailleAlphabet = DICEWARE.length
            } else {
                this.entropie = this.tailleAlphabet > 0 ? Math.round(this.motDePasse.length * Math.log2(this.tailleAlphabet)) : 0
            }

            if (this.entropie === 0) { this.forceSegments = 0; this.forceLabel = ''; this.forceColor = '#16283a' }
            else if (this.entropie < 40) { this.forceSegments = 1; this.forceLabel = 'Très faible'; this.forceColor = '#ff5a64' }
            else if (this.entropie < 60) { this.forceSegments = 2; this.forceLabel = 'Faible'; this.forceColor = '#ff8a50' }
            else if (this.entropie < 80) { this.forceSegments = 3; this.forceLabel = 'Moyen'; this.forceColor = '#f2b544' }
            else if (this.entropie < 100) { this.forceSegments = 4; this.forceLabel = 'Fort'; this.forceColor = '#4fc3f7' }
            else { this.forceSegments = 5; this.forceLabel = 'Très fort';   this.forceColor = '#3df5c0' }
        },

        async copierMdp(): Promise<void> {
            if (!this.motDePasse) return
            try {
                await copySecret(this.motDePasse)
                showToast('success', 'Copié !', 'Effacement tenté après 30 s si le navigateur le permet et si le contenu est inchangé.')
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },
    }
}
