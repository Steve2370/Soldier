<x-emails.layout sujet="{{ $expediteur->name }} a partagé un accès dans le groupe famille">
    @if($expediteur->avatar)
        <div style="text-align:center; margin-bottom:20px;">
            <img src="https://soldierkey.com/storage/{{ $expediteur->avatar }}"
                 alt="{{ $expediteur->name }}"
                 width="64" height="64"
                 style="border-radius:50%; border:2px solid #217eaa; object-fit:cover;">
        </div>
    @else
        <div class="icon-wrap icon-info">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
        </div>
    @endif

    <h1>{{ $expediteur->name }} a partagé un accès</h1>
    <p>Un nouveau partage a été effectué dans votre groupe famille Soldier.</p>

    <div class="panel">
        <ul>
            <li><strong>Partagé par :</strong> {{ $expediteur->name }} ({{ $expediteur->email }})</li>
            <li><strong>Coffre :</strong> {{ $nomCoffre }}</li>
            <li><strong>Destinataire :</strong> {{ $destinataire->name }}</li>
        </ul>
    </div>

    <div class="btn-wrap">
        <a href="{{ $lienAcceptation }}" class="btn btn-primary">
            Accepter l'accès →
        </a>
    </div>

    <p style="font-size:0.8rem;color:#484848;">
        Ce lien expire dans <strong style="color:#606060;">7 jours</strong>.
        Vous devez être connecté à Soldier pour accepter.
    </p>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;"><strong style="color:#808080;">L'équipe Soldier</strong></p>
</x-emails.layout>
