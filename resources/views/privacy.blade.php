@extends('layouts.app')

@section('title', 'Politique de confidentialité Soldier')

@section('content')
<style>
    .privacy-doc {
        --cyan: var(--accent-bright);
        --teal: var(--neon);
        --gold: var(--warning);
        --white: var(--text-primary);
        --dim: var(--text-secondary);
        --p-card: rgba(4, 10, 16, 0.88);
        --p-border: rgba(79, 195, 247, 0.16);
        position: relative;
        padding: 0 16px;
        color: var(--dim);
        line-height: 1.8;
    }
    .privacy-guest-nav { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; border-bottom: 1px solid var(--p-border); background: rgba(2,5,10,.85); backdrop-filter: blur(12px); }
    .privacy-guest-nav nav { display: flex; flex-wrap: wrap; gap: 18px; }
    .privacy-guest-nav nav a { color: var(--dim); text-decoration: none; font-size: 13px; transition: color .2s; }
    .privacy-guest-nav nav a:hover { color: var(--cyan); }

    .privacy-doc .hero { position: relative; z-index: 1; text-align: center; padding: 3rem 1rem 2.5rem; }
    .privacy-doc .hero-tag { display: inline-block; font-family: var(--font-mono); font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: var(--cyan); border: 1px solid rgba(79,195,247,.35); padding: 4px 14px; border-radius: 999px; margin-bottom: 1.4rem; box-shadow: 0 0 16px rgba(79,195,247,.15); }
    .privacy-doc .hero h1 { font-size: clamp(1.8rem, 5vw, 3.1rem); font-weight: 400; color: var(--white); letter-spacing: .03em; line-height: 1.15; margin-bottom: 1rem; }
    .privacy-doc .hero h1 span { color: var(--accent-ultra); text-shadow: 0 0 10px rgba(79,195,247,.75), 0 0 32px rgba(79,195,247,.3); }
    .privacy-doc .hero-meta { font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); letter-spacing: .05em; }

    .privacy-doc .privacy-container { position: relative; z-index: 1; max-width: 860px; margin: 0 auto; padding: 0 0 5rem; }

    .privacy-doc .toc { position: relative; background: var(--p-card); border: 1px solid var(--p-border); border-left: 3px solid var(--cyan); padding: 1.5rem 2rem; margin-bottom: 3rem; border-radius: 14px; }
    .privacy-doc .toc-title { font-family: var(--font-mono); font-size: 11px; letter-spacing: .18em; text-transform: uppercase; color: var(--cyan); margin-bottom: 1rem; }
    .privacy-doc .toc ol { list-style: decimal; padding-left: 1.2rem; display: grid; grid-template-columns: 1fr 1fr; gap: .3rem 2rem; }
    .privacy-doc .toc li::marker { color: var(--cyan); font-family: var(--font-mono); }
    .privacy-doc .toc li a { color: var(--dim); text-decoration: none; font-size: 14px; transition: color .2s, text-shadow .2s; }
    .privacy-doc .toc li a:hover { color: var(--accent-ultra); text-shadow: 0 0 10px rgba(79,195,247,.6); }

    .privacy-doc .section { margin-bottom: 3rem; scroll-margin-top: 90px; animation: s-boot .5s backwards; }
    .privacy-doc .section-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.2rem; padding-bottom: .8rem; border-bottom: 1px solid var(--p-border); }
    .privacy-doc .section-num { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 10px; background: rgba(79,195,247,.1); border: 1px solid rgba(79,195,247,.35); box-shadow: 0 0 14px rgba(79,195,247,.18); }
    .privacy-doc .section-num svg { width: 18px; height: 18px; stroke: var(--cyan); stroke-width: 1.5px; fill: none; stroke-linecap: round; stroke-linejoin: round; }
    .privacy-doc .section:target .section-num { border-color: var(--neon); box-shadow: 0 0 22px rgba(61,245,192,.45); }
    .privacy-doc .section h2 { font-size: 1.12rem; font-weight: 400; color: var(--white); letter-spacing: .04em; }
    .privacy-doc .section p { margin-bottom: 1rem; color: var(--dim); }
    .privacy-doc .section ul, .privacy-doc .section ol { padding-left: 1.5rem; margin-bottom: 1rem; }
    .privacy-doc .section li { margin-bottom: .4rem; color: var(--dim); }

    .privacy-doc .highlight-icon { flex-shrink: 0; margin-right: .5rem; display: inline-flex; vertical-align: middle; }
    .privacy-doc .highlight-icon svg { width: 16px; height: 16px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; stroke: var(--cyan); }
    .privacy-doc .highlight.green .highlight-icon svg { stroke: var(--teal); }
    .privacy-doc .highlight.gold .highlight-icon svg { stroke: var(--gold); }
    .privacy-doc .highlight { background: rgba(79,195,247,.05); border: 1px solid rgba(79,195,247,.2); border-left: 3px solid var(--cyan); padding: 1rem 1.4rem; border-radius: 12px; margin: 1.2rem 0; }
    .privacy-doc .highlight.green { background: rgba(61,245,192,.05); border-color: rgba(61,245,192,.22); border-left-color: var(--teal); }
    .privacy-doc .highlight.gold { background: rgba(242,181,68,.05); border-color: rgba(242,181,68,.22); border-left-color: var(--gold); }
    .privacy-doc .highlight p { margin: 0; }

    .privacy-doc .data-table { width: 100%; border-collapse: collapse; margin: 1.2rem 0; font-size: 14px; display: block; overflow-x: auto; }
    .privacy-doc .data-table th { font-family: var(--font-mono); font-weight: 400; font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--cyan); background: rgba(79,195,247,.07); padding: .6rem 1rem; text-align: left; border-bottom: 1px solid var(--p-border); }
    .privacy-doc .data-table td { padding: .7rem 1rem; border-bottom: 1px solid rgba(79,195,247,.07); color: var(--dim); vertical-align: top; }
    .privacy-doc .data-table tr:last-child td { border-bottom: none; }

    .privacy-doc .badge { display: inline-block; font-family: var(--font-mono); font-size: 10px; letter-spacing: .08em; padding: 2px 8px; border-radius: 999px; border: 1px solid; text-transform: none; }
    .privacy-doc .badge.never { color: var(--teal); border-color: rgba(61,245,192,.4); background: rgba(61,245,192,.08); }
    .privacy-doc .badge.local { color: var(--cyan); border-color: rgba(79,195,247,.4); background: rgba(79,195,247,.08); }
    .privacy-doc .badge.server { color: var(--gold); border-color: rgba(242,181,68,.4); background: rgba(242,181,68,.08); }

    .privacy-doc .contact-card { background: var(--p-card); border: 1px solid var(--p-border); padding: 1.5rem 2rem; border-radius: 14px; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; }
    .privacy-doc .contact-icon { flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: rgba(79,195,247,.1); border: 1px solid rgba(79,195,247,.35); border-radius: 12px; }
    .privacy-doc .contact-icon svg { width: 24px; height: 24px; stroke: var(--cyan); }
    .privacy-doc .contact-card a { color: var(--cyan); text-decoration: none; }
    .privacy-doc .contact-card a:hover { text-decoration: underline; }

    .privacy-doc .privacy-footer { position: relative; z-index: 1; border-top: 1px solid var(--p-border); padding: 2rem 1rem; text-align: center; font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); letter-spacing: .05em; }

    @media (max-width: 640px) {
        .privacy-doc .toc { padding: 1.2rem 1.2rem; }
        .privacy-doc .toc ol { grid-template-columns: 1fr; }
        .privacy-doc .contact-card { padding: 1.2rem; }
    }
