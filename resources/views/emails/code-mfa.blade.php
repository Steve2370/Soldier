<x-emails.layout sujet="Votre code de vérification Soldier">

    <div class="icon-wrap icon-info">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
    </div>

    <h1>Code de vérification</h1>
    <p>Bonjour <strong>{{ $user->name }}</strong>,<br>Voici votre code à usage unique.</p>

    <div class="code-box">
        <div class="code-digits">{{ $code }}</div>
        <div class="code-expire">Ce code expire dans <strong style="color:#606060;">10 minutes</strong></div>
    </div>

    <p style="font-size:0.82rem;">Si vous n'avez pas tenté de vous connecter, votre compte pourrait être compromis.</p>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/connexion" class="btn btn-primary">
            Accéder à mon compte
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;"><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>
