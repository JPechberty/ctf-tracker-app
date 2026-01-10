# 3. Tech Stack

> **SECTION CRITIQUE** — Cette table est la source de vérité unique pour toutes les technologies.

## 3.1 Cloud Infrastructure

| Aspect | Choix | Rationale |
|--------|-------|-----------|
| **Provider** | Self-hosted / VPS | Événement ponctuel |
| **Serveur web** | Nginx + PHP-FPM | Standard |
| **Déploiement** | Manuel (git pull) | Pas de CI/CD pour MVP |

## 3.2 Technology Stack Table

| Category | Technology | Version | Purpose | Rationale |
|----------|------------|---------|---------|-----------|
| **Language** | PHP | 8.4 | Backend runtime | Dernière version stable |
| **Framework** | Symfony | 7.4 | Application framework | Écosystème mature, LTS |
| **ORM** | Doctrine | 3.x | Data persistence | Standard Symfony |
| **Database** | SQLite | 3.x | Data storage | Fichier unique, zéro config |
| **Admin Panel** | EasyAdmin | 4.x | CRUD admin | Génération automatique |
| **Templates** | Twig | 3.x | Server-side rendering | Intégration native |
| **CSS Framework** | Bootstrap | 5.3.3 | UI styling | CDN, responsive natif |
| **JS Interactivity** | Stimulus | 3.x | Lightweight JS | Timer countdown |
| **Auth** | Symfony Security | 7.4 | Sessions + Firewalls | Natif |
| **Password Hashing** | PasswordHasher | 7.4 | Secure passwords | bcrypt/argon2 |
| **Fixtures** | DoctrineFixturesBundle | 3.x | Test data | Données reproductibles |
| **Unit Testing** | PHPUnit | 10.x | Unit tests | Standard PHP |

## 3.3 Packages Composer

```json
{
    "require": {
        "php": ">=8.4",
        "symfony/framework-bundle": "^7.4",
        "symfony/twig-bundle": "^7.4",
        "symfony/security-bundle": "^7.4",
        "symfony/form": "^7.4",
        "symfony/validator": "^7.4",
        "symfony/asset-mapper": "^7.4",
        "symfony/stimulus-bundle": "^2.0",
        "doctrine/orm": "^3.0",
        "doctrine/doctrine-bundle": "^2.12",
        "easycorp/easyadmin-bundle": "^4.0"
    },
    "require-dev": {
        "doctrine/doctrine-fixtures-bundle": "^3.6",
        "phpunit/phpunit": "^10.0",
        "symfony/browser-kit": "^7.4",
        "symfony/css-selector": "^7.4"
    }
}
```

---
