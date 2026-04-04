@component('mail::message')
    # Nouvelle connexion détectée

    Bonjour **{{ $user->name }}**,

    Une nouvelle connexion a été détectée sur votre compte Soldier.

    @component('mail::panel')
        **Détails de la connexion :**
        - **Date :** {{ $date }}
        - **Adresse IP :** {{ $ip }}
        - **Appareil :** {{ $appareil }}
    @endcomponent

    Si c'était vous, ignorez cet email.

    Si vous ne reconnaissez pas cette connexion, votre compte est peut-être compromis. Agissez immédiatement :

    @component('mail::button', ['url' => config('app.url') . '/settings', 'color' => 'error'])
        Sécuriser mon compte
    @endcomponent

    **L'équipe Soldier**
@endcomponent
