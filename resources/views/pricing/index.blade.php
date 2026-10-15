@extends('layouts.public')
@section('title', 'Tarifs — Soldier Password Manager')

@section('content')
<div style="position: relative; overflow: hidden;">
    <x-soldier.backdrop :locks="5" opacity="0.18" />

    {{-- Hero --}}
    <section class="pricing-hero s-stagger">
        @if($errors->has('billing'))
            <p role="alert" class="pricing-alert">{{ $errors->first('billing') }}</p>
        @endif
        <h1>
            Gratuit pour toujours.<br>
            <span class="s-neon-blue">Famille si vous le souhaitez.</span>
        </h1>
        <p class="pricing-lead">
            Aucune carte de crédit requise pour commencer. Soldier est gratuit et le restera toujours. Le plan Famille est là pour ceux qui veulent aller plus loin.
        </p>
    </section>

    {{-- Plans --}}
    <section style="position: relative; padding: 0 16px 80px;">
        <div class="pricing-grid">

            {{-- Plan Gratuit --}}
            <div class="pricing-card s-hud">
                <div style="margin-bottom: 28px;">
                    <div class="pricing-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="pricing-plan">Gratuit</div>
                    <div class="pricing-price">
                        <span>0$</span>
                        <small>/mois</small>
                    </div>
                    <p class="pricing-desc">Pour les individus qui veulent protéger leurs mots de passe sans compromis.</p>
                </div>

                <div class="pricing-features">
                    @foreach([
                        'Coffres illimités',
                        'Services illimités',
                        'Extension Chrome',
                        'MFA Triple (TOTP, Passkeys, Email)',
                        'Partage sécurisé RSA-4096',
                        'Générateur de mots de passe',
                        'Chiffrement AES-256-GCM',
                        'Open Source',
                    ] as $feature)
                    <div class="pricing-feature">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2.5" style="flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn-secondary pricing-cta">
                        Votre plan actuel →
                    </a>
                @else
                    <a href="{{ route('inscription') }}" class="btn-secondary pricing-cta">
                        Commencer gratuitement →
                    </a>
                @endauth
            </div>

            {{-- Plan Famille --}}
            <div class="pricing-card pricing-card-hot s-orbit-border s-orbit-neon">
                <span class="pricing-popular">Populaire</span>

                <div style="margin-bottom: 28px;">
                    <div class="pricing-icon pricing-icon-hot">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="pricing-plan" style="color: var(--neon);">Famille</div>
                    <div class="pricing-price">
                        <span class="s-neon">3,99$</span>
                        <small>/mois</small>
                    </div>
                    <p class="pricing-desc">Pour les familles qui veulent partager la sécurité jusqu'à 6 membres.</p>
                </div>

                <div class="pricing-features">
                    <div class="s-tag" style="color: var(--neon); margin-bottom: 12px;">Tout le plan gratuit +</div>
                    @foreach([
                        'Jusqu\'à 6 membres',
                        'Tableau de bord familial',
                        'Coffres familiaux partagés',
                        'Gestion centralisée des accès',
                        'Support prioritaire par email',
                        'Facturation mensuelle simple',
                    ] as $feature)
                    <div class="pricing-feature">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2.5" style="flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                @auth
                    @if($abonnement)
                        <a href="{{ route('pricing.portail') }}" class="btn-primary pricing-cta">
                            Gérer mon abonnement →
                        </a>
                    @elseif(auth()->user()->family_granted_at)
                        <a href="{{ route('famille.index') }}" class="btn-secondary pricing-cta" style="color: var(--neon); border-color: rgba(61,245,192,0.45);">Votre accès Famille est offert →</a>
                    @else
                        <form method="POST" action="{{ route('pricing.checkout') }}">
                            @csrf
                            <button type="submit" class="btn-primary pricing-cta">
                                Commencer — 3,99$/mois →
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('inscription') }}" class="btn-primary pricing-cta">
                        Créer un compte →
                    </a>
                @endauth
            </div>

        </div>
    </section>

    <section style="position: relative; padding: 0 16px 80px;">
        <div style="max-width: 680px; margin: 0 auto;">
            <h2 class="pricing-h2">Questions fréquentes</h2>

            <div class="card s-hud" style="padding: 6px 24px;">
            @foreach([
                ['Q' => 'Le plan gratuit est-il vraiment gratuit ?', 'R' => 'Oui, complètement gratuit et sans limite. Aucune carte de crédit, aucune fonctionnalité cachée derrière un paywall. Soldier est open source et restera gratuit pour toujours.'],
                ['Q' => 'Mes données sont-elles en sécurité avec Soldier ?', 'R' => 'Soldier chiffre les données sensibles avec AES-256-GCM et protège les clés avec Argon2id. L\'implémentation web actuelle déverrouille le coffre côté serveur ; une architecture zero-knowledge complète est encore en cours de réalisation.'],
                ['Q' => 'Comment fonctionne le plan Famille ?', 'R' => 'Vous invitez jusqu\'à 5 autres membres. Chacun a son propre compte sécurisé et vous pouvez partager des accès entre membres de façon contrôlée.'],
                ['Q' => 'Puis-je annuler mon abonnement Famille ?', 'R' => 'Oui, à tout moment depuis votre espace de gestion. L\'annulation prend effet à la fin de la période de facturation en cours. Vos données restent accessibles.'],
                ['Q' => 'Le bouton "Offrez-moi un café" est-il obligatoire ?', 'R' => 'Non, c\'est complètement optionnel. Si vous aimez Soldier et voulez encourager le projet, vous pouvez contribuer. Mais rien n\'est requis.'],
            ] as $faq)
            <div x-data="{ open: false }" class="pricing-faq" :class="open ? 'is-open' : ''">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()">
                    <span>{{ $faq['Q'] }}</span>
                    <svg :style="open ? 'transform:rotate(45deg)' : ''" style="flex-shrink: 0; transition: transform 0.25s;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <div x-show="open" x-transition style="padding-top: 12px;">
                    <p>{{ $faq['R'] }}</p>
                </div>
            </div>
            @endforeach
            </div>
        </div>
    </section>

    <section style="position: relative; padding: 0 16px 80px; text-align: center;">
        <div class="card s-hud" style="max-width: 480px; margin: 0 auto; padding: 32px; display: flex; flex-direction: column; align-items: center; gap: 20px;">
            <div style="text-align: center;">
                <div style="font-size: 1rem; color: var(--text-primary); margin-bottom: 6px;">Soldier est 100% gratuit</div>
                <div style="font-size: 0.82rem; color: var(--text-muted);">Si vous aimez le projet, vous pouvez m'encourager</div>
            </div>
            <a href="https://paypal.me/BriceSteve" target="_blank" rel="noopener noreferrer" class="pricing-coffee">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.643-8.993 6.643H9.38l-1.165 7.388h3.114l.777-4.927h2.19c5.146 0 8.132-2.87 9.05-7.132a5.918 5.918 0 0 0-.124-1.685z"/></svg>
                Offrez moi un café 😄
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="pricing-footer">
        <a href="{{ route('privacy') }}">Politique de confidentialité</a>
        <p class="s-mono">Soldier Password Manager · AES-256-GCM · Argon2id · RSA-4096</p>
    </footer>

