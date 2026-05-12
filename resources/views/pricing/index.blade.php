@extends('layouts.public')
@section('title', 'Tarifs — Soldier Password Manager')

@section('content')
<div style="overflow:hidden;">

    {{-- Hero --}}
    <section style="padding: 100px 24px 60px; text-align:center; max-width:720px; margin:0 auto;">
        <span style="display:inline-flex; align-items:center; background:rgba(33,126,170,0.12); border:1px solid rgba(33,126,170,0.3); border-radius:20px; padding:4px 14px; font-size:0.75rem; font-weight:700; color:var(--accent-bright); letter-spacing:0.04em; margin-bottom:20px;">
            TARIFS SIMPLES ET TRANSPARENTS
        </span>
        <h1 style="font-size:clamp(2.2rem,5vw,3.5rem); font-weight:800; color:var(--text-primary); line-height:1.1; margin-bottom:16px; letter-spacing:-0.02em;">
            Gratuit pour toujours.<br>
            <span style="color:var(--accent-bright);">Famille si vous le souhaitez.</span>
        </h1>
        <p style="font-size:1rem; color:var(--text-secondary); line-height:1.75; max-width:520px; margin:0 auto;">
            Aucune carte de crédit requise pour commencer. Soldier est gratuit et le restera toujours. Le plan Famille est là pour ceux qui veulent aller plus loin.
        </p>
    </section>

    {{-- Plans --}}
    <section style="padding: 0 24px 80px;">
        <div style="max-width:900px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:24px;">

            {{-- Plan Gratuit --}}
            <div style="background:rgba(22,37,52,0.5); border:1px solid rgba(33,126,170,0.2); border-radius:24px; padding:40px; backdrop-filter:blur(16px); display:flex; flex-direction:column;">
                <div style="margin-bottom:28px;">
                    <div style="width:48px; height:48px; background:rgba(33,126,170,0.1); border:1px solid rgba(33,126,170,0.25); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); letter-spacing:0.08em; text-transform:uppercase; margin-bottom:8px;">Gratuit</div>
                    <div style="display:flex; align-items:baseline; gap:4px; margin-bottom:8px;">
                        <span style="font-size:3rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.02em;">0$</span>
                        <span style="font-size:0.9rem; color:var(--text-muted);">/mois</span>
                    </div>
                    <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.6; margin:0;">Pour les individus qui veulent protéger leurs mots de passe sans compromis.</p>
                </div>

                <div style="border-top:1px solid rgba(33,126,170,0.15); padding-top:24px; margin-bottom:32px; flex:1;">
                    @foreach([
                        'Coffres illimités',
                        'Services illimités',
                        'Extension Chrome',
                        'MFA Triple (TOTP, Passkeys, Email)',
                        'Partage sécurisé RSA-4096',
                        'Générateur de mots de passe',
                        'Zero-Knowledge complet',
                        'Open Source',
                    ] as $feature)
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.04);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2.5" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                        <span style="font-size:0.875rem; color:var(--text-secondary);">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                @auth
                    @if($abonnement)
                        <a href="{{ route('dashboard') }}" style="display:block; text-align:center; background:rgba(33,126,170,0.1); color:var(--accent-bright); border:1px solid rgba(33,126,170,0.3); border-radius:12px; padding:14px; font-size:0.9rem; font-weight:700; text-decoration:none;">
                            Votre plan actuel →
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" style="display:block; text-align:center; background:rgba(33,126,170,0.1); color:var(--accent-bright); border:1px solid rgba(33,126,170,0.3); border-radius:12px; padding:14px; font-size:0.9rem; font-weight:700; text-decoration:none;">
                            Votre plan actuel →
                        </a>
                    @endif
                @else
                    <a href="{{ route('inscription') }}" style="display:block; text-align:center; background:rgba(33,126,170,0.1); color:var(--accent-bright); border:1px solid rgba(33,126,170,0.3); border-radius:12px; padding:14px; font-size:0.9rem; font-weight:700; text-decoration:none;">
                        Commencer gratuitement →
                    </a>
                @endauth
            </div>

            <div style="background:rgba(22,37,52,0.8); border:1px solid rgba(45,159,212,0.5); border-radius:24px; padding:40px; backdrop-filter:blur(16px); display:flex; flex-direction:column; position:relative; overflow:hidden;">

                <div style="position:absolute; top:20px; right:20px; background:linear-gradient(135deg,#217eaa,#2d9fd4); border-radius:20px; padding:4px 12px; font-size:0.7rem; font-weight:700; color:#fff; letter-spacing:0.04em;">
                    POPULAIRE
                </div>

                {{-- Accent bar --}}
                <div style="position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#217eaa,#2d9fd4,#217eaa);"></div>

                <div style="margin-bottom:28px;">
                    <div style="width:48px; height:48px; background:rgba(45,159,212,0.15); border:1px solid rgba(45,159,212,0.35); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div style="font-size:0.8rem; font-weight:700; color:var(--accent-bright); letter-spacing:0.08em; text-transform:uppercase; margin-bottom:8px;">Famille</div>
                    <div style="display:flex; align-items:baseline; gap:4px; margin-bottom:8px;">
                        <span style="font-size:3rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.02em;">3,99$</span>
                        <span style="font-size:0.9rem; color:var(--text-muted);">/mois</span>
                    </div>
                    <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.6; margin:0;">Pour les familles qui veulent partager la sécurité jusqu'à 6 membres.</p>
                </div>

                <div style="border-top:1px solid rgba(45,159,212,0.2); padding-top:24px; margin-bottom:32px; flex:1;">
                    <div style="font-size:0.78rem; font-weight:700; color:var(--accent-bright); margin-bottom:12px; letter-spacing:0.04em;">TOUT LE PLAN GRATUIT +</div>
                    @foreach([
                        'Jusqu\'à 6 membres',
                        'Tableau de bord familial',
                        'Coffres familiaux partagés',
                        'Gestion centralisée des accès',
                        'Support prioritaire par email',
                        'Facturation mensuelle simple',
                    ] as $feature)
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.04);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2d9fd4" stroke-width="2.5" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                        <span style="font-size:0.875rem; color:var(--text-secondary);">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                @auth
                    @if($abonnement)
                        <a href="{{ route('pricing.portail') }}" style="display:block; text-align:center; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:12px; padding:14px; font-size:0.9rem; font-weight:700; text-decoration:none;">
                            Gérer mon abonnement →
                        </a>
                    @else
                        <form method="POST" action="{{ route('pricing.checkout') }}">
                            @csrf
                            <button type="submit" style="width:100%; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:12px; padding:14px; font-size:0.9rem; font-weight:700; cursor:pointer;">
                                Commencer — 3,99$/mois →
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('inscription') }}" style="display:block; text-align:center; background:linear-gradient(135deg,#217eaa,#2d9fd4); color:#fff; border:none; border-radius:12px; padding:14px; font-size:0.9rem; font-weight:700; text-decoration:none;">
                        Créer un compte →
                    </a>
                @endauth
            </div>

        </div>
    </section>

    <section style="padding: 0 24px 80px;">
        <div style="max-width:680px; margin:0 auto;">
            <h2 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); text-align:center; margin-bottom:40px; letter-spacing:-0.02em;">Questions fréquentes</h2>

            @foreach([
                ['Q' => 'Le plan gratuit est-il vraiment gratuit ?', 'R' => 'Oui, complètement gratuit et sans limite. Aucune carte de crédit, aucune fonctionnalité cachée derrière un paywall. Soldier est open source et restera gratuit pour toujours.'],
                ['Q' => 'Mes données sont-elles en sécurité avec Soldier ?', 'R' => 'Absolument. Soldier utilise un chiffrement AES-256-GCM et une architecture Zero-Knowledge. Même nous ne pouvons pas lire vos mots de passe — c\'est mathématiquement impossible.'],
                ['Q' => 'Comment fonctionne le plan Famille ?', 'R' => 'Vous invitez jusqu\'à 5 autres membres. Chacun a son propre compte sécurisé et vous pouvez partager des accès entre membres de façon contrôlée.'],
                ['Q' => 'Puis-je annuler mon abonnement Famille ?', 'R' => 'Oui, à tout moment depuis votre espace de gestion. L\'annulation prend effet à la fin de la période de facturation en cours. Vos données restent accessibles.'],
                ['Q' => 'Le bouton "Offrez-moi un café" est-il obligatoire ?', 'R' => 'Non, c\'est complètement optionnel. Si vous aimez Soldier et voulez encourager le projet, vous pouvez contribuer. Mais rien n\'est requis.'],
            ] as $faq)
            <div x-data="{ open: false }" style="border-bottom:1px solid rgba(33,126,170,0.1); padding:20px 0;">
                <button @click="open = !open" style="width:100%; text-align:left; background:none; border:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:16px;">
                    <span style="font-size:0.9375rem; font-weight:700; color:var(--text-primary);">{{ $faq['Q'] }}</span>
                    <svg :style="open ? 'transform:rotate(180deg)' : ''" style="flex-shrink:0; transition:transform 0.2s;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open" x-transition style="padding-top:12px;">
                    <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.75; margin:0;">{{ $faq['R'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <section style="padding: 0 24px 80px; text-align:center;">
        <div style="max-width:480px; margin:0 auto; background:rgba(22,37,52,0.5); border:1px solid rgba(33,126,170,0.15); border-radius:20px; padding:32px 40px; backdrop-filter:blur(16px); display:flex; flex-direction:column; align-items:center; gap:20px;">
            <div style="text-align:center;">
                <div style="font-weight:700; font-size:1rem; color:var(--text-primary); margin-bottom:6px;">Soldier est 100% gratuit</div>
                <div style="font-size:0.82rem; color:var(--text-muted);">Si vous aimez le projet, vous pouvez m'encourager</div>
            </div>
            <a href="https://paypal.me/BriceSteve" target="_blank"
               style="display:inline-flex; align-items:center; gap:8px; background:#0070ba; color:#fff; border:none; border-radius:10px; padding:10px 20px; font-size:0.8375rem; font-weight:700; font-family:'Audiowide',sans-serif; text-decoration:none; transition:background 0.15s;"
               onmouseover="this.style.background='#005ea6'"
               onmouseout="this.style.background='#0070ba'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.643-8.993 6.643H9.38l-1.165 7.388h3.114l.777-4.927h2.19c5.146 0 8.132-2.87 9.05-7.132a5.918 5.918 0 0 0-.124-1.685z"/></svg>
                Offrez moi un café 😄
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer style="padding:24px 40px; border-top:1px solid rgba(33,126,170,0.1); text-align:center;">
        <a href="{{ route('privacy') }}" style="color:var(--text-muted); font-size:0.75rem; text-decoration:none;">Politique de confidentialité</a>
        <p style="color:var(--text-muted); font-size:0.75rem; letter-spacing:0.04em; margin-top:8px;">
            Soldier Password Manager · AES-256-GCM · Argon2id · RSA-4096 · Zero-Knowledge
        </p>
    </footer>

</div>

<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns:1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
