# Revue Projet – 3 septembre 2025 16h10

## 1. Résumé Exécutif

Application backend Symfony 7.3 (API Platform) pour gestion utilisateurs, abonnements, paiements,
journalisation et sécurité JWT. Base technique stabilisée (containers Docker), premières entités
créées. Pipeline qualité amorcé (tests unitaires + checklist revue + PHPStan niveau 3). Prochaine
phase : enrichir couverture tests, durcir analyse statique (monter progressivement jusqu’à niveau
max pertinent), implémenter cas métiers clés (abonnement / facturation / parental settings) et
sécuriser surface API.

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

- Tests unitaires: démarrés (utilitaire `Assert` 100% couvert)
- Couverture globale actuelle: ~3% (une seule classe testée)
- Mutation testing (Infection): configuré (seuils 80/85) – pas encore exécuté sur un périmètre large
- Checklist revue: `docs/REVIEW_CHECKLIST.md` (doit être utilisée dans chaque PR)
- Analyse statique: PHPStan installé (niveau 3 passé sans erreurs). Reste à monter les niveaux
  (objectif: atteindre palier strict après correction types collections / nullabilité).
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
| Faible couverture tests        | Régressions silencieuses | Élevé  | Prioriser tests sur entités critiques               | Approche TDD services métier  |
| Absence services métier        | Couplage contrôleurs/ORM | Moyen  | Introduire services Application                     | Strate Domain + Value Objects |
| Analyse statique partielle     | Types implicites restants| Moyen  | Monter niveaux 4→6 rapidement                      | Niveau strict + baseline CI   |
| Xdebug actif par défaut        | Performance              | Faible | Basculer via variable ENV                           | Multi-stage build prod        |
| Manque migrations validées CI  | Drift schéma             | Moyen  | Ajouter job `doctrine:migrations:migrate --dry-run` | Validation auto pré-merge     |

## 8. Prochaines Priorités (proposition sprint)

1. Monter PHPStan niveaux 4→6 (résoudre types collections / signatures)
2. Introduire services métier (SubscriptionService, PaymentService) + tests unitaires ciblés
3. Tests API (CRUD principaux + scénarios abonnement) via ApiTestCase
4. Implémenter transitions état abonnement (enum `SubscriptionStatus` + `SubscriptionEvent`)
5. Ajouter événements domaine + logs d’audit automatiques
6. Pipeline CI (tests + stan + couverture) puis injection progressive mutation testing
7. Durcir sécurité (CORS affiner, headers sécurité, rotation clés JWT planifiée)

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

1. Monter PHPStan niveau 4 puis 5 (corriger types manquants) – script `composer stan`
2. Ajouter tests sur entités abonnement + transitions (définir invariants)
3. Définir conventions commit / branche (Conventional Commits)
4. Mettre en place pipeline CI (install cache, phpunit, coverage, phpstan) avant mutation
5. Préparer baseline optionnelle PHPStan uniquement si blocage montée vers niveau strict

---

Document mis à jour le 3 septembre 2025 16h10.
