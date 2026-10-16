<x-emails.layout :sujet="$proprietaire->name.' vous a ajouté à son groupe Soldier Famille'" :preheader="'Vous faites maintenant partie du groupe Famille de '.$proprietaire->name.'.'">
    <x-emails.icon name="users-blue" alt="Groupe" />
    <x-emails.title>Vous avez rejoint un groupe Famille</x-emails.title>
    <x-emails.text><strong style="color:#eef3f8;">{{ $proprietaire->name }}</strong> vous a ajouté à son groupe Soldier Famille.</x-emails.text>

    <x-emails.panel title="Groupe">
        <x-emails.row label="Propriétaire">{{ $proprietaire->name }}</x-emails.row>
        <x-emails.row label="Ajouté le">{{ now()->format('d/m/Y à H:i') }}</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="url('/famille')">Voir mon groupe →</x-emails.button>
    <x-emails.text muted>Connectez-vous à votre compte Soldier pour accéder au coffre familial.</x-emails.text>
    <x-emails.signature />
</x-emails.layout>
