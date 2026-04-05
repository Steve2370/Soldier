<x-emails.layout sujet="{{ $destinataire->name }} a accepté votre invitation">

    <div class="icon-wrap icon-success">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </div>

    <h1>Invitation acceptée</h1>
    <p>Bonjour <strong>{{ $proprietaire->name }}</strong>,<br><strong>{{ $destinataire->name }}</strong> a accepté votre invitation.</p>

    <div class="panel">
        <h2>Détails :</h2>
        <ul>
            <li><strong>Coffre partagé :</strong> {{ $nomCoffre }}</li>
            <li><strong>Accès accordé à :</strong> {{ $destinataire->name }} ({{ $destinataire->email }})</li>
        </ul>
    </div>

    <div class="btn-wrap">
        <a href="{{ config('app.url') }}/partage" class="btn btn-primary">
            Gérer mes partages
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;">Vous pouvez révoquer cet accès à tout moment.<br><strong style="color:#808080;">L'équipe Soldier</strong></p>

</x-emails.layout>
