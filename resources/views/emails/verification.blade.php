<x-emails.layout sujet="Vérifiez votre adresse email">
    <div class="icon-wrap icon-info">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
        </svg>
    </div>

    <h1>Vérifiez votre email</h1>
    <p>Merci de vous être inscrit sur Soldier. Cliquez sur le bouton ci-dessous pour vérifier votre adresse email et accéder à votre coffre.</p>

    <div class="btn-wrap">
        <a href="{{ $url }}" class="btn btn-primary">
            Vérifier mon email →
        </a>
    </div>

    <p style="font-size:0.8rem; color:#484848;">
        Ce lien expire dans <strong style="color:#606060;">60 minutes</strong>.<br>
        Si vous n'avez pas créé de compte Soldier, ignorez cet email.
    </p>

    <div class="warning-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink:0; margin-top:2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <p>Votre Master Password ne quitte jamais votre appareil. Nous ne vous le demanderons jamais par email.</p>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem; color:#505050;">
        Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
        <a href="{{ $url }}" style="color:#217eaa; font-size:0.75rem; word-break:break-all;">{{ $url }}</a>
    </p>
    <p style="font-size:0.82rem; color:#505050; margin-top:12px;"><strong style="color:#808080;">L'équipe Soldier</strong></p>
</x-emails.layout>
