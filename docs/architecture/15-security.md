# 15. Security

## 15.1 Overview

| Aspect | Implementation |
|--------|----------------|
| **Auth** | Sessions Symfony |
| **Passwords** | bcrypt/argon2 |
| **CSRF** | Tokens Symfony Forms |
| **XSS** | Twig auto-escape |
| **SQLi** | Doctrine parameterized |

## 15.2 Dual Firewall

```yaml
security:
    firewalls:
        admin:
            pattern: ^/admin
            provider: admin_provider
        main:
            pattern: ^/
            provider: team_provider
```

## 15.3 Access Control

```yaml
access_control:
    - { path: ^/admin/login$, roles: PUBLIC_ACCESS }
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/dashboard, roles: ROLE_TEAM }
    - { path: ^/leaderboard, roles: PUBLIC_ACCESS }
```

## 15.4 Security Headers (Nginx)

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
```

---
