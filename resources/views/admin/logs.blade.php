@extends('layouts.app')

@section('content')
    <div style="padding:24px 28px;">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">Logs d'activité</h1>
                <p style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Journal complet des actions utilisateurs</p>
            </div>
            <a href="{{ route('admin.index') }}" style="color:var(--text-muted); font-size:0.85rem; text-decoration:none;">← Retour</a>
        </div>

        <form method="GET" style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:20px; margin-bottom:20px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label style="font-size:0.75rem; color:var(--text-muted); font-weight:600; display:block; margin-bottom:6px;">UTILISATEUR</label>
                <select name="user_id" style="background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:7px 12px; font-size:0.82rem;">
                    <option value="">Tous</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.75rem; color:var(--text-muted); font-weight:600; display:block; margin-bottom:6px;">ACTION</label>
                <select name="action" style="background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:7px 12px; font-size:0.82rem;">
                    <option value="">Toutes</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.75rem; color:var(--text-muted); font-weight:600; display:block; margin-bottom:6px;">DU</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" style="background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:7px 12px; font-size:0.82rem;">
            </div>
            <div>
                <label style="font-size:0.75rem; color:var(--text-muted); font-weight:600; display:block; margin-bottom:6px;">AU</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" style="background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:7px 12px; font-size:0.82rem;">
            </div>
            <button type="submit" style="background:var(--accent); color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:0.85rem; font-weight:600; cursor:pointer;">Filtrer</button>
            <a href="{{ route('admin.logs.export', request()->all()) }}" style="background:rgba(34,197,94,0.1); color:#22c55e; border:1px solid rgba(34,197,94,0.3); border-radius:8px; padding:8px 16px; font-size:0.85rem; font-weight:600; text-decoration:none;">
                ⬇ Télécharger CSV
            </a>
        </form>

        <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; overflow:hidden; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:800px;">
                <thead>
                <tr style="background:rgba(255,255,255,0.03); border-bottom:1px solid var(--border-primary);">
                    <th style="text-align:left; padding:12px 16px; font-size:0.72rem; color:var(--text-muted); font-weight:600;">DATE</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.72rem; color:var(--text-muted); font-weight:600;">UTILISATEUR</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.72rem; color:var(--text-muted); font-weight:600;">ACTION</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.72rem; color:var(--text-muted); font-weight:600;">DESCRIPTION</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.72rem; color:var(--text-muted); font-weight:600;">IP</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.72rem; color:var(--text-muted); font-weight:600;">NAVIGATEUR</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:10px 16px; font-size:0.75rem; color:var(--text-muted); white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td style="padding:10px 16px;">
                            <div style="font-size:0.82rem; color:var(--text-primary); font-weight:600;">{{ $log->user->name ?? 'Supprimé' }}</div>
                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ $log->user->email ?? '' }}</div>
                        </td>
                        <td style="padding:10px 16px;">
                            @php
                                $colors = [
                                    'inscription' => ['bg'=>'rgba(34,197,94,0.1)','color'=>'#22c55e'],
                                    'connexion' => ['bg'=>'rgba(45,159,212,0.1)','color'=>'#2d9fd4'],
                                    'deconnexion' => ['bg'=>'rgba(96,96,96,0.1)','color'=>'#909090'],
                                    'service_cree' => ['bg'=>'rgba(139,92,246,0.1)','color'=>'#8b5cf6'],
                                    'service_supprime' => ['bg'=>'rgba(239,68,68,0.1)','color'=>'#ef4444'],
                                    'partage_envoye' => ['bg'=>'rgba(236,72,153,0.1)','color'=>'#ec4899'],
                                    'partage_accepte' => ['bg'=>'rgba(245,158,11,0.1)','color'=>'#f59e0b'],
                                ];
                                $c = $colors[$log->action] ?? ['bg'=>'rgba(45,159,212,0.1)','color'=>'#2d9fd4'];
                            @endphp
                            <span style="background:{{ $c['bg'] }}; color:{{ $c['color'] }}; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600; white-space:nowrap;">
                            {{ $log->action }}
                        </span>
                        </td>
                        <td style="padding:10px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $log->description }}</td>
                        <td style="padding:10px 16px; font-size:0.75rem; color:var(--text-muted); font-family:monospace;">{{ $log->ip_address }}</td>
                        <td style="padding:10px 16px; font-size:0.72rem; color:var(--text-muted);">{{ Str::limit($log->user_agent, 40) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:32px; text-align:center; color:var(--text-muted);">Aucun log trouvé</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="padding:16px; border-top:1px solid var(--border-primary);">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
