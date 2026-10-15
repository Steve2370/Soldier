@extends('layouts.app')

@section('content')
<style>
    .admin-dashboard { --bg-card: rgba(4,10,16,0.9); --border-primary: var(--border); }
    .admin-dashboard table thead tr { background: rgba(79,195,247,0.04) !important; }
    .admin-dashboard table th { font-family: var(--font-mono); letter-spacing: .14em; font-weight: 400 !important; font-size: .66rem !important; }
    .admin-dashboard tbody tr { transition: background .2s; }
    .admin-dashboard tbody tr:hover { background: rgba(79,195,247,0.04); }
    .admin-panel { background: var(--bg-card); border: 1px solid var(--border-primary); border-radius: 16px; position: relative; overflow: hidden; }
    .admin-tabs { display: inline-flex; flex-wrap: wrap; gap: 6px; padding: 5px; border: 1px solid var(--border); border-radius: 14px; background: rgba(4,10,16,.85); }
    .admin-tab { display: flex; align-items: center; gap: 8px; min-height: 40px; padding: 8px 18px; border-radius: 10px; border: 1px solid transparent; background: none; color: var(--text-muted); font-family: var(--font-display); font-size: .8rem; cursor: pointer; transition: color .2s, background .2s, border-color .2s, box-shadow .2s; }
    .admin-tab:hover { color: var(--text-primary); background: rgba(79,195,247,.06); }
    .admin-tab.is-active { color: #e6f7ff; background: rgba(45,159,212,.16); border-color: rgba(79,195,247,.45); box-shadow: 0 0 18px rgba(79,195,247,.22); }
    .admin-tab-count { font-family: var(--font-mono); font-size: .68rem; padding: 1px 8px; border-radius: 999px; background: rgba(79,195,247,.12); color: #8fd8ff; border: 1px solid rgba(79,195,247,.3); }
    .admin-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .admin-stat { padding: 20px; }
    .admin-stat-bar { height: 3px; border-radius: 3px; margin-top: 14px; background: #12202e; overflow: hidden; }
    .admin-stat-bar > span { display: block; height: 100%; width: 100%; transform-origin: left; animation: admin-bar 1.4s cubic-bezier(.2,.8,.2,1) backwards; }
    @keyframes admin-bar { from { transform: scaleX(0); } }
    .admin-charts { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; }
    .admin-dashboard summary { list-style: none; display: inline-flex; align-items: center; gap: 6px; min-height: 34px; padding: 6px 12px; border-radius: 9px; border: 1px solid var(--border-bright); font-size: .78rem; }
    .admin-dashboard summary::-webkit-details-marker { display: none; }
    .admin-dashboard details[open] > summary { border-color: rgba(79,195,247,.5); }
    @media (max-width: 1100px) { .admin-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .admin-charts { grid-template-columns: 1fr; } }
    .admin-users-table th, .admin-users-table td { padding: 12px 8px !important; }
    .admin-users-table td { overflow-wrap: anywhere; }
    .admin-users-table td:last-child { min-width: 210px !important; }
    .admin-users-table summary { white-space: normal; }
    @media (max-width: 1100px) {
        .admin-users-table, .admin-users-table tbody { display: block; }
        .admin-users-table thead { display: none; }
        .admin-users-table tr { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); padding: 12px; }
        .admin-users-table td { display: block; min-width: 0 !important; }
        .admin-users-table td:first-child, .admin-users-table td:nth-child(2), .admin-users-table td:last-child { grid-column: 1 / -1; }
        .admin-users-table td:last-child { min-width: 0 !important; }
        .admin-users-table td::before { content: attr(data-label); display: block; color: var(--text-muted); font-size: 0.65rem; text-transform: uppercase; margin-bottom: 6px; }
    }
</style>
<div x-data="adminDashboard()" class="admin-dashboard">
    @if($errors->any())
        <div role="alert" style="margin:0 0 20px;padding:16px;border:1px solid rgba(255,90,100,.5);background:rgba(255,90,100,.08);border-radius:12px;color:var(--text-primary);box-shadow:0 0 24px rgba(255,90,100,.15);">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="s-page-head">
        <div>
            <span class="s-tag">// Poste de commandement</span>
            <h1 style="margin-top: 10px;">Panel Admin</h1>
            <p class="s-sub">Vue d'ensemble de Soldier</p>
        </div>
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
            <span class="s-chip s-chip-ok"><span class="s-dot" aria-hidden="true"></span> Système opérationnel</span>
            <a href="{{ route('admin.logs') }}" class="btn-primary" style="text-decoration:none;">
                Voir les logs →
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="margin-bottom:24px;"><div class="admin-tabs">
        <button type="button" @click="tab='overview'" class="admin-tab" :class="tab==='overview' ? 'is-active' : ''">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Vue d'ensemble
        </button>
        <button type="button" @click="tab='users'" class="admin-tab" :class="tab==='users' ? 'is-active' : ''">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Utilisateurs <span class="admin-tab-count">{{ $totalUsers }}</span>
        </button>
        <button type="button" @click="tab='famille'" class="admin-tab" :class="tab==='famille' ? 'is-active' : ''">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Famille <span class="admin-tab-count">{{ $totalFamille }}</span>
        </button>
        <button type="button" @click="tab='emails'" class="admin-tab" :class="tab==='emails' ? 'is-active' : ''">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Emails
        </button>
    </div></div>

    {{-- ══ ONGLET OVERVIEW ══ --}}
    <div x-show="tab==='overview'" style="padding: 0 0 28px;">

        <div class="admin-stats s-stagger">
            @foreach([
                ['label'=>'Total users','value'=>$totalUsers,'color'=>'#4fc3f7','icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
                ['label'=>'Nouveaux (7j)','value'=>$newUsersWeek,'color'=>'#3df5c0','icon'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>'],
                ['label'=>'Actifs (30j)','value'=>$activeUsers30d,'color'=>'#f2b544','icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
                ['label'=>'Services','value'=>$totalServices,'color'=>'#6fd8ff','icon'=>'<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
                ['label'=>'Partages actifs','value'=>$totalShares,'color'=>'#7df7d2','icon'=>'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
            ] as $stat)
            <div class="admin-panel s-hud admin-stat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $stat['color'] }}" stroke-width="2" style="margin-bottom:10px; filter: drop-shadow(0 0 6px {{ $stat['color'] }});">{!! $stat['icon'] !!}</svg>
                <div style="font-size:1.75rem; color:{{ $stat['color'] }}; text-shadow: 0 0 18px {{ $stat['color'] }}66;"><span data-countup>{{ $stat['value'] }}</span></div>
                <div class="s-mono" style="font-size:0.7rem; letter-spacing:.12em; text-transform:uppercase; color:var(--text-muted); margin-top:6px;">{{ $stat['label'] }}</div>
                <div class="admin-stat-bar" aria-hidden="true"><span style="background: {{ $stat['color'] }}; box-shadow: 0 0 10px {{ $stat['color'] }};"></span></div>
            </div>
            @endforeach
        </div>

        <div class="admin-charts">
            <div class="admin-panel s-hud" style="padding:24px;">
                <h3 style="font-size:0.9rem; color:var(--text-primary); margin-bottom:16px;">Inscriptions (30 derniers jours)</h3>
                <canvas id="inscriptionsChart" height="160" style="display:block; width:100%; height:160px;"></canvas>
            </div>
            <div class="admin-panel s-hud" style="padding:24px;">
                <h3 style="font-size:0.9rem; color:var(--text-primary); margin-bottom:16px;">Actions par type</h3>
                <canvas id="actionsChart" height="160" style="display:block; width:100%; height:160px;"></canvas>
            </div>
        </div>

        <div class="admin-panel s-hud" style="padding:24px;">
            <h3 style="font-size:0.9rem; color:var(--text-primary); margin-bottom:16px;">Activité récente</h3>
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
                    <td style="padding:8px 12px;"><span class="s-mono" style="background:rgba(79,195,247,0.1); color:#8fd8ff; border:1px solid rgba(79,195,247,.25); border-radius:6px; padding:2px 8px; font-size:0.68rem;">{{ $log->action }}</span></td>
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
    <div x-show="tab==='users'" style="padding: 0 0 28px;">
        <p style="color:var(--text-secondary);margin-bottom:16px;">L’accès Famille offert est indépendant de Stripe. Son retrait ne résilie aucun abonnement payé. Sans abonnement payé, les membres du groupe perdent leur accès ; les coffres du propriétaire sont conservés.</p>
        <div class="admin-panel" style="overflow-x:auto;">
            <table class="admin-users-table" style="width:100%; border-collapse:collapse;">
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
                    <th style="text-align:left; padding:12px 16px; font-size:0.75rem; color:var(--text-muted); font-weight:600;">ACTIONS</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td data-label="Utilisateur" style="padding:12px 16px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#12263a,#1d5b80); border:1px solid rgba(79,195,247,.45); display:flex; align-items:center; justify-content:center; font-size:0.8rem; color:#e6f7ff; overflow:hidden; flex-shrink:0;">
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
                    <td data-label="Email" style="padding:12px 16px; font-size:0.82rem; color:var(--text-muted);">{{ $u->email }}</td>
                    <td data-label="Inscription" style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td data-label="Forfait" style="padding:12px 16px;">
                        @if($u->isFamilySubscriber())
                            <span class="badge badge-info">Famille payé</span>
                        @endif
                        @if($u->family_granted_at)
                            <span class="badge badge-warning">Famille offert</span>
                        @elseif(!$u->isFamilySubscriber() && $u->familyMembership)
                            <span style="color:var(--text-muted); font-size:0.78rem;">Membre d’un groupe</span>
                        @elseif(!$u->isFamilySubscriber())
                            <span class="badge">Free</span>
                        @endif
                    </td>
                    <td data-label="Connexion" style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $u->oauth_provider ? ucfirst($u->oauth_provider) : 'Email' }}</td>
                    <td data-label="MFA" style="padding:12px 16px;">
                        @if($u->mfa_email_enabled || $u->totp_enabled)
                            <span style="color:var(--neon); font-size:0.78rem;">✓ Actif</span>
                        @else
                            <span style="color:var(--text-muted); font-size:0.78rem;">—</span>
                        @endif
                    </td>
                    <td data-label="Services" style="padding:12px 16px; font-size:0.82rem; color:var(--text-primary); font-weight:600;">
                        {{ $u->coffres->sum('elements_count') }}
                    </td>
                    <td data-label="Rôle" style="padding:12px 16px;">
                        @if($u->is_admin)
                            <span class="badge badge-warning">Admin</span>
                        @else
                            <span style="color:var(--text-muted); font-size:0.78rem;">—</span>
                        @endif
                    </td>
                    <td data-label="Actions" style="padding:12px 16px; min-width:240px; color:var(--text-primary);">
                        @if($u->family_granted_at)
                            <details>
                                <summary style="cursor:pointer;">Retirer l’accès offert</summary>
                                <form method="POST" action="{{ route('admin.users.family-grant.destroy', $u) }}" style="margin:12px 0;">
                                    @csrf @method('DELETE')
                                    <label><input type="checkbox" name="confirmation" value="1" required> Confirmer le retrait pour {{ $u->email }}. L’abonnement payé, s’il existe, sera conservé.</label>
                                    <button type="submit" class="btn-secondary" style="margin-top:8px;">Retirer l’accès offert</button>
                                </form>
                            </details>
                        @else
                            <form method="POST" action="{{ route('admin.users.family-grant.store', $u) }}">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-secondary">Offrir le forfait Famille</button>
                            </form>
                        @endif
                        @if($u->id !== auth()->id())
                            <details style="margin-top:12px;">
                                <summary style="color:var(--danger);border-color:rgba(255,90,100,.35);cursor:pointer;">Supprimer le compte</summary>
                                <form method="POST" action="{{ route('admin.users.supprimer', $u) }}" style="margin-top:12px;">
                                    @csrf @method('DELETE')
                                    <p>Suppression définitive de {{ $u->name }}, de ses coffres et de ses partages. Cette action est irréversible et ne résilie aucun paiement.</p>
                                    <label for="delete-email-{{ $u->id }}">Recopiez {{ $u->email }} pour confirmer :</label>
                                    <input id="delete-email-{{ $u->id }}" name="confirmation_email" type="email" required autocomplete="off" class="input" style="margin:8px 0;">
                                    <button type="button" class="btn-secondary" @click="$el.closest('details').open = false">Annuler</button>
                                    <button type="submit" class="btn-danger">Supprimer définitivement</button>
                                </form>
                            </details>
                        @else
                            <p style="font-size:0.75rem;color:var(--text-muted);margin-top:8px;">Votre compte ne peut pas être supprimé ici.</p>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══ ONGLET FAMILLE ══ --}}
    <div x-show="tab==='famille'" style="padding: 0 0 28px;">
        <div class="admin-panel" style="overflow-x:auto;">
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
                            <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#12263a,#1d5b80); border:1px solid rgba(79,195,247,.45); display:flex; align-items:center; justify-content:center; font-size:0.8rem; color:#e6f7ff; overflow:hidden; flex-shrink:0;">
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
                            <span style="font-size:0.875rem; color:var(--text-primary);">{{ $group->members->count() }}</span>
                            <span style="font-size:0.78rem; color:var(--text-muted);">/ 6</span>
                            <div style="flex:1; max-width:60px; background:rgba(255,255,255,0.05); border-radius:4px; height:4px; overflow:hidden;">
                                <div style="background:var(--neon); box-shadow:0 0 8px rgba(61,245,192,.8); height:100%; width:{{ min(100, ($group->members->count() / 6) * 100) }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px; font-size:0.78rem; color:var(--text-muted);">{{ $group->created_at->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;">
                        @if($group->owner->hasFamilyPlan())
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-danger">Inactif</span>
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
    <div x-show="tab==='emails'" style="padding: 0 0 28px;" x-data="{ destinataire: 'tous' }">
        <div style="max-width:680px;">
            <div style="margin-bottom:24px;">
                <h2 style="font-size:1rem; color:var(--text-primary); margin-bottom:4px;">Envoyer un email</h2>
                <p style="font-size:0.8rem; color:var(--text-muted);">Envoyez un message à vos utilisateurs depuis le panel admin.</p>
            </div>

            <div class="admin-panel s-orbit-border" style="padding:28px;">
                <form method="POST" action="{{ route('admin.mail.envoyer') }}">
                    @csrf

                    <div style="margin-bottom:20px;">
                        <label style="font-size:0.8rem; color:var(--text-muted); margin-bottom:10px; display:block;">Destinataires</label>
                        <div style="display:flex; gap:20px; flex-wrap:wrap;">
                            @foreach(['tous' => 'Tous les utilisateurs', 'abonnes' => 'Abonnés Famille', 'user' => 'Utilisateur spécifique'] as $val => $label)
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;" @click="destinataire='{{ $val }}'">
                                <div :style="destinataire==='{{ $val }}' ? 'width:18px;height:18px;border-radius:50%;border:2px solid var(--accent-bright);background:var(--accent);box-shadow:0 0 12px rgba(79,195,247,.6);display:flex;align-items:center;justify-content:center;flex-shrink:0;' : 'width:18px;height:18px;border-radius:50%;border:2px solid rgba(255,255,255,0.2);background:transparent;display:flex;align-items:center;justify-content:center;flex-shrink:0;'">
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
    <script>
        const inscriptions = @js($inscriptionsParJour->map(fn ($item) => [
            'label' => date('d/m', strtotime($item->date)),
            'value' => (int) $item->total,
        ])->values());
        const actions = @js($actionsParType->map(fn ($item) => [
            'label' => $item->action,
            'value' => (int) $item->total,
        ])->values());

        function dessinerBarres(canvas, donnees, couleur) {
            if (!canvas || !donnees.length || !canvas.clientWidth) return;
            const ctx = canvas.getContext('2d');
            canvas.width = canvas.clientWidth * devicePixelRatio;
            canvas.height = canvas.clientHeight * devicePixelRatio;
            ctx.scale(devicePixelRatio, devicePixelRatio);
            const max = Math.max(...donnees.map((item) => item.value), 1);
            const zoneLargeur = canvas.clientWidth / donnees.length;
            const zoneHauteur = canvas.clientHeight - 30;
            const reduit = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const debut = performance.now();

            const peindre = (maintenant) => {
                const t = reduit ? 1 : Math.min(1, (maintenant - debut) / 900);
                const progression = 1 - Math.pow(1 - t, 3);
                ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);
                ctx.strokeStyle = 'rgba(79,195,247,0.08)';
                for (let i = 0; i <= 4; i++) {
                    const ligne = 5 + (zoneHauteur / 4) * i;
                    ctx.beginPath(); ctx.moveTo(0, ligne); ctx.lineTo(canvas.clientWidth, ligne); ctx.stroke();
                }
                donnees.forEach((item, index) => {
                    const barreHauteur = (item.value / max) * zoneHauteur * progression;
                    const x = index * zoneLargeur + zoneLargeur * 0.2;
                    const y = zoneHauteur - barreHauteur + 5;
                    const degrade = ctx.createLinearGradient(0, y, 0, zoneHauteur + 5);
                    degrade.addColorStop(0, couleur);
                    degrade.addColorStop(1, 'rgba(79,195,247,0.08)');
                    ctx.shadowColor = couleur;
                    ctx.shadowBlur = 10;
                    ctx.fillStyle = degrade;
                    ctx.fillRect(x, y, zoneLargeur * 0.6, barreHauteur);
                    ctx.shadowBlur = 0;
                    const pas = Math.max(1, Math.ceil(42 / zoneLargeur));
                    if (index % pas === 0) {
                        ctx.fillStyle = '#8fa2b8';
                        ctx.font = '10px "DM Mono", monospace';
                        ctx.textAlign = 'center';
                        ctx.fillText(item.label, x + zoneLargeur * 0.3, canvas.clientHeight - 8);
                    }
                });
                if (t < 1) requestAnimationFrame(peindre);
            };
            requestAnimationFrame(peindre);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('adminDashboard', () => ({
                tab: @js(in_array(request('tab'), ['overview', 'users', 'famille', 'emails'], true) ? request('tab') : 'overview'),
                init() {
                    this.$nextTick(() => this.initCharts());
                    this.$watch('tab', (valeur) => { if (valeur === 'overview') this.$nextTick(() => this.initCharts()); });
                },
                initCharts() {
                    dessinerBarres(document.getElementById('inscriptionsChart'), inscriptions, '#4fc3f7');
                    dessinerBarres(document.getElementById('actionsChart'), actions, '#3df5c0');
                }
            }));
        });
    </script>
@endpush
@endsection
