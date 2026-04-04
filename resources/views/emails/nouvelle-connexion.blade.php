<x-emails.layout sujet="Nouvelle connexion à votre compte Soldier">

    <div class="icon-wrap icon-info">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
    </div>

    <h1>Nouvelle connexion détectée</h1>
    <p>Bonjour <strong>{{ $user->name }}</strong>,<br>Une connexion a été détectée sur votre compte Soldier.</p>

    <div class="panel">
        <h2>Détails de la connexion :</h2>
        <ul>
            <li><strong>Date :</strong> {{ $date }}</li>
            <li><strong>Adresse IP :</strong> {{ $ip }}</li>
            <li><strong>Appareil :</strong> {{ Str::limit($appareil, 60) }}</li>
        </ul>
    </div>

    <p>Si c'était vous, ignorez cet email.</p>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/settings" class="btn btn-danger">
            Ce n'était pas moi — Sécuriser
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;"><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>
