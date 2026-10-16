@props(['label' => null])
<tr>
    @if($label)
        <td valign="top" style="padding:5px 12px 5px 0; width:38%; font-family:'DM Mono', Menlo, Consolas, 'Courier New', monospace; font-size:12px; color:#8fa2b8; white-space:nowrap;">{{ $label }}</td>
        <td valign="top" style="padding:5px 0; font-size:14px; line-height:1.55; color:#eef3f8; word-break:break-word;">{{ $slot }}</td>
    @else
        <td valign="top" width="18" style="padding:5px 0; width:18px; color:#3df5c0; font-size:14px;">›</td>
        <td valign="top" style="padding:5px 0; font-size:14px; line-height:1.55; color:#c3cedb;">{{ $slot }}</td>
    @endif
</tr>
