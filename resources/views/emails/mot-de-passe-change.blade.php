@component('mail::message')
    # Mot de passe modifié

    Bonjour **{{ $user->name }}**,

    Le mot de passe de votre compte Soldier a été modifié.

    @component('mail::panel')
        **Détails de l'action :**
        - **Date :** {{ $date ?? now()->format('d/m/Y à H:i') }}
        - **Adresse IP :** {{ $ip }}
        - **Appareil :** {{ $appareil }}
    @endcomponent

    Si vous n'avez pas effectué cette modification, votre compte est compromis. Contactez notre support immédiatement.

    @component('mail::button', ['url' => config('app.url') . '/settings', 'color' => 'primary'])
        Sécuriser mon compte
    @endcomponent

    **L'équipe Soldier**
@endcomponent
