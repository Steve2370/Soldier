@props(['name', 'alt' => ''])
<table role="presentation" align="center" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 22px;">
    <tr><td align="center"><img src="{{ rtrim(config('app.url', 'https://soldierkey.com'), '/') }}/assets/email/icon-{{ $name }}.png" width="64" height="64" alt="{{ $alt }}" style="display:block; width:64px; height:64px; border:0;"></td></tr>
</table>
