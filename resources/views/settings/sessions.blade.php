@extends('layouts.app')
@section('title', 'Sessions actives')
@section('content')
    <h1 style="margin-bottom: 12px;">Sessions actives</h1>
    <p style="color: var(--text-muted); margin-bottom: 24px;">Déconnectez les appareils que vous ne reconnaissez pas.</p>
    <a href="{{ route('settings') }}" class="btn-secondary" style="margin-bottom: 20px;">Retour aux paramètres</a>
    @forelse($sessions as $session)
        <div class="card" style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div>
                <h2 style="font-size: 1rem;">{{ $session['device'] }} · {{ $session['browser'] }}</h2>
                <p style="color: var(--text-muted); margin-top: 8px;">{{ $session['ip'] }} · {{ $session['last_active'] }}</p>
            </div>
            @if($session['is_current'])
                <span class="badge badge-success">Cet appareil</span>
            @else
                <form method="POST" action="{{ route('settings.sessions.revoquer', $session['id']) }}">
                    @csrf @method('DELETE')
                    <button class="btn-secondary" type="submit">Déconnecter</button>
                </form>
            @endif
        </div>
    @empty
        <p>Aucune session persistante à afficher.</p>
    @endforelse
    <form method="POST" action="{{ route('settings.sessions.revoquer.toutes') }}" style="margin-top: 24px;">
        @csrf @method('DELETE')
        <button class="btn-primary" type="submit">Déconnecter tous les autres appareils</button>
    </form>
@endsection
