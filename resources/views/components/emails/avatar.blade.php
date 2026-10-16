@props(['user'])
<table role="presentation" align="center" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 22px;">
    <tr><td align="center" style="border-radius:50%; padding:3px; background-color:#0d2131; border:2px solid #3df5c0;">
        <img src="https://soldierkey.com/storage/{{ $user->avatar }}" width="64" height="64" alt="{{ $user->name }}" style="display:block; width:64px; height:64px; border-radius:50%; border:0; object-fit:cover;">
    </td></tr>
</table>
