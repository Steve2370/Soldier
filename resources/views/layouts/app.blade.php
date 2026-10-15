<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#02050a">
    <title>@yield('title', 'Soldier') Password Manager</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=999">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?v=999">
    <link rel="apple-touch-icon" href="{{ asset('assets/Soldier-Logotype.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>

<body x-data="{ ...toastManager(), mobileMenu: false }" @toast.window="addToast($event.detail)" class="s-grid-bg">

<x-soldier.backdrop fixed :floor="false" :locks="4" opacity="0.14" />

@auth
    <div class="mobile-topbar">
        <button @click="mobileMenu = !mobileMenu" class="mobile-menu-btn" aria-label="Menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <g x-show="!mobileMenu"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></g>
                <g x-show="mobileMenu"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></g>
            </svg>
        </button>
        <div class="sidebar-brand" style="padding: 0;">
            <span class="s-logo-ring"><img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Soldier" style="width: 32px; height: 32px; border-radius: 50%;"></span>
            <span style="font-size: 15px; letter-spacing: .14em;">SOLDIER</span>
        </div>
        <span class="s-dot" style="margin-right: 14px;" aria-hidden="true"></span>
    </div>

    <div x-show="mobileMenu" @click="mobileMenu = false"
         style="position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 39;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <aside class="sidebar" :class="mobileMenu ? 'sidebar-open' : ''">
        <div class="sidebar-brand">
            <span class="s-logo-ring"><img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Logo" style="width: 34px; height: 34px; border-radius: 50%;"></span>
            <span style="font-size: 16px; letter-spacing: .14em;">SOLDIER</span>
        </div>

        <nav style="padding: 4px 0 10px; flex: 1; overflow-y: auto;">
            <div class="nav-section-label">Principal</div>

            <a href="{{ route('dashboard') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('dashboard') || request()->routeIs('services.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Tableau de bord
            </a>

            <a href="{{ route('generateur') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('generateur') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/></svg>
                Générateur
            </a>

            <a href="{{ route('partage.index') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('partage.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>
                Partage
            </a>

            <a href="{{ route('famille.index') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('famille.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-7 7-7s7 3 7 7"/><path d="M16 4a4 4 0 0 1 0 8M22 21c0-3-2-5.5-5-6.5"/></svg>
                Famille
                @if(!auth()->user()->hasFamilyPlan() && !auth()->user()->familyMembership)
                    <span class="nav-badge">NEW</span>
                @endif
            </a>

            <a href="{{ route('corbeille') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('corbeille*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/></svg>
                Corbeille
            </a>

            <div class="nav-section-label">Compte</div>

            <a href="{{ route('settings') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('settings*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/><circle cx="15" cy="6" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                Paramètres
            </a>

            @if(auth()->user()->is_admin)
                <div class="nav-section-label">Administration</div>
                <a href="{{ route('admin.index') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/></svg>
                    Panel Admin
                </a>
                <a href="{{ route('admin.logs') }}" @click="mobileMenu = false" class="nav-item {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Logs d'activité
                </a>
            @endif
        </nav>

        <div style="padding: 12px 12px 16px; border-top: 1px solid #12202e; display: flex; flex-direction: column; gap: 10px; position: relative; z-index: 1;">
            <a href="https://paypal.me/BriceSteve" target="_blank" rel="noopener noreferrer" class="card s-glow" style="padding: 12px 14px; display: flex; gap: 12px; align-items: center; color: var(--text-secondary); font-size: 0.72rem; line-height: 1.5; text-decoration: none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f5c870" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M4 8h13v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8zM17 10h2a2 2 0 0 1 0 4h-2M8 3v2M12 3v2"/></svg>
                <span>Soldier est gratuit.<br><span style="color: #8fd8ff;">Offrez moi un café 😄</span></span>
            </a>
            <div style="display: flex; align-items: center; gap: 10px; padding: 6px 4px 0;">
                <div style="position: relative; width: 36px; height: 36px; border-radius: 50%; background: #12263a; color: #8fd8ff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0;">
                    <div style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        @elseif(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <span style="position: absolute; right: -1px; bottom: -1px; width: 10px; height: 10px; border-radius: 50%; background: var(--neon); border: 2px solid #03080e;"></span>
                </div>
                <div style="overflow: hidden; flex: 1; min-width: 0;">
                    <div style="font-size: 0.78rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</div>
                    <div class="s-mono" style="font-size: 0.68rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('deconnexion') }}">
                @csrf
                <button type="submit" class="btn-secondary" style="width: 100%; min-height: 40px; font-size: 0.75rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>
@endauth

<main class="{{ Auth::check() ? 'main-content' : '' }}">
    @auth
        <div class="app-topbar">
            <span class="s-chip s-chip-ok"><span class="s-dot" aria-hidden="true"></span>Coffre déverrouillé</span>
            <span class="s-session-label">Verrouillage auto <b id="s-session-left">60:00</b></span>
            <form method="POST" action="{{ route('deconnexion') }}">
                @csrf
                <button type="submit" class="btn-secondary" style="min-height: 38px; padding: 0 14px; font-size: 0.72rem;" title="Verrouiller le coffre et se déconnecter">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    Verrouiller
                </button>
            </form>
        </div>
        <div class="s-session-bar" aria-hidden="true" style="margin-bottom: 30px;"><span id="s-session-bar"></span></div>
    @endauth
    @yield('content')
</main>

<div style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column-reverse; gap: 10px; pointer-events: none;">
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="'toast-item toast-' + toast.type"
            style="pointer-events: all; min-width: 300px; max-width: 380px;"
        >
            <div style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px;">
                <div class="toast-icon" :class="'toast-icon-' + toast.type">
                    <template x-if="toast.type === 'success'"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></template>
                    <template x-if="toast.type === 'error'"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></template>
                    <template x-if="toast.type === 'warning'"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></template>
                    <template x-if="toast.type === 'info'"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></template>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.85rem; color: var(--text-primary);" x-text="toast.titre"></div>
                    <div style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 3px;" x-text="toast.message" x-show="toast.message"></div>
                </div>
                <button @click="removeToast(toast.id)" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 2px;" aria-label="Fermer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div style="height: 2px; background: rgba(255,255,255,0.06);">
                <div :class="'toast-bar-' + toast.type" :style="'animation-duration:' + toast.duration + 'ms; height:100%; animation: toast-shrink linear forwards;'"></div>
            </div>
        </div>
    </template>
</div>

@if(session('toast'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('toast', { detail: @json(session('toast')) }));
            }, 300);
        });
    </script>
