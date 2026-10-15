# Administration, accès Famille offert, paiements et partage — 15 octobre 2026

## Changements livrés localement

- Administration : actions visibles sur ordinateur et fiches adaptées au mobile. La suppression définitive exige de recopier l’email. Le serveur refuse les non-administrateurs, l’auto-suppression et les comptes ayant encore un abonnement Stripe non terminé, y compris un autre produit ou une période de grâce.
- Suppression : transaction pour le compte, les coffres actifs ou en corbeille, clés, partages, groupe, MFA, passkeys, jetons, sessions et liens de réinitialisation. Les données des autres utilisateurs sont conservées. L’avatar local non partagé est nettoyé après validation de la transaction ; un échec du stockage est journalisé sans annoncer une fausse erreur de suppression du compte.
- Famille offert : ajout/retrait indépendant de Stripe, avec date d’attribution protégée contre l’assignation en masse et trace de l’administrateur dans le journal. Ce droit permet de créer son propre groupe ; il ne donne pas accès aux coffres d’une autre famille.
- Retrait : si un abonnement payé reste valide, le groupe et ses accès sont conservés. Sinon, les accès des membres et les partages familiaux sont révoqués ; les coffres du propriétaire et les partages indépendants restent présents. Une nouvelle attribution n’inscrit pas automatiquement les anciens membres.
- Les écrans Famille, partage, tableau de bord, tarifs et administration reconnaissent le droit offert. Un compte offert sans abonnement payé n’est pas dirigé vers un paiement inutile.
- Paiement : suppression du prix Stripe de secours codé en dur, erreurs Stripe présentées sans détails sensibles, abonnement incomplet existant redirigé vers sa gestion, page de retour basée sur les droits réellement enregistrés. Un simple `session_id` dans l’URL n’active rien.
- Webhook : refus lorsque le secret manque, vérification des signatures conservée, identification précise de l’abonnement concerné. Une annulation d’un autre produit ne retire pas Famille. Les accès offerts et les autres abonnements Famille valides sont préservés.
- Don : les liens PayPal ouvrent un nouvel onglet avec `noopener noreferrer`. Le profil public « Brice Tchagam — @BriceSteve » est accessible ; le destinataire a été confirmé par l’utilisateur. Aucun don n’a été envoyé.

## Correctifs complémentaires du partage

- Le formulaire envoyait deux valeurs `partage_groupe` pour un propriétaire Famille, même en mode individuel. Le navigateur reproduisait `0` puis `1` dans la même requête. Des boutons radio natifs transmettent maintenant un seul choix ; l’email est désactivé en mode familial.
- Les cases des autres coffres étaient masquées mais soumises. Elles sont maintenant désactivées hors du coffre choisi. Les cases natives sont utilisables au clavier et conservent exactement la sélection après une erreur, y compris une sélection vide. Suppression de l’appel à une fonction de soumission inexistante.
- Le serveur valide le mode, le coffre, la permission et les éléments. Un partage individuel cible uniquement la personne choisie. Le mode familial exige un forfait payé ou offert et détermine les destinataires depuis le groupe appartenant à l’expéditeur, jamais depuis un identifiant de groupe fourni par le formulaire.
- Normalisation des emails et refus du partage avec soi-même, même avec une adresse en majuscules. Un compte absent ou sans clés reçoit désormais un message demandant son initialisation, au lieu de créer une invitation sans enveloppe déchiffrable. Le coffre familial ne peut pas produire d’invitation inutilisable destinée à un non-membre.
- Les invitations familiales sont créées dans une transaction ; un échec de chiffrement annule tout le lot avant tout courriel. Les clés locales sont nettoyées dans un `finally`. Chaque destinataire conserve sa propre enveloppe RSA.
- Un groupe vide ou dont tous les membres possèdent déjà un partage ne produit plus un faux succès avec zéro invitation. Les accès existants restent inchangés : ce formulaire crée de nouveaux accès, il ne réduit ni ne remplace un partage déjà accordé.
- Les échecs partiels du transport de courriel produisent un avertissement avec le nombre d’invitations créées et de messages acceptés par le transport. Cela ne prouve pas la réception dans une boîte de messagerie réelle.

## Organisation du code

