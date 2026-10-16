@props(['expire' => null])
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;">
    <tr>
        <td align="center" bgcolor="#02050a" style="background-color:#02050a; border:1px solid #2a4258; border-radius:14px; padding:26px 16px 22px;">
            <p class="s-code" style="margin:0; font-family:'DM Mono', Menlo, Consolas, 'Courier New', monospace; font-size:40px; line-height:1.2; letter-spacing:14px; padding-left:14px; color:#3df5c0; font-weight:500;">{{ $slot }}</p>
            @if($expire)
                <p style="margin:12px 0 0; font-size:12px; color:#8fa2b8;">{{ $expire }}</p>
            @endif
        </td>
    </tr>
</table>
