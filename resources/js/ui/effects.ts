/**
 * Effets visuels du thème Soldier.
 * Purement décoratifs : aucun secret n'est lu ni transmis. Tout est coupé
 * lorsque l'utilisateur a demandé à réduire les animations.
 */

const GLYPHS = 'ABCDEF0123456789#$%&@*<>/'
const B64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'

export function prefersReducedMotion(): boolean {
    return typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function randomFrom(set: string): string {
    return set.charAt(Math.floor(Math.random() * set.length))
}

/** Fait « déchiffrer » un texte : les caractères se stabilisent de gauche à droite. */
export function scramble(el: HTMLElement, finalText: string, duration = 900): Promise<void> {
    if (prefersReducedMotion() || finalText.length === 0) {
        el.textContent = finalText
        return Promise.resolve()
    }
    return new Promise((resolve) => {
        const start = performance.now()
        const step = (now: number) => {
            const progress = Math.min(1, (now - start) / duration)
            let out = ''
            for (let i = 0; i < finalText.length; i++) {
                const c = finalText.charAt(i)
                out += c === ' ' || progress >= (i + 1) / finalText.length ? c : randomFrom(GLYPHS)
            }
            el.textContent = out
            if (progress < 1) {
                requestAnimationFrame(step)
            } else {
                el.textContent = finalText
                resolve()
            }
        }
        requestAnimationFrame(step)
    })
}

/** Titres décorés : <span data-scramble data-scramble-loop="12">MOTS DE PASSE</span> */
function initScrambleTitles(): void {
    document.querySelectorAll<HTMLElement>('[data-scramble]').forEach((el) => {
        const text = (el.textContent ?? '').trim()
        if (!text || el.dataset.scrambleReady) return
        el.dataset.scrambleReady = '1'

        const sr = document.createElement('span')
        sr.className = 's-sr'
        sr.textContent = text
        el.after(sr)
        el.setAttribute('aria-hidden', 'true')

        const run = () => scramble(el, text, Number(el.dataset.scrambleDuration || 1000))
        run()
        const loop = Number(el.dataset.scrambleLoop || 0)
        if (loop > 0 && !prefersReducedMotion()) window.setInterval(run, loop * 1000)
    })
}

/** Compteurs : <span data-countup>248</span> */
function initCountUp(): void {
    const format = new Intl.NumberFormat('fr-CA')
    document.querySelectorAll<HTMLElement>('[data-countup]').forEach((el) => {
        const target = Number((el.textContent ?? '').replace(/[^\d.-]/g, ''))
        if (!Number.isFinite(target) || prefersReducedMotion()) return
        const duration = 1300
        const start = performance.now()
        const step = (now: number) => {
            const p = Math.min(1, (now - start) / duration)
            const eased = 1 - Math.pow(1 - p, 3)
            el.textContent = format.format(Math.round(target * eased))
            if (p < 1) requestAnimationFrame(step)
        }
        requestAnimationFrame(step)
    })
}

/** Jauges circulaires : <circle class="s-gauge-arc" r="36" data-gauge="86"> */
function initGauges(): void {
    document.querySelectorAll<SVGCircleElement>('circle[data-gauge]').forEach((circle) => {
        const r = Number(circle.getAttribute('r') || 0)
        const circumference = 2 * Math.PI * r
        const value = Math.max(0, Math.min(100, Number(circle.dataset.gauge || 0)))
        circle.style.strokeDasharray = String(circumference)
        circle.style.strokeDashoffset = String(circumference)
        requestAnimationFrame(() => requestAnimationFrame(() => {
            circle.style.strokeDashoffset = String(circumference * (1 - value / 100))
        }))
    })
}

/** Flux de « texte chiffré » purement visuel : <span data-cipher-stream="30"></span> */
function initCipherStreams(): void {
    const els = Array.from(document.querySelectorAll<HTMLElement>('[data-cipher-stream]'))
    if (els.length === 0) return
    const paint = () => {
        els.forEach((el) => {
            const n = Number(el.dataset.cipherStream || 28)
            let s = ''
            for (let i = 0; i < n; i++) s += randomFrom(B64)
            el.textContent = s + '=='
        })
    }
    paint()
    if (!prefersReducedMotion()) window.setInterval(paint, 140)
}

/**
 * Anneau TOTP : les codes changent toutes les 30 s alignées sur l'horloge Unix.
 * <circle data-totp-ring r="15"> + <span data-totp-left>
 */
function initTotpRing(): void {
    const rings = Array.from(document.querySelectorAll<SVGCircleElement>('circle[data-totp-ring]'))
    const labels = Array.from(document.querySelectorAll<HTMLElement>('[data-totp-left]'))
    if (rings.length === 0 && labels.length === 0) return
    const paint = () => {
        const left = 30 - (Math.floor(Date.now() / 1000) % 30)
        rings.forEach((circle) => {
            const c = 2 * Math.PI * Number(circle.getAttribute('r') || 0)
            circle.style.strokeDasharray = String(c)
            circle.style.strokeDashoffset = String(c * (1 - left / 30))
        })
        labels.forEach((el) => { el.textContent = String(left) })
    }
    paint()
    window.setInterval(paint, 1000)
}

/** Temps moyen estimé pour casser un secret de `bits` bits à 10 milliards d'essais par seconde. */
export function crackTime(bits: number): string {
    if (!Number.isFinite(bits) || bits <= 0) return '—'
    const seconds = Math.pow(2, bits) / 2 / 1e10
    const years = seconds / 31_557_600
    const fmt = new Intl.NumberFormat('fr-CA', { maximumFractionDigits: 0 })
    if (seconds < 1) return 'instantané'
    if (seconds < 60) return 'quelques secondes'
    if (seconds < 3600) return `${fmt.format(seconds / 60)} minutes`
    if (seconds < 86_400) return `${fmt.format(seconds / 3600)} heures`
    if (years < 1) return `${fmt.format(seconds / 86_400)} jours`
    if (years < 1e6) return `${fmt.format(years)} ans`
    if (years < 1e9) return `≈ ${fmt.format(years / 1e6)} millions d'années`
    if (years < 1.4e10) return `≈ ${fmt.format(years / 1e9)} milliards d'années`
    return "plus que l'âge de l'univers"
}

export function initEffects(): void {
    initTotpRing()
    initScrambleTitles()
    initCountUp()
    initGauges()
    initCipherStreams()
}
