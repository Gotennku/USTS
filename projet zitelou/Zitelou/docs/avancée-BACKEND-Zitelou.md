# Revue Projet – 4 septembre 2025 08h10

## 1. Résumé Exécutif

Application backend Symfony 7.3 (API Platform) pour gestion utilisateurs, abonnements, paiements,
journalisation et sécurité JWT. Base technique stabilisée (containers Docker). Nouvelle étape :
extraction en cours d’un module Stripe découplé (`StripeIntegration`). Suite derniers commits :
24 tests verts (77 assertions) – ajout test unitaire `CheckoutServiceTest` + adaptation tests API.
PHPStan niveau 4 (0 erreurs) toujours stable. Couverture via Xdebug actualisée : Classes 61.90% /
Méthodes 90.03% / Lignes 78.12%. Baisse (classes) due au périmètre total (ancien service checkout
à supprimer) mais lignes en hausse (meilleure densité logique testée). Prochaine phase : migrer
Billing Portal dans le module, refactor webhooks en ports/adapters, renforcer idempotence.

## 2. Pile & Architecture

- Langage: PHP 8.3 (CLI image Docker)
- Framework: Symfony 7.3 + API Platform 4.1
- Authentification: JWT (LexikJWTAuthenticationBundle)
- Base de données: MariaDB 10.11
- Front (non présent pour l’instant) – exposition JSON/Hydra
- Organisation code: `Entity/`, `Repository/`, contrôleurs API, utilitaire interne `Assert`
- Absents encore: couche Service / Domain explicite, Value Objects, événements de domaine.

## 3. Avancement Fonctionnel (entités principales)

- Sécurité / Auth: `User`, `AuthToken`, `PasswordResetToken`
- Abonnements & Paiements: `Subscription`, `SubscriptionHistory`, `SubscriptionPlan`, `Payment`,
  `StripeWebhookLog`
- Administration / Logs: `AdminConfig`, `AdminLog`, `UserLog`, `AuditLog`, `BackOfficeStat`
- Paramétrage / Enfants: `Child`, `ParentalSettings`, `FeatureAccess`, `BanList`
- Urgences / Contacts: `EmergencyCall`, `EmergencyContact`, `AuthorizedContact`, `AuthorizedApp`,
  `GeoLocation`

Statut: Modèle de données initial posé; logique métier (validation, transitions d’états,
agrégations) encore à implémenter.

## 4. Qualité & Tests

- Tests: 24 tests / 77 assertions (persistance, relations, tests fonctionnels Stripe checkout & portail placeholder, test unitaire service checkout).
  Couverture Xdebug: Classes 61.90% | Méthodes 90.03% | Lignes 78.12%.
- Mutation testing (Infection): configuré (seuils 80/85) – pas encore exécuté sur un périmètre large
- Checklist revue: `docs/REVIEW_CHECKLIST.md` (doit être utilisée dans chaque PR)
- Analyse statique: PHPStan niveau 4 OK (0 erreurs). Nullabilité harmonisée en rendant certaines
  colonnes DB nullable (à confirmer via migration) + ajout generics Collection. Cible prochaine:
  niveaux 5→6 puis niveau max avec éventuellement baseline si nécessaire.

### Couverture (focus actuel)

Points forts: Entités centrales largement couvertes. Nouveau service `CheckoutService` partiellement (manque scénario d’erreur plan sans price).
Points faibles ciblés:
1. `StripeWebhookHandler` (27.78% lignes) – besoin scénarios update / cancel / invoices.
2. Adapters StripeIntegration (tests unitaires manquants).
3. Chemins d’erreur (price absent, customer port échec) non testés.
Prochaines actions couverture: tests négatifs checkout, scénarios webhook supplémentaires, futur BillingPortalService testé.

### Migration nullabilité (17h15)

Migration `Version20250903151723` générée et appliquée : colonnes *foreign key* rendues `DEFAULT NULL` pour aligner code PHP (propriétés nullable) et schéma Doctrine (relations optionnelles). Reversibilité assurée via méthode `down()` rétablissant `NOT NULL`.
- Manque: normalisation codestyle (PHP CS Fixer / PHPCS), tests intégration API.

## 5. Infrastructure & Exécution

- Docker: Image unique PHP + MariaDB + Adminer + Mailpit (override)
- Xdebug activé en dev (couverture + debug). À désactiver en prod.
- Optimisation: couche cache Composer partielle en place (copie composer.json avant
  `composer install`).

## 6. Sécurité

- JWT clé privée/publique déjà générées
- Points à prévoir: rotation des clés, durées tokens, rate limiting, audit endpoints sensibles,
  intégration future d’un WAF ou reverse proxy.

## 7. Stripe – État Intégration (mise à jour 4 sept 08h10)

Implémenté :
- Client `StripeClientFactory` (version API figée 2024-06-20)
- Module `StripeIntegration` (ports: customer, plan price; DTOs checkout; service `CheckoutService` injecté contrôleur)
- Endpoint checkout migré vers service modulaire
- Placeholder Billing Portal (501) en attente `BillingPortalService`
- Webhooks: subscription created/updated/deleted, invoice payment succeeded/failed (logiciel présent, couverture partielle)
- Métadonnées user_id / plan_id propagées dans `subscription_data`
- Création Subscription & Payment, historisation (SubscriptionHistory), logging (StripeWebhookLog)

Lacunes clés actuelles :
- Idempotence event.id non implémentée (risque duplication)
- Billing Portal non migré (501)
- Webhook coverage faible (scénarios update/cancel/invoices non testés)
- Montants Payment en float (doit migrer vers int cents)
- Absence SubscriptionPersister (port défini mais pas implémenté)
- Pas de tests négatifs checkout / portal
- Pas d’endpoint cancel / upgrade plan
- Pas de mapping états intermédiaires (incomplete, past_due, trialing)

