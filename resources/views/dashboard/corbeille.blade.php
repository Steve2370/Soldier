@extends('layouts.app')
@section('title', 'Corbeille')
@section('content')
    <h1 style="margin-bottom: 12px;">Corbeille</h1>
    <p style="color: var(--text-muted); margin-bottom: 24px;">Vos éléments restent restaurables pendant 30 jours. Ils sont ensuite supprimés définitivement par la purge planifiée.</p>
    @forelse($elements as $element)
        <div class="card" style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div>
                <h2 style="font-size: 1rem;">{{ $element->label }}</h2>
                <p style="color: var(--text-muted); margin-top: 8px;">Supprimé {{ $element->deleted_at->diffForHumans() }}</p>
            </div>
            <form method="POST" action="{{ route('corbeille.restaurer', $element->id) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-primary">Restaurer</button>
            </form>
        </div>
    @empty
        <p>Votre corbeille est vide.</p>
    @endforelse
    {{ $elements->links() }}
@endsection
