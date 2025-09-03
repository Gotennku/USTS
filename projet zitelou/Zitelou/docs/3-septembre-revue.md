# Revue Projet – 3 septembre 2025 16h30

## 1. Résumé Exécutif

Application backend Symfony 7.3 (API Platform) pour gestion utilisateurs, abonnements, paiements,
journalisation et sécurité JWT. Base technique stabilisée (containers Docker), premières entités
créées. Pipeline qualité amorcé (18 tests couvrant persistance/relations, checklist revue, PHPStan
niveau 3). Nouveau: test transitions d'état abonnement. Prochaine phase : enrichir couverture tests
(services métier), monter niveaux d’analyse statique, introduire logique de domaine et sécuriser
surface API.

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

- Tests: 18 tests / 43 assertions (persistance, relations, transitions simples). Pas encore de
  driver couverture actif (warning PHPUnit) → activer Xdebug/PCOV pour chiffres réels.
- Mutation testing (Infection): configuré (seuils 80/85) – pas encore exécuté sur un périmètre large
- Checklist revue: `docs/REVIEW_CHECKLIST.md` (doit être utilisée dans chaque PR)
- Analyse statique: PHPStan niveau 3 OK (0 erreurs). Cible prochaine: niveaux 4→6 (typage collections,
  nullabilité stricte) puis éventuellement baseline.
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

## 7. Dette & Risques

| Zone                           | Risque                   | Impact | Mitigation courte                                   | Mitigation long terme         |
| ------------------------------ | ------------------------ | ------ | --------------------------------------------------- | ----------------------------- |
| Faible couverture mesurée      | Régressions silencieuses | Élevé  | Activer couverture + ajouter tests services         | Approche TDD services métier  |
| Absence services métier        | Couplage contrôleurs/ORM | Moyen  | Introduire services Application                     | Strate Domain + Value Objects |
| Analyse statique partielle     | Types implicites restants| Moyen  | Monter niveaux 4→6 rapidement                      | Niveau strict + baseline CI   |
| Couverture code inconnue       | Manque de métriques      | Moyen  | Activer Xdebug/PCOV                                 | Intégration CI reporting      |
| Xdebug actif par défaut        | Performance              | Faible | Basculer via variable ENV                           | Multi-stage build prod        |
| Manque migrations validées CI  | Drift schéma             | Moyen  | Ajouter job `doctrine:migrations:migrate --dry-run` | Validation auto pré-merge     |

## 8. Prochaines Priorités (proposition sprint)

1. Activer driver couverture (Xdebug/PCOV) pour baseline réelle
2. Monter PHPStan niveaux 4→6 (types collections, nullabilité)
3. Services métier (SubscriptionService, PaymentService) + tests unitaires ciblant transitions
4. Tests API (CRUD + scénarios d’abonnement complets)
5. Événements domaine + logs d’audit automatiques
6. Pipeline CI (tests + stan + couverture) puis mutation progressive
7. Durcir sécurité (CORS, headers, rotation clés JWT planifiée)

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

| KPI                          | Actuel | Cible Phase 1 | Cible Phase 2                    |
| ---------------------------- | ------ | ------------- | -------------------------------- |
| Couverture lignes            | ~3%    | 40%           | 70%                              |
| Couverture classes critiques | 0%     | 60%           | 90%                              |
| Mutation Score (MSI)         | n/a    | 50%           | 80%                              |
| Temps build CI               | n/a    | <5 min        | <8 min (avec mutation partielle) |

## 12. Actions Immédiates Recommandées

1. Activer Xdebug/PCOV dans environnement test pour produire couverture
2. Monter PHPStan niveau 4 puis 5 – script `composer stan`
3. Ajouter services + tests transitions abonnement (annulation, expiration, renouvellement)
4. Définir conventions commit / branche (Conventional Commits)
5. Pipeline CI (install cache, phpunit, coverage, phpstan) avant mutation
6. Préparer baseline PHPStan uniquement si blocage montée strict

---

Document mis à jour le 3 septembre 2025 16h30.
