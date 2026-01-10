# 4. Technical Assumptions

## 4.1 Repository Structure

**Monorepo** - Application Symfony unique

- Un seul repository Git contenant tout le code
- Structure standard Symfony (`src/`, `templates/`, `public/`, etc.)
- Pas de séparation frontend/backend (Twig intégré)

## 4.2 Service Architecture

**Monolith** - Application Symfony monolithique

| Composant | Technologie | Rationale |
|-----------|-------------|-----------|
| Framework | Symfony 7.4 | Écosystème mature, bundles éprouvés |
| Runtime | PHP 8.4 | Dernière version stable, performances |
| Base de données | SQLite | Fichier unique, zéro configuration, suffisant pour 10 équipes |
| ORM | Doctrine | Standard Symfony, migrations intégrées |
| Admin | EasyAdmin 4.x | CRUD automatique, gain de temps considérable |
| Templates | Twig | Intégration native Symfony, sécurité XSS |
| Auth | Sessions Symfony | Simplicité, pas de complexité JWT |
| Interactivité | JavaScript vanilla | Timer countdown, feedback léger |

## 4.3 Testing Requirements

**Unit + Integration** - Stratégie de test pragmatique

| Type | Scope | Priorité |
|------|-------|----------|
| Unit Tests | `FlagValidationService` (6 contrôles de validation) | **Critique** |
| Unit Tests | Méthodes utilitaires des entités (`isActive()`, `addPoints()`) | Haute |
| Functional Tests | Routes publiques (login, leaderboard) | Moyenne |
| Functional Tests | Soumission de flag (parcours complet) | Haute |
| E2E Tests | Non prévu pour MVP | Basse |

## 4.4 Additional Technical Assumptions

- **Fixtures DoctrineFixturesBundle** : Données de test pré-configurées (admin, challenges, flags, équipes)
- **PasswordHasher Symfony** : Hashage sécurisé des mots de passe (bcrypt/argon2)
- **Pas de rate limiting MVP** : Simplicité, peut être ajouté post-MVP si abus détectés
- **Pas de cache** : Score calculé synchrone, pas de Redis/Memcached
- **Déploiement** : Serveur web standard (Apache/Nginx + PHP-FPM), fichier SQLite sur filesystem
- **Pas de CI/CD** : Déploiement manuel pour cet événement ponctuel
- **Index DB** : `idx_flag_challenge_value` et `idx_submission_team_flag_success` pour performance

---
