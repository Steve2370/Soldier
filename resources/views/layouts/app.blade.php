<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Soldier') — Password Manager</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=999">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?v=999">
    <link rel="apple-touch-icon" href="{{ asset('assets/Soldier-Logotype.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <style>
        :root {
            --bg-base: #000000;
            --bg-surface: #202020;
            --bg-elevated: #404040;
            --bg-hover: #606060;
            --border: #404040;
            --border-bright: #606060;
            --accent: #217eaa;
            --accent-bright: #2d9fd4;
            --accent-dim: rgba(33,126,170,0.15);
            --text-primary: #ffffff;
            --text-secondary:#e0e0e0;
            --text-muted: #808080;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #22c55e;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Audiowide', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #404040; border-radius: 3px; }

        .sidebar {
            background: var(--bg-surface);
            border-right: 1px solid #303030;
            width: 252px;
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 40;
        }

        .sidebar-logo {
            padding: 22px 20px;
            border-bottom: 1px solid #303030;
            display: flex; align-items: center; gap: 10px;
        }

        .logo-icon {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .nav-section-label {
            font-size: 0.68rem; font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 12px 16px 4px;
        }

        .nav-item {
            position: relative;
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; margin: 1px 6px;
            border-radius: 8px;
            color: var(--text-secondary);
            font-size: 0.9rem; font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.06);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: rgba(33,126,170,0.2);
            color: var(--accent-bright);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -6px; top: 25%; bottom: 25%;
            width: 3px;
            background: var(--accent-bright);
            border-radius: 0 2px 2px 0;
        }

        .nav-item svg {
            width: 17px; height: 17px;
            flex-shrink: 0; opacity: 0.6;
            transition: opacity 0.15s;
        }

        .nav-item:hover svg, .nav-item.active svg { opacity: 1; }

        .main-content {
            margin-left: 252px;
            min-height: 100vh;
            padding: 32px 36px;
            position: relative;
            z-index: 1;
            isolation: isolate;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid #303030;
            border-radius: 14px;
            padding: 24px;
        }

        .input {
            background: var(--bg-elevated);
            border: 1px solid #505050;
            border-radius: 9px;
            color: var(--text-primary);
            padding: 10px 14px;
            font-size: 0.875rem;
            font-family: 'Audiowide', sans-serif;
            width: 100%;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .input:focus {
            border-color: var(--accent-bright);
            box-shadow: 0 0 0 3px rgba(33,126,170,0.2);
        }

        .input::placeholder { color: var(--text-muted); }

        label {
            display: block;
            font-size: 0.8125rem; font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            border: none; border-radius: 9px;
            padding: 10px 20px;
            font-size: 0.875rem; font-weight: 700;
            font-family: 'Audiowide', sans-serif;
            cursor: pointer; transition: all 0.15s;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none;
        }
        .btn-primary:hover { background: var(--accent-bright); }

        .btn-secondary {
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border: 1px solid #505050;
            border-radius: 9px; padding: 10px 20px;
            font-size: 0.875rem; font-weight: 500;
            font-family: 'Audiowide', sans-serif;
            cursor: pointer; transition: all 0.15s;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none;
        }
        .btn-secondary:hover {
            border-color: var(--accent-bright);
            color: var(--text-primary);
            background: rgba(255,255,255,0.06);
        }

        .error-msg {
            color: #fca5a5; font-size: 0.8rem;
            margin-top: 5px;
            display: flex; align-items: center; gap: 5px;
        }

        .divider { height: 1px; background: #303030; margin: 14px 0; }
        .input-error { border-color: #ef4444 !important; }

        .eye-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: var(--text-muted); cursor: pointer; transition: color 0.15s;
        }
        .eye-btn:hover { color: var(--accent-bright); }
    </style>
</head>

<body x-data="toastManager()" @toast.window="addToast($event.detail)">

<x-rain-background />

@auth
    <aside class="sidebar">
        <div style="display: flex; justify-content: flex-start; margin: 20px 0 10px 20px;">
            <img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Logo" style="width: 90px; height: auto;">
        </div>

        <nav style="padding: 10px 0; flex: 1; overflow-y: auto;">
            <div class="nav-section-label">Principal</div>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Dashboard
            </a>

            <a href="{{ route('generateur') }}" class="nav-item {{ request()->routeIs('generateur') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Générateur
            </a>

            <a href="{{ route('partage.index') }}" class="nav-item {{ request()->routeIs('partage.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Partage
            </a>

            <div class="nav-section-label">Compte</div>

            <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Paramètres
            </a>
        </nav>

        <div style="padding: 14px; border-top: 1px solid #303030;">
            <div style="background: var(--bg-elevated); border-radius: 9px; padding: 10px; display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; color: #fff; flex-shrink: 0;">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <div style="overflow: hidden; flex: 1;">
                    <div style="font-size: 0.8125rem; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('deconnexion') }}">
                @csrf
                <button type="submit" class="btn-secondary" style="width: 100%; justify-content: center; padding: 8px 12px; font-size: 0.8rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>
@endauth

<main class="{{ Auth::check() ? 'main-content' : '' }}">
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
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);" x-text="toast.titre"></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;" x-text="toast.message" x-show="toast.message"></div>
                </div>
                <button @click="removeToast(toast.id)" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 2px;">
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
        document.addEventListener('alpine:init', () => {
            window.dispatchEvent(new CustomEvent('toast', { detail: @json(session('toast')) }));
        });
    </script>
@endif

<style>
    .toast-item { border-radius: 12px; border: 1px solid; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.7); backdrop-filter: blur(12px); }
    .toast-success { background: rgba(20,20,20,0.97); border-color: rgba(34,197,94,0.4); }
    .toast-error { background: rgba(20,20,20,0.97); border-color: rgba(239,68,68,0.4); }
    .toast-warning { background: rgba(20,20,20,0.97); border-color: rgba(245,158,11,0.4); }
    .toast-info { background: rgba(20,20,20,0.97); border-color: rgba(33,126,170,0.5); }
    .toast-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .toast-icon-success { background: rgba(34,197,94,0.15); color: #22c55e; }
    .toast-icon-error { background: rgba(239,68,68,0.15); color: #ef4444; }
    .toast-icon-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .toast-icon-info { background: rgba(33,126,170,0.15); color: var(--accent-bright); }
    .toast-bar-success { background: #22c55e; }
    .toast-bar-error { background: #ef4444; }
    .toast-bar-warning { background: #f59e0b; }
    .toast-bar-info { background: var(--accent-bright); }
    @keyframes toast-shrink { from { transform: scaleX(1); transform-origin: left; } to { transform: scaleX(0); transform-origin: left; } }
</style>

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
