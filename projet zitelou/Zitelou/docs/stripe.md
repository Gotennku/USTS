# Intégration Stripe

## Endpoints

- POST `/api/stripe/checkout/session/{planId}` crée une session Checkout (mode subscription) et retourne `checkout_url`.
- POST `/api/stripe/portal` ouvre le portail de facturation (Billing Portal) Stripe.
- POST `/api/stripe/webhook` endpoint webhook (événements: `customer.subscription.created|updated|deleted`, `invoice.payment_succeeded|failed`).

## Metadata

Les métadonnées `user_id` et `plan_id` sont ajoutées à la session Checkout ET à la subscription (via `subscription_data.metadata`) pour corréler les événements.

## Flux principal

1. Le front appelle `POST /api/stripe/checkout/session/{planId}`.
2. Redirection vers l'URL de Checkout renvoyée.
3. Stripe crée la subscription -> webhook `customer.subscription.created`.
4. L'app associe Subscription à l'utilisateur et au plan, status `ACTIVE`.
5. Chaque facture payée déclenche `invoice.payment_succeeded` -> création d'un `Payment` (status `SUCCEEDED`).
6. Échec de paiement -> `invoice.payment_failed` -> enregistrement Payment (status `FAILED`).
7. Annulation -> `customer.subscription.deleted` -> status `CANCELLED`.
8. Expiration (si période passée) traitée lors d'un update -> status `EXPIRED`.

## Entités impactées

- `Subscription`: champs `stripeSubscriptionId`, `status`, `plan`, `user`.
- `Payment`: un enregistrement par facture payée (montant en float, converti depuis cents).
- `StripeWebhookLog`: trace brute + indicateur `processed`.
- `SubscriptionHistory`: enregistrement des événements métier (CREATED, RENEWED, CANCELLED, EXPIRED).

## Variables d'environnement

```
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx (optionnel en dev)
```

## Sécurité webhook

Si `STRIPE_WEBHOOK_SECRET` défini, la signature Stripe est vérifiée. En absence (environnement de dev), l'event est construit sans vérification (à ne pas utiliser en production).

## Tests recommandés (à ajouter)

- Création session Checkout mockée.
- Traitement webhook `customer.subscription.created` (assert Subscription créée).
- Traitement `invoice.payment_succeeded` (assert Payment créé, idempotence).

## Prochaines améliorations

- Gérer prorata / upgrades.
- Gérer retries échec paiement avec notification utilisateur.
- Support multi-devises (actuellement currency pris depuis l'event).
- Validation plan ↔ price Stripe par synchronisation automatique.