@endif

<script>
    function toastManager() {
        return {
            toasts: [],
            addToast(data) {
                const id = Date.now() + Math.random();
                const duration = data.duration || 5000;
                this.toasts.push({ id, visible: true, duration, ...data });
                setTimeout(() => this.removeToast(id), duration);
            },
            removeToast(id) {
                const t = this.toasts.find(t => t.id === id);
                if (t) t.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
            }
        };
    }
    function showToast(type, titre, message = '', duration = 5000) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type, titre, message, duration } }));
    }
</script>

<div id="session-warning" style="display:none; position:fixed; inset:0; background:rgba(2,5,10,0.8); backdrop-filter: blur(6px); z-index:9999; align-items:center; justify-content:center;">
    <div class="card s-orbit-border" style="padding:34px; max-width:420px; width:90%; text-align:center; border-radius: 16px; border-color: transparent;">
        <div style="width:60px; height:60px; border-radius:16px; background:rgba(242,181,68,0.1); border:1px solid rgba(242,181,68,0.4); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; box-shadow: 0 0 24px rgba(242,181,68,.25);">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f2b544" stroke-width="2" stroke-linecap="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        </div>
        <h2 style="color:#fff; font-size:1.1rem; font-weight: 400; margin-bottom:10px;">Session sur le point d'expirer</h2>
        <p style="color:var(--text-muted); font-size:0.85rem; line-height: 1.6; margin-bottom:24px;">Vous serez déconnecté dans <span id="countdown" class="s-mono" style="color:#f2b544;">2:00</span> en raison d'inactivité.</p>
        <button onclick="resetSessionTimer()" class="btn-primary" style="width:100%;">
            Rester connecté
        </button>
    </div>
</div>

<script>
    (function() {
        const TIMEOUT = 60 * 60 * 1000;
        const WARNING = 2 * 60 * 1000;
        let warningTimer, logoutTimer, countdownInterval;
        let countdownSec = 120;
        let deadline = Date.now() + TIMEOUT;

        function resetSessionTimer() {
            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);
            clearInterval(countdownInterval);
            document.getElementById('session-warning').style.display = 'none';
            countdownSec = 120;
            deadline = Date.now() + TIMEOUT;
            warningTimer = setTimeout(showWarning, TIMEOUT - WARNING);
            logoutTimer = setTimeout(doLogout, TIMEOUT);
        }

        function showWarning() {
            countdownSec = 120;
            document.getElementById('session-warning').style.display = 'flex';
            countdownInterval = setInterval(() => {
                countdownSec--;
                const m = Math.floor(countdownSec / 60).toString().padStart(1,'0');
                const s = (countdownSec % 60).toString().padStart(2,'0');
                document.getElementById('countdown').textContent = m + ':' + s;
                if (countdownSec <= 0) clearInterval(countdownInterval);
            }, 1000);
        }

        function doLogout() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("deconnexion") }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }

        function paintSessionIndicator() {
            const left = Math.max(0, deadline - Date.now());
            const label = document.getElementById('s-session-left');
            const bar = document.getElementById('s-session-bar');
            if (label) {
                const total = Math.floor(left / 1000);
                label.textContent = Math.floor(total / 60) + ':' + String(total % 60).padStart(2, '0');
            }
            if (bar) bar.style.transform = 'scaleX(' + (left / TIMEOUT).toFixed(4) + ')';
        }

        window.resetSessionTimer = resetSessionTimer;
        ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(ev => {
            document.addEventListener(ev, resetSessionTimer, { passive: true });
        });

        resetSessionTimer();
        paintSessionIndicator();
        setInterval(paintSessionIndicator, 1000);
    })();
</script>

@stack('scripts')
</body>
</html>
