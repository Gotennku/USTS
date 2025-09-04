# Revue Projet – 4 septembre 2025 12h51

## 1. Résumé Exécutif

Application backend Symfony 7.3 (API Platform) pour gestion utilisateurs, abonnements, paiements,
journalisation et sécurité JWT. Base technique stabilisée (containers Docker), entités principales
créées. Pipeline qualité renforcé : 68 tests verts (162 assertions) couvrant la logique Stripe
(webhooks multi‑événements, checkout, billing portal), l’idempotence, l’authentification, les repositories
et les branches d’erreur/succès contrôleurs (login redirect, logout exception, checkout défaut, portal défaut).
PHPStan niveau 4 (0 erreurs) stable.

Nouvelle couverture (Xdebug) : Classes 82.00% / Méthodes 94.43% / Lignes 85.89%.
Objectif prochain palier : 90% classes & ≥96% méthodes après finalisation StripeCheckoutController (méthodes restantes)
et couverture totale entité User (2 méthodes manquantes), puis montée PHPStan 5+.

## 2. Pile & Architecture

- Langage: PHP 8.3 (CLI image Docker)
- Framework: Symfony 7.3 + API Platform 4.1
- Authentification: JWT (LexikJWTAuthenticationBundle)
- Base de données: MariaDB 10.11
- Front (non présent pour l’instant) – exposition JSON/Hydra
- Organisation code: `Entity/`, `Repository/`, contrôleurs API, ports/adapters Stripe, utilitaire `Assert`
- En cours d’introduction: couche Application / Domain (services métier structurés)

## 3. Avancement Fonctionnel (entités principales)

- Sécurité / Auth: `User`, `AuthToken`, `PasswordResetToken`
- Abonnements & Paiements: `Subscription`, `SubscriptionHistory`, `SubscriptionPlan`, `Payment`, `StripeWebhookLog`
- Administration / Logs: `AdminConfig`, `AdminLog`, `UserLog`, `AuditLog`, `BackOfficeStat`
- Paramétrage / Enfants: `Child`, `ParentalSettings`, `FeatureAccess`, `BanList`
- Urgences / Contacts: `EmergencyCall`, `EmergencyContact`, `AuthorizedContact`, `AuthorizedApp`, `GeoLocation`

Statut: Modèle de données stabilisé; logique métier avancée (transitions, facturation évoluée) à industrialiser.

## 4. Qualité & Tests

- Tests: 68 tests / 162 assertions (entités, repositories, authenticator, contrôleurs login/security + logout, services Stripe, webhooks, idempotence, scénarios checkout & portal défaut / erreurs).
- Mutation testing (Infection): configuré (seuils 80/85) – campagne complète encore à réaliser.
- Checklist revue: `docs/REVIEW_CHECKLIST.md` utilisée comme référence de PR.
- Analyse statique: PHPStan niveau 4 OK (0 erreurs). Montée programmée vers 5 puis 6.

### Couverture (activée)

Progrès récents (depuis 09h35):
1. Suppression test legacy skipped (webhook) remplacé par test persistant sans skip.
2. Ajout tests branches manquantes Stripe Checkout (auth manquante, URLs par défaut, plan absent, exception service).
3. Ajout test exception explicite `SecurityController::logout()` (100% contrôleur Security).
4. DoctrineCustomerProvider & AppAuthenticator 100% (branches succès/erreurs).
5. Nettoyage tests redondants / suppression classe vide.

Restant principal pour 100% ciblé:
- `StripeCheckoutController`: méthode portal non comptée (réduction conditionnelle doublon auth). Une simplification appliquée (suppression double vérif token) – re‑générer le rapport pour voir si compteur méthodes couvertes évolue.
- Entité `User`: 2 méthodes non couvertes (probablement helpers ou setters peu utilisés) – ajouter test direct.
- Webhook erreurs JSON/signature (chemins 400) encore à valider si absence dans suite.

Prochain focus couverture: compléter `StripeCheckoutController` (appel direct méthode portal via requête authentifiée) et finaliser entité User.

### Migration nullabilité (rappel)

Migration `Version20250903151723` : colonnes *foreign key* `DEFAULT NULL` (alignement code / schéma). Reversibilité via `down()`.
À faire: normalisation codestyle (PHP CS Fixer/PHPCS) & tests intégration API.

## 5. Infrastructure & Exécution

- Docker multi‑services (PHP, MariaDB, Adminer, Mailpit) – Xdebug activable via variable env.
- Optimisation cache Composer (installation plus rapide).
- Prévoir désactivation Xdebug en prod (performance).

## 6. Sécurité

- JWT en place (clés test & prod séparables). Prochaines étapes: rotation clés, durées, rate limiting, audit endpoints sensibles.

## 7. Stripe – État Intégration (mise à jour 04/09 09h35)

Implémenté (mis à jour):
- Client `StripeClientFactory` (API 2024-06-20)
- Checkout abonnement & Billing Portal (services + tests complets)
- Webhooks: subscription created/updated/deleted/expired, invoice payment_succeeded/payment_failed
- Idempotence: stockage eventId unique (DoctrineEventIdempotencyChecker + unique + tests)
- Logging centralisé (StripeWebhookLog) + enrichissement payload si vide
- Subscription & Payment + historisation (SubscriptionHistory)