</style>

@guest
    <div class="privacy-guest-nav">
        <a href="{{ route('welcome') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="{{ asset('assets/Soldier-Logo.png') }}" alt="Logo" style="width: 36px; height: 36px; object-fit: contain;">
            <span style="color: var(--text-primary); letter-spacing: .16em;">SOLDIER</span>
        </a>
        <nav>
            <a href="{{ route('welcome') }}">Accueil</a>
            <a href="{{ route('connexion') }}">Connexion</a>
            <a href="{{ route('inscription') }}">S'inscrire</a>
        </nav>
    </div>
@endguest

<div class="privacy-doc">
<div class="hero">
    <div class="hero-tag">Légal &amp; Confidentialité</div>
    <h1>Politique de <span>confidentialité</span></h1>
    <p class="hero-meta">Dernière mise à jour : {{ date('d F Y') }} &nbsp;·&nbsp; soldierkey.com</p>
</div>

<div class="privacy-container">

    <div class="highlight green">
        <p><span class="highlight-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></span><strong style="color:var(--teal)">Résumé en une phrase :</strong> Soldier ne peut pas lire vos mots de passe. Tout est chiffré sur votre appareil avant d'atteindre nos serveurs. Même nous, nous ne pouvons pas y accéder.</p>
    </div>

    <div class="toc">
        <div class="toc-title">Sommaire</div>
        <ol>
            <li><a href="#s1">Qui sommes-nous</a></li>
            <li><a href="#s2">Architecture de chiffrement</a></li>
            <li><a href="#s3">Données collectées</a></li>
            <li><a href="#s4">Extension Chrome</a></li>
            <li><a href="#s5">Cookies et stockage local</a></li>
            <li><a href="#s6">Partage des données</a></li>
            <li><a href="#s7">Sécurité</a></li>
            <li><a href="#s8">Vos droits</a></li>
            <li><a href="#s9">Rétention des données</a></li>
            <li><a href="#s10">Contact</a></li>
        </ol>
    </div>

    <div class="section" id="s1">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>
            <h2>Qui sommes-nous</h2>
        </div>
        <p>Soldier est un gestionnaire de mots de passe open source, auto-hébergé, développé et exploité à titre personnel. Le service est accessible à l'adresse <strong style="color:var(--white)">soldierkey.com</strong>.</p>
        <p>Ce projet est un projet indépendant, sans investisseurs, sans publicité, et sans modèle économique basé sur la revente de données.</p>
    </div>

    <div class="section" id="s2">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg></div>
            <h2>Architecture de chiffrement</h2>
        </div>
        <p>Soldier est conçu selon un principe fondamental : <strong style="color:var(--white)">le serveur ne peut jamais lire vos données sensibles</strong>.</p>
        <div class="highlight">
            <p><span class="highlight-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>Vos mots de passe et données de coffre sont chiffrés avec <strong style="color:var(--cyan)">AES-256-GCM</strong> avant leur stockage. La clé de chiffrement est protégée par <strong style="color:var(--cyan)">Argon2id</strong>. Dans la version web actuelle, le serveur participe au déverrouillage ; le zero-knowledge complet n'est pas encore garanti.</p>
        </div>
        <p>En conséquence :</p>
        <ul>
            <li>Nous ne pouvons pas lire vos mots de passe, même en cas d'accès direct à la base de données.</li>
            <li>Si vous oubliez votre mot de passe maître, vos données ne sont pas récupérables — il n'existe aucune "porte dérobée".</li>
            <li>Le partage de coffre entre utilisateurs utilise du chiffrement asymétrique <strong style="color:var(--white)">RSA-4096</strong>.</li>
        </ul>
    </div>

    <div class="section" id="s3">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg></div>
            <h2>Données collectées</h2>
        </div>
        <p>Voici l'exhaustivité des données traitées par Soldier :</p>

        <table class="data-table">
            <thead>
            <tr>
                <th>Donnée</th>
                <th>Forme stockée</th>
                <th>Où</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Adresse e-mail</td>
                <td>En clair (identifiant de compte)</td>
                <td><span class="badge server">Serveur</span></td>
            </tr>
            <tr>
                <td>Mot de passe maître</td>
                <td><strong style="color:var(--teal)">Jamais stocké</strong> — sert uniquement à dériver la clé de chiffrement</td>
                <td><span class="badge never">Jamais</span></td>
            </tr>
            <tr>
                <td>Mots de passe du coffre</td>
                <td>Chiffrés AES-256 (ciphertext uniquement)</td>
                <td><span class="badge server">Serveur</span></td>
            </tr>
            <tr>
                <td>Clé de chiffrement (KEK)</td>
                <td><strong style="color:var(--teal)">Jamais transmise</strong> — dérivée localement uniquement</td>
                <td><span class="badge never">Jamais</span></td>
            </tr>
            <tr>
                <td>Token de session (Sanctum)</td>
                <td>Haché en base de données</td>
                <td><span class="badge server">Serveur</span></td>
            </tr>
            <tr>
                <td>Clé publique RSA</td>
                <td>En clair (par conception)</td>
                <td><span class="badge server">Serveur</span></td>
            </tr>
            <tr>
                <td>Clé privée RSA</td>
                <td>Chiffrée avec votre KEK</td>
                <td><span class="badge server">Serveur</span></td>
            </tr>
            <tr>
                <td>Logs d'accès</td>
                <td>IP, date/heure (durée limitée)</td>
                <td><span class="badge server">Serveur</span></td>
            </tr>
            <tr>
                <td>Données de navigation</td>
                <td><strong style="color:var(--teal)">Non collectées</strong></td>
                <td><span class="badge never">Jamais</span></td>
            </tr>
            </tbody>
        </table>

        <div class="highlight gold">
            <p><span class="highlight-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></span>Soldier ne collecte <strong>aucune donnée analytique</strong>, n'utilise <strong>aucun tracker</strong> (Google Analytics, Hotjar, etc.) et ne revend <strong>aucune donnée</strong> à des tiers.</p>
        </div>
    </div>

    <div class="section" id="s4">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></div>
            <h2>Extension Chrome</h2>
        </div>
        <p>L'extension Chrome de Soldier utilise les mêmes formats chiffrés. La garantie zero-knowledge complète dépendra du déplacement de tout le déchiffrement côté client.</p>
        <ul>
            <li><strong style="color:var(--white)">Permission <code>storage</code> :</strong> utilisée uniquement pour persister votre token de session chiffré et l'URL de votre instance. Aucun mot de passe n'est stocké dans le stockage du navigateur.</li>
            <li><strong style="color:var(--white)">Permission <code>activeTab</code> :</strong> utilisée pour détecter le domaine de la page active et suggérer les identifiants correspondants. Activée uniquement sur action explicite de l'utilisateur.</li>
            <li><strong style="color:var(--white)">Permission <code>scripting</code> :</strong> utilisée pour remplir automatiquement les champs identifiant/mot de passe sur la page active, uniquement à la demande de l'utilisateur.</li>
            <li><strong style="color:var(--white)">Accès hôte :</strong> limité à <code>soldierkey.com</code> pour communiquer avec l'API. Aucun autre domaine n'est accédé.</li>
        </ul>
        <div class="highlight green">
            <p><span class="highlight-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>L'extension ne lit pas le contenu des pages, ne surveille pas votre navigation, et ne transmet aucune donnée à des services tiers.</p>
        </div>
    </div>

    <div class="section" id="s5">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/></svg></div>
            <h2>Cookies et stockage local</h2>
        </div>
        <p>Soldier utilise :</p>
        <ul>
            <li><strong style="color:var(--white)">Cookie de session Laravel</strong> — nécessaire au fonctionnement de l'authentification. Expire à la fermeture de session.</li>
            <li><strong style="color:var(--white)">Token CSRF</strong> — protection contre les attaques de type cross-site request forgery.</li>
        </ul>
        <p>Soldier n'utilise <strong>aucun cookie publicitaire</strong> ni <strong>aucun cookie tiers</strong>.</p>
    </div>

    <div class="section" id="s6">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></div>
            <h2>Partage des données</h2>
        </div>
        <p>Soldier <strong style="color:var(--white)">ne vend pas, ne loue pas et ne partage pas</strong> vos données avec des tiers, à l'exception des cas suivants :</p>
        <ul>
            <li><strong style="color:var(--white)">Hébergement :</strong> le serveur est hébergé sur Digital Ocean (Toronto). Les données chiffrées y sont stockées. Digital Ocean ne peut pas lire leur contenu.</li>
            <li><strong style="color:var(--white)">Cloudflare :</strong> utilisé pour le DNS et la protection DDoS. Cloudflare peut voir les métadonnées de connexion (IP, timestamp) mais pas le contenu des requêtes.</li>
            <li><strong style="color:var(--white)">Obligation légale :</strong> en cas d'injonction légale valide, les seules données communicables seraient l'adresse e-mail et les logs d'accès. Les données du coffre sont inaccessibles même à nous.</li>
        </ul>
    </div>

    <div class="section" id="s7">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
            <h2>Sécurité</h2>
        </div>
        <p>Les mesures de sécurité en place incluent :</p>
        <ul>
            <li>Chiffrement AES-256 de toutes les données sensibles côté client</li>
            <li>Dérivation de clé Argon2id (résistant aux attaques GPU/ASIC)</li>
            <li>Transport HTTPS avec TLS 1.3 (certificat Let's Encrypt)</li>
            <li>Double authentification TOTP (Google Authenticator compatible)</li>
            <li>Support des clés de sécurité (Passkeys / WebAuthn)</li>
            <li>Tokens Sanctum hachés en base de données</li>
            <li>Protection CSRF sur toutes les routes</li>
        </ul>
        <div class="highlight gold">
            <p><span class="highlight-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span>Soldier est un projet open source. Le code source peut être audité par toute personne souhaitant vérifier ces affirmations.</p>
        </div>
    </div>

    <div class="section" id="s8">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><line x1="12" y1="3" x2="12" y2="21"/><path d="M3 6l9-3 9 3"/><path d="M3 18l9 3 9-3"/><path d="M3 6l4 6-4 6"/><path d="M21 6l-4 6 4 6"/></svg></div>
            <h2>Vos droits</h2>
        </div>
        <p>Conformément au RGPD (si applicable) et aux lois canadiennes sur la protection des données (LPRPDE), vous disposez des droits suivants :</p>
        <ul>
            <li><strong style="color:var(--white)">Accès :</strong> vous pouvez consulter toutes vos données via votre tableau de bord.</li>
            <li><strong style="color:var(--white)">Rectification :</strong> vous pouvez modifier vos informations dans les paramètres du compte.</li>
            <li><strong style="color:var(--white)">Suppression :</strong> vous pouvez supprimer votre compte et toutes vos données à tout moment depuis les paramètres.</li>
            <li><strong style="color:var(--white)">Portabilité :</strong> vos données peuvent être exportées depuis le tableau de bord.</li>
        </ul>
    </div>

    <div class="section" id="s9">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <h2>Rétention des données</h2>
        </div>
        <p>Vos données sont conservées tant que votre compte est actif. À la suppression du compte :</p>
        <ul>
            <li>Toutes les entrées du coffre sont supprimées immédiatement.</li>
            <li>Votre adresse e-mail est supprimée dans un délai de 30 jours.</li>
            <li>Les logs d'accès sont conservés 90 jours maximum puis supprimés automatiquement.</li>
        </ul>
    </div>

    <div class="section" id="s10">
        <div class="section-header">
            <div class="section-num"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
            <h2>Contact</h2>
        </div>
        <p>Pour toute question relative à cette politique de confidentialité ou à vos données :</p>
        <div class="contact-card">
            <div class="contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
                </svg>
            </div>
            <div>
                <div style="color:var(--white); font-family: 'Audiowide', sans-serif; margin-bottom:.3rem;">Soldier — Responsable des données</div>
                <div>E-mail : <a href="mailto:privacy@soldierkey.com">privacy@soldierkey.com</a></div>
                <div style="margin-top:.3rem; font-size:14px;">Site : <a href="https://soldierkey.com">soldierkey.com</a></div>
            </div>
        </div>
    </div>

</div>

<footer class="privacy-footer">
    © {{ date('Y') }} Soldier Password Manager &nbsp;·&nbsp; Tous droits réservés &nbsp;·&nbsp;
    <a href="/" style="color: var(--accent-bright); text-decoration: none;">soldierkey.com</a>
</footer>

</div>
@endsection
