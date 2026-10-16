<x-emails.layout sujet="Authenticator TOTP activé sur votre compte" preheader="Votre compte est maintenant protégé par une application d’authentification.">
    <x-emails.icon name="shieldcheck-neon" alt="Bouclier validé" />
    <x-emails.title>Authenticator <span style="color:#3df5c0;">activé</span></x-emails.title>
    <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $user->name }}</strong>,<br>l’authentification par application a été activée sur votre compte.</x-emails.text>

    <x-emails.panel title="Ce qui change" tone="neon">
        <x-emails.row>Un code de votre application sera demandé à chaque connexion.</x-emails.row>
        <x-emails.row>Compatible avec Google Authenticator, Authy et 1Password.</x-emails.row>
        <x-emails.row>Chaque code n’est valable que <strong style="color:#eef3f8;">30 secondes</strong>.</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="config('app.url').'/settings'">Mes paramètres de sécurité</x-emails.button>
    <x-emails.signature>Vous n’êtes pas à l’origine de cette action ? Sécurisez votre compte immédiatement.<br></x-emails.signature>
</x-emails.layout>
