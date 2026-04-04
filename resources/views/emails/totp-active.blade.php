@component('mail::message')
    # Authenticator TOTP activé

    Bonjour **{{ $user->name }}**,

    L'authentification par application Authenticator (TOTP) a été **activée** sur votre compte Soldier.

    @component('mail::panel')
        **Ce que cela signifie :**
        - À chaque connexion, vous devrez entrer un code de votre app Authenticator
        - Compatible avec Google Authenticator, Authy, 1Password
        - Codes valides 30 secondes
    @endcomponent

    Votre compte est maintenant protégé par une couche de sécurité supplémentaire.

    Si vous n'avez pas effectué cette action, désactivez immédiatement le TOTP dans vos paramètres et changez vos mots de passe.

    @component('mail::button', ['url' => config('app.url') . '/settings', 'color' => 'primary'])
        Mes paramètres de sécurité
    @endcomponent

    **L'équipe Soldier**
@endcomponent
