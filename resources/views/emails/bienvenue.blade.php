<x-emails.layout sujet="Bienvenue sur Soldier" preheader="Votre coffre chiffré est prêt.">
    <x-emails.icon name="shield-neon" alt="Bouclier" />
    <x-emails.title>Bienvenue sur Soldier,<br><span style="color:#3df5c0;">{{ $user->name }}</span></x-emails.title>
    <x-emails.text>Votre compte est créé et votre coffre chiffré est prêt à recevoir vos mots de passe.</x-emails.text>

    <x-emails.panel title="Votre coffre est protégé par" tone="neon">
        <x-emails.row><strong style="color:#eef3f8;">AES-256-GCM</strong> — chaque élément est chiffré et son intégrité vérifiée.</x-emails.row>
        <x-emails.row><strong style="color:#eef3f8;">Argon2id</strong> — votre Master Password est transformé en clé, lentement, pour résister aux attaques.</x-emails.row>
        <x-emails.row><strong style="color:#eef3f8;">RSA-4096</strong> — vos partages ne sont lisibles que par la personne choisie.</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="config('app.url').'/dashboard'">Ouvrir mon coffre →</x-emails.button>

    <x-emails.notice>
        <strong style="color:#f2b544;">Gardez votre Master Password en lieu sûr.</strong> Il n’est jamais conservé en clair : si vous le perdez, personne, pas même nous, ne pourra récupérer vos données.
    </x-emails.notice>

    <x-emails.signature>Bienvenue dans la résistance,<br></x-emails.signature>
</x-emails.layout>
