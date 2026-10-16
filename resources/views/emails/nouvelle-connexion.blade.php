<x-emails.layout sujet="Nouvelle connexion à votre compte Soldier" :preheader="'Connexion le '.$date.' depuis '.$ip.'.'">
    <x-emails.icon name="login-blue" alt="Connexion" />
    <x-emails.title>Nouvelle connexion détectée</x-emails.title>
    <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $user->name }}</strong>,<br>une connexion à votre compte Soldier vient d’avoir lieu.</x-emails.text>

    <x-emails.panel title="Connexion">
        <x-emails.row label="Date">{{ $date }}</x-emails.row>
        <x-emails.row label="Adresse IP">{{ $ip }}</x-emails.row>
        <x-emails.row label="Appareil">{{ Str::limit($appareil, 60) }}</x-emails.row>
    </x-emails.panel>

    <x-emails.text muted>C’était vous ? Ignorez simplement cet e-mail.</x-emails.text>
    <x-emails.button :href="config('app.url').'/settings'" variant="danger">Ce n’était pas moi — sécuriser</x-emails.button>
    <x-emails.signature />
</x-emails.layout>
