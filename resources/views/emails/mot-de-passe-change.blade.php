<x-emails.layout sujet="Mot de passe modifié sur votre compte Soldier" preheader="Le mot de passe de votre compte vient d’être modifié.">
    <x-emails.icon name="lock-amber" alt="Cadenas" />
    <x-emails.title>Mot de passe modifié</x-emails.title>
    <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $user->name }}</strong>,<br>le mot de passe de votre compte Soldier vient d’être modifié.</x-emails.text>

    <x-emails.panel title="Détails" tone="amber">
        <x-emails.row label="Date">{{ $date ?? now()->format('d/m/Y à H:i') }}</x-emails.row>
        <x-emails.row label="Adresse IP">{{ $ip }}</x-emails.row>
        <x-emails.row label="Appareil">{{ Str::limit($appareil, 60) }}</x-emails.row>
    </x-emails.panel>

    <x-emails.text muted>C’était vous ? Vous n’avez rien à faire.</x-emails.text>
    <x-emails.button :href="config('app.url').'/settings'" variant="danger">Ce n’était pas moi — sécuriser</x-emails.button>
    <x-emails.signature />
</x-emails.layout>
