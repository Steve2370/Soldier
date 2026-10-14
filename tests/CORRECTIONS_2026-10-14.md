# Soldier après les corrections du 14 octobre 2026

## Résultat local

Les défauts reproduits dans l’audit initial A01 à A26 sont corrigés dans le code local. La campagne étendue réussit : **208 tests PHP, 628 assertions et 26 tests navigateur**, sans échec ni test ignoré. Les 26 tests navigateur comprennent 22 tests de composants et 4 parcours complets, dont un parcours couvrant les six types d’éléments du coffre. Ces nombres ne représentent pas une couverture de code de 100 % ni une certification de sécurité.

L’inscription est vérifiée jusqu’à la requête suivante, à la validation de l’email et à l’ouverture du coffre sur une base créée uniquement par les migrations livrées. Le complément artificiel de schéma du premier audit a été retiré. Aucun déploiement, commit, push ou changement de base de production n’a été effectué.

| Contrôle | Avant corrections | Après corrections |
| --- | --- | --- |
| Tests PHP | 140 réussis sur 181 | 208 réussis sur 208 |
| Tests navigateur | 15 réussis sur 19 | 26 réussis sur 26 |
| Alertes Composer | 2 avis sur un paquet | Aucun avis signalé |
| Alertes npm | 9 paquets signalés | Aucun paquet signalé |
| TypeScript et compilation | Réussis | Réussis |
| Syntaxe PHP | Réussie | 142 fichiers contrôlés |
| Style des fichiers PHP concernés | Non conforme pour certains fichiers | Réussi |

Les audits de dépendances correspondent aux versions verrouillées et aux registres consultés le jour du contrôle. Le style global reste à harmoniser dans des fichiers préexistants hors corrections ; cela ne constitue pas une faille et ne remet pas en cause les résultats fonctionnels.

## Corrections livrées

| Domaine et constats initiaux | Comportement corrigé |
| --- | --- |
| Partages A01 et A03 | Contrôle des éléments autorisés pour lire, modifier, supprimer et changer les favoris. Une sélection vide ne donne plus accès au coffre entier, dans les pages comme dans l’API. |
| Famille A02 | Révocation transactionnelle des partages et invitations liés à l’adhésion lors d’un retrait, départ ou événement de perte d’abonnement. Les anciens accès au coffre familial canonique sont refusés en l’absence d’adhésion. |
| Schéma et erreurs serveur A04, A05, A20 et A21 | Migration de réparation additive, colonnes Sanctum, journaux, suppression logique et privilège administrateur. Alias de permissions API déclarés, page des sessions ajoutée et profil non initialisé traité sans erreur 500. |
| Conservation A06, A07 et A22 | Contenus de notes, autres éléments, champs personnalisés et passphrases SSH conservés. Les valeurs absentes et les effacements explicites sont distingués. Corbeille restaurable pendant 30 jours et purge planifiée. |
| Authentification A09 à A19 | État MFA séparé de la session connectée, code envoyé après déverrouillage OAuth, récupération à usage unique, expiration des défis, protection anti-rejeu TOTP, contrôle de l’email vérifié et recherche insensible à la casse. Une panne de notification ne transforme plus une connexion réussie en erreur 500. |
| Secrets A12 et A19 | Champs sensibles exclus des anciennes saisies après erreur, validation des clés de session et accès refusé tant que le coffre n’est pas réellement déverrouillé. |
| Génération et interface A08, A24 et A25 | Entropie calculée sur le véritable dictionnaire, tirage uniforme par rejet, erreurs de favoris affichées correctement et effacement conditionnel du presse-papier. |
| Contrats techniques A23 et A26 | Réponse 403 conservée pour les clients machine et les webhooks non signés. Chiffrement et déchiffrement AES cohérents pour une chaîne vide. |

Les tests dans les véritables pages ont également permis de corriger les champs masqués de création soumis en double, les attributs JavaScript mal délimités, le menu mobile, des fenêtres de confirmation obsolètes, une variable de paramètres inexistante et le filtre des favoris qui ne suivait pas une modification sans rechargement. Les libellés contenant apostrophes, guillemets et balises sont testés sans exécution de code injecté.

Le renvoi du lien de vérification et les invitations indiquent désormais une panne de courriel sans annoncer un envoi réussi. Les espaces significatifs des mots de passe sont conservés lors de leur modification. Les défis WebAuthn expirent et ne peuvent être réutilisés ; une véritable attestation puis assertion cryptographique est validée avec un authentificateur virtuel Chrome.

