@extends('layouts.app')
@section('content')
    <div x-data="{ onglet: 'groupe' }" style="max-width:720px; margin:0 auto; padding:32px 20px;">

        <div style="margin-bottom:28px;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                <div style="width:40px; height:40px; background:rgba(45,159,212,0.1); border:1px solid rgba(45,159,212,0.2); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">Famille</h1>
            </div>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-left:52px;">Gérez votre groupe et vos secrets partagés</p>
        </div>

        <div style="display:flex; gap:24px; border-bottom:1px solid var(--border-primary); margin-bottom:28px;">
            <button @click="onglet='groupe'"
                    :style="onglet==='groupe' ? 'border-bottom:2px solid var(--accent); color:var(--accent-bright);' : 'color:var(--text-muted);'"
                    style="background:none; border:none; padding:10px 24px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px; font-family:'Audiowide',sans-serif; display:flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Groupe
            </button>
            <button @click="onglet='coffre'"
                    :style="onglet==='coffre' ? 'border-bottom:2px solid var(--accent); color:var(--accent-bright);' : 'color:var(--text-muted);'"
                    style="background:none; border:none; padding:10px 24px; font-size:0.875rem; font-weight:600; cursor:pointer; margin-bottom:-1px; font-family:'Audiowide',sans-serif; display:flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Coffre famille
                @if(auth()->user()->subscribed('famille'))
                    <span style="background:rgba(34,197,94,0.1); color:#22c55e; border-radius:20px; padding:1px 7px; font-size:0.65rem; font-weight:700;">ACTIF</span>
                @endif
            </button>
        </div>

        <div x-show="onglet==='groupe'" x-transition>
            @php $hasGroup = $group || $membership; @endphp

            @if(!$hasGroup)
                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:48px 32px; text-align:center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(45,159,212,0.3)" stroke-width="1.5" style="margin:0 auto 16px; display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <h2 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:8px;">Vous n'appartenez à aucun groupe</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.6;">Activez le plan Famille pour créer votre groupe, ou demandez à être invité par un membre existant.</p>
                    <button @click="onglet='coffre'" style="margin-top:20px; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:10px; padding:10px 24px; font-size:0.85rem; font-weight:700; cursor:pointer; font-family:'Audiowide',sans-serif;">
                        Activer le plan Famille →
                    </button>
                </div>

            @else
                @php $currentGroup = $group ?? $membership->group; @endphp
                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                        <div>
                            <h2 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:3px;">{{ $currentGroup->nom }}</h2>
                            <p style="font-size:0.75rem; color:var(--text-muted);">{{ $currentGroup->members->count() }} / 6 membres</p>
                        </div>
                        @if($group)
                            <span style="background:rgba(45,159,212,0.1); border:1px solid rgba(45,159,212,0.3); border-radius:20px; padding:4px 12px; font-size:0.72rem; font-weight:700; color:#2d9fd4;">PROPRIÉTAIRE</span>
                        @else
                            <span style="background:rgba(255,255,255,0.05); border:1px solid var(--border-primary); border-radius:20px; padding:4px 12px; font-size:0.72rem; font-weight:700; color:var(--text-muted);">MEMBRE</span>
                        @endif
                    </div>

                    <div style="background:rgba(255,255,255,0.05); border-radius:4px; height:4px; margin-bottom:20px; overflow:hidden;">
                        <div style="background:linear-gradient(90deg,#217eaa,#2d9fd4); height:100%; width:{{ ($currentGroup->members->count() / 6) * 100 }}%; border-radius:4px;"></div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:{{ $group && !$currentGroup->isFull() ? '20px' : '0' }};">
                        @foreach($currentGroup->members as $member)
                            <div style="background:var(--bg-elevated); border:1px solid {{ $member->role === 'owner' ? 'rgba(45,159,212,0.25)' : 'rgba(255,255,255,0.05)' }}; border-radius:10px; padding:11px 14px; display:flex; align-items:center; gap:12px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:{{ $member->role === 'owner' ? 'var(--accent)' : 'var(--bg-card)' }}; border:1px solid {{ $member->role === 'owner' ? 'rgba(45,159,212,0.4)' : 'var(--border)' }}; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; color:#fff; flex-shrink:0; overflow:hidden;">
                                    @if($member->user->avatar)
                                        <img src="{{ Storage::url($member->user->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                                    @elseif($member->user->avatar_url)
                                        <img src="{{ $member->user->avatar_url }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <span style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">{{ $member->user->name }}</span>
                                        @if($member->role === 'owner')
                                            <span style="font-size:0.65rem; color:#2d9fd4; font-weight:700; background:rgba(45,159,212,0.1); border-radius:20px; padding:1px 6px;">PROPRIETAIRE</span>
                                        @endif
                                    </div>
                                    <div style="font-size:0.72rem; color:var(--text-muted);">{{ $member->user->email }}</div>
                                </div>
                                <span style="font-size:0.72rem; color:var(--text-muted);">{{ $member->joined_at?->diffForHumans() }}</span>
                                @if($group && $member->role !== 'owner')
                                    <form method="POST" action="{{ route('famille.retirer', $member) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background:rgba(239,68,68,0.08); color:#ef4444; border:1px solid rgba(239,68,68,0.2); border-radius:6px; padding:4px 10px; font-size:0.72rem; cursor:pointer;">Retirer</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($group && !$currentGroup->isFull())
                        <div style="border-top:1px solid var(--border-primary); padding-top:16px;">
                            <form method="POST" action="{{ route('famille.inviter') }}" style="display:flex; gap:10px;">
                                @csrf
                                <input type="email" name="email" placeholder="Inviter par email..."
                                       style="flex:1; background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:9px 12px; font-size:0.85rem; font-family:'Audiowide',sans-serif; outline:none;">
                                <button type="submit" style="background:var(--accent); color:#fff; border:none; border-radius:8px; padding:9px 18px; font-size:0.85rem; font-weight:700; cursor:pointer; font-family:'Audiowide',sans-serif;">
                                    Inviter →
                                </button>
                            </form>
                            <p style="font-size:0.72rem; color:var(--text-muted); margin-top:6px;">L'utilisateur doit avoir un compte Soldier existant.</p>
                        </div>
                    @elseif($membership)
                        <div style="border-top:1px solid var(--border-primary); padding-top:14px; display:flex; align-items:center; justify-content:space-between;">
                            <p style="font-size:0.8rem; color:var(--text-muted);">Vous souhaitez quitter ce groupe ?</p>
                            <form method="POST" action="{{ route('famille.quitter') }}">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:rgba(239,68,68,0.08); color:#ef4444; border:1px solid rgba(239,68,68,0.2); border-radius:7px; padding:6px 14px; font-size:0.78rem; font-weight:600; cursor:pointer;">Quitter</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px; margin-top:16px;">
                    <h3 style="font-size:0.9rem; font-weight:700; color:var(--text-primary); margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Secrets du coffre famille
                    </h3>
                    @forelse($elementsFamiliaux ?? [] as $element)
                        <div style="background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:10px; padding:12px 16px; margin-bottom:8px; display:flex; align-items:center; gap:12px;">
                            <div style="width:36px; height:36px; background:rgba(45,159,212,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;">
                                @if($element['favicon_url'])
                                    <img src="{{ $element['favicon_url'] }}" style="width:22px; height:22px; object-fit:contain;">
                                @else
                                    <span style="font-size:0.8rem; font-weight:700; color:#2d9fd4;">{{ strtoupper(substr($element['label'], 0, 1)) }}</span>
                                @endif
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">{{ $element['label'] }}</div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">{{ ucfirst($element['type']) }}@if($element['url']) · {{ $element['url'] }}@endif</div>
                            </div>
                            <div style="display:flex; gap:6px;">
                                <a href="{{ route('services.afficher', $element['id']) }}" style="background:rgba(45,159,212,0.1); color:#2d9fd4; border:1px solid rgba(45,159,212,0.3); border-radius:6px; padding:4px 10px; font-size:0.75rem; text-decoration:none; font-family:'Audiowide',sans-serif;">
                                    Voir →
                                </a>
                                @if($group)
                                    <form method="POST" action="{{ route('services.supprimer', $element['id']) }}" onsubmit="return confirm('Supprimer {{ addslashes($element[\'label\']) }} ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background:rgba(239,68,68,0.08); color:#ef4444; border:1px solid rgba(239,68,68,0.2); border-radius:6px; padding:4px 10px; font-size:0.75rem; cursor:pointer;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:24px; border:1px dashed rgba(45,159,212,0.2); border-radius:10px;">
                            <p style="font-size:0.85rem; color:var(--text-muted);">Aucun secret dans le coffre famille pour l'instant.</p>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Ajoutez des services depuis le dashboard en choisissant le coffre <strong style="color:#2d9fd4;">Famille</strong>.</p>
                        </div>
                    @endforelse
                </div>

                @if($secretsPartages->count() > 0)
                    <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px; margin-top:16px;">
                        <h3 style="font-size:0.9rem; font-weight:700; color:var(--text-primary); margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Secrets partagés dans le groupe
                        </h3>
                    @forelse($secretsPartages as $share)
                        <div style="background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:10px; padding:12px 16px; margin-bottom:8px; display:flex; align-items:center; gap:12px;">
                            <div style="width:36px; height:36px; background:rgba(45,159,212,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">{{ $share->coffre->nom }}</div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">
                                    Partagé par {{ $share->proprietaire->name }}
                                    @if($share->accepte_le) · {{ $share->accepte_le->diffForHumans() }} @endif
                                </div>
                            </div>
                            <span style="background:rgba(34,197,94,0.1); color:#22c55e; border-radius:20px; padding:2px 10px; font-size:0.72rem; font-weight:600;">Actif</span>
                        </div>
                    @empty
                        <div style="text-align:center; padding:24px; border:1px dashed rgba(45,159,212,0.2); border-radius:10px;">
                            <p style="font-size:0.85rem; color:var(--text-muted);">Aucun secret partagé dans le groupe pour l'instant.</p>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Partagez un coffre depuis la page <a href="{{ route('partage.index') }}" style="color:#2d9fd4;">Partage</a>.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <div x-show="onglet==='coffre'" x-transition>
            @if(auth()->user()->subscribed('famille'))

                @if(!$group && !$membership)
                    <div style="background:var(--bg-card); border:1px solid rgba(45,159,212,0.2); border-radius:20px; padding:48px 40px; text-align:center;">
                        <div style="width:72px; height:72px; background:rgba(45,159,212,0.08); border:1px solid rgba(45,159,212,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:12px;">Créez votre groupe famille</h2>
                        <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:32px; line-height:1.7; max-width:400px; margin-left:auto; margin-right:auto;">
                            Un coffre familial partagé sera créé automatiquement. Invitez jusqu'à 5 membres.
                        </p>
                        <form method="POST" action="{{ route('famille.creer') }}">
                            @csrf
                            <button type="submit" style="background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:12px; padding:14px 40px; font-size:0.9rem; font-weight:700; cursor:pointer; font-family:'Audiowide',sans-serif;">
                                Créer mon groupe →
                            </button>
                        </form>
                    </div>

                @elseif($group)
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px;">
                        <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:16px 18px;">
                            <div style="font-size:1.75rem; font-weight:800; color:#2d9fd4;">{{ $group->members->count() }}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">/ 6 membres</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:16px 18px;">
                            <div style="font-size:1.75rem; font-weight:800; color:#2d9fd4;">{{ $group->members->count() }}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">places libres</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:12px; padding:16px 18px;">
                            <div style="font-size:1.75rem; font-weight:800; color:#2d9fd4;">{{ $group->members->count() }}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">invités</div>
                        </div>
                    </div>

                    <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:24px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                            <div>
                                <h2 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:3px;">{{ $group->nom }}</h2>
                                <p style="font-size:0.75rem; color:var(--text-muted);">Créé le {{ $group->created_at->format('d/m/Y') }}</p>
                            </div>
                            <span style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); border-radius:20px; padding:4px 12px; font-size:0.72rem; font-weight:700; color:#22c55e;">PROPRIÉTAIRE</span>
                        </div>

                        <div style="background:rgba(255,255,255,0.04); border-radius:4px; height:4px; margin-bottom:20px; overflow:hidden;">
                            <div style="background:linear-gradient(90deg,#217eaa,#2d9fd4); height:100%; width:{{ ($group->members->count() / 6) * 100 }}%;"></div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:20px;">
                            @foreach($group->members as $member)
                                <div style="background:var(--bg-elevated); border:1px solid {{ $member->role === 'owner' ? 'rgba(45,159,212,0.25)' : 'rgba(255,255,255,0.05)' }}; border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:12px;">
                                    <div style="width:34px; height:34px; border-radius:50%; background:{{ $member->role === 'owner' ? 'var(--accent)' : 'var(--bg-card)' }}; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; color:#fff; flex-shrink:0; overflow:hidden;">
                                        @if($member->user->avatar)
                                            <img src="{{ Storage::url($member->user->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                                        @elseif($member->user->avatar_url)
                                            <img src="{{ $member->user->avatar_url }}" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div style="flex:1;">
                                        <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">{{ $member->user->name }}</div>
                                        <div style="font-size:0.7rem; color:var(--text-muted);">{{ $member->user->email }}</div>
                                    </div>
                                    @if($member->role !== 'owner')
                                        <form method="POST" action="{{ route('famille.retirer', $member) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="background:rgba(239,68,68,0.08); color:#ef4444; border:1px solid rgba(239,68,68,0.2); border-radius:6px; padding:4px 10px; font-size:0.7rem; cursor:pointer;">Retirer</button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if(!$group->isFull())
                            <div style="border-top:1px solid var(--border-primary); padding-top:16px;">
                                <form method="POST" action="{{ route('famille.inviter') }}" style="display:flex; gap:10px;">
                                    @csrf
                                    <input type="email" name="email" placeholder="Inviter par email..."
                                           style="flex:1; background:var(--bg-elevated); border:1px solid var(--border-primary); border-radius:8px; color:var(--text-primary); padding:9px 12px; font-size:0.85rem; font-family:'Audiowide',sans-serif; outline:none;">
                                    <button type="submit" style="background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:8px; padding:9px 18px; font-size:0.85rem; font-weight:700; cursor:pointer; font-family:'Audiowide',sans-serif;">
                                        Inviter →
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if($group->coffre_id)
                        <div style="background:rgba(45,159,212,0.05); border:1px solid rgba(45,159,212,0.2); border-radius:12px; padding:16px 20px; display:flex; align-items:center; gap:14px;">
                            <div style="width:40px; height:40px; background:rgba(45,159,212,0.1); border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:0.875rem; font-weight:700; color:var(--text-primary); margin-bottom:2px;">Coffre "Famille" actif</div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">Tous les membres ont accès. Ajoutez vos services partagés depuis le dashboard.</div>
                            </div>
                            <a href="{{ route('dashboard') }}" style="background:rgba(45,159,212,0.1); color:#2d9fd4; border:1px solid rgba(45,159,212,0.3); border-radius:8px; padding:7px 14px; font-size:0.78rem; font-weight:700; text-decoration:none; font-family:'Audiowide',sans-serif;">
                                Voir →
                            </a>
                        </div>
                    @endif

                @elseif($membership)
                    <div style="background:var(--bg-card); border:1px solid var(--border-primary); border-radius:16px; padding:32px; text-align:center;">
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:4px;">Vous êtes membre du groupe de</p>
                        <p style="font-size:1.1rem; font-weight:800; color:var(--text-primary); margin-bottom:20px;">{{ $membership->group->owner->name }}</p>
                        <a href="{{ route('dashboard') }}" style="background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border-radius:10px; padding:10px 28px; font-size:0.85rem; font-weight:700; text-decoration:none; font-family:'Audiowide',sans-serif;">
                            Voir le coffre familial →
                        </a>
                    </div>
                @endif

            @else
                <div style="background:var(--bg-card); border:1px solid rgba(45,159,212,0.2); border-radius:20px; padding:48px 40px; text-align:center;">
                    <div style="width:72px; height:72px; background:rgba(45,159,212,0.08); border:1px solid rgba(45,159,212,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <span style="background:rgba(45,159,212,0.1); border:1px solid rgba(45,159,212,0.3); border-radius:20px; padding:4px 14px; font-size:0.72rem; font-weight:700; color:#2d9fd4; letter-spacing:0.06em; display:inline-block; margin-bottom:16px;">PLAN FAMILLE</span>
                    <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:12px;">Activez le plan Famille</h2>
                    <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:12px; line-height:1.7; max-width:420px; margin-left:auto; margin-right:auto;">
                        Partagez Soldier avec votre famille. Un coffre commun créé automatiquement, accès immédiat pour tous les membres.
                    </p>

                    <div style="display:flex; justify-content:center; gap:20px; margin-bottom:32px; flex-wrap:wrap;">
                        @foreach(['Coffre familial commun','Jusqu\'à 6 membres','Partage automatique','3,99$/mois'] as $feat)
                            <div style="display:flex; align-items:center; gap:6px; font-size:0.8rem; color:var(--text-muted);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                {{ $feat }}
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('pricing') }}" style="display:inline-block; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border-radius:12px; padding:14px 40px; font-size:0.9rem; font-weight:700; text-decoration:none; font-family:'Audiowide',sans-serif;">
                        Commencer — 3,99$/mois →
                    </a>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:12px;">Annulable à tout moment · Paiement sécurisé par Stripe</p>
                </div>
            @endif
        </div>

    </div>
@endsection
