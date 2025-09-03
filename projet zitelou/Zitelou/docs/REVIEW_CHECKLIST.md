# Checklist Revue de Code Interne

Objectif: inspiré des bonnes pratiques critiques (adaptation "light"). Chaque PR doit cocher les points ou justifier.

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
- [ ] Tests unitaires ajoutés/ajustés
- [ ] Couverture lignes fichier(s) modifié(s) >= 90%
- [ ] Mutation score stable (infection)

## 6. Performance
- [ ] Pas de requête N+1
- [ ] Pas de boucle lourde sur collection volumineuse sans pagination

## 7. Documentation
- [ ] PHPDoc si logique non triviale
- [ ] README / ADR mis à jour si nécessaire

## 8. Qualité Automatisée
- [ ] PHPUnit vert
- [ ] Infection >= seuils (MSI / Covered MSI)
- [ ] Static analysis (à ajouter ultérieurement)

## 9. Maintenance
- [ ] Noms explicites (variables, méthodes)
- [ ] Aucune classe > 500 lignes, méthode > 50 lignes

## 10. Validation Finale
- [ ] Pair review effectuée
- [ ] Justifications déviations listées
