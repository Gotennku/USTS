# Module StripeIntegration

## Objectif
Extraire une couche Stripe découplée des entités Doctrine pour :
- Faciliter la réutilisation (dépendances via ports/interfaces)
- Simplifier les tests unitaires (mock ports + client Stripe)
- Préparer évolution (persistance d'abonnements, portail billing, webhooks) sans coupler directement contrôleurs et SDK.

## Architecture
```
StripeIntegration/
  Checkout/
    CheckoutServiceInterface.php
    CheckoutService.php
    CheckoutSessionInput.php
    CheckoutSessionResult.php
  Port/
    CustomerProviderInterface.php
    PlanPriceProviderInterface.php
    SubscriptionPersisterInterface.php (future persistance abstraite)
  Adapter/
    DoctrineCustomerProvider.php
    DoctrinePlanPriceProvider.php
```

### DTOs
- `CheckoutSessionInput`: (userId, planId, successUrl, cancelUrl)
- `CheckoutSessionResult`: (url)

### Ports
- `CustomerProviderInterface` : retourne ou crée un customer Stripe pour un user métier.
- `PlanPriceProviderInterface` : résout le `price_id` Stripe associé à un plan interne.
- `SubscriptionPersisterInterface` : (placeholder) scellera la logique d'enregistrement / mise à jour d'abonnements.

### Service
`CheckoutService` orchestration checkout subscription :
1. Résolution du price via `PlanPriceProviderInterface`.
2. Résolution/ensure du customer via `CustomerProviderInterface`.
3. Création session Checkout Stripe (mode=subscription) avec métadonnées (user_id, plan_id) utilisées plus tard pour les webhooks.

### Adapters Doctrine
- `DoctrineCustomerProvider` : crée un customer Stripe si `stripeCustomerId` absent sur l'entité User, flush, retourne l'id.
- `DoctrinePlanPriceProvider` : lit `stripePriceId` depuis l'entité `SubscriptionPlan`.

## Intégration Controller
`StripeCheckoutController` reçoit `CheckoutServiceInterface` (DI). Le contrôleur:
1. Charge plan Doctrine.
2. Récupère user depuis token.
3. Construit `CheckoutSessionInput`.
4. Retourne JSON avec `checkout_url`.

Le portail billing (endpoint `/api/stripe/portal`) restera 501 jusqu'à ajout d'un `BillingPortalService` dédié (analogue au checkout).

## Tests
- `StripeCheckoutApiTest` : stub de `CheckoutServiceInterface` pour isoler HTTP/auth.
- `CheckoutServiceTest` : test unitaire du flux succès (mock `StripeClientFactory` + ports).
- Webhook resté dans couche legacy `Service\Stripe` pour transition progressive.

## Roadmap Migration Webhooks
1. Introduire un port `WebhookEventProcessor` ou découper par type (SubscriptionEventsHandler, InvoiceEventsHandler).
2. Déplacer logique d'idempotence / persistance vers un adapter (Doctrine) implémentant de nouveaux ports.
3. Enrichir `SubscriptionPersisterInterface` (create/update/cancel + recordPayment(status)).
4. Ajouter tests unitaires ciblés + tests fonctionnels multi-event.

## Avantages Attendus
- Tests rapides (unitaires) sans démarrage complet Kernel quand non nécessaire.
- Remplacement simple des adapters (ex: autre storage) sans toucher au service métier StripeIntegration.
- Point d'entrée unique pour la création de sessions checkout ; évite duplication de logique (métadonnées cohérentes).

## Améliorations Futures
- Ajouter validation explicite (Assert) sur DTO (`successUrl`, `cancelUrl`).
- Gérer idempotency key (header) lors création session Stripe.
- Centraliser erreurs Stripe en exceptions domaine (ex: PriceNotConfiguredException).
- Implémenter BillingPortalService + tests.
- Couverture : viser >90% lignes sur namespace `StripeIntegration`.

## Sécurité & Observabilité
- Métadonnées limitées (user_id, plan_id) pour corrélation côté webhook.
- Ajouter plus tard un logger dédié (canal `stripe`) pour debug.
- Vérifier exceptions remontées côté API ne divulguent pas d'info sensible Stripe.

## Suppressions planifiées
- Ancien `StripeCheckoutService` sera retiré après portail migré.
- Refactoring `StripeWebhookHandler` en plusieurs composants modulaires.

---
Documentation générée le 4 septembre 2025.
