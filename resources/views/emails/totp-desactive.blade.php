@component('mail::message')
    # Authenticator TOTP désactivé

    Bonjour **{{ $user->name }}**,

    L'authentification par application Authenticator (TOTP) a été **désactivée** sur votre compte Soldier.

    @component('mail::panel')
        **Votre compte est maintenant moins sécurisé.**

        Nous vous recommandons vivement de réactiver le TOTP ou d'activer la vérification par email.
    @endcomponent

    Si vous n'avez pas effectué cette action, votre compte est peut-être compromis. Agissez immédiatement :

    @component('mail::button', ['url' => config('app.url') . '/settings', 'color' => 'error'])
        Sécuriser mon compte
    @endcomponent

    **L'équipe Soldier**
@endcomponent
