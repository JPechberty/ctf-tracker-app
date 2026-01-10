# Story 1.2 : Admin Entity & Authentication

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P0 - Critical Path
**Status:** Ready for Development

---

## User Story

**As an** administrator,
**I want** to log in with my credentials,
**so that** I can securely access the administration interface.

---

## Acceptance Criteria

1. `Admin` entity exists with fields: `id`, `username` (unique), `password` (hashed)
2. Admin implements `UserInterface` and `PasswordAuthenticatedUserInterface`
3. Admin has role `ROLE_ADMIN` returned by `getRoles()`
4. Security firewall is configured for admin authentication via form login
5. Login page exists at `/admin/login` with username and password fields
6. Successful login redirects to `/admin` dashboard
7. Invalid credentials display error message "Identifiants incorrects"
8. `/admin/*` routes are protected and redirect unauthenticated users to login
9. Logout functionality works at `/admin/logout`

---

## Technical Notes

**Architecture Reference:** `docs/architecture/4-data-models.md`, `docs/architecture/15-security.md`

**Entity Structure:**
```php
#[ORM\Entity]
#[ORM\Table(name: 'admin')]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $password = null;

    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }
}
```

**Security Configuration (security.yaml):**
```yaml
security:
    providers:
        admin_provider:
            entity:
                class: App\Entity\Admin
                property: username

    firewalls:
        admin:
            pattern: ^/admin
            lazy: true
            provider: admin_provider
            form_login:
                login_path: admin_login
                check_path: admin_login
                default_target_path: /admin
            logout:
                path: admin_logout
                target: admin_login

    access_control:
        - { path: ^/admin/login$, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

---

## Dependencies

- Story 1.1 (Project Setup)

---

## Definition of Done

- [ ] Admin entity created with proper fields
- [ ] UserInterface and PasswordAuthenticatedUserInterface implemented
- [ ] Security firewall configured
- [ ] Login page functional at /admin/login
- [ ] Protected routes redirect to login
- [ ] Logout works correctly
- [ ] Migration created and applied
