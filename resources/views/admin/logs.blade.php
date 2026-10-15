@extends('layouts.app')
@section('title', "Logs d'activité")

@section('content')
    <div>

        <div class="s-page-head">
            <div>
                <a href="{{ route('admin.index') }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 32px; color: var(--text-muted); font-size: 0.8rem; text-decoration: none;">← Retour</a>
                <span class="s-tag" style="display: block; margin-top: 8px;">// Journal d'audit</span>
                <h1 style="margin-top: 10px;">Logs d'activité</h1>
                <p class="s-sub">Journal complet des actions utilisateurs</p>
            </div>
            <span class="s-chip s-chip-ok"><span class="s-dot" aria-hidden="true"></span> Enregistrement actif</span>
        </div>

        <form method="GET" class="card s-hud logs-filters">
            <div>
                <label for="logs_user">Utilisateur</label>
                <select id="logs_user" name="user_id" class="input">
                    <option value="">Tous</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="logs_action">Action</label>
                <select id="logs_action" name="action" class="input">
                    <option value="">Toutes</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="logs_from">Du</label>
                <input id="logs_from" type="date" name="date_from" value="{{ request('date_from') }}" class="input">
            </div>
            <div>
                <label for="logs_to">Au</label>
                <input id="logs_to" type="date" name="date_to" value="{{ request('date_to') }}" class="input">
            </div>
            <div class="logs-actions">
                <button type="submit" class="btn-primary">Filtrer</button>
                <a href="{{ route('admin.logs.export', request()->all()) }}" class="btn-secondary" style="text-decoration: none; color: var(--neon); border-color: rgba(61,245,192,0.4);">
                    ⬇ Télécharger CSV
                </a>
            </div>
        </form>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="logs-bar" aria-hidden="true">
                <span></span><span></span><span></span>
                <p class="s-mono">soldier://audit.log — flux en direct<span class="s-caret"></span></p>
            </div>
            <div style="overflow-x: auto;">
                <table class="logs-table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP</th>
                        <th>Navigateur</th>
                    </tr>
                    </thead>
                    <tbody class="s-stagger">
                    @forelse($logs as $log)
                        @php
                            $colors = [
                                'inscription' => '#3df5c0',
                                'connexion' => '#4fc3f7',
                                'deconnexion' => '#8fa2b8',
                                'service_cree' => '#6fd8ff',
                                'service_supprime' => '#ff5a64',
                                'partage_envoye' => '#7df7d2',
                                'partage_accepte' => '#f2b544',
                            ];
                            $c = $colors[$log->action] ?? '#4fc3f7';
                        @endphp
                        <tr>
                            <td class="s-mono" style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div style="font-size: 0.82rem; color: var(--text-primary);">{{ $log->user->name ?? 'Supprimé' }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $log->user->email ?? '' }}</div>
                            </td>
                            <td>
                                <span class="s-mono" style="display: inline-flex; align-items: center; gap: 6px; color: {{ $c }}; border: 1px solid {{ $c }}55; background: {{ $c }}14; border-radius: 999px; padding: 2px 10px; font-size: 0.68rem; white-space: nowrap;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $c }}; box-shadow: 0 0 8px {{ $c }};"></span>{{ $log->action }}
                                </span>
                            </td>
                            <td style="font-size: 0.78rem; color: var(--text-secondary);">{{ $log->description }}</td>
                            <td class="s-mono" style="font-size: 0.72rem; color: var(--text-muted);">{{ $log->ip_address }}</td>
                            <td style="font-size: 0.72rem; color: var(--text-muted);">{{ Str::limit($log->user_agent, 40) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding: 32px; text-align: center; color: var(--text-muted);">Aucun log trouvé</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px; border-top: 1px solid var(--border);">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <style>
        .logs-filters { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; padding: 20px; margin-bottom: 20px; }
        .logs-filters > div { flex: 1 1 160px; }
        .logs-filters label { font-family: var(--font-mono); font-size: 0.66rem; letter-spacing: .14em; text-transform: uppercase; }
        .logs-filters .logs-actions { display: flex; gap: 10px; flex: 0 0 auto; flex-wrap: wrap; }
        .logs-bar { display: flex; align-items: center; gap: 8px; padding: 12px 18px; border-bottom: 1px solid var(--border); background: rgba(79,195,247,0.04); }
        .logs-bar > span { width: 10px; height: 10px; border-radius: 50%; background: #1d3347; }
        .logs-bar > span:first-child { background: var(--danger); box-shadow: 0 0 8px rgba(255,90,100,.6); }
        .logs-bar > span:nth-child(2) { background: var(--warning); }
        .logs-bar > span:nth-child(3) { background: var(--neon); }
        .logs-bar p { margin-left: 10px; font-size: 0.72rem; color: var(--text-muted); }
        .logs-table { width: 100%; min-width: 800px; border-collapse: collapse; }
        .logs-table th { text-align: left; padding: 12px 16px; font-family: var(--font-mono); font-weight: 400; font-size: 0.66rem; letter-spacing: .14em; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        .logs-table td { padding: 10px 16px; border-bottom: 1px solid rgba(79,195,247,0.06); }
        .logs-table tbody tr { transition: background .2s; }
        .logs-table tbody tr:hover { background: rgba(79,195,247,0.05); }
    </style>
@endsection