## Organisation des responsabilités et SOLID

Les décisions d’accès par élément restent dans la politique et les critères de partage communs. `FamilyAccessRevocationService` gère la révocation, `FamilyMembershipService` l’ajout d’un membre, et `AcceptShareInvitation` l’acceptation transactionnelle. Les contrôleurs délèguent ces opérations.

`ElementPayloadInterface` sépare la construction des données de leur chiffrement et de leur persistance. Les schémas par type sont déclarés dans `config/vault.php`, sans multiplier les conditions dans le service de coffre. Les noms de stockage historiques sont conservés.

`MfaChallengeService` centralise la transition vers une authentification en attente. Les contrats de notification et de vérification d’email isolent le transport des opérations métier. Les composants du navigateur partagent les fonctions de tirage aléatoire et de copie des secrets. Cette refactorisation ciblée applique la séparation des responsabilités et l’inversion des dépendances ; elle ne prétend pas certifier chaque classe historique au regard de SOLID.

## Conditions de mise en production

1. Sauvegarder la base avec un mécanisme SQLite cohérent, y compris en présence d’un journal WAL, puis vérifier sa restauration avant toute migration. Une simple copie du fichier principal pendant des écritures n’est pas une validation de sauvegarde.
2. Tester la nouvelle migration `2026_10_14_000001_repair_security_schema.php` sur une copie anonymisée de la véritable base. Les tests locaux couvrent une base neuve, une réparation répétée et un schéma historique partiel simulé, avec conservation des clés, jetons et privilèges existants.
3. Déployer ensemble le code, les dépendances verrouillées, les ressources compilées et la migration ; recharger les processus et les caches selon l’installation. Ne pas exécuter `migrate:fresh` ni réinitialiser la base de production. La migration de réparation refuse volontairement le retour arrière destructif : prévoir une migration corrective ou la restauration d’une sauvegarde validée.
4. Vérifier que le planificateur Laravel fonctionne pour la purge à 30 jours. La déclaration de la tâche dans le code ne prouve pas son exécution sur le serveur.
5. Rejouer les parcours essentiels sur un environnement de préproduction avant d’ouvrir la nouvelle version aux utilisateurs.

## Limites restant à valider

- Google, GitHub, SMTP et Stripe sont simulés dans les tests serveur. Leurs paramètres réels, signatures, livraisons et comportements de reprise nécessitent un environnement de test propre à ces fournisseurs. Aucun paiement réel n’a été effectué.
- Chrome et un authentificateur virtuel ont été utilisés. Safari, Firefox, les authentificateurs matériels, l’extension complète et un audit d’accessibilité restent à contrôler.
- Les écritures sensibles ont reçu des transactions, verrous ou consommations conditionnelles, mais cette campagne n’est pas un essai de charge ni une preuve exhaustive des scénarios concurrents.
- Les anciens partages de coffres personnels envoyés à un groupe avant l’ajout de `family_group_id` ne portent pas tous une provenance permettant de les distinguer d’un partage personnel volontaire. Ils doivent être rapprochés de l’historique avant révocation ; le correctif ne supprime pas arbitrairement ces accès. Une révocation serveur ne retire pas les secrets déjà copiés par un destinataire.
- L’architecture continue de déchiffrer côté serveur. Elle n’est pas transformée en architecture « zero knowledge ». La sécurité des sessions, de la clé applicative, des sauvegardes et du serveur reste essentielle.
- Les anciens contenus déjà perdus ne sont pas recréés par le correctif. Une restauration éventuelle dépend de sauvegardes antérieures.

## Preuves et reproduction

Les instructions sont dans [README_AUDIT.md](README_AUDIT.md). L’[audit initial](AUDIT_2026-10-14.md) reste conservé comme état avant corrections.

- [Résultats PHP et assertions au format JUnit](results/2026-10-14-corrections/phpunit.xml)
- [Sortie PHP](results/2026-10-14-corrections/phpunit.txt)
- [Résultats navigateur](results/2026-10-14-corrections/browser.txt)
- [Audit des dépendances Composer](results/2026-10-14-corrections/composer-audit.json)
- [Audit des dépendances npm](results/2026-10-14-corrections/npm-audit.json)

Les tests utilisent exclusivement des comptes et secrets synthétiques, SQLite en mémoire ou une base temporaire dédiée, et des profils navigateur neufs. Les fichiers de résultats peuvent contenir des chemins locaux et des valeurs de test ; ils ne constituent pas des identifiants à réutiliser.
