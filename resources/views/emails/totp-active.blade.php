<x-emails.layout sujet="Authenticator TOTP activé sur votre compte">

    <div class="icon-wrap icon-success">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5">
            <rect x="5" y="11" width="14" height="10" rx="2"/>
            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
            <circle cx="12" cy="16" r="1" fill="#22c55e"/>
        </svg>
    </div>

    <h1>Authenticator TOTP activé ✓</h1>
    <p>Bonjour <strong>{{ $user->name }}</strong>,<br>L'authentification par Authenticator a été activée sur votre compte.</p>

    <div class="panel">
        <h2>Ce que cela signifie :</h2>
        <ul>
            <li>À chaque connexion, un code de votre app Authenticator sera requis</li>
            <li><strong>Compatible :</strong> Google Authenticator, Authy, 1Password</li>
            <li>Codes valides pendant <strong>30 secondes</strong></li>
        </ul>
    </div>

    <p>Votre compte est maintenant protégé par une couche de sécurité supplémentaire.</p>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/settings" class="btn btn-primary">
            Mes paramètres de sécurité
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;">Si vous n'avez pas effectué cette action, sécurisez votre compte immédiatement.<br><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>

