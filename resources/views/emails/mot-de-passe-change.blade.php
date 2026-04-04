<x-emails.layout sujet="Mot de passe modifié sur votre compte Soldier">

    <div class="icon-wrap icon-warning">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    <h1>Mot de passe modifié</h1>
    <p>Bonjour <strong>{{ $user->name }}</strong>,<br>Le mot de passe de votre compte Soldier a été modifié.</p>

    <div class="panel">
        <h2>Détails de l'action :</h2>
        <ul>
            <li><strong>Date :</strong> {{ $date ?? now()->format('d/m/Y à H:i') }}</li>
            <li><strong>Adresse IP :</strong> {{ $ip }}</li>
            <li><strong>Appareil :</strong> {{ Str::limit($appareil, 60) }}</li>
        </ul>
    </div>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/settings" class="btn btn-danger">
            Sécuriser mon compte
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;">Si vous n'avez pas effectué cette modification, contactez notre support immédiatement.<br><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>
