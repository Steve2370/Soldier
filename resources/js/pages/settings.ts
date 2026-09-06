import QRCode from 'qrcode'
import { showToast } from '../toast'

interface SettingsData {
    onglet: string
    showDesactiverEmail: boolean
    showDesactiverTotp: boolean
    showTotpSetup: boolean
    totpSecret: string
    configurerTotp(url: string): Promise<void>
    copierTotp(): Promise<void>
}

/**
 * Propriétés magiques injectées par Alpine sur le composant à l'exécution
 * (non déclarées par @types/alpinejs, qui ne type que l'objet global Alpine).
 */
interface AlpineComponentThis extends SettingsData {
    $nextTick(): Promise<void>
    $refs: Record<string, HTMLElement>
}

export function settings(initialOnglet: string, hasErrors: boolean): SettingsData {
    return {
        onglet: initialOnglet,
        showDesactiverEmail: hasErrors,
        showDesactiverTotp: false,
        showTotpSetup: false,
        totpSecret: '',

        async configurerTotp(this: AlpineComponentThis, url: string): Promise<void> {
            this.showTotpSetup = true
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content,
                        'Accept': 'application/json',
                    },
                })
                const data = await res.json()
                this.totpSecret = data.secret

                // Le QR code est généré entièrement côté client à partir de l'otpauth_url :
                // aucune donnée du secret TOTP ne transite plus vers un service tiers.
                await this.$nextTick()
                const canvas = this.$refs.totpQrCanvas as HTMLCanvasElement | undefined
                if (canvas && data.otpauth_url) {
                    await QRCode.toCanvas(canvas, data.otpauth_url, { width: 150, margin: 1 })
                }
            } catch {
                showToast('error', 'Erreur', 'Impossible de générer le QR code.')
            }
        },

        async copierTotp(): Promise<void> {
            if (!this.totpSecret) return
            try {
                await navigator.clipboard.writeText(this.totpSecret)
                showToast('success', 'Secret copié !')
            } catch {
                showToast('error', 'Erreur', "Impossible d'accéder au presse-papier.")
            }
        },
    }
}

export function avatarUpload(initialUrl: string) {
    return {
        previewUrl: initialUrl as string,
        uploading: false as boolean,

        previewFile(event: Event): void {
            const input = event.target as HTMLInputElement
            const file = input.files?.[0]
            if (!file) return

            const reader = new FileReader()
            reader.onload = (e) => {
                this.previewUrl = e.target?.result as string
            }
            reader.readAsDataURL(file)
            this.uploading = true
        },
    }
}
