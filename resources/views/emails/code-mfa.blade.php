<x-emails.layout sujet="Votre code de vérification Soldier" :preheader="'Votre code : '.$code.' — valable 10 minutes.'">
    <x-emails.icon name="clock-blue" alt="Horloge" />
    <x-emails.title>Code de vérification</x-emails.title>
    <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $user->name }}</strong>,<br>voici votre code à usage unique pour vous connecter.</x-emails.text>

    <x-emails.code expire="Ce code expire dans 10 minutes.">{{ $code }}</x-emails.code>

    <x-emails.notice>
        Vous n’essayez pas de vous connecter ? Quelqu’un connaît peut-être votre mot de passe : changez-le dès maintenant et ne communiquez jamais ce code.
    </x-emails.notice>

    <x-emails.button :href="config('app.url').'/connexion'">Accéder à mon compte</x-emails.button>
    <x-emails.signature />
</x-emails.layout>
