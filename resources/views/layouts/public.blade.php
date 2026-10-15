<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#02050a">
    <title>@yield('title', 'Soldier') Password Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>

<body x-data="toastManager()" @toast.window="addToast($event.detail)">

<nav class="navbar">
    <a href="{{ route('welcome') }}" class="navbar-logo">
        <span class="s-logo-ring"><img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Soldier Logo" class="logo-img"></span>
        <span>SOLDIER</span>
    </a>
    <div class="navbar-links">
        <a href="{{ route('pricing') }}" class="btn-nav-connexion {{ request()->routeIs('pricing') ? 'is-active' : '' }}">Tarifs</a>
        <a href="{{ route('connexion') }}" class="btn-nav-connexion {{ request()->routeIs('connexion*') ? 'is-active' : '' }}">Connexion</a>
        <a href="{{ route('inscription') }}" class="btn-nav-inscription">Créer un compte</a>
    </div>
</nav>

<div class="page-content" style="padding-top: 66px;">
    @yield('content')
</div>

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

@stack('scripts')
</body>
</html>
