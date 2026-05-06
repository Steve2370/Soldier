@extends('layouts.app')

@section('content')
    <div x-data="adminDashboard()">

        <div style="padding: 24px 28px 0; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">Panel Admin</h1>
                <p style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Vue d'ensemble de Soldier</p>
            </div>
            <a href="{{ route('admin.logs') }}" style="background:var(--accent); color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:0.85rem; font-weight:600; text-decoration:none;">
                Voir les logs →
            </a>
        </div>

        <div style="padding: 20px 28px 0; display:flex; gap:8px; border-bottom:1px solid var(--border-primary); margin-bottom:24px;">
            <button @click="tab='overview'" :style="tab==='overview' ? 'border-bottom:2px solid var(--accent); color:var(--accent);' : 'color:var(--text-muted);'"
                    style="background:none; border:none; padding:8px 16px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px;">
                Vue d'ensemble
            </button>
            <button @click="tab='users'" :style="tab==='users' ? 'border-bottom:2px solid var(--accent); color:var(--accent);' : 'color:var(--text-muted);'"
                    style="background:none; border:none; padding:8px 16px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px;">
                Utilisateurs ({{ $totalUsers }})
            </button>
        </div>

        <div x-show="tab==='overview'" style="padding: 0 28px 28px;">

            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:28px;">
                @foreach([
                    ['label'=>'Total users','value'=>$totalUsers,'icon'=>'👥','color'=>'#2d9fd4'],
                    ['label'=>'Nouveaux (7j)','value'=>$newUsersWeek,'icon'=>'🆕','color'=>'#22c55e'],
                    ['label'=>'Actifs (30j)','value'=>$activeUsers30d,'icon'=>'⚡','color'=>'#f59e0b'],
                    ['label'=>'Services créés','value'=>$totalServices,'icon'=>'🔐','color'=>'#8b5cf6'],
                    ['label'=>'Partages actifs','value'=>$totalShares,'icon'=>'🤝','color'=>'#ec4899'],
                ] as $stat)
                    <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:20px;">
                        <div style="font-size:1.5rem; margin-bottom:8px;">{{ $stat['icon'] }}</div>
                        <div style="font-size:1.75rem; font-weight:800; color:{{ $stat['color'] }};">{{ $stat['value'] }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:28px;">

                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:24px;">
                    <h3 style="font-size:0.9rem; font-weight:700; color:var(--text-primary); margin-bottom:16px;">Inscriptions (30 derniers jours)</h3>
                    <canvas id="inscriptionsChart" height="120"></canvas>
                </div>

                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:24px;">
                    <h3 style="font-size:0.9rem; font-weight:700; color:var(--text-primary); margin-bottom:16px;">Actions par type</h3>
                    <canvas id="actionsChart" height="120"></canvas>
                </div>
            </div>

            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:24px;">
                <h3 style="font-size:0.9rem; font-weight:700; color:var(--text-primary); margin-bottom:16px;">Activité récente</h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                    <tr style="border-bottom:1px solid var(--border-primary);">
                        <th style="text-align:left; padding:8px 12px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">DATE</th>
                        <th style="text-align:left; padding:8px 12px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">UTILISATEUR</th>
                        <th style="text-align:left; padding:8px 12px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">ACTION</th>
                        <th style="text-align:left; padding:8px 12px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">IP</th>
                        <th style="text-align:left; padding:8px 12px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">DESCRIPTION</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentLogs as $log)
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td style="padding:10px 12px; font-size:0.78rem; color:var(--text-muted);">{{ $log->created_at->format('d/m H:i') }}</td>
                            <td style="padding:10px 12px; font-size:0.8rem; color:var(--text-primary);">{{ $log->user->name ?? 'Supprimé' }}</td>
                            <td style="padding:10px 12px;">
                            <span style="background:rgba(45,159,212,0.1); color:#2d9fd4; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">
                                {{ $log->action }}
                            </span>
                            </td>
                            <td style="padding:10px 12px; font-size:0.78rem; color:var(--text-muted); font-family:monospace;">{{ $log->ip_address }}</td>
                            <td style="padding:10px 12px; font-size:0.78rem; color:var(--text-muted);">{{ Str::limit($log->description, 60) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="padding:24px; text-align:center; color:var(--text-muted); font-size:0.85rem;">Aucune activité enregistrée</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="tab==='users'" style="padding: 0 28px 28px;">
            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                    <tr style="background:rgba(255,255,255,0.03); border-bottom:1px solid var(--border-primary);">
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">UTILISATEUR</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">EMAIL</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">INSCRIPTION</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">FORFAIT</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">AUTH</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">MFA</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">SERVICES</th>
                        <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">ADMIN</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $u)
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);" class="hover-row">
                            <td style="padding:12px 16px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    @if($u->avatar)
                                        <img src="{{ Storage::url($u->avatar) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                    @else
                                        <div style="width:32px;height:32px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;">
                                            {{ strtoupper(substr($u->name,0,1)) }}
                                        </div>
                                    @endif
                                    <span style="font-size:0.85rem; color:var(--text-primary); font-weight:600;">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td style="padding:12px 16px; font-size:0.82rem; color:var(--text-muted);">{{ $u->email }}</td>
                            <td style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $u->created_at->format('d/m/Y') }}</td>
                            <td style="padding:12px 16px;">
                            <span style="background:rgba(34,197,94,0.1); color:#22c55e; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">
                                Free
                            </span>
                            </td>
                            <td style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">
                                {{ $u->oauth_provider ? ucfirst($u->oauth_provider) : 'Email' }}
                            </td>
                            <td style="padding:12px 16px;">
                                @if($u->mfa_email_enabled || $u->totp_enabled)
                                    <span style="color:#22c55e; font-size:0.78rem;">✓ Actif</span>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.78rem;">—</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px; font-size:0.82rem; color:var(--text-primary); font-weight:600;">
                                {{ $u->coffres->sum(fn($c) => $c->elements->count()) }}
                            </td>
                            <td style="padding:12px 16px;">
                                @if($u->is_admin)
                                    <span style="color:#f59e0b; font-size:0.78rem;">⭐ Admin</span>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.78rem;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            function adminDashboard() {
                return {
                    tab: 'overview',
                    init() {
                        this.$nextTick(() => this.initCharts());
                    },
                    initCharts() {
                        const inscCtx = document.getElementById('inscriptionsChart');
                        if (inscCtx) {
                            new Chart(inscCtx, {
                                type: 'line',
                                data: {
                                    labels: {!! $inscriptionsParJour->pluck('date')->map(fn($d) => '"'.date('d/m', strtotime($d)).'"')->join(',') !!},
                                    datasets: [{
                                        label: 'Inscriptions',
                                        data: {!! $inscriptionsParJour->pluck('total')->join(',') !!},
                                        borderColor: '#2d9fd4',
                                        backgroundColor: 'rgba(45,159,212,0.1)',
                                        borderWidth: 2,
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 3,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { ticks: { color: '#606060', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
                                        y: { ticks: { color: '#606060', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
                                    }
                                }
                            });
                        }

                        const actCtx = document.getElementById('actionsChart');
                        if (actCtx) {
                            new Chart(actCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: {!! $actionsParType->pluck('action')->map(fn($a) => '"'.$a.'"')->join(',') !!},
                                    datasets: [{
                                        data: {!! $actionsParType->pluck('total')->join(',') !!},
                                        backgroundColor: ['#2d9fd4','#22c55e','#f59e0b','#8b5cf6','#ec4899','#ef4444'],
                                        borderWidth: 0,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { position: 'bottom', labels: { color: '#808080', font: { size: 10 }, boxWidth: 12 } }
                                    }
                                }
                            });
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
