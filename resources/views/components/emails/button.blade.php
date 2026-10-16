@props(['href', 'variant' => 'primary'])
@php
    $styles = [
        'primary' => ['bg' => '#2d9fd4', 'color' => '#02121b', 'border' => '#4fc3f7'],
        'danger' => ['bg' => '#2a0d11', 'color' => '#ff8a90', 'border' => '#ff5a64'],
        'neon' => ['bg' => '#3df5c0', 'color' => '#02130e', 'border' => '#7df7d2'],
    ][$variant] ?? ['bg' => '#2d9fd4', 'color' => '#02121b', 'border' => '#4fc3f7'];
@endphp
<table role="presentation" align="center" cellspacing="0" cellpadding="0" border="0" style="margin:26px auto;">
    <tr>
        <td align="center" bgcolor="{{ $styles['bg'] }}" style="border-radius:12px; background-color:{{ $styles['bg'] }}; border:1px solid {{ $styles['border'] }};">
            <a href="{{ $href }}" target="_blank" style="display:inline-block; padding:15px 34px; font-family:'Audiowide', 'Trebuchet MS', Arial, sans-serif; font-size:14px; letter-spacing:0.5px; color:{{ $styles['color'] }}; text-decoration:none; border-radius:12px;">{{ $slot }}</a>
        </td>
    </tr>
</table>
