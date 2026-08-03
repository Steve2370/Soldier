<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13.x">
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/Zero--Knowledge-Encryption-0A2540" alt="Zero-Knowledge">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License MIT">
</p>

<h1 align="center"> Soldier</h1>

<p align="center">
  <strong>Un gestionnaire de mots de passe auto-hébergé, zero-knowledge, open source.</strong><br>
  <a href="https://soldierkey.com">soldierkey.com</a>
</p>

---

## À propos

**Soldier** est un gestionnaire de mots de passe conçu autour d'un principe simple : **votre mot de passe maître ne quitte jamais votre appareil.** Le serveur ne stocke et ne manipule que des données déjà chiffrées côté client — un déchiffrement côté serveur est architecturalement impossible.

Le projet est développé avec Laravel en suivant les principes SOLID, et distribué gratuitement avec un soutien volontaire (don) plutôt qu'un modèle payant, pour rester accessible au plus grand nombre.

## - Fonctionnalités

- **Chiffrement zero-knowledge de bout en bout**
    - Dérivation de clé locale via **Argon2id**
    - Chiffrement des coffres via **AES-256-GCM**
    - Partage sécurisé de coffres via **RSA-4096**
- **Partage de coffres** avec gestion des permissions (lecture seule, édition)
- **Authentification forte**
    - Passkeys via **WebAuthn**
    - MFA par **TOTP**
    - OAuth (Google, GitHub)
- **Extension Chrome** (Manifest V3) avec Argon2id en WebAssembly pour un remplissage automatique sécurisé
- **Auto-hébergeable** : vous gardez le contrôle total de vos données

## - Architecture

Le projet suit une architecture en couches strictes, pensée pour la testabilité et l'inversion de dépendance :

```
Controller → Service → Repository → Model / Base de données
```

- **Repository** : gère l'accès aux données (comment).
- **Service** : gère la logique métier — vérifications de permissions, orchestration du chiffrement, dispatch d'événements (pourquoi/quoi).
- **Interface** : définit le contrat ; les Services dépendent de l'interface via injection de dépendance, jamais de l'implémentation concrète, ce qui garantit le respect du principe d'inversion de dépendance (DIP).
- Un **ServiceProvider** central gère le binding des interfaces vers leurs implémentations.

## - Chaîne cryptographique

```
Mot de passe maître (client uniquement)
        │
        ▼
   Argon2id  ──────────►  Clé de chiffrement locale (KEK)
        │
        ▼
  AES-256-GCM  ─────────►  Chiffrement du coffre
        │
        ▼
   RSA-4096  ────────────►  Partage sécurisé entre utilisateurs
```

Le serveur ne stocke que :
- des blobs chiffrés (coffres, entrées),
- des clés publiques RSA.

Le mot de passe maître et les clés privées ne sont jamais transmis au serveur.

## - Stack technique

| Domaine | Technologies |
|---|---|
| **Backend** | Laravel 13.x, PHP 8.5, SQLite, Sanctum |
| **Frontend** | Alpine.js, TypeScript, Vite, Tailwind CSS, Blade |
| **Cryptographie** | AES-256-GCM, Argon2id (`sodium_crypto_pwhash`), RSA-4096, WebCrypto API, argon2-bundled.js (WASM) |
| **Authentification** | WebAuthn, TOTP (`pragmarx/google2fa`), OAuth (Google, GitHub) |
| **Infrastructure** | DigitalOcean, Cloudflare DNS, Nginx, PHP-FPM, Let's Encrypt |
| **Email** | Resend |
| **Extension navigateur** | Chrome (Manifest V3) |

## - Installation

```bash
git clone https://github.com/Steve2370/soldier.git
cd soldier

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate

npm run build
php artisan serve
```

### Déploiement en production

```bash
# En local, après chaque modification :
git push origin main

# Sur le serveur :
git pull
bash /var/www/deploy.sh
```

>  Les fichiers vidéo (démonstrations, assets marketing) sont stockés hors du dépôt Git dans `/var/www/videos/`, avec un lien symbolique vers `public/videos/`, afin d'éviter les problèmes de Git LFS.

## - Extension Chrome

L'extension permet le remplissage automatique des identifiants directement dans le navigateur, tout en conservant les garanties zero-knowledge grâce à une implémentation WASM d'Argon2id exécutée localement.

Disponible sur le Chrome Web Store.

## -️ Roadmap

- [ ] Durcissement de la sécurité en production (HTTPS/HSTS, CSP, rate limiting sur les endpoints d'authentification, `APP_DEBUG=false`, `security.txt`, politique de rétention des données)
- [ ] Application mobile ou remplissage automatique au niveau OS
- [ ] Validation finale de l'extension Chrome sur le Web Store

## - Soutenir le projet

Soldier est **gratuit et open source**. Si l'outil vous est utile, vous pouvez soutenir son développement via le bouton de don PayPal disponible sur [soldierkey.com](https://soldierkey.com) et sur le tableau de bord de l'application.

## - Contribuer

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## - Licence

Ce projet est distribué sous licence [MIT](https://opensource.org/licenses/MIT).

---

<p align="center"><em>Votre mot de passe maître vous appartient. À vous seul.</em></p>
