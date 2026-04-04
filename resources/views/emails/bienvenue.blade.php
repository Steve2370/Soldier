<x-emails.layout sujet="Bienvenue sur Soldier">

    <div class="icon-wrap icon-success">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
    </div>

    <h1>Bienvenue sur Soldier,<br>{{ $user->name }} !</h1>
    <p>Votre compte a été créé avec succès.<br>Votre coffre chiffré est prêt à l'emploi.</p>

    <div class="panel">
        <h2>Votre coffre est protégé par :</h2>
        <ul>
            <li><strong>Chiffrement AES-256-GCM</strong> — données illisibles sans votre clé</li>
            <li><strong>Argon2id</strong> — dérivation de clé résistante aux attaques</li>
            <li><strong>Architecture Zero-knowledge</strong> — nous ne pouvons jamais lire vos données</li>
        </ul>
    </div>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/dashboard" class="btn btn-primary">
            Accéder à mon coffre →
        </a>
    </div>

    <div class="warning-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink:0;margin-top:2px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <p><strong>Rappel important :</strong> Votre Master Password n'est <strong>jamais stocké</strong> sur nos serveurs. Si vous le perdez, vos données sont irrécupérables. Conservez-le dans un endroit sûr.</p>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;">Bienvenue dans la résistance,<br><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>
