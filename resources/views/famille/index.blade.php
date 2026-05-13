@extends('layouts.app')
@section('content')
    <div style="max-width:720px; margin:0 auto; padding:32px 20px;">

        <div style="margin-bottom:32px;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                <div style="width:40px; height:40px; background:rgba(45,159,212,0.1); border:1px solid rgba(45,159,212,0.2); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">Groupe Famille</h1>
            </div>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-left:52px;">Partagez Soldier avec jusqu'à 6 membres · Zero-Knowledge</p>
        </div>

        @if(!$group && !$membership)
            <div style="background:var(--bg-card); border:1px solid rgba(45,159,212,0.2); border-radius:20px; padding:48px 40px; text-align:center;">
                <div style="width:72px; height:72px; background:rgba(45,159,212,0.08); border:1px solid rgba(45,159,212,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:12px;">Créez votre groupe famille</h2>
                <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:12px; line-height:1.7; max-width:420px; margin-left:auto; margin-right:auto;">
                    Un coffre familial partagé est créé automatiquement. Invitez jusqu'à 5 membres — ils y ont accès immédiatement.
                </p>

                <div style="display:flex; justify-content:center; gap:24px; margin-bottom:32px; flex-wrap:wrap;">
                    @foreach(['Coffre familial commun', 'Partage automatique', 'Accès immédiat', 'Gestion centralisée'] as $feat)
                        <div style="display:flex; align-items:center; gap:6px; font-size:0.8rem; color:var(--text-muted);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ $feat }}
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('famille.creer') }}">
                    @csrf
                    <button type="submit" style="background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:12px; padding:14px 40px; font-size:0.9rem; font-weight:700; cursor:pointer; font-family:'Audiowide',sans-serif;">
                        Créer mon groupe →
                    </button>
                </form>
            </div>

        @elseif($group)

            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px;">
                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:16px 18px;">
                    <div style="font-size:1.75rem; font-weight:800; color:#2d9fd4;">{{ $group->members->count() }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">/ 6 membres</div>
                </div>
                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:16px 18px;">
                    <div style="font-size:1.75rem; font-weight:800; color:#22c55e;">{{ 6 - $group->members->count() }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">places disponibles</div>
                </div>
                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:16px 18px;">
                    <div style="font-size:1.75rem; font-weight:800; color:#f59e0b;">{{ $group->members->where('role','member')->count() }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">membres invités</div>
                </div>
            </div>

            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px; margin-bottom:20px;">

                {{-- Header groupe --}}
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <div>
                        <h2 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">{{ $group->nom }}</h2>
                        <p style="font-size:0.75rem; color:var(--text-muted);">Créé le {{ $group->created_at->format('d/m/Y') }}</p>
                    </div>
                    <span style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); border-radius:20px; padding:4px 14px; font-size:0.72rem; font-weight:700; color:#22c55e; letter-spacing:0.04em;">
                PROPRIÉTAIRE
            </span>
                </div>

                <div style="margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-bottom:6px;">
                        <span>Membres</span>
                        <span>{{ $group->members->count() }} / 6</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.05); border-radius:4px; height:5px; overflow:hidden;">
                        <div style="background:linear-gradient(90deg,#217eaa,#2d9fd4); height:100%; width:{{ ($group->members->count() / 6) * 100 }}%; border-radius:4px; transition:width 0.3s;"></div>
                    </div>
                </div>

                {{-- Liste membres --}}
                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:24px;">
                    @foreach($group->members as $member)
                        <div style="background:var(--bg-elevated); border:1px solid {{ $member->role === 'owner' ? 'rgba(45,159,212,0.3)' : 'rgba(255,255,255,0.06)' }}; border-radius:10px; padding:12px 16px; display:flex; align-items:center; gap:12px;">
                            {{-- Avatar --}}
                            <div style="width:38px; height:38px; border-radius:50%; background:{{ $member->role === 'owner' ? 'var(--accent)' : 'var(--bg-card)' }}; border:1px solid {{ $member->role === 'owner' ? 'rgba(45,159,212,0.4)' : 'var(--border)' }}; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; color:#fff; flex-shrink:0; overflow:hidden;">
                                @if($member->user->avatar)
                                    <img src="{{ Storage::url($member->user->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                @endif
                            </div>

                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                                    <span style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">{{ $member->user->name }}</span>
                                    @if($member->role === 'owner')
                                        <span style="background:rgba(45,159,212,0.1); color:#2d9fd4; border-radius:20px; padding:1px 8px; font-size:0.68rem; font-weight:700;">Propriétaire</span>
                                    @endif
                                </div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">{{ $member->user->email }}</div>
                            </div>

                            @if($member->role === 'owner')
                                <span style="font-size:0.72rem; color:var(--text-muted);">Depuis {{ $member->joined_at?->format('d/m/Y') }}</span>
                            @else
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:0.72rem; color:var(--text-muted);">{{ $member->joined_at?->diffForHumans() }}</span>
                                    <form method="POST" action="{{ route('famille.retirer', $member) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background:rgba(239,68,68,0.08); color:#ef4444; border:1px solid rgba(239,68,68,0.2); border-radius:6px; padding:4px 10px; font-size:0.72rem; cursor:pointer; font-family:'Audiowide',sans-serif;">
                                            Retirer
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if(!$group->isFull())
                    <div style="border-top:1px solid var(--border-primary); padding-top:20px;">
                        <h3 style="font-size:0.875rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Inviter un membre</h3>
                        <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:12px;">L'utilisateur doit avoir un compte Soldier. Il aura accès au coffre familial immédiatement.</p>
                        <form method="POST" action="{{ route('famille.inviter') }}" style="display:flex; gap:10px;">
                            @csrf
                            <input type="email" name="email" placeholder="email@exemple.com"
                                   style="flex:1; background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:10px 14px; font-size:0.85rem; font-family:'Audiowide',sans-serif; outline:none; transition:border-color 0.15s;"
                                   onfocus="this.style.borderColor='rgba(45,159,212,0.5)'"
                                   onblur="this.style.borderColor='var(--border-primary)'">
                            <button type="submit" style="background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:8px; padding:10px 20px; font-size:0.85rem; font-weight:700; cursor:pointer; white-space:nowrap; font-family:'Audiowide',sans-serif;">
                                Inviter →
                            </button>
                        </form>
                    </div>
                @else
                    <div style="border-top:1px solid var(--border-primary); padding-top:16px; background:rgba(34,197,94,0.04); border-radius:0 0 12px 12px; text-align:center; padding:16px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" style="margin-bottom:6px;"><polyline points="20 6 9 17 4 12"/></svg>
                        <p style="font-size:0.85rem; color:#22c55e; font-weight:600;">Groupe complet — 6 / 6 membres</p>
                    </div>
                @endif
            </div>

            @if($group->coffre_id)
                <div style="background:rgba(45,159,212,0.05); border:1px solid rgba(45,159,212,0.2); border-radius:12px; padding:16px 20px; display:flex; align-items:center; gap:14px;">
                    <div style="width:40px; height:40px; background:rgba(45,159,212,0.1); border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:0.875rem; font-weight:700; color:var(--text-primary); margin-bottom:2px;">Coffre familial actif</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Tous les membres ont accès au coffre "👨‍👩‍👧‍👦 Famille". Ajoutez-y vos services partagés.</div>
                    </div>
                    <a href="{{ route('dashboard') }}" style="background:rgba(45,159,212,0.1); color:#2d9fd4; border:1px solid rgba(45,159,212,0.3); border-radius:8px; padding:7px 14px; font-size:0.78rem; font-weight:700; text-decoration:none; white-space:nowrap; font-family:'Audiowide',sans-serif;">
                        Voir →
                    </a>
                </div>
            @endif

        @elseif($membership)
            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:32px; text-align:center; margin-bottom:20px;">
                <div style="width:64px; height:64px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:1.25rem; font-weight:700; color:#fff; overflow:hidden; margin:0 auto 16px;">
                    @if($membership->group->owner->avatar)
                        <img src="{{ Storage::url($membership->group->owner->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        {{ strtoupper(substr($membership->group->owner->name, 0, 1)) }}
                    @endif
                </div>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:4px;">Vous êtes membre du groupe de</p>
                <p style="font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-bottom:6px;">{{ $membership->group->owner->name }}</p>
                <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:24px;">Rejoint {{ $membership->joined_at?->diffForHumans() }}</p>

                <div style="background:rgba(45,159,212,0.05); border:1px solid rgba(45,159,212,0.2); border-radius:10px; padding:12px 16px; margin-bottom:24px; text-align:left;">
                    <div style="font-size:0.78rem; color:#2d9fd4; font-weight:700; margin-bottom:8px;">Vos accès dans ce groupe</div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--text-muted);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Accès au coffre familial partagé
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" style="display:inline-block; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border-radius:10px; padding:10px 28px; font-size:0.85rem; font-weight:700; text-decoration:none; margin-bottom:16px; font-family:'Audiowide',sans-serif;">
                    Voir le coffre familial →
                </a>
            </div>

            <div style="background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.15); border-radius:12px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <div>
                    <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-bottom:2px;">Quitter le groupe</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">Vous perdrez l'accès au coffre familial.</div>
                </div>
                <form method="POST" action="{{ route('famille.quitter') }}">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3); border-radius:8px; padding:8px 16px; font-size:0.8rem; font-weight:600; cursor:pointer; font-family:'Audiowide',sans-serif;">
                        Quitter
                    </button>
                </form>
            </div>
        @endif

    </div>
@endsection
