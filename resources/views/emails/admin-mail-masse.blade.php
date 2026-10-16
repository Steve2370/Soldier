<x-emails.layout :sujet="$sujet" :preheader="$sujet">
    <x-emails.icon name="megaphone-blue" />
    <x-emails.title>{{ $sujet }}</x-emails.title>

    @if($destinataireName)
        <x-emails.text>Bonjour <strong style="color:#eef3f8;">{{ $destinataireName }}</strong>,</x-emails.text>
    @else
        <x-emails.text>Bonjour,</x-emails.text>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:8px 0 6px;">
        <tr>
            <td bgcolor="#040c14" style="background-color:#040c14; border:1px solid #1a2b3c; border-radius:12px; padding:20px 22px; font-size:15px; line-height:1.8; color:#eef3f8;">
                {!! nl2br(e($contenu)) !!}
            </td>
        </tr>
    </table>

    <x-emails.signature />
</x-emails.layout>
