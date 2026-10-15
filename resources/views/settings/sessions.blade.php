@extends('layouts.app')
@section('title', 'Sessions actives')
@section('content')
    <div style="max-width: 900px;">
        <a href="{{ route('settings') }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 40px; font-size: 0.8rem; color: var(--text-muted); text-decoration: none; margin-bottom: 14px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Retour aux paramètres
        </a>

        <div class="s-page-head">
            <div>
                <span class="s-tag">// Périmètre</span>
                <h1 style="margin-top: 10px;">Sessions actives</h1>
                <p class="s-sub">Déconnectez les appareils que vous ne reconnaissez pas.</p>
            </div>
            <form method="POST" action="{{ route('settings.sessions.revoquer.toutes') }}">
                @csrf @method('DELETE')
                <button class="btn-primary" type="submit">Déconnecter tous les autres appareils</button>
            </form>
        </div>

        @if(count($sessions) > 0)
            <div class="card s-stagger" style="padding: 0;">
                @foreach($sessions as $session)
                    <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; padding: 18px 22px; border-top: {{ $loop->first ? '0' : '1px solid #12202e' }};">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            @if($session['is_current'])
                                <span class="s-dot" aria-hidden="true"></span>
                            @else
                                <span aria-hidden="true" style="width: 7px; height: 7px; border-radius: 50%; background: #6e8aa3; flex-shrink: 0;"></span>
                            @endif
                            <div style="min-width: 0;">
                                <h2 style="font-size: 0.92rem; font-weight: 400;">{{ $session['device'] }} · {{ $session['browser'] }}</h2>
                                <p class="s-mono" style="color: var(--text-muted); font-size: 0.72rem; margin-top: 6px;">{{ $session['ip'] }} · {{ $session['last_active'] }}</p>
                            </div>
                        </div>
                        @if($session['is_current'])
                            <span class="s-chip s-chip-ok badge badge-success">Cet appareil</span>
                        @else
                            <form method="POST" action="{{ route('settings.sessions.revoquer', $session['id']) }}">
                                @csrf @method('DELETE')
                                <button class="btn-danger" type="submit">Déconnecter</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="card"><p style="color: var(--text-muted);">Aucune session persistante à afficher.</p></div>
        @endif
    </div>
@endsection
