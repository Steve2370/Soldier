@extends('layouts.app')
@section('content')
    <div style="max-width:700px; margin:0 auto; padding:32px 20px;">

        <div style="margin-bottom:28px;">
            <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:4px;">Groupe Famille</h1>
            <p style="font-size:0.85rem; color:var(--text-muted);">Gérez les membres de votre groupe — jusqu'à 6 personnes.</p>
        </div>

        @if(!$group && !$membership)
            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:40px; text-align:center;">
                <div style="width:64px; height:64px; background:rgba(45,159,212,0.1); border:1px solid rgba(45,159,212,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:10px;">Créez votre groupe famille</h2>
                <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:24px; line-height:1.6;">Invitez jusqu'à 5 autres membres et partagez la sécurité Soldier en famille.</p>
                <form method="POST" action="{{ route('famille.creer') }}">
                    @csrf
                    <button type="submit" style="background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:10px; padding:12px 32px; font-size:0.9rem; font-weight:700; cursor:pointer;">
                        Créer mon groupe →
                    </button>
                </form>
            </div>

        @elseif($group)
            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px; margin-bottom:20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <div>
                        <h2 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">{{ $group->nom }}</h2>
                        <p style="font-size:0.78rem; color:var(--text-muted);">{{ $group->members->count() }} / 6 membres</p>
                    </div>
                    <div style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); border-radius:20px; padding:4px 12px; font-size:0.75rem; font-weight:600; color:#22c55e;">
                        Propriétaire
                    </div>
                </div>

                <div style="background:rgba(255,255,255,0.05); border-radius:4px; height:6px; margin-bottom:24px; overflow:hidden;">
                    <div style="background:linear-gradient(90deg,#217eaa,#2d9fd4); height:100%; width:{{ ($group->members->count() / 6) * 100 }}%; border-radius:4px;"></div>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:24px;">
                    @foreach($group->members as $member)
                        <div style="background:var(--bg-elevated); border:1px solid {{ $member->role === 'owner' ? 'rgba(45,159,212,0.3)' : 'var(--border)' }}; border-radius:10px; padding:12px 16px; display:flex; align-items:center; gap:12px;">
                            <div style="width:36px; height:36px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; color:#fff; flex-shrink:0; overflow:hidden;">
                                @if($member->user->avatar)
                                    <img src="{{ Storage::url($member->user->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                @endif
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">{{ $member->user->name }}</div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">{{ $member->user->email }}</div>
                            </div>
                            @if($member->role === 'owner')
                                <span style="font-size:0.72rem; color:#2d9fd4; font-weight:600;">Propriétaire</span>
                            @else
                                <form method="POST" action="{{ route('famille.retirer', $member) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3); border-radius:6px; padding:4px 10px; font-size:0.75rem; cursor:pointer;">
                                        Retirer
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if(!$group->isFull())
                    <div style="border-top:1px solid var(--border-primary); padding-top:20px;">
                        <h3 style="font-size:0.875rem; font-weight:700; color:var(--text-primary); margin-bottom:12px;">Inviter un membre</h3>
                        <form method="POST" action="{{ route('famille.inviter') }}" style="display:flex; gap:10px;">
                            @csrf
                            <input type="email" name="email" placeholder="email@exemple.com"
                                   style="flex:1; background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:9px 12px; font-size:0.85rem; font-family:'Audiowide',sans-serif; outline:none;">
                            <button type="submit" style="background:var(--accent); color:#fff; border:none; border-radius:8px; padding:9px 18px; font-size:0.85rem; font-weight:700; cursor:pointer; white-space:nowrap;">
                                Inviter →
                            </button>
                        </form>
                        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px;">L'utilisateur doit avoir un compte Soldier existant.</p>
                    </div>
                @else
                    <div style="border-top:1px solid var(--border-primary); padding-top:16px; text-align:center;">
                        <p style="font-size:0.85rem; color:var(--text-muted);">Groupe complet — 6/6 membres.</p>
                    </div>
                @endif
            </div>

        @elseif($membership)
            <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px;">
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:20px;">
                    <div style="width:48px; height:48px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700; color:#fff; overflow:hidden; flex-shrink:0;">
                        @if($membership->group->owner->avatar)
                            <img src="{{ Storage::url($membership->group->owner->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ strtoupper(substr($membership->group->owner->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:2px;">Vous êtes membre du groupe de</p>
                        <p style="font-size:1rem; font-weight:700; color:var(--text-primary);">{{ $membership->group->owner->name }}</p>
                    </div>
                </div>

                <div style="background:rgba(239,68,68,0.05); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">Quitter ce groupe famille ?</p>
                    <form method="POST" action="{{ route('famille.quitter') }}">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3); border-radius:7px; padding:6px 14px; font-size:0.8rem; font-weight:600; cursor:pointer;">
                            Quitter
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
