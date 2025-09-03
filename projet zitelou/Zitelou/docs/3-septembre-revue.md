# Revue Projet – 3 septembre 2025 18h10

## 1. Résumé Exécutif

Application backend Symfony 7.3 (API Platform) pour gestion utilisateurs, abonnements, paiements,
journalisation et sécurité JWT. Base technique stabilisée (containers Docker), premières entités
créées. Pipeline qualité renforcé : 20 tests verts (69 assertions) après ajout test fonctionnel
Stripe (création subscription via webhook simulé). PHPStan niveau 4 (0 erreurs) stable. Prochaine
phase : activer driver de couverture (Xdebug/PCOV), monter niveaux 5+, compléter scénarios Stripe
(payments invoices, idempotence complète), introduire services métier & tests API bout‑à‑bout.

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

- Tests: 19 tests / 66 assertions (persistance, relations, transitions simples + couverture
  généralisée via réflexion). Pas encore de driver couverture actif (warning PHPUnit) → activer
  Xdebug ou PCOV pour chiffres réels.
- Mutation testing (Infection): configuré (seuils 80/85) – pas encore exécuté sur un périmètre large
- Checklist revue: `docs/REVIEW_CHECKLIST.md` (doit être utilisée dans chaque PR)
- Analyse statique: PHPStan niveau 4 OK (0 erreurs). Nullabilité harmonisée en rendant certaines
  colonnes DB nullable (à confirmer via migration) + ajout generics Collection. Cible prochaine:
  niveaux 5→6 puis niveau max avec éventuellement baseline si nécessaire.

### Couverture (à activer)

Driver de couverture toujours non activé (Xdebug/PCOV absent à l'exécution de la suite → aucune
stat actuelle fiable). Anciennes mesures locales (avant refactor Stripe) montraient une couverture
élevée artificiellement (tests réflexifs getters/setters). Objectif : réactiver collecte puis
réorienter vers tests comportement métier (abonnements, paiements, expiration, sécurité).

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

## 7. Stripe – État Intégration (ajout 18h10)

Implémenté :
- Client `StripeClientFactory` (version API figée 2024-06-20)
- Checkout abonnement (`/api/stripe/checkout/session/{id}`) + Billing Portal
- Webhooks: subscription created/updated/deleted, invoice payment succeeded/failed
- Métadonnées user_id / plan_id propagées dans `subscription_data`
- Création Subscription & Payment, historisation (SubscriptionHistory), logging (StripeWebhookLog)

Lacunes clés :
- Idempotence par event.id absente (risque de duplications si retry Stripe)
- Status intermédiaires Stripe (incomplete, past_due, trialing) non mappés
- Montants Payment en float (précision) – préférer int (cents)
- Pas de test invoices (paiement réussi/échec) ni cancel
- Pas d’endpoint d’annulation volontaire / upgrade plan
- Pas de synchronisation auto plans ↔ prices Stripe
- Handler public exposé directement (à restreindre en prod)

Priorité prochaine itération : idempotence + tests invoices + migration montant en cents.

## 8. Dette & Risques

| Zone                           | Risque                   | Impact | Mitigation courte                                   | Mitigation long terme         |
| ------------------------------ | ------------------------ | ------ | --------------------------------------------------- | ----------------------------- |
| Couverture réelle inactive (driver manquant) | Régressions silencieuses | Élevé  | Installer driver (Xdebug / PCOV) | Tests métier ciblés + suppression tests réflexifs |
| Absence services métier        | Couplage contrôleurs/ORM | Moyen  | Introduire services Application                     | Strate Domain + Value Objects |
| Idempotence webhooks incomplète| Replay Stripe dupliqué   | Moyen  | Stocker event.id + contrainte unique                | File asynchrone + reprocessing |
| Float pour montants            | Erreurs arrondi          | Moyen  | Migrer vers int cents                               | Value Object Money             |
| Mapping status Stripe minimal  | États incohérents        | Moyen  | Étendre mapping + transitions                        | Machine à états / domain rules |
| Analyse statique partielle     | Types implicites restants| Moyen  | Monter niveaux 4→6 rapidement                      | Niveau strict + baseline CI   |
| Couverture code inconnue       | Manque de métriques      | Moyen  | Activer Xdebug/PCOV                                 | Intégration CI reporting      |
| Xdebug actif par défaut        | Performance              | Faible | Basculer via variable ENV                           | Multi-stage build prod        |
| Manque migrations validées CI  | Drift schéma             | Moyen  | Ajouter job `doctrine:migrations:migrate --dry-run` | Validation auto pré-merge     |

## 8. Prochaines Priorités (proposition sprint)

1. Activer driver couverture (Xdebug déjà partiellement configuré, vérifier extension active / sinon PCOV)
2. Monter PHPStan niveaux 5→6 (collections, generics, règles additionnelles doctrine)
3. Générer migration pour refléter colonnes désormais nullable (relations ManyToOne/OneToOne)
4. Services métier (SubscriptionService, PaymentService) + tests transitions complexes
5. Tests API (CRUD + scénarios abonnement end-to-end)
6. Pipeline CI (tests + stan + couverture) puis mutation progressive
7. Événements domaine + audit automatique / durcir sécurité (CORS, headers, rotation JWT)

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

1. Activer Xdebug/PCOV dans environnement test (résoudre warning "No code coverage driver available")
2. Monter PHPStan niveau 5 puis 6 – script `composer stan`
3. Générer / appliquer migration pour nouvelles nullabilités relationnelles
4. Introduire services métier + tests transitions abonnement avancées (annulation, expiration, renouvellement)
5. Définir conventions commit / branche (Conventional Commits)
6. Pipeline CI (install cache, phpunit, coverage, phpstan) avant mutation
7. Baseline PHPStan uniquement si blocage montée niveau max

---

Document mis à jour le 3 septembre 2025 18h10.
