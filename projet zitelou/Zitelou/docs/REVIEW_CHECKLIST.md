# Checklist Revue de Code Interne

Objectif: inspiré des bonnes pratiques critiques (adaptation "light"). Chaque PR doit cocher les
points ou justifier.

## 1. Général

- [ ] Nom de branche descriptif
- [ ] Description claire (pourquoi + quoi)
- [ ] Ticket/référence liée

## 2. Conception

- [ ] Pas d'effet de bord caché
- [ ] Complexité cyclomatique raisonnable (méthodes <= 10 idéal)
- [ ] Aucune duplication significative (vérifier phpcpd)

## 3. Sécurité

- [ ] Entrées validées / assertions (ex: `Assert` interne)
- [ ] Pas d'injection SQL (paramètres préparés Doctrine)
- [ ] Aucune donnée sensible loggée

## 4. Erreurs & Exceptions

- [ ] Messages d'erreur explicites
- [ ] Exceptions spécifiques préférées aux génériques

## 5. Tests

- [ ] Tests unitaires ajoutés/ajustés (nouvelle logique couverte)
- [ ] Couverture lignes des fichiers modifiés >= 90% (vérifier rapport HTML ou `--coverage-text`)
- [ ] Aucun test purement artificiel (éviter uniquement getters/setters sans logique) ajouté pour gonfler la métrique
- [ ] Mutation score stable (Infection) ou justification (zone complexe / faux positifs)
- [ ] Tests API ajoutés si endpoints REST/GraphQL modifiés

## 6. Performance

- [ ] Pas de requête N+1
- [ ] Pas de boucle lourde sur collection volumineuse sans pagination

## 7. Documentation

- [ ] PHPDoc si logique non triviale
- [ ] README / ADR mis à jour si nécessaire

## 8. Qualité Automatisée

- [ ] PHPUnit vert (CI / local)
- [ ] Infection >= seuils (MSI / Covered MSI) ou exécution planifiée si volumineux
- [ ] Analyse statique PHPStan (niveau courant: 4, objectif: augmenter) sans nouvelles régressions
- [ ] Alerte N+1 via profiler Symfony vérifiée si requêtes ajoutées
- [ ] Pas de TODO laissé sans ticket référencé

## 9. Maintenance

- [ ] Noms explicites (variables, méthodes, DTO, services)
- [ ] Aucune classe > 500 lignes, méthode > 50 lignes (sinon refactor planifié)
- [ ] Pas de logique métier dans contrôleur (redirigée vers service / domaine) si ajout significatif
- [ ] Relations Doctrine cohérentes (nullable vs non-nullable) + migration associée générée
- [ ] Collections typées via phpdoc generics

## 10. Sécurité & Données

- [ ] Aucune clé secrète commitée / diff contenant secrets
- [ ] Données personnelles minimisées (privacy by design)
- [ ] Champs sensibles (mot de passe, token) jamais loggés ou exposés en réponse

## 11. Validation Finale

- [ ] Pair review effectuée
- [ ] Justifications déviations listées (section PR)
- [ ] Migration exécutée localement (le cas échéant) + résultat `doctrine:schema:validate` OK
- [ ] Changelog / revue de jour mise à jour si impact visible
