@props(['align' => 'center', 'muted' => false])
<p style="margin:0 0 18px; font-size:{{ $muted ? '13px' : '15px' }}; line-height:1.7; color:{{ $muted ? '#8fa2b8' : '#c3cedb' }}; text-align:{{ $align }};">{{ $slot }}</p>
