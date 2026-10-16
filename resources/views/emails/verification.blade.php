<x-emails.layout sujet="Vérifiez votre adresse email" preheader="Confirmez votre adresse pour ouvrir votre coffre Soldier.">
    <x-emails.icon name="mail-blue" alt="Enveloppe" />
    <x-emails.title>Vérifiez votre e-mail</x-emails.title>
    <x-emails.text>Merci de vous être inscrit sur Soldier. Confirmez votre adresse pour accéder à votre coffre.</x-emails.text>

    <x-emails.button :href="$url">Vérifier mon e-mail →</x-emails.button>

    <x-emails.text muted>Ce lien expire dans <strong style="color:#c3cedb;">60 minutes</strong>.<br>Vous n’avez pas créé de compte Soldier ? Ignorez cet e-mail.</x-emails.text>

    <x-emails.notice>
        Soldier ne vous demandera <strong style="color:#f2b544;">jamais</strong> votre Master Password par e-mail, par téléphone ou par message.
    </x-emails.notice>

    <x-emails.text muted align="left">Le bouton ne fonctionne pas ? Copiez ce lien dans votre navigateur :<br>
        <a href="{{ $url }}" style="color:#4fc3f7; font-size:12px; word-break:break-all;">{{ $url }}</a>
    </x-emails.text>
    <x-emails.signature />
</x-emails.layout>
