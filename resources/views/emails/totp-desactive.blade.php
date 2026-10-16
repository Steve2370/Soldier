<x-emails.layout sujet="Authenticator TOTP désactivé sur votre compte" preheader="L’authentification par application a été retirée de votre compte.">
    <x-emails.icon name="alert-amber" alt="Attention" />
    <x-emails.title>Authenticator <span style="color:#f2b544;">désactivé</span></x-emails.title>
    <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $user->name }}</strong>,<br>l’authentification par application a été <strong style="color:#eef3f8;">désactivée</strong> sur votre compte.</x-emails.text>

    <x-emails.notice>
        Votre compte est maintenant <strong style="color:#f2b544;">moins protégé</strong>. Nous vous recommandons de réactiver l’Authenticator ou la vérification par e-mail.
    </x-emails.notice>

    <x-emails.button :href="config('app.url').'/settings'" variant="danger">Sécuriser mon compte</x-emails.button>
    <x-emails.signature>Vous n’avez pas fait cette action ? Votre compte est peut-être compromis.<br></x-emails.signature>
</x-emails.layout>
