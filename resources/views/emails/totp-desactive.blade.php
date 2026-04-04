<x-emails.layout sujet="Authenticator TOTP désactivé sur votre compte">

    <div class="icon-wrap icon-warning">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>

    <h1>TOTP désactivé</h1>
    <p>Bonjour <strong>{{ $user->name }}</strong>,<br>L'authentification par Authenticator a été <strong>désactivée</strong> sur votre compte.</p>

    <div class="panel" style="border-left-color: #f59e0b;">
        <p style="color:#808080;">Votre compte est maintenant <strong>moins sécurisé</strong>. Nous vous recommandons vivement de réactiver le TOTP ou d'activer la vérification par email.</p>
    </div>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/settings" class="btn btn-danger">
            Sécuriser mon compte
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;">Si vous n'avez pas effectué cette action, votre compte est peut-être compromis.<br><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>
