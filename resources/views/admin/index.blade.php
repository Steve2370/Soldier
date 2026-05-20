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

    {{-- Tabs --}}
    <div style="padding: 24px 28px 0; display:flex; gap:4px; border-bottom:1px solid var(--border-primary); margin-bottom:28px;">
        <button @click="tab='overview'"
                :style="tab==='overview' ? 'border-bottom:2px solid var(--accent); color:var(--accent-bright);' : 'color:var(--text-muted);'"
                style="background:none; border:none; padding:10px 20px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px; font-family:'Audiowide',sans-serif; display:flex; align-items:center; gap:7px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Vue d'ensemble
        </button>
        <button @click="tab='users'"
                :style="tab==='users' ? 'border-bottom:2px solid var(--accent); color:var(--accent-bright);' : 'color:var(--text-muted);'"
                style="background:none; border:none; padding:10px 20px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px; font-family:'Audiowide',sans-serif; display:flex; align-items:center; gap:7px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Utilisateurs <span style="background:rgba(45,159,212,0.1); color:#2d9fd4; border-radius:20px; padding:1px 8px; font-size:0.7rem;">{{ $totalUsers }}</span>
        </button>
        <button @click="tab='famille'"
                :style="tab==='famille' ? 'border-bottom:2px solid var(--accent); color:var(--accent-bright);' : 'color:var(--text-muted);'"
                style="background:none; border:none; padding:10px 20px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px; font-family:'Audiowide',sans-serif; display:flex; align-items:center; gap:7px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Famille <span style="background:rgba(45,159,212,0.1); color:#2d9fd4; border-radius:20px; padding:1px 8px; font-size:0.7rem;">{{ $totalFamille }}</span>
        </button>
        <button @click="tab='emails'"
                :style="tab==='emails' ? 'border-bottom:2px solid var(--accent); color:var(--accent-bright);' : 'color:var(--text-muted);'"
                style="background:none; border:none; padding:10px 20px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px; font-family:'Audiowide',sans-serif; display:flex; align-items:center; gap:7px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Emails
        </button>
    </div>

    {{-- ══ ONGLET OVERVIEW ══ --}}
    <div x-show="tab==='overview'" style="padding: 0 28px 28px;">

        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:28px;">
            @foreach([
                ['label'=>'Total users','value'=>$totalUsers,'color'=>'#2d9fd4','icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
                ['label'=>'Nouveaux (7j)','value'=>$newUsersWeek,'color'=>'#22c55e','icon'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>'],
                ['label'=>'Actifs (30j)','value'=>$activeUsers30d,'color'=>'#f59e0b','icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
                ['label'=>'Services','value'=>$totalServices,'color'=>'#8b5cf6','icon'=>'<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
                ['label'=>'Partages actifs','value'=>$totalShares,'color'=>'#ec4899','icon'=>'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
            ] as $stat)
            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:20px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $stat['color'] }}" stroke-width="2" style="margin-bottom:10px;">{!! $stat['icon'] !!}</svg>
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
                    <td style="padding:8px 12px; font-size:0.75rem; color:var(--text-muted);">{{ $log->created_at->format('d/m H:i') }}</td>
                    <td style="padding:8px 12px; font-size:0.78rem; color:var(--text-primary);">{{ $log->user?->name ?? '—' }}</td>
                    <td style="padding:8px 12px;"><span style="background:rgba(45,159,212,0.1); color:#2d9fd4; border-radius:4px; padding:2px 7px; font-size:0.7rem;">{{ $log->action }}</span></td>
                    <td style="padding:8px 12px; font-size:0.75rem; color:var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                    <td style="padding:8px 12px; font-size:0.78rem; color:var(--text-secondary);">{{ Str::limit($log->description, 60) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:24px; text-align:center; color:var(--text-muted);">Aucune activité récente</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══ ONGLET USERS ══ --}}
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
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; color:#fff; overflow:hidden; flex-shrink:0;">
                                @if($u->avatar)
                                    <img src="{{ Storage::url($u->avatar) }}" style="width:100%;height:100%;object-fit:cover;">
                                @elseif($u->avatar_url)
                                    <img src="{{ $u->avatar_url }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                @endif
                            </div>
                            <span style="font-size:0.85rem; color:var(--text-primary); font-weight:600;">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px; font-size:0.82rem; color:var(--text-muted);">{{ $u->email }}</td>
                    <td style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;">
                        @if($u->subscribed('famille'))
                            <span style="background:rgba(45,159,212,0.1); color:#2d9fd4; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">Famille</span>
                        @else
                            <span style="background:rgba(34,197,94,0.1); color:#22c55e; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">Free</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $u->oauth_provider ? ucfirst($u->oauth_provider) : 'Email' }}</td>
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
                            <span style="color:#f59e0b; font-size:0.78rem; font-weight:600;">Admin</span>
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

    {{-- ══ ONGLET FAMILLE ══ --}}
    <div x-show="tab==='famille'" style="padding: 0 28px 28px;">
        <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; overflow:hidden; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:600px;">
                <thead>
                <tr style="background:rgba(255,255,255,0.03); border-bottom:1px solid var(--border-primary);">
                    <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">PROPRIÉTAIRE</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">GROUPE</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">MEMBRES</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">CRÉÉ LE</th>
                    <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">STATUT</th>
                </tr>
                </thead>
                <tbody>
                @forelse($familyGroups as $group)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; color:#fff; overflow:hidden; flex-shrink:0;">
                                @if($group->owner->avatar)
                                    <img src="{{ Storage::url($group->owner->avatar) }}" style="width:100%;height:100%;object-fit:cover;">
                                @elseif($group->owner->avatar_url)
                                    <img src="{{ $group->owner->avatar_url }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($group->owner->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">{{ $group->owner->name }}</div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">{{ $group->owner->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px; font-size:0.85rem; color:var(--text-primary);">{{ $group->nom }}</td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="font-size:0.875rem; font-weight:700; color:var(--text-primary);">{{ $group->members->count() }}</span>
                            <span style="font-size:0.78rem; color:var(--text-muted);">/ 6</span>
                            <div style="flex:1; max-width:60px; background:rgba(255,255,255,0.05); border-radius:4px; height:4px; overflow:hidden;">
                                <div style="background:#2d9fd4; height:100%; width:{{ ($group->members->count() / 6) * 100 }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $group->created_at->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;">
                        @if($group->owner->subscribed('famille'))
                            <span style="background:rgba(34,197,94,0.1); color:#22c55e; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">Actif</span>
                        @else
                            <span style="background:rgba(239,68,68,0.1); color:#ef4444; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">Inactif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:32px; text-align:center; color:var(--text-muted);">Aucun groupe famille</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══ ONGLET EMAILS ══ --}}
    <div x-show="tab==='emails'" style="padding: 0 28px 28px;" x-data="{ destinataire: 'tous' }">
        <div style="max-width:680px;">
            <div style="margin-bottom:24px;">
                <h2 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">Envoyer un email</h2>
                <p style="font-size:0.8rem; color:var(--text-muted);">Envoyez un message à vos utilisateurs depuis le panel admin.</p>
            </div>

            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:28px;">
                <form method="POST" action="{{ route('admin.mail.envoyer') }}">
                    @csrf

                    <div style="margin-bottom:20px;">
                        <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:10px; display:block;">Destinataires</label>
                        <div style="display:flex; gap:20px; flex-wrap:wrap;">
                            @foreach(['tous' => 'Tous les utilisateurs', 'abonnes' => 'Abonnés Famille', 'user' => 'Utilisateur spécifique'] as $val => $label)
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;" @click="destinataire='{{ $val }}'">
                                <div :style="destinataire==='{{ $val }}' ? 'width:18px;height:18px;border-radius:50%;border:2px solid var(--accent);background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;' : 'width:18px;height:18px;border-radius:50%;border:2px solid rgba(255,255,255,0.2);background:transparent;display:flex;align-items:center;justify-content:center;flex-shrink:0;'">
                                    <div x-show="destinataire==='{{ $val }}'" style="width:8px;height:8px;border-radius:50%;background:#fff;"></div>
                                </div>
                                <input type="radio" name="destinataire" value="{{ $val }}" x-model="destinataire" style="display:none;">
                                <span style="font-size:0.875rem; color:var(--text-primary);">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="destinataire==='user'" style="margin-bottom:20px;">
                        <label>Choisir l'utilisateur</label>
                        <select name="user_id" class="input" style="cursor:pointer;">
                            <option value="">Sélectionner un utilisateur...</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label>Sujet</label>
                        <input type="text" name="sujet" class="input" placeholder="Objet de l'email..." required>
                    </div>

                    <div style="margin-bottom:24px;">
                        <label>Message</label>
                        <textarea name="contenu" class="input" rows="8" placeholder="Rédigez votre message ici..." required style="resize:vertical; min-height:160px; line-height:1.6;"></textarea>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <p style="font-size:0.75rem; color:var(--text-muted);">
                            L'email sera envoyé depuis <strong style="color:var(--text-secondary);">noreply@soldierkey.com</strong>
                        </p>
                        <button type="submit" class="btn-primary">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Envoyer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminDashboard', () => ({
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
                                labels: [{!! $inscriptionsParJour->pluck('date')->map(fn($d) => '"'.date('d/m', strtotime($d)).'"')->join(',') !!}],
                                datasets: [{
                                    label: 'Inscriptions',
                                    data: [{!! $inscriptionsParJour->pluck('total')->join(',') !!}],
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
                                labels: [{!! $actionsParType->pluck('action')->map(fn($a) => '"'.$a.'"')->join(',') !!}],
                                datasets: [{
                                    data: [{!! $actionsParType->pluck('total')->join(',') !!}],
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
            }));
        });
    </script>
@endpush
@endsection
