// @ts-ignore
import { showToast } from './toast'

function base64urlToUint8Array(base64url: string): Uint8Array {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/')
    const padded = base64.padEnd(base64.length + (4 - base64.length % 4) % 4, '=')
    const binary = atob(padded)
    return new Uint8Array(binary.split('').map(c => c.charCodeAt(0)))
}

function arrayBufferToBase64url(buffer: ArrayBuffer): string {
    const bytes = new Uint8Array(buffer)
    let binary = ''
    bytes.forEach(b => binary += String.fromCharCode(b))
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '')
}

function getCsrfToken(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? ''
}

export async function inscrirePasskey(nom?: string): Promise<void> {
    try {
        showToast('info', 'Passkey', 'Initialisation...')

        const optionsRes = await fetch('/passkeys/options-inscription', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        const options = await optionsRes.json()

        options.challenge = base64urlToUint8Array(options.challenge)
        options.user.id = base64urlToUint8Array(options.user.id)

        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map((c: any) => ({
                ...c,
                id: base64urlToUint8Array(c.id),
            }))
        }

        const credential = await navigator.credentials.create({
            publicKey: options,
        }) as PublicKeyCredential

        if (!credential) throw new Error('Création annulée')

        const response = credential.response as AuthenticatorAttestationResponse

        const payload = {
            nom: nom || '',
            credential: {
                id:    credential.id,
                rawId: arrayBufferToBase64url(credential.rawId),
                type: credential.type,
                response: {
                    clientDataJSON: arrayBufferToBase64url(response.clientDataJSON),
                    attestationObject: arrayBufferToBase64url(response.attestationObject),
                },
            },
        }

        const saveRes = await fetch('/passkeys/inscrire', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        })

        const result = await saveRes.json()

        if (result.success) {
            showToast('success', 'Passkey ajouté !', `« ${result.nom} » est maintenant enregistré.`)
            setTimeout(() => window.location.reload(), 1500)
        } else {
            showToast('error', 'Erreur', result.error ?? 'Impossible d\'enregistrer le passkey.')
        }

    } catch (err: any) {
        if (err.name === 'NotAllowedError') {
            showToast('warning', 'Annulé', 'L\'opération a été annulée.')
        } else {
            showToast('error', 'Erreur WebAuthn', err.message ?? 'Une erreur est survenue.')
        }
    }
}

export async function connecterAvecPasskey(): Promise<void> {
    try {
        showToast('info', 'Passkey', 'Vérification de votre identité...')

        const optionsRes = await fetch('/passkeys/options-connexion', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        const options = await optionsRes.json()

        options.challenge = base64urlToUint8Array(options.challenge)

        if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map((c: any) => ({
                ...c,
                id: base64urlToUint8Array(c.id),
            }))
        }

        const credential = await navigator.credentials.get({
            publicKey: options,
        }) as PublicKeyCredential

        if (!credential) throw new Error('Authentification annulée')

        const response = credential.response as AuthenticatorAssertionResponse

        const payload = {
            credential: {
                id: credential.id,
                rawId: arrayBufferToBase64url(credential.rawId),
                type: credential.type,
                response: {
                    clientDataJSON: arrayBufferToBase64url(response.clientDataJSON),
                    authenticatorData: arrayBufferToBase64url(response.authenticatorData),
                    signature: arrayBufferToBase64url(response.signature),
                    userHandle: response.userHandle ? arrayBufferToBase64url(response.userHandle) : null,
                },
            },
        }

        const result = await fetch('/passkeys/connecter', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        })

        const data = await result.json()

        if (data.success) {
            showToast('success', 'Authentifié !', 'Redirection...')
            setTimeout(() => window.location.href = data.redirect, 800)
        } else {
            showToast('error', 'Erreur', data.error ?? 'Authentification échouée.')
        }

    } catch (err: any) {
        if (err.name === 'NotAllowedError') {
            showToast('warning', 'Annulé', 'L\'opération a été annulée.')
        } else {
            showToast('error', 'Erreur WebAuthn', err.message ?? 'Une erreur est survenue.')
        }
    }
}

declare global {
    interface Window {
        inscrirePasskey: typeof inscrirePasskey
        connecterAvecPasskey: typeof connecterAvecPasskey
    }
}

window.inscrirePasskey = inscrirePasskey
window.connecterAvecPasskey = connecterAvecPasskey
