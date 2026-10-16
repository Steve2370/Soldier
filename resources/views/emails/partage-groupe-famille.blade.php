<x-emails.layout :sujet="$expediteur->name.' a partagé un accès dans le groupe famille'" :preheader="$expediteur->name.' vous partage « '.$nomCoffre.' ».'">
    @if($expediteur->avatar)
        <x-emails.avatar :user="$expediteur" />
    @else
        <x-emails.icon name="share-blue" alt="Partage" />
    @endif
    <x-emails.title>{{ $expediteur->name }}<br><span style="color:#4fc3f7;">a partagé un accès</span></x-emails.title>
    <x-emails.text>Un nouveau partage a été fait dans votre groupe Famille Soldier.</x-emails.text>

    <x-emails.panel title="Détails du partage">
        <x-emails.row label="Partagé par">{{ $expediteur->name }} ({{ $expediteur->email }})</x-emails.row>
        <x-emails.row label="Coffre">{{ $nomCoffre }}</x-emails.row>
        <x-emails.row label="Destinataire">{{ $destinataire->name }}</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="$lienAcceptation">Accepter l’accès →</x-emails.button>
    <x-emails.text muted>Ce lien expire dans <strong style="color:#c3cedb;">7 jours</strong>. Vous devez être connecté à Soldier pour l’accepter.</x-emails.text>
    <x-emails.signature />
</x-emails.layout>
