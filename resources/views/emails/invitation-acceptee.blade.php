@component('mail::message')
    # {{ $destinataire->name }} a accepté votre invitation ✓

    Bonjour **{{ $proprietaire->name }}**,

    Bonne nouvelle ! **{{ $destinataire->name }}** a accepté votre invitation et a maintenant accès à votre coffre.

    @component('mail::panel')
        **Détails :**
        - **Coffre partagé :** {{ $nomCoffre }}
        - **Accès accordé à :** {{ $destinataire->name }} ({{ $destinataire->email }})
    @endcomponent

    Vous pouvez gérer ou révoquer cet accès à tout moment depuis votre page de partage.

    @component('mail::button', ['url' => config('app.url') . '/partage', 'color' => 'primary'])
        Gérer mes partages
    @endcomponent

    **L'équipe Soldier**
@endcomponent
