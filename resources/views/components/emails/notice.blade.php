@props(['tone' => 'amber'])
@php $c = ['amber' => ['#f2b544', '#1a1408'], 'red' => ['#ff5a64', '#1c0b0e']][$tone] ?? ['#f2b544', '#1a1408']; @endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:22px 0;">
    <tr>
        <td bgcolor="{{ $c[1] }}" style="background-color:{{ $c[1] }}; border:1px solid {{ $c[0] }}66; border-radius:12px; padding:14px 16px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>
                <td valign="top" width="28" style="width:28px; padding-top:2px;"><img src="{{ rtrim(config('app.url', 'https://soldierkey.com'), '/') }}/assets/email/icon-warn.png" width="18" height="18" alt="!" style="display:block; border:0;"></td>
                <td valign="top" style="font-size:13px; line-height:1.65; color:#c3cedb;">{{ $slot }}</td>
            </tr></table>
        </td>
    </tr>
</table>
