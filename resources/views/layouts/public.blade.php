<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Soldier') Password Manager</title>
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
            --border: rgba(255,255,255,0.1);
            --border-bright: rgba(255,255,255,0.25);
            --accent: #217eaa;
            --accent-bright: #2d9fd4;
            --accent-dim: rgba(33,126,170,0.12);
            --accent-ultra: #5bc8f0;
            --text-primary: #ffffff;
            --text-secondary: #e0e0e0;
            --text-muted: #808080;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #22c55e;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Audiowide', serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .page-content {
            position: relative;
            z-index: 1;
        }

        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            background: rgba(20,20,20,0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            border-radius: 0;
            padding: 0 40px;
            height: 64px;
            width: 100%;
            max-width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 32px rgba(0,0,0,0.6);
        }

        .navbar-logo {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }

        .logo-img {
            height: 52px;
            width: auto;
            object-fit: contain;
        }

        .navbar-links {
            display: flex; align-items: center; gap: 10px;
        }

        .btn-nav-connexion {
            padding: 7px 18px; border-radius: 9px;
            border: 1px solid rgba(255,255,255,0.15);
            background: transparent;
            color: var(--text-secondary);
            font-family: 'Audiowide', sans-serif;
            font-size: 0.875rem; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-nav-connexion:hover {
            border-color: rgba(255,255,255,0.35);
            color: var(--text-primary);
            background: rgba(255,255,255,0.05);
        }

        .btn-nav-inscription {
            padding: 7px 18px; border-radius: 9px;
            border: none;
            background: var(--accent);
            color: #fff;
            font-family: 'Audiowide', serif;
            font-size: 0.875rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-nav-inscription:hover {
            background: var(--accent-bright);
            transform: translateY(-1px);
        }

        .card {
            background: rgba(30,30,30,0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 18px;
            padding: 30px;
            backdrop-filter: blur(16px);
        }

        .input {
            background: rgba(0,0,0,0.6);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            color: var(--text-primary);
            padding: 12px 16px;
            font-size: 0.9375rem;
            font-family: 'Audiowide', sans-serif;
            width: 100%; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input:focus {
            border-color: var(--accent-bright);
            box-shadow: 0 0 0 3px rgba(33,126,170,0.2);
        }
        .input::placeholder { color: var(--text-muted); }

        label {
            display: block;
            font-size: 0.8125rem; font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 7px;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff; border: none; border-radius: 10px;
            padding: 12px 24px;
            font-size: 0.9375rem; font-weight: 600;
            font-family: 'Audiowide', sans-serif;
            cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none;
        }
        .btn-primary:hover {
            background: var(--accent-bright);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(40,40,40,0.8);
            color: var(--text-secondary);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px; padding: 12px 24px;
            font-size: 0.9375rem; font-weight: 500;
            font-family: 'Audiowide', sans-serif;
            cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none;
        }
        .btn-secondary:hover {
            border-color: rgba(255,255,255,0.3);
            color: var(--text-primary);
            background: rgba(255,255,255,0.05);
        }

        .error-msg {
            color: #fca5a5; font-size: 0.8rem;
            margin-top: 6px;
            display: flex; align-items: center; gap: 5px;
        }

        .divider {
            height: 1px;
            background: rgba(255,255,255,0.08);
            margin: 22px 0;
        }

        .eye-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            transition: color 0.15s; padding: 3px;
        }
        .eye-btn:hover { color: var(--accent-bright); }

        .input-error {
            border-color: rgba(239,68,68,0.6) !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.08) !important;
        }

        .badge-accent {
            display: inline-flex; align-items: center;
            background: var(--accent-dim);
            border: 1px solid rgba(45,159,212,0.3);
            border-radius: 20px; padding: 3px 12px;
            font-size: 0.72rem; font-weight: 600;
            color: var(--accent-bright);
            letter-spacing: 0.02em;
        }

        .badge-live {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px; padding: 6px 16px;
        }
        .badge-live-dot {
            width: 7px; height: 7px;
            background: var(--accent-bright);
            border-radius: 50%;
            animation: blink 2s ease-in-out infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        @media (max-width: 640px) {
            .navbar {
                padding: 0 16px;
                height: 56px;
            }
            .logo-img {
                height: 38px;
            }
            .btn-nav-connexion {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            .btn-nav-inscription {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            .card {
                padding: 20px 16px;
                border-radius: 14px;
            }
            .btn-primary, .btn-secondary {
                padding: 10px 18px;
                font-size: 0.8375rem;
            }
        }
    </style>
</head>

<body x-data="toastManager()" @toast.window="addToast($event.detail)">

<x-rain-background />

<nav class="navbar">
    <a href="{{ route('welcome') }}" class="navbar-logo">
        <img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Soldier Logo" class="logo-img">
    </a>
    <div class="navbar-links">
        <a href="{{ route('connexion') }}" class="btn-nav-connexion">Connexion</a>
        <a href="{{ route('inscription') }}" class="btn-nav-inscription">Créer un compte</a>
    </div>
</nav>

<div class="page-content" style="padding-top: 64px;">
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
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('toast', { detail: @json(session('toast')) }));
            }, 300);
        });
    </script>
@endif

<style>
    .toast-item { border-radius: 14px; border: 1px solid; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.7); backdrop-filter: blur(16px); }
    .toast-success { background: rgba(15,15,15,0.97); border-color: rgba(34,197,94,0.3); }
    .toast-error { background: rgba(15,15,15,0.97); border-color: rgba(239,68,68,0.3); }
    .toast-warning { background: rgba(15,15,15,0.97); border-color: rgba(245,158,11,0.3); }
    .toast-info { background: rgba(15,15,15,0.97); border-color: rgba(45,159,212,0.35); }
    .toast-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .toast-icon-success { background: rgba(34,197,94,0.12); color: #22c55e; }
    .toast-icon-error { background: rgba(239,68,68,0.12); color: #ef4444; }
    .toast-icon-warning { background: rgba(245,158,11,0.12); color: #f59e0b; }
    .toast-icon-info { background: rgba(45,159,212,0.12); color: var(--accent-bright); }
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
