export type ToastType = 'success' | 'error' | 'warning' | 'info'

interface ToastOptions {
    duration?: number
    message?: string
}

let container: HTMLElement | null = null

function getContainer(): HTMLElement {
    if (container && document.body.contains(container)) return container

    container = document.createElement('div')
    container.id = 'toast-container'
    container.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999999;
        display: flex;
        flex-direction: column-reverse;
        gap: 10px;
        pointer-events: none;
    `
    document.body.appendChild(container)
    return container
}

const ICONS: Record<ToastType, string> = {
    success: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
    error: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    warning: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    info: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
}

const COLORS: Record<ToastType, { bg: string; border: string; icon: string; bar: string }> = {
    success: { bg: 'rgba(15,15,15,0.97)', border: 'rgba(34,197,94,0.35)',   icon: '#22c55e', bar: '#22c55e' },
    error: { bg: 'rgba(15,15,15,0.97)', border: 'rgba(239,68,68,0.35)',   icon: '#ef4444', bar: '#ef4444' },
    warning: { bg: 'rgba(15,15,15,0.97)', border: 'rgba(245,158,11,0.35)',  icon: '#f59e0b', bar: '#f59e0b' },
    info: { bg: 'rgba(15,15,15,0.97)', border: 'rgba(45,159,212,0.4)',   icon: '#2d9fd4', bar: '#2d9fd4' },
}

export function showToast(
    type: ToastType,
    titre: string,
    message: string = '',
    duration: number = 5000
): void {
    const c = getContainer()
    const colors = COLORS[type]

    const toast = document.createElement('div')
    toast.style.cssText = `
        min-width: 300px;
        max-width: 380px;
        border-radius: 14px;
        border: 1px solid ${colors.border};
        background: ${colors.bg};
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0,0,0,0.7);
        backdrop-filter: blur(16px);
        pointer-events: all;
        transform: translateY(16px);
        opacity: 0;
        transition: all 0.3s ease;
        font-family: 'Audiowide', sans-serif;
    `

    toast.innerHTML = `
        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;">
            <div style="width:28px;height:28px;border-radius:8px;background:${colors.icon}1a;color:${colors.icon};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                ${ICONS[type]}
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:0.875rem;color:#ffffff;">${titre}</div>
                ${message ? `<div style="font-size:0.8rem;color:#e0e0e0;margin-top:3px;">${message}</div>` : ''}
            </div>
            <button class="toast-close-btn" style="background:none;border:none;color:#808080;cursor:pointer;padding:2px;display:flex;align-items:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="height:3px;background:rgba(255,255,255,0.06);">
            <div class="toast-bar" style="height:100%;background:${colors.bar};width:100%;transition:width ${duration}ms linear;"></div>
        </div>
    `

    c.appendChild(toast)

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)'
            toast.style.opacity = '1'
        })
    })

    requestAnimationFrame(() => {
        const bar = toast.querySelector('.toast-bar') as HTMLElement
        if (bar) {
            setTimeout(() => { bar.style.width = '0%' }, 50)
        }
    })

    const closeBtn = toast.querySelector('.toast-close-btn') as HTMLElement
    const dismiss = () => {
        toast.style.transform = 'translateY(8px)'
        toast.style.opacity = '0'
        setTimeout(() => toast.remove(), 300)
    }

    closeBtn?.addEventListener('click', dismiss)
    setTimeout(dismiss, duration)
}

declare global {
    interface Window {
        showToast: typeof showToast
    }
}

window.showToast = showToast