Les responsabilités sont séparées : validation de suppression dans une requête dédiée, orchestration dans `DeleteUserService`, nettoyage de l’avatar dans `RemoveUserAvatar`, attribution dans `FamilyGrantService`, accès au paiement derrière `FamilyBillingGateway` et son implémentation Cashier. Le calcul des droits payés/offerts est centralisé sur le modèle utilisateur.

Pour le partage : `SendShareInvitationRequest` valide les entrées, `ShareAudienceResolver` détermine les destinataires autorisés et `SendShareInvitations` orchestre le chiffrement, la transaction et la livraison. Le contrôleur transmet la clé de session sans connaître les opérations cryptographiques. Les dépendances de chiffrement et de courriel restent injectées derrière leurs interfaces existantes ; les simulations des tests n’altèrent pas le code de production.

## Vérifications

- Suite PHP complète : **267 tests, 986 assertions**, sans échec, après correction du partage.
- Navigateur : **32 tests** (22 composants + 10 parcours Laravel réels), sur base SQLite temporaire, avec Chrome isolé et accès externes bloqués.
- Parcours ajoutés : attribution, retrait, création de groupe offert, annulation de suppression, mauvais email de confirmation, suppression confirmée, rejet CSRF réel, affichage des erreurs de paiement et retour sans confirmation, administration mobile.
- Partage : **22 nouveaux scénarios PHP** dans `SharingAudienceTest`, plus **3 nouveaux parcours navigateur**. Contrôle du formulaire réellement soumis et des destinataires enregistrés, changement de coffre et de mode, sélection au clavier, conservation après erreur, mobile, forfait payé/offert, refus des entrées invalides et des accès non autorisés, déchiffrement réel par chaque destinataire, acceptation des invitations familiales, préservation des partages existants, annulation transactionnelle et échec partiel des courriels. Les tests historiques d’acceptation/révocation et de restriction aux éléments sélectionnés restent verts.
- Stripe : vrai SDK et Cashier avec transport HTTP simulé qui refuse tout appel imprévu. Contrôle du client, du prix, des métadonnées Famille, des URLs de retour, du portail et des erreurs. Webhooks créés/signés localement : activation, répétition, annulation, impayé, mauvais secret, signature expirée, absence de secret, produit indépendant, droits offerts et coexistence d’abonnements.
- Compilation Vite et vérification TypeScript : réussies. Validation Composer, style des fichiers PHP modifiés et contrôle des espaces du diff : réussis.
- La configuration locale contient un prix Famille, une clé secrète et un secret webhook ; seules leurs présences ont été affichées.

Commandes de reproduction : voir [README_AUDIT.md](README_AUDIT.md). Les tests utilisent les migrations livrées et ne doivent jamais être exécutés contre la base de production. La vérification des notifications et des retours de paiement suit le fonctionnement décrit dans la [documentation officielle Cashier](https://laravel.com/framework/docs/13.x/billing#handling-stripe-webhooks).

## Déploiement et limites

Ces changements ne sont ni commités, ni poussés, ni déployés. Appliquer la migration `2026_10_15_000001_add_family_grant_to_users` au déploiement, puis régénérer les caches. Elle ajoute une colonne nullable, sans transformer les abonnements ou les clés existants. Les correctifs de partage n’ajoutent aucune migration et ne modifient aucun partage historique. Sauvegarder la base selon la procédure habituelle avant toute migration de production.

Le serveur doit disposer de `STRIPE_PRICE_FAMILLE`, `STRIPE_SECRET` et `STRIPE_WEBHOOK_SECRET` corrects pour le même environnement Stripe. Le prix configuré doit correspondre au montant et à la devise annoncés. Le portail de facturation et les événements requis par Cashier doivent être configurés dans Stripe. Une variable présente localement ne prouve ni sa validité ni la configuration de production.

Il reste à valider le parcours dans un environnement de recette Stripe : carte de test, retour, réception effective du webhook, renouvellement/impayé et portail. Aucun encaissement Stripe ou PayPal réel, aucune modification d’un abonnement externe et aucune suppression de compte réel n’ont été effectués. L’accès au profil PayPal ne prouve pas qu’un paiement aboutira pour tous les comptes/pays.

Les résultats couvrent les scénarios exercés, pas une garantie d’absence de tout bug, de toute course concurrente, ni un audit exhaustif de l’ensemble de Soldier.
