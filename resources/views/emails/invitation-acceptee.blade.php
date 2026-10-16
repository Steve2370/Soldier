<x-emails.layout :sujet="$destinataire->name.' a accepté votre invitation'" :preheader="$destinataire->name.' a maintenant accès à « '.$nomCoffre.' ».'">
    <x-emails.icon name="check-neon" alt="Validé" />
    <x-emails.title>Invitation acceptée</x-emails.title>
    <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $proprietaire->name }}</strong>,<br><strong style="color:#eef3f8;">{{ $destinataire->name }}</strong> a accepté votre invitation.</x-emails.text>

    <x-emails.panel title="Partage" tone="neon">
        <x-emails.row label="Coffre">{{ $nomCoffre }}</x-emails.row>
        <x-emails.row label="Accès donné à">{{ $destinataire->name }} ({{ $destinataire->email }})</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="config('app.url').'/partage'">Gérer mes partages</x-emails.button>
    <x-emails.signature>Vous pouvez retirer cet accès à tout moment.<br></x-emails.signature>
</x-emails.layout>
