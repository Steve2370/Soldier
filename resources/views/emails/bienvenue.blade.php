@component('mail::message')
    # Bienvenue sur Soldier, {{ $user->name }} !

    Votre compte a été créé avec succès. Votre coffre chiffré est prêt à l'emploi.

    @component('mail::panel')
        **Votre coffre est protégé par :**
        - Chiffrement **AES-256-GCM**
        - Dérivation de clé **Argon2id**
        - Architecture **Zero-knowledge** — nous ne pouvons jamais lire vos données
    @endcomponent

    **Ce que vous pouvez faire maintenant :**
    - Ajouter vos premiers mots de passe
    - Configurer l'authentification à deux facteurs (TOTP)
    - Partager votre coffre en toute sécurité

    @component('mail::button', ['url' => config('app.url') . '/dashboard', 'color' => 'primary'])
        Accéder à mon coffre
    @endcomponent

    > **⚠️ Rappel important :** Votre Master Password n'est **jamais** stocké sur nos serveurs. Si vous le perdez, vos données sont irrécupérables. Conservez-le dans un endroit sûr.

    Bienvenue dans la résistance,<br>
    **L'équipe Soldier**
@endcomponent
