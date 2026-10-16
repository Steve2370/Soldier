<x-emails.layout :sujet="$membre->name.' a rejoint votre groupe Soldier Famille'" :preheader="$membre->name.' fait maintenant partie de votre groupe Famille.'">
    <x-emails.icon name="users-neon" alt="Groupe" />
    <x-emails.title>Nouveau membre dans votre groupe</x-emails.title>
    <x-emails.text><strong style="color:#eef3f8;">{{ $membre->name }}</strong> vient de rejoindre votre groupe Soldier Famille.</x-emails.text>

    <x-emails.panel title="Membre" tone="neon">
        <x-emails.row label="Nom">{{ $membre->name }}</x-emails.row>
        <x-emails.row label="E-mail">{{ $membre->email }}</x-emails.row>
        <x-emails.row label="Arrivée">{{ now()->format('d/m/Y à H:i') }}</x-emails.row>
    </x-emails.panel>

    <x-emails.button :href="url('/famille')">Gérer mon groupe →</x-emails.button>
    <x-emails.signature />
</x-emails.layout>
