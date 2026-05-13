@extends('layouts.public')
@section('title', 'Vérifiez votre email')

@section('content')
    <div style="min-height: calc(100vh - 88px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%; max-width: 440px; text-align:center;">

            <div style="width:72px; height:72px; background:rgba(45,159,212,0.1); border:1px solid rgba(45,159,212,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>

            <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:12px;">Vérifiez votre email</h1>
            <p style="font-size:0.875rem; color:var(--text-muted); line-height:1.7; margin-bottom:32px;">
                Un lien de vérification a été envoyé à <strong style="color:var(--text-primary);">{{ auth()->user()->email }}</strong>.<br>
                Cliquez sur le lien dans l'email pour activer votre compte.
            </p>

            <div class="card" style="text-align:left;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:13px;">
                        Renvoyer l'email de vérification
                    </button>
                </form>

                <div class="divider"></div>

                <form method="POST" action="{{ route('deconnexion') }}">
                    @csrf
                    <button type="submit" class="btn-secondary" style="width:100%; justify-content:center; padding:11px; font-size:0.875rem;">
                        Se déconnecter
                    </button>
                </form>
            </div>

            <p style="margin-top:20px; font-size:0.75rem; color:var(--text-muted);">
                Vérifiez vos spams si vous ne trouvez pas l'email.
            </p>
        </div>
    </div>
@endsection
