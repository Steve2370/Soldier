@props(['title' => null, 'tone' => 'blue'])
@php $accent = ['blue' => '#4fc3f7', 'neon' => '#3df5c0', 'amber' => '#f2b544', 'red' => '#ff5a64'][$tone] ?? '#4fc3f7'; @endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:22px 0;">
    <tr>
        <td width="3" bgcolor="{{ $accent }}" style="width:3px; background-color:{{ $accent }}; border-radius:3px 0 0 3px;"></td>
        <td bgcolor="#040c14" style="background-color:#040c14; border:1px solid #1a2b3c; border-left:0; border-radius:0 12px 12px 0; padding:18px 20px;">
            @if($title)
                <p style="margin:0 0 10px; font-family:'DM Mono', Menlo, Consolas, 'Courier New', monospace; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:{{ $accent }};">{{ $title }}</p>
            @endif
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                {{ $slot }}
            </table>
        </td>
    </tr>
</table>
