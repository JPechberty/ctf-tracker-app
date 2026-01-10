# 13. Coding Standards

## 13.1 Core Standards

| Aspect | Standard |
|--------|----------|
| **Style** | PSR-12 |
| **Typage** | `declare(strict_types=1)` obligatoire |
| **Dates** | DateTimeImmutable uniquement |

## 13.2 Critical Rules

| # | Règle |
|---|-------|
| 1 | Toujours utiliser les Repository (pas de DQL dans controllers) |
| 2 | Injection de dépendances (jamais `new Service()`) |
| 3 | DateTimeImmutable (jamais DateTime mutable) |
| 4 | Validation dans les Services (pas dans Controllers) |
| 5 | CSRF sur tous les formulaires |

## 13.3 Naming Conventions

| Element | Convention | Exemple |
|---------|------------|---------|
| Classes | PascalCase | `FlagValidationService` |
| Méthodes | camelCase | `validateSubmission()` |
| Tables DB | snake_case | `team`, `flag` |
| Routes | snake_case | `app_dashboard` |

---