Lacunes actuelles:
- États intermédiaires (incomplete, past_due, trialing) non mappés finement
- Montants Payment encore float (pas de Money object / cents)
- Pas d’endpoint explicite annulation / changement plan
- Pas de synchronisation descendante plans/prices Stripe
- Contrôleur Webhook: chemins d’erreur (signature/JSON) non testés

Priorité prochaine itération : tests contrôleurs manquants + migration montants + mapping états.

## 8. Dette & Risques

| Zone                           | Risque / État                       | Impact | Mitigation courte                                   | Mitigation long terme         |
| ------------------------------ | ----------------------------------- | ------ | --------------------------------------------------- | ----------------------------- |
| Couverture réelle partiellement représentative | Réduit mais encore perfectible | Moyen  | Focus tests métier contrôleurs/restes              | Pyramide + mutation testing   |
| Absence services métier        | Couplage contrôleurs/ORM            | Moyen  | Introduire services Application                     | Strate Domain + Value Objects |
| Idempotence webhooks           | Couvert (contrainte + test)         | Faible | Surveillance logs                                    | File asynchrone + reprocessing |
| Float pour montants            | Erreurs arrondi potentielles        | Moyen  | Migrer vers int cents                               | Value Object Money            |
| Mapping status Stripe minimal  | États incohérents                   | Moyen  | Étendre mapping + transitions                       | Machine à états / règles      |
| Analyse statique partielle     | Types implicites restants           | Moyen  | Monter niveaux 5→6                                   | Niveau strict + baseline CI   |
| Webhook erreurs non testées    | Risque non détecté                  | Faible | Ajouter tests erreurs (400)                         | Hardening + monitoring        |
| Xdebug actif en dev            | Performance locale                  | Faible | Activer seulement coverage                          | Build multi‑stage             |
| Montée version libs future     | Régressions potentielles            | Moyen  | Tests + mutation                                    | Renovate + surveillance       |

## 9. Prochaines Priorités (proposition sprint)

1. Compléter couverture `StripeCheckoutController` (portal + createSession 100% méthodes)
2. Couvrir les 2 méthodes manquantes `User`
3. Tests erreurs Webhook (signature absente / JSON invalide)
4. Migration montants Payment -> cents
5. PHPStan niveau 5 puis 6
6. Services métier Subscription / Payment (use cases + transitions)
7. Tests API end‑to‑end + badge couverture CI
8. Value Object Money + migration
9. Mapping états Stripe étendu & endpoints annulation / upgrade

## 10. Commandes Utiles (Dev & Qualité)

### Docker / Environnement

```bash
# Construire image PHP avec Xdebug
docker compose build php

# Lancer l’écosystème
docker compose up -d

# Arrêter
docker compose down
```

### Dépendances / Composer

```bash
# Installer dépendances (local ou dans conteneur)
composer install
# (Dans conteneur)
docker compose run --rm php composer install
```

### Base de Données & Migrations

```bash
# Générer une migration
docker compose run --rm php php bin/console make:migration
# Exécuter migrations
docker compose run --rm php php bin/console doctrine:migrations:migrate --no-interaction
# Vérifier schéma vs mapping
docker compose run --rm php php bin/console doctrine:schema:validate
```

### Tests & Couverture

```bash
# Tests rapides
docker compose run --rm php vendor/bin/phpunit

# Couverture (Clover + HTML)
docker compose run --rm -e XDEBUG_MODE=coverage php composer coverage

# Couverture texte directe
docker compose run --rm -e XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-text
```

### Mutation Testing

```bash
# Lancer Infection (Xdebug requis)
docker compose run --rm php composer mutation
```

### Qualité (analyse statique future)

```bash
# Analyse statique (après montée niveau)
docker compose run --rm php vendor/bin/phpstan analyse --memory-limit=1G
```

### Génération JWT (si rotation clés)

```bash
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem
chmod 600 config/jwt/private.pem
```

## 11. Modulation & Structuration Future

Axes d’évolution:
- Couche Domain avec Value Objects (PlanId, Money, SubscriptionPeriod)
- Services Application orchestrant cas d’usage (Activate/Cancel/Upgrade Subscription)
- Ports/Adapters externalisés (Stripe, Mail) + mapping statuts consolidé
- Événements domaine (SubscriptionActivated, PaymentCaptured) + projections
- Pyramide de tests (unit > application > API) + campagne mutation ciblée

## 12. KPIs de Qualité Cibles (révisés)

| KPI                          | Actuel | Cible Phase 1 | Cible Phase 2 |
| ---------------------------- | ------ | ------------- | ------------- |
| Couverture lignes            | 84.77% | 85%           | 90%           |
| Couverture classes critiques | 75.00% | 80%           | 90%           |
| Mutation Score (MSI)         | n/a    | 50%           | 80%           |
| Temps build CI               | n/a    | <5 min        | <8 min        |

## 13. Actions Immédiates Recommandées

1. Couverture StripeCheckout (méthodes) & User (2 méthodes)
2. Webhook erreurs (signature/JSON)
3. Migration montants Payment (float -> int cents)
4. PHPStan niveau 5 puis 6
5. Services métier Subscription/Payment + endpoints annulation/upgrade
6. Tests API end‑to‑end + badge couverture CI
7. Value Object Money + migration
8. Mapping étendu statuts Stripe

---

Document mis à jour le 4 septembre 2025 12h51.