</div>

<style>
    .pricing-hero { position: relative; padding: 90px 16px 56px; text-align: center; max-width: 760px; margin: 0 auto; }
    .pricing-hero h1 { font-size: clamp(2rem, 5vw, 3.3rem); font-weight: 400; line-height: 1.15; margin: 18px 0 16px; }
    .pricing-lead { font-size: 1rem; color: var(--text-secondary); line-height: 1.75; max-width: 540px; margin: 0 auto; }
    .pricing-alert { margin: 0 auto 24px; max-width: 560px; padding: 16px 20px; border-radius: 12px; border: 1px solid rgba(255,90,100,.5); background: rgba(255,90,100,.08); color: var(--danger); box-shadow: 0 0 24px rgba(255,90,100,.15); }
    .pricing-grid { max-width: 920px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .pricing-card { position: relative; display: flex; flex-direction: column; padding: 40px; border-radius: 24px; background: rgba(4,10,16,0.85); border: 1px solid var(--border); backdrop-filter: blur(14px); transition: transform .3s, box-shadow .3s; }
    .pricing-card:hover { transform: translateY(-4px); box-shadow: 0 18px 50px rgba(0,0,0,.5), 0 0 30px rgba(79,195,247,.12); }
    .pricing-card-hot { background: rgba(6,16,26,0.92); box-shadow: 0 0 50px rgba(61,245,192,.08); }
    .pricing-popular { position: absolute; top: 20px; right: 20px; padding: 4px 12px; border-radius: 999px; font-family: var(--font-mono); font-size: .66rem; letter-spacing: .14em; text-transform: uppercase; color: #02130e; background: var(--neon); box-shadow: 0 0 18px rgba(61,245,192,.6); animation: s-breathe 2.4s infinite; }
    .pricing-icon { width: 48px; height: 48px; display: grid; place-items: center; margin-bottom: 20px; border-radius: 12px; background: var(--accent-dim); border: 1px solid rgba(79,195,247,.3); }
    .pricing-icon-hot { background: rgba(61,245,192,.08); border-color: rgba(61,245,192,.4); box-shadow: 0 0 22px rgba(61,245,192,.2); }
    .pricing-plan { font-family: var(--font-mono); font-size: .72rem; letter-spacing: .16em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; }
    .pricing-price { display: flex; align-items: baseline; gap: 4px; margin-bottom: 8px; }
    .pricing-price span { font-size: 3rem; color: var(--text-primary); }
    .pricing-price small { font-size: .9rem; color: var(--text-muted); }
    .pricing-desc { font-size: .875rem; color: var(--text-secondary); line-height: 1.6; }
    .pricing-features { flex: 1; padding-top: 24px; margin-bottom: 32px; border-top: 1px solid var(--border); }
    .pricing-feature { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid rgba(79,195,247,.06); font-size: .875rem; color: var(--text-secondary); }
    .pricing-cta { width: 100%; justify-content: center; text-align: center; text-decoration: none; padding: 14px; }
    .pricing-h2 { font-size: 1.6rem; font-weight: 400; text-align: center; margin-bottom: 32px; }
    .pricing-faq { padding: 18px 0; border-bottom: 1px solid rgba(79,195,247,.08); }
    .pricing-faq:last-child { border-bottom: 0; }
    .pricing-faq button { width: 100%; min-height: 40px; display: flex; align-items: center; justify-content: space-between; gap: 16px; text-align: left; background: none; border: none; cursor: pointer; color: var(--text-primary); font-family: var(--font-display); font-size: .9rem; }
    .pricing-faq.is-open button span { color: var(--accent-ultra); }
    .pricing-faq p { font-size: .875rem; color: var(--text-secondary); line-height: 1.75; }
    .pricing-coffee { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 10px 20px; border-radius: 10px; background: #0070ba; color: #fff; font-size: .84rem; text-decoration: none; box-shadow: 0 0 22px rgba(0,112,186,.4); transition: background .15s, transform .15s; }
    .pricing-coffee:hover { background: #005ea6; transform: translateY(-1px); }
    .pricing-footer { position: relative; padding: 24px 16px; border-top: 1px solid var(--border); text-align: center; }
    .pricing-footer a { color: var(--text-muted); font-size: .75rem; text-decoration: none; }
    .pricing-footer p { color: var(--text-muted); font-size: .7rem; letter-spacing: .08em; margin-top: 8px; }
    @media (max-width: 768px) {
        .pricing-grid { grid-template-columns: 1fr; }
        .pricing-card { padding: 28px 22px; }
    }
</style>
@endsection
