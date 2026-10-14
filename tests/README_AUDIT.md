# Exécuter la campagne de tests Soldier

Cette suite protège les comportements corrigés à la suite de l’audit du 14 octobre 2026. Les assertions initiales sont conservées, avec des contrôles renforcés et des parcours navigateur de bout en bout. Ne pas ignorer les tests en échec ni assouplir leurs attentes pour obtenir un résultat vert.

État après corrections : [CORRECTIONS_2026-10-14.md](CORRECTIONS_2026-10-14.md). Audit historique avant corrections : [AUDIT_2026-10-14.md](AUDIT_2026-10-14.md).

## Précautions

- Exécuter depuis le dossier applicatif contenant `artisan`, `vendor/` et `package.json`, **sur un poste local ou une CI isolée**, jamais directement sur une base de production.
- Les nouveaux tests fonctionnels refusent toute base autre que SQLite `:memory:` et exigent `APP_ENV=testing`.
- Toutes les suites PHP utilisent désormais les migrations livrées, sans ajout artificiel de colonnes par les fixtures. `LegacySchemaAuditTestCase` conserve uniquement un nom historique et une fabrique d’utilisateurs.
- Les messages, notifications et événements externes sont simulés. Ne pas remplacer les clés de test par des secrets réels pour lancer cette suite.
- Prérequis serveur : dépendances Composer existantes et extensions requises par le projet, dont SQLite, sodium, OpenSSL et DOM. Prérequis interface : dépendances npm existantes, Playwright et un navigateur compatible.

## PHP

Les variables de cache évitent de charger une ancienne configuration Laravel mise en cache. Le répertoire temporaire est créé spécifiquement pour cette exécution ; son chemin n’est jamais celui d’une base existante.

```bash
cd /Users/leonbeltran/PhpstormProjects/Soldier/Soldier
audit_tmp=$(mktemp -d /private/tmp/soldier-tests.XXXXXX)

APP_ENV=testing \
DB_CONNECTION=sqlite \
DB_DATABASE=:memory: \
DB_URL= \
APP_CONFIG_CACHE="$audit_tmp/config.php" \
APP_ROUTES_CACHE="$audit_tmp/routes.php" \
vendor/bin/phpunit --log-junit "$audit_tmp/phpunit.xml"
```

Sur Linux, remplacer `/private/tmp` par `/tmp`. Les variables ci-dessus sont affectées uniquement au processus de test. Le fichier `phpunit.xml` configure aussi les sessions et caches en mémoire et le transport de courriel de test.

Pour isoler une cause, conserver les mêmes variables et ajouter par exemple `--filter SharingAuthorizationTest`, `--filter SchemaContractTest` ou `--filter VaultWorkflowTest`.

## Navigateur

Les tests de composants démarrent Vite sur l’interface locale. Les tests de bout en bout démarrent le véritable serveur Laravel avec une base SQLite temporaire dédiée, reconstruite par les migrations. Les deux utilisent Chrome sans fenêtre, un profil neuf sans session personnelle et bloquent les requêtes vers d’autres origines. Ils ne se connectent pas à `soldierkey.com`. Les courriels sont capturés en mémoire ; les secrets et comptes sont fictifs.

Le parcours complet vérifie l’inscription, le lien signé de vérification d’email, les six types d’éléments, la modification, la restauration, les favoris, la navigation mobile et WebAuthn avec un authentificateur virtuel Chrome. Le contrôle CSRF réel reste actif. Les répertoires temporaires `soldier-e2e-*` sont conservés pour diagnostic ; ils ne contiennent que les données fictives du test. Utiliser une machine isolée où aucun autre processus ne remplace la configuration des tests pendant leur exécution.

Avec Playwright disponible dans l’environnement et son navigateur installé :

```bash
npm run build
node --test tests/Browser/frontend.test.mjs tests/Browser/e2e.test.mjs
```

Commande effectivement utilisée sur ce poste, en réutilisant le moteur existant et Chrome installé, sans ajouter de dépendances au projet :

```bash
SOLDIER_PLAYWRIGHT_MODULE=/Users/leonbeltran/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/index.mjs \
SOLDIER_BROWSER_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' \
node --test tests/Browser/frontend.test.mjs tests/Browser/e2e.test.mjs
```

Sur une autre machine, fournir un module Playwright et un navigateur compatibles. Playwright n’a pas été ajouté au `package.json` pendant cet audit : cette commande doit être intégrée explicitement à la CI avec ses dépendances. Une erreur de lancement de navigateur ou de port n’est pas un bug applicatif ; le résultat final du rapport provient d’une exécution où le navigateur a réellement démarré.

## Contrôles complémentaires

```bash
./node_modules/.bin/tsc --noEmit
npm run build
composer validate --no-check-publish
composer audit
npm audit
vendor/bin/pint --test
```

Les audits de dépendances interrogent les registres : leurs résultats peuvent évoluer. Ils n’installent pas de correctifs. La compilation régénère les ressources locales dans `public/build` ; elle ne déploie pas l’application.

Pour contrôler seulement le style des nouveaux tests :

```bash
vendor/bin/pint --test tests/Support tests/Feature/Audit tests/Unit/CryptoBoundaryTest.php
```

Un résultat de test réussi signifie uniquement que les assertions de ce scénario passent dans cet environnement. Il ne vaut pas validation de Google, GitHub, SMTP, Stripe, d’un authentificateur physique ou du serveur de production. Le test de WebAuthn exerce une authentification cryptographique réelle avec des clés virtuelles de test, pas une clé matérielle.
