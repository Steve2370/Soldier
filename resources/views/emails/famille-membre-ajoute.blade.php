<x-emails.layout sujet="{{ $membre->name }} a rejoint votre groupe Soldier Famille">
    <div class="icon-wrap icon-success">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
    </div>
    <h1>Nouveau membre dans votre groupe !</h1>
    <p><strong style="color:#e0e0e0;">{{ $membre->name }}</strong> ({{ $membre->email }}) vient de rejoindre votre groupe Soldier Famille.</p>
    <div class="panel">
        <ul>
            <li><strong>Membre :</strong> {{ $membre->name }}</li>
            <li><strong>Email :</strong> {{ $membre->email }}</li>
            <li><strong>Rejoint le :</strong> {{ now()->format('d/m/Y à H:i') }}</li>
        </ul>
    </div>
    <div class="btn-wrap">
        <a href="{{ url('/famille') }}" class="btn btn-primary">Gérer mon groupe →</a>
    </div>
    <div class="divider"></div>
    <p style="font-size:0.82rem;color:#505050;"><strong style="color:#808080;">L'équipe Soldier</strong></p>
</x-emails.layout>
