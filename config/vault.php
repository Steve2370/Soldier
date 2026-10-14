<?php

return [
    // Storage names remain stable so existing encrypted payloads stay readable.
    'payloads' => [
        'login' => ['fields' => ['identifiant' => '', 'mot_de_passe' => '', 'totp_secret' => null, 'notes' => null, 'champs_personnels' => []], 'preserve_blank' => ['mot_de_passe', 'totp_secret']],
        'carte' => ['fields' => ['numero' => '', 'titulaire' => '', 'expiration' => '', 'cvv' => '', 'code_pin' => null, 'notes' => null]],
        'note' => ['fields' => ['contenu' => '']],
        'identite' => ['fields' => ['prenom' => '', 'nom' => '', 'email' => null, 'telephone' => null, 'adresse' => null, 'passeport' => null, 'notes' => null]],
        'cles' => ['fields' => ['serveur' => '', 'port' => 22, 'username' => '', 'cle_privee' => null, 'mot_de_passe' => null, 'notes' => null], 'aliases' => ['mot_de_passe' => 'passphrase_ssh'], 'preserve_blank' => ['cle_privee', 'mot_de_passe']],
        'autre' => ['fields' => ['contenu' => '', 'notes' => null]],
    ],
];
