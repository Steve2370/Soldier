import { showToast } from '../toast'

export function settings(initialOnglet: string, hasErrors: boolean) {
    return {
        onglet: initialOnglet as string,
        showDesactiverEmail: hasErrors as boolean,
        showTotpSetup: false as boolean,
        totpQrUrl: '' as string,
        totpSecret: '' as string,

        async configurerTotp(url: string): Promise<void> {
            this.showTotpSetup = true
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content,
                        'Accept': 'application/json',
                    },
                })
                const data = await res.json()
                this.totpQrUrl = data.qr_url
                this.totpSecret = data.secret
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
