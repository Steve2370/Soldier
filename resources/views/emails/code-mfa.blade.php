@component('mail::message')
    # Code de vérification

    Bonjour **{{ $user->name }}**,

    Voici votre code de vérification à usage unique pour accéder à votre compte Soldier.

    @component('mail::panel')
        # {{ $code }}

        Ce code expire dans **10 minutes**.
    @endcomponent

    Si vous n'avez pas tenté de vous connecter, votre compte pourrait être compromis. Changez votre mot de passe immédiatement.

    @component('mail::button', ['url' => config('app.url') . '/connexion', 'color' => 'primary'])
        Accéder à mon compte
    @endcomponent

    **L'équipe Soldier**
@endcomponent
