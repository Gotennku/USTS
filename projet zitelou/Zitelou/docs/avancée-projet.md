# Avancée projet – Couverture & Qualité (maj 04/09/2025)

## Commande standard pour exécuter les tests avec couverture

```
docker compose run --rm -e XDEBUG_MODE=coverage php composer coverage
```

Cette commande lance PHPUnit dans le conteneur PHP avec Xdebug actif et génère :
- Rapport Clover : `var/coverage/clover.xml`
- Rapport HTML : `var/coverage/html/index.html`
- Résumé texte en sortie standard

## Résumé actuel de la couverture
Date génération : 2025-09-04 09:28:40

- Classes : 75.00% (36/48)
- Méthodes : 93.22% (316/339)
- Lignes : 84.77% (707/834)

Objectif cible : 90% classes.
Écart restant principal : classes contrôleurs & quelques adaptateurs / repo partiellement couverts.

## Détail par grands modules

### 1. Couche Domaine / Entités
Toutes les entités métier ont 100% (getters/setters & logique simple) sauf `User` (2 méthodes non exercées, possiblement liées à changements de rôle ou helper secondaire). Elles sont couvertes indirectement par les tests (création, persistance, mises à jour via services ou providers).

### 2. Sécurité
- `AppAuthenticator` : 75% méthodes (chemin succès / échec couvert, login URL couvert, reste un chemin secondaire ou edge non testé — probablement `supports()` ou variante de `onAuthenticationSuccess`/`onAuthenticationFailure`).
- `SecurityController` : 0% méthodes (test de rendu page login + redirection utilisateur authentifié manquant). Un test fonctionnel ciblé augmentera immédiatement le pourcentage de classes.

### 3. Repositories & Adaptateurs
- `UserRepository` : test d'`upgradePassword()` (50%). Reste : ajouter un test minimal validant que le repository est instanciable / ou autre méthode spécifique si ajoutée ultérieurement.
- `DoctrineCustomerProvider` : logique principale couverte (création & réutilisation ID). Méthode reportée comme 1/2 (probablement constructeur ou méthode auxiliaire non comptée). Considéré suffisant pour le comportement.
- `DoctrinePlanPriceProvider` : 100%.
- `DoctrineEventIdempotencyChecker` : 100% après ajout des tests couvrant `isAlreadyProcessed()` et les deux branches de `markProcessed()` (création + enrichissement payload vide).

### 4. Intégration Stripe
- Services Checkout / Billing Portal : 100% (chemin nominal + erreur retour URL vide ou price absent couvert).
- Webhook Handlers (Invoice / Subscription) : 100% (tests supplémentaires pour events: subscription updated/renewed/expired/deleted, invoice payment_failed...).
- Dispatcher & Factory : 100%.
- Stub Stripe : `StripeStubClient` introduit pour éliminer les dépréciations de propriétés dynamiques.

### 5. Contrôleurs HTTP / API
- `StripeCheckoutController` : 33.33% (1/3) – seulement le chemin principal; ajouter tests pour cas d'erreur (ex: absence de price, ou paramètre invalide) si pas déjà indirectement couvert par service; vérifier méthodes annexes.
- `StripeWebhookController` : 50% – manque un test pour payload invalide (signature manquante / JSON corrompu) ou type d’event inconnu.
- `SecurityController` : 0% – voir plus haut.

### 6. Utilitaires
- `Assert` : 100%.

## Jeu de tests existant – cartographie (principaux fichiers)

| Zone | Fichier(s) de test | Ce qui est validé |
|------|--------------------|-------------------|
| Auth / Sécurité | `tests/Unit/Security/AppAuthenticatorTest.php`, `tests/Controller/SecurityControllerTest.php` | Authenticator (chemin succès/échec), rendu login, logout (partiel) |
| Login API | `tests/Controller/LoginControllerTest.php` | Endpoint `/api/login` réponses 200/401 |
| Stripe Checkout | `tests/Unit/CheckoutServiceTest.php` | Création session + gestion absence de price (erreur) |
| Billing Portal | `tests/Unit/BillingPortalServiceTest.php` | Création session portail + validation URL retour non vide |
| Customer Provider | `tests/Unit/StripeIntegration/DoctrineCustomerProviderTest.php` | Création client Stripe + idempotence (2e appel) |
| Webhooks principaux | `tests/Api/StripeWebhookTest.php` (si présent) / `tests/Api/StripeWebhookAdditionalEventsTest.php` | Dispatch d'événements & branches d'abonnement/facture (updated, deleted, expired, failed) |
| Idempotency | `tests/Unit/StripeIntegration/DoctrineEventIdempotencyCheckerTest.php` | isAlreadyProcessed + markProcessed (création & enrichissement) |
| Stripe Factory | `tests/Unit/Stripe/StripeClientFactoryTest.php` | Instanciation client + secret |
| User Repository | `tests/Unit/Repository/UserRepositoryTest.php` | upgradePassword persiste le hash |
| App Utility | (inclus probablement dans d'autres tests) | Assertions utilitaires via usage indirect |

(Remarque : certains noms exacts peuvent varier légèrement selon l'organisation des sous-dossiers.)

## Dépréciations PHP 8.2+ (résolu)
Les dépréciations de propriétés dynamiques Stripe (`$checkout`, `$billingPortal`, `$customers`) ont été supprimées via le stub typé `StripeStubClient`.

## Prochaines actions (pour atteindre 90% classes)
Priorité (chaque point augmente le dénominateur couvert) :
1. Ajouter un test fonctionnel `SecurityController` pour chemin "déjà authentifié" (ex: se connecter puis GET /login => redirection). (+1 classe couverte)
2. Test `StripeWebhookController` pour :
   - Signature webhook manquante / invalide (attente 400)
   - Payload JSON invalide (attente 400)
3. Tests supplémentaires `StripeCheckoutController` couvrant :
   - Paramètres manquants (ex: price absent) si endpoint gère directement l'erreur (sinon stub service pour forcer exception). 
4. `UserRepository` : test d'instanciation simple ou d'une autre méthode (si ajout d'une méthode finder spécifique) pour passer à 100%.
5. (Optionnel) Couvrir méthode restante `AppAuthenticator` (ex: `supports()` avec requête non JSON / autre route) si ce n’est pas déjà implicitement exécuté.

Estimation : ces ajouts devraient propulser la couverture classes > 85% rapidement puis proche de 90% après 3–4 nouveaux tests.

## Qualité & Maintenance
- Structure claire Ports/Adapters pour Stripe (facile à mocker).
- Idempotency centralisée, couverte y compris branche d'enrichissement.
- Aucune dépréciation active post-stub.
- Rapports HTML disponibles pour inspection fine des lignes non couvertes.

## Recommandations complémentaires
- Envisager d'exclure (configuration couverture) les classes purement DTO ou entités générées si leur couverture est jugée peu utile (sinon continuer tests ciblés). 
- Ajouter un job CI pour exécuter la commande coverage et publier le rapport (artifacts) + gate minimal (ex: 70% lignes / +1% vs main).
- Surveiller croissance du nombre de classes : s'assurer que nouveaux contrôleurs arrivent déjà avec tests fonctionnels.

---
générée le 04/09/2025 – prochaine révision après ajout des tests contrôleurs manquants._
