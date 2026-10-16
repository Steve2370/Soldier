<x-emails.layout :sujet="$expediteur->name.' partage un coffre avec vous'" :preheader="'Invitation à accéder à « '.$nomCoffre.' » sur Soldier.'">
    @if($expediteur->avatar)
        <x-emails.avatar :user="$expediteur" />
    @else
        <x-emails.icon name="share-blue" alt="Partage" />
    @endif
    <x-emails.title>{{ $expediteur->name }}<br><span style="color:#4fc3f7;">partage un coffre avec vous</span></x-emails.title>
    <x-emails.text>Vous êtes invité à accéder à un coffre chiffré sur Soldier.</x-emails.text>

    <x-emails.panel title="Détails du partage">
        <x-emails.row label="Coffre">{{ $nomCoffre }}</x-emails.row>
        <x-emails.row label="Permission">{{ $permission === 'ecriture' ? 'Lecture et écriture' : 'Lecture seule' }}</x-emails.row>
        <x-emails.row label="Partagé par">{{ $expediteur->name }} ({{ $expediteur->email }})</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="$lienAcceptation">Accepter l’invitation →</x-emails.button>
    <x-emails.text muted>Ce lien expire dans <strong style="color:#c3cedb;">7 jours</strong>. Vous devrez vous connecter ou créer un compte Soldier pour l’accepter.</x-emails.text>
    <x-emails.signature />
</x-emails.layout>
