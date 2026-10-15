@extends('layouts.public')
@section('title', 'Soldier Le Gestionnaire Sécurisé')
<meta name="google-site-verification" content="lt3gvS7FOofvMaIMj0QKdhlRNYtoTRLmcUr7r1rHrVY" />
@section('content')
    <div style="overflow: hidden;">

        <section class="w-hero">
            <x-soldier.backdrop :video="false" :locks="9" />
            <video autoplay muted loop playsinline class="w-hero-video" aria-hidden="true">
                <source src="{{ asset('assets/Soldier.mp4') }}" type="video/mp4">
            </video>
            <div class="w-hero-shade" aria-hidden="true"></div>

            <div class="w-hero-inner s-stagger">
                <x-soldier.emblem :size="150" />
                <h1 class="w-hero-title">
                    Soldier blinde vos<br>
                    <span class="s-neon s-neon-flicker" data-scramble data-scramble-loop="10" data-scramble-duration="1200">mots de passe.</span>
                </h1>
                <p class="w-hero-sub">Un coffre-fort chiffré pour vos identifiants, vos notes et vos cartes — partagé seulement avec qui vous choisissez.</p>
                <div class="w-hero-ctas">
                    <a href="{{ route('inscription') }}" class="btn-primary" style="text-decoration: none; padding: 14px 30px;">Créer un compte gratuit →</a>
                    <a href="{{ route('connexion') }}" class="btn-secondary" style="text-decoration: none; padding: 14px 26px;">J'ai déjà un compte</a>
                </div>
                <div class="w-hero-chips">
                    <span class="s-chip s-chip-ok"><span class="s-dot" aria-hidden="true"></span> AES-256-GCM</span>
                    <span class="s-chip s-chip-b">Argon2id</span>
                    <span class="s-chip s-chip-b">RSA-4096</span>
                    <span class="s-chip s-chip-b">MFA · Passkeys</span>
                </div>
            </div>

            <a href="#w-stats" class="w-scroll" aria-label="Défiler vers le contenu">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
        </section>

        {{-- ══ STATS ══ --}}
        <section id="w-stats" class="w-stats">
            <div class="w-stats-grid s-stagger">
                @foreach([
                    ['Blindage total', 'Chiffrement', '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
                    ['Partage sécurisé', 'Accès contrôlé', '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
                    ['Protection renforcée', 'Mot de passe maître', '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
                    ['Confidentialité totale', 'Vie privée', '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'],
                ] as [$valeur, $label, $icon])
                    <div class="card s-hud s-glow w-stat">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="1.8" aria-hidden="true">{!! $icon !!}</svg>
                        <div class="w-stat-value">{{ $valeur }}</div>
                        <div class="w-stat-label">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        @php
            $features = [
                [
                    'video' => 'Chiffrement.mp4',
                    'titre' => 'Chiffrement AES-256-GCM',
                    'badge' => 'Authenticated Encryption',
                    'desc' => 'Tes mots de passe sont transformés en code secret avant d\'être enregistrés. Même si quelqu\'un accède aux données, il ne pourra rien lire.',
                    'points' => ['Tes données sont protégées automatiquement', 'Impossible à lire sans ton mot de passe', 'Un véritable coffre fort'],
                    'icon' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                ],
                [
                    'video' => 'zero-knowledge.mp4',
                    'titre' => 'Architecture de chiffrement',
                    'badge' => 'Serveur aveugle',
                    'desc' => 'Tes informations restent privées. Tout se passe sur ton appareil, pas sur nos serveurs.',
                    'points' => ['Tes données ne quittent jamais ton appareil en clair', 'Personne ne peut voir tes mots de passe, même pas nous', 'Même en cas de fraude, tes données restent protégées'],
                    'icon' => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>',
                ],
                [
                    'video' => 'Partage.mp4',
                    'titre' => 'Partage RSA-4096',
                    'badge' => 'Cryptographie asymétrique',
                    'desc' => 'Partage un accès en toute sécurité avec quelqu\'un de confiance, sans révéler ton mot de passe.',
                    'points' => ['Partage sécurisé en un clic', 'Seule la personne autorisée peut y accéder', 'Tu peux retirer l\'accès à tout moment'],
                    'icon' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
                ],
                [
                    'video' => 'Generateur.mp4',
                    'titre' => 'Générateur de mots de passe',
                    'badge' => 'Puissance infinie',
                    'desc' => 'Crée des mots de passe forts en un clic, sans avoir à réfléchir. Un générateur de mots de passe 100% fiable.',
                    'points' => ['Mots de passe ultra sécurisés générés', 'Options Phrase faciles à retenir', 'Copie sécurisée avec suppression automatique'],
                    'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                ],
                [
                    'video' => 'double-authen.mp4',
                    'titre' => 'Double authentification (MFA)',
                    'badge' => 'Email + TOTP',
                    'desc' => 'Ajoutez une couche de sécurité avec un code email ou un authenticator. Pour rajouter une couche de blindage.',
                    'points' => ['Code envoyé par email ou application', 'Compatible avec Google Authenticator', 'Protection contre les accès non autorisés'],
                    'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                ],
                [
                    'video' => 'extension-google.mp4',
                    'titre' => 'Extension Chrome',
                    'badge' => 'Autofill intelligent',
                    'desc' => 'Connecte-toi automatiquement sur tes sites préférés sans avoir à saisir tes mots de passe.',
                    'points' => ['Remplissage automatique des identifiants', 'Connexion en un clic', 'Fonctionne sur tous les sites'],
                    'icon' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="21.17" y1="8" x2="12" y2="8"/><line x1="3.95" y1="6.06" x2="8.54" y2="14"/><line x1="10.88" y1="21.94" x2="15.46" y2="14"/>',
                ],
            ];
        @endphp

        {{-- ══ FONCTIONNALITÉS ══ --}}
        @foreach($features as $i => $feature)
            @php $pair = $i % 2 === 0; @endphp
            <section class="feature-section">
                <div class="feature-section-inner" style="{{ $pair ? '' : 'flex-direction: row-reverse;' }}">

                    <div class="feature-video-wrap s-hud s-scan">
                        <span class="s-scan-line" aria-hidden="true"></span>
                        <video autoplay muted loop playsinline class="feature-video">
                            <source src="{{ asset('assets/' . $feature['video']) }}" type="video/mp4">
                        </video>
                        <div class="video-fallback">
                            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="rgba(79,195,247,0.45)" stroke-width="1">{!! $feature['icon'] !!}</svg>
                        </div>
                        <span class="feature-num s-mono" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="feature-text">
                        <span class="feature-badge-pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">{!! $feature['icon'] !!}</svg>
                            {{ $feature['badge'] }}
                        </span>
                        <h2 class="feature-titre">{{ $feature['titre'] }}</h2>
                        <p class="feature-desc">{{ $feature['desc'] }}</p>
                        <ul class="feature-points">
                            @foreach($feature['points'] as $point)
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2.5" style="flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </section>
        @endforeach

        {{-- ══ APPEL À L'ACTION ══ --}}
        <section style="position: relative; padding: 100px 16px; text-align: center;">
            <div class="card s-orbit-border s-orbit-neon w-cta">
                <div style="display: flex; justify-content: center; margin-bottom: 24px;"><x-soldier.emblem :size="96" :pulse="false" /></div>
                <h2>Prêt à sécuriser<br><span class="s-neon">vos mots de passe ?</span></h2>
                <p>Gratuit. Sécurisé. Vos données vous appartiennent.</p>
                <a href="{{ route('inscription') }}" class="btn-primary" style="font-size: 1rem; padding: 15px 44px; text-decoration: none;">
                    Commencer maintenant →
                </a>
            </div>
        </section>

        <section style="padding: 0 16px 80px; text-align: center;">
            <div class="card s-hud" style="max-width: 480px; margin: 0 auto; padding: 32px; display: flex; flex-direction: column; align-items: center; gap: 20px;">
                <div style="text-align: center;">
                    <div style="font-size: 1rem; color: var(--text-primary); margin-bottom: 6px;">Soldier est 100% gratuit</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Si vous aimez le projet, vous pouvez m'encourager</div>
                </div>
                <a href="https://paypal.me/BriceSteve" target="_blank" rel="noopener noreferrer" class="w-coffee">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.643-8.993 6.643H9.38l-1.165 7.388h3.114l.777-4.927h2.19c5.146 0 8.132-2.87 9.05-7.132a5.918 5.918 0 0 0-.124-1.685z"/></svg>
                    Offrez moi un café 😄
                </a>
            </div>
        </section>

        <footer class="w-footer">
            <a href="{{ route('privacy') }}">Politique de confidentialité</a>
            <p class="s-mono">Soldier Password Manager · AES-256-GCM · Argon2id · RSA-4096</p>
        </footer>

    </div>

    <style>
        .w-hero { position: relative; min-height: calc(100vh - 66px); display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 56px 16px 96px; }
        .w-hero-video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .35; z-index: 0; }
        .w-hero-shade { position: absolute; inset: 0; z-index: 0; background: radial-gradient(ellipse at 50% 45%, rgba(2,5,10,.2), rgba(2,5,10,.85) 70%, #02050a); }
        .w-hero .s-backdrop { z-index: 1; }
        .w-hero-inner { position: relative; z-index: 2; max-width: 820px; display: flex; flex-direction: column; align-items: center; gap: 22px; text-align: center; }
        .w-hero-title { font-size: clamp(2.3rem, 6.5vw, 4.6rem); font-weight: 400; line-height: 1.08; color: var(--text-primary); }
        .w-hero-sub { max-width: 560px; color: var(--text-secondary); font-size: 1rem; line-height: 1.75; }
        .w-hero-ctas { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 6px; }
        .w-hero-chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
        .w-scroll { position: absolute; bottom: 28px; left: 50%; z-index: 2; color: rgba(143,216,255,.7); transform: translateX(-50%); animation: w-bounce 2s ease-in-out infinite; }
        @keyframes w-bounce { 0%, 100% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(8px); } }

        .w-stats { position: relative; padding: 64px 16px; border-top: 1px solid rgba(79,195,247,.1); }
        .w-stats-grid { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
        .w-stat { padding: 22px; display: flex; flex-direction: column; gap: 10px; }
        .w-stat-value { font-size: 1.05rem; color: var(--accent-ultra); text-shadow: 0 0 14px rgba(79,195,247,.45); }
        .w-stat-label { font-family: var(--font-mono); font-size: .68rem; letter-spacing: .14em; text-transform: uppercase; color: var(--text-muted); }

        .feature-section { padding: 80px 16px; border-top: 1px solid rgba(79,195,247,.08); }
        .feature-section-inner { max-width: 1100px; margin: 0 auto; display: flex; align-items: center; gap: 64px; }
        .feature-video-wrap { flex: 1; min-width: 0; position: relative; aspect-ratio: 16 / 10; border-radius: 20px; overflow: hidden; border: 1px solid rgba(79,195,247,.28); background: var(--bg-surface); box-shadow: 0 24px 60px rgba(0,0,0,.55), 0 0 40px rgba(79,195,247,.1); transition: box-shadow .4s, transform .4s; }
        .feature-video-wrap:hover { transform: translateY(-4px); box-shadow: 0 30px 70px rgba(0,0,0,.6), 0 0 50px rgba(79,195,247,.22); }
        .feature-video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .video-fallback { display: none; position: absolute; inset: 0; align-items: center; justify-content: center; background: var(--bg-surface); }
        .feature-num { position: absolute; top: 14px; left: 16px; z-index: 2; padding: 2px 10px; border-radius: 999px; font-size: .7rem; letter-spacing: .14em; color: #8fd8ff; background: rgba(2,5,10,.7); border: 1px solid rgba(79,195,247,.35); }
        .feature-text { flex: 1; min-width: 0; }
        .feature-badge-pill { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 5px 14px; border-radius: 999px; font-family: var(--font-mono); font-size: .7rem; letter-spacing: .12em; text-transform: uppercase; color: var(--accent-ultra); background: rgba(45,159,212,.12); border: 1px solid rgba(79,195,247,.35); box-shadow: 0 0 16px rgba(79,195,247,.15); }
        .feature-titre { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 400; color: var(--text-primary); margin-bottom: 14px; line-height: 1.2; }
        .feature-desc { font-size: 1rem; color: var(--text-secondary); line-height: 1.75; margin-bottom: 24px; }
        .feature-points { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
        .feature-points li { display: flex; align-items: center; gap: 10px; font-size: .9rem; color: var(--text-secondary); }

        .w-cta { max-width: 580px; margin: 0 auto; padding: 56px 32px; border-radius: 24px; background: rgba(4,10,16,.88); }
        .w-cta h2 { font-size: clamp(1.7rem, 4vw, 2.25rem); font-weight: 400; color: var(--text-primary); line-height: 1.2; margin-bottom: 14px; }
        .w-cta p { color: var(--text-secondary); margin-bottom: 32px; line-height: 1.65; }
        .w-coffee { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 10px 20px; border-radius: 10px; background: #0070ba; color: #fff; font-size: .84rem; text-decoration: none; box-shadow: 0 0 22px rgba(0,112,186,.4); transition: background .15s, transform .15s; }
        .w-coffee:hover { background: #005ea6; transform: translateY(-1px); }
        .w-footer { padding: 24px 16px; border-top: 1px solid var(--border); text-align: center; }
        .w-footer a { color: var(--text-muted); font-size: .75rem; text-decoration: none; }
        .w-footer p { color: var(--text-muted); font-size: .7rem; letter-spacing: .08em; margin-top: 8px; }

        @media (max-width: 900px) { .w-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 768px) {
            .feature-section-inner { flex-direction: column !important; gap: 32px; }
            .feature-video-wrap { width: 100%; }
            .w-hero-ctas a { width: 100%; justify-content: center; }
        }
        @media (prefers-reduced-motion: reduce) { .w-scroll { animation: none; } }
    </style>

    @push('scripts')
        <script>
            document.querySelectorAll('.feature-video-wrap video').forEach(video => {
                video.addEventListener('error', function() {
                    const fallback = this.parentElement.querySelector('.video-fallback');
                    if (fallback) {
                        this.style.display = 'none';
                        fallback.style.display = 'flex';
                    }
                }, true);
            });
        </script>
    @endpush
@endsection
