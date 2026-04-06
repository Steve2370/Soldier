@extends('layouts.public')
@section('title', 'Soldier Le Gestionnaire Sécurisé')

@section('content')
    <div style="overflow: hidden;">

        <section style="position: relative; height: 100vh; overflow: hidden;">

            <video
                autoplay muted loop playsinline
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"
            >
                <source src="{{ asset('assets/Soldier.mp4') }}" type="video/mp4">
            </video>
            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); z-index: 1;"></div>

            <div style="position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); z-index: 2; animation: bounce 2s ease-in-out infinite;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
        </section>

        <section style="padding: 80px 24px 60px; text-align: center; max-width: 820px; margin: 0 auto;">

            <h1 style="font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 800; color: var(--text-primary); line-height: 1.1; margin-bottom: 22px; letter-spacing: -0.02em;">
                Soldier blinde vos,<br>
                <span style="color: var(--accent-bright);">mots de passe.</span>
            </h1>

            <div style="display: flex; gap: 48px; justify-content: center; flex-wrap: wrap; padding-top: 32px; border-top: 1px solid rgba(33,126,170,0.15);">
                @foreach([
                    ['Blindage total', 'Chiffrement'],
                    ['Partage sécurisé', 'Accès contrôlé'],
                    ['Protection renforcée', 'Mot de passe maître'],
                    ['Confidentialité totale', 'Vie privée'],
                ] as [$valeur, $label])
                    <div style="text-align: center;">
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--accent-bright);">{{ $valeur }}</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em;">{{ $label }}</div>
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
                    'desc' => 'Tes mots de passe sont transformés en code secret avant d\'être enregistrées. Même si quelqu\'un accède aux données, il ne pourra rien lire.',
                    'points' => ['Tes données sont protégées automatiquement', 'Impossible à lire sans ton mot de passe', 'Un véritable coffre fort'],
                    'icon' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                ],
                [
                    'video' => 'zero-knowledge.mp4',
                    'titre' => 'Architecture Zero-Knowledge',
                    'badge' => 'Serveur aveugle',
                    'desc' => 'Tes informations restent privés. Tout se passe sur ton appareil, par sur nos serveur.',
                    'points' => ['Tes données ne quittent jamais ton appareil en clair', 'Personne ne peut voir tes mots de passe, même pas nous', 'Même en cas de fraude, tes données restent protégées'],
                    'icon' => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>',
                ],
                [
                    'video' => 'Partage.mp4',
                    'titre' => 'Partage RSA-4096',
                    'badge' => 'Cryptographie asymétrique',
                    'desc' => 'Partage un accès en toute sécurité avec quelqu\'un de confiance, sans révéler ton mot de passe',
                    'points' => ['Partage sécurisé en un clic', 'Seule la personne autorisée peut y accéder', 'Tu peux retirer l\'accès à tout moment'],
                    'icon' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
                ],
                [
                    'video' => 'Generateur.mp4',
                    'titre' => 'Générateur de mots de passe',
                    'badge' => 'Puissance infinie',
                    'desc' => 'Crée des mots de passe forts en un clic, sans avoir à réflechir. Une génératrice de mot de passe 100% fiable',
                    'points' => ['Mots de passe ultra sécurisés générés', 'Options Phrase faciles à retenir', 'Copie sécurisée avec suppression automatique'],
                    'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                ],
                [
                    'video' => 'double-authen.mp4',
                    'titre' => 'Double authentification (MFA)',
                    'badge' => 'Email + TOTP',
                    'desc' => 'Ajoutez une couche de sécurité avec un code email ou un authenticator. Pour rajouter une couche de blindage',
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

        @foreach($features as $i => $feature)
            @php $pair = $i % 2 === 0; @endphp
            <section class="feature-section">
                <div class="feature-section-inner" style="{{ $pair ? '' : 'flex-direction: row-reverse;' }}">

                    <div class="feature-video-wrap">
                        <video
                            autoplay muted loop playsinline
                            class="feature-video"
                            poster="{{ asset('videos/' . str_replace('.mp4', '-poster.jpg', $feature['video'])) }}"
                        >
                            <source src="{{ asset('assets/' . $feature['video']) }}" type="video/mp4">
                            <div class="video-placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(33,126,170,0.4)" stroke-width="1">{!! $feature['icon'] !!}</svg>
                            </div>
                        </video>
                        <div class="video-fallback">
                            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="rgba(33,126,170,0.35)" stroke-width="1">{!! $feature['icon'] !!}</svg>
                        </div>
                    </div>

                    <div class="feature-text">
                        <span class="feature-badge-pill">{{ $feature['badge'] }}</span>
                        <h2 class="feature-titre">{{ $feature['titre'] }}</h2>
                        <p class="feature-desc">{{ $feature['desc'] }}</p>
                        <ul class="feature-points">
                            @foreach($feature['points'] as $point)
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2.5" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </section>
        @endforeach

        <section style="padding: 100px 24px; text-align: center;">
            <div style="max-width: 560px; margin: 0 auto; background: rgba(22,37,52,0.7); border: 1px solid rgba(33,126,170,0.2); border-radius: 24px; padding: 60px 40px; backdrop-filter: blur(16px);">
                <h2 style="font-size: 2.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 14px; letter-spacing: -0.02em;">
                    Prêt à sécuriser<br>vos mots de passe ?
                </h2>
                <p style="color: var(--text-secondary); margin-bottom: 36px; font-size: 1rem; line-height: 1.65;">
                    Gratuit. Sécurisé. Vos données vous appartiennent.
                </p>
                <a href="{{ route('inscription') }}" class="btn-primary" style="font-size: 1.05rem; padding: 15px 44px;">
                    Commencer maintenant →
                </a>
            </div>
        </section>

        <section style="padding: 0 24px 80px; text-align: center;">
            <div style="max-width: 480px; margin: 0 auto; background: rgba(22,37,52,0.5); border: 1px solid rgba(33,126,170,0.15); border-radius: 20px; padding: 32px 40px; backdrop-filter: blur(16px); display: flex; flex-direction: column; align-items: center; gap: 20px;">
                <div style="text-align: center;">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary); margin-bottom: 6px;">Soldier est 100% gratuit</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Si vous aimez le projet, vous pouvez m'encourager</div>
                </div>
                <a href="https://paypal.me/BriceSteve" target="_blank"
                   style="display: inline-flex; align-items: center; gap: 8px; background: #0070ba; color: #fff; border-radius: 10px; padding: 10px 20px; font-size: 0.8375rem; font-weight: 700; font-family: 'Audiowide', sans-serif; text-decoration: none; transition: background 0.15s;"
                   onmouseover="this.style.background='#005ea6'"
                   onmouseout="this.style.background='#0070ba'">
                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg"
                         alt="PayPal" style="height: 18px; width: auto; border-radius: 2px;">
                    Offrez moi un café 😄
                </a>
            </div>
        </section>

        <footer style="padding: 24px 40px; border-top: 1px solid rgba(33,126,170,0.1); text-align: center;">
            <p style="color: var(--text-muted); font-size: 0.75rem; letter-spacing: 0.04em;">
                Soldier Password Manager · AES-256-GCM · Argon2id · RSA-4096 · Zero-Knowledge
            </p>
        </footer>

    </div>

    <style>
        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50%       { transform: translateX(-50%) translateY(8px); }
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(1.4); }
        }

        .feature-section {
            padding: 80px 24px;
            border-top: 1px solid rgba(33,126,170,0.08);
        }

        .feature-section-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 64px;
        }

        .feature-video-wrap {
            flex: 1;
            min-width: 0;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(33,126,170,0.2);
            background: var(--bg-surface);
            position: relative;
            aspect-ratio: 16 / 10;
        }

        .feature-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 20px;
        }

        .video-fallback {
            display: none;
            position: absolute;
            inset: 0;
            align-items: center;
            justify-content: center;
            background: var(--bg-surface);
        }

        .feature-text {
            flex: 1;
            min-width: 0;
        }

        .feature-badge-pill {
            display: inline-flex;
            align-items: center;
            background: rgba(33,126,170,0.12);
            border: 1px solid rgba(33,126,170,0.3);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--accent-bright);
            letter-spacing: 0.04em;
            margin-bottom: 16px;
        }

        .feature-titre {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 14px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .feature-desc {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.75;
            margin-bottom: 24px;
        }

        .feature-points {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .feature-points li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .feature-section-inner {
                flex-direction: column !important;
                gap: 32px;
            }
            .feature-video-wrap {
                width: 100%;
            }
        }
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
                });
            });
        </script>
    @endpush
@endsection
