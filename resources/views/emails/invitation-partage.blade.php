@component('mail::message')
    # {{ $expediteur->name }} partage un coffre avec vous

    Bonjour,

    **{{ $expediteur->name }}** vous invite à accéder à son coffre sécurisé sur Soldier.

    @component('mail::panel')
        **Détails du partage :**
        - **Coffre :** {{ $nomCoffre }}
        - **Permission :** {{ $permission === 'ecriture' ? 'Lecture et écriture' : 'Lecture seule' }}
        - **Partagé par :** {{ $expediteur->name }} ({{ $expediteur->email }})
    @endcomponent

    Pour accepter cette invitation, cliquez sur le bouton ci-dessous. Vous devrez vous connecter ou créer un compte Soldier.

    @component('mail::button', ['url' => $lienAcceptation, 'color' => 'primary'])
        Accepter l'invitation
    @endcomponent

    > Ce lien expire dans **7 jours**.

    **L'équipe Soldier**
@endcomponent
