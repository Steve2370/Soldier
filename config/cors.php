<?php

// M1 (audit sécurité) : ce fichier n'existait pas — sans lui, `config('cors.paths')`
// résout à un tableau vide et le middleware HandleCors ne fait rien du tout (il ne
// matche jamais aucune route). En pratique cela bloque déjà tout fetch() cross-origin
// depuis une page web classique (politique du navigateur par défaut sans en-têtes
// CORS), mais laisse la configuration implicite et non documentée : un futur ajout
// de route API, ou une mise à jour de dépendance changeant ce comportement par
// défaut, pourrait rouvrir la porte sans qu'on s'en rende compte. On publie donc une
// configuration explicite et restrictive.
//
// Note : l'extension Chrome (Manifest V3, background service worker avec
// host_permissions) n'est normalement pas soumise à cette politique CORS — Chrome
// dispense ces requêtes-là du contrôle CORS classique. Si un jour l'extension
// effectue ses appels depuis un content script injecté dans des pages web (contexte
// alors soumis au CORS standard), renseigner EXTENSION_ORIGIN dans .env avec l'origine
// exacte (ex: chrome-extension://<id-de-l-extension>).

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter([
        rtrim(env('APP_URL', ''), '/'),
        env('EXTENSION_ORIGIN'),
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