Priorité prochaine itération : idempotence (event.id) + tests invoices + migration montant en cents.

## 8. Dette & Risques

| Zone                           | Risque                   | Impact | Mitigation courte                                   | Mitigation long terme         |
| ------------------------------ | ------------------------ | ------ | --------------------------------------------------- | ----------------------------- |
| Couverture réelle partiellement représentative | Faux sentiment de sécurité | Moyen  | Réduire tests réflexifs, ajouter scénarios métier | Pyramide tests + mutation testing |
| Absence services métier        | Couplage contrôleurs/ORM | Moyen  | Introduire services Application                     | Strate Domain + Value Objects |
| Idempotence webhooks incomplète| Replay Stripe dupliqué   | Moyen  | Stocker event.id + contrainte unique                | File asynchrone + reprocessing |
| Float pour montants            | Erreurs arrondi          | Moyen  | Migrer vers int cents                               | Value Object Money             |
| Mapping status Stripe minimal  | États incohérents        | Moyen  | Étendre mapping + transitions                        | Machine à états / domain rules |
| Analyse statique partielle     | Types implicites restants| Moyen  | Monter niveaux 4→6 rapidement                      | Niveau strict + baseline CI   |
| Couverture code inconnue       | Manque de métriques      | Moyen  | Activer Xdebug/PCOV                                 | Intégration CI reporting      |
| Xdebug actif par défaut        | Performance              | Faible | Basculer via variable ENV                           | Multi-stage build prod        |
| Manque migrations validées CI  | Drift schéma             | Moyen  | Ajouter job `doctrine:migrations:migrate --dry-run` | Validation auto pré-merge     |

## 8. Prochaines Priorités (révisé)

1. Migrer Billing Portal (nouveau `BillingPortalService` + tests API) & supprimer ancien service legacy.
2. Idempotence webhooks (stockage event_id unique) + tests replay.
3. Implémenter `SubscriptionPersisterInterface` + refactor `StripeWebhookHandler` en handlers spécialisés.
4. Migration montant Payment float -> int (cents) + adaptation entité/tests & Value Object Money futur.
5. Étendre couverture: scénarios webhook (update, cancel, invoices success/fail), tests négatifs checkout.
6. Monter PHPStan vers niveau 5 puis 6 (ajout règles doctrine, generics collections).
7. CI: ajout étapes phpunit (couverture), phpstan, futur infection ciblée.
8. Domain services (SubscriptionService, PaymentService) + transitions upgrade/downgrade.
9. Mapping états Stripe intermédiaires (incomplete, past_due, trialing) -> SubscriptionStatus.
10. Endpoint cancel / upgrade plan.

## 9. Commandes Utiles (Dev & Qualité)

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
# Lancer tests simples
docker compose run --rm php vendor/bin/phpunit
# Couverture texte
docker compose run --rm php vendor/bin/phpunit --coverage-text
# Couverture HTML (sortie dans var/coverage)
docker compose run --rm php vendor/bin/phpunit --coverage-html var/coverage
```

### Mutation Testing

```bash
# Lancer Infection (Xdebug requis)
docker compose run --rm php composer mutation
```

### Qualité (à ajouter après installation outils)

```bash
# (Prévu) Analyse statique
# docker compose run --rm php vendor/bin/phpstan analyse --memory-limit=1G
# (Prévu) Mutation ciblée sur un dossier
# docker compose run --rm php vendor/bin/infection --filter=src/Subscription
```

### Génération JWT (si needed refresh clés)

```bash
# Regénérer clés (exemple) – ATTENTION en prod
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem
chmod 600 config/jwt/private.pem
```

## 10. Modulation & Structuration Future

Pour rendre le code plus modulable:

- Introduire couche `src/Domain/<Aggregate>/` avec Value Objects (ex: `PlanId`,
  `SubscriptionPeriod`)
- Services applicatifs `src/Application/` orchestrant cas d’usage (ex:
  `ActivateSubscriptionHandler`)
- Ports/Adapters: extraire intégrations externes (Stripe, mail) dans `src/Infrastructure/`
- Événements de domaine (ex: `SubscriptionActivated`) + projection/statistiques
- Tests: pyramide (unit -> application -> API) + tests mutation sur logique pure

## 11. KPIs de Qualité Cibles (suggestion)

| KPI                          | Actuel (Xdebug) | Cible Phase 1 | Cible Phase 2                    |
| ---------------------------- | -------------- | ------------- | -------------------------------- |
| Couverture lignes            | 73.46%         | 75%           | 80%                              |
| Couverture classes critiques | 64.86%         | 75%           | 90%                              |
| Mutation Score (MSI)         | n/a            | 50%           | 80%                              |
| Temps build CI               | n/a            | <5 min        | <8 min (avec mutation partielle) |

## 12. Actions Immédiates Recommandées

1. Couverture qualitative: ajouter scénarios invoices / idempotence / annulation
2. Monter PHPStan niveau 5 puis 6 – script `composer stan`
3. Migration montants Payment (float -> int cents) + adaptation tests & conversions Stripe
4. Services métier + transitions abonnement (annulation, expiration, renouvellement)
5. Définitions conventions commit / branche (Conventional Commits)
6. Pipeline CI (cache deps, phpunit couverture HTML, phpstan, future infection partielle)
7. Baseline PHPStan uniquement si blocage montée niveau max

---

Document mis à jour le 4 septembre 2025 08h10.
