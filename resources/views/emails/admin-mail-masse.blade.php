<x-emails.layout sujet="{{ $sujet }}">
    <h1>{{ $sujet }}</h1>

    @if($destinataireName)
        <p>Bonjour <strong style="color:#e0e0e0;">{{ $destinataireName }}</strong>,</p>
    @else
        <p>Bonjour,</p>
    @endif

    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:20px 24px; margin:20px 0; line-height:1.8; color:#e0e0e0;">
        {!! nl2br(e($contenu)) !!}
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;"><strong style="color:#808080;">L'équipe Soldier</strong> · soldierkey.com</p>
</x-emails.layout>
