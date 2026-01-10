# Story 1.2 : Admin Entity & Authentication

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P0 - Critical Path
**Status:** Ready for Review

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

- [x] Admin entity created with proper fields
- [x] UserInterface and PasswordAuthenticatedUserInterface implemented
- [x] Security firewall configured
- [x] Login page functional at /admin/login
- [x] Protected routes redirect to login
- [x] Logout works correctly
- [x] Migration created and applied

---

## Dev Agent Record

### Agent Model Used
Claude Opus 4.5

### File List
- `src/Entity/Admin.php` - Admin entity with UserInterface
- `src/Repository/AdminRepository.php` - Admin repository with PasswordUpgrader
- `src/Controller/SecurityController.php` - Login/logout controller
- `src/Controller/AdminController.php` - Admin dashboard controller
- `templates/security/login.html.twig` - Login form template
- `templates/admin/dashboard.html.twig` - Dashboard template
- `config/packages/security.yaml` - Security firewall configuration
- `migrations/Version20260110231211.php` - Database migration
- `tests/Controller/SecurityControllerTest.php` - Authentication tests

### Change Log
- Created Admin entity with id, username (unique), password fields
- Implemented UserInterface and PasswordAuthenticatedUserInterface
- Created AdminRepository with PasswordUpgraderInterface
- Configured security.yaml with admin firewall and form login
- Created SecurityController with login/logout routes
- Created login template with French labels and "Identifiants incorrects" error
- Created AdminController with dashboard route
- Created dashboard template with logout link
- Generated and applied database migration
- Created 5 tests for authentication flow (18 assertions)

### Completion Notes
- All 6 tests pass (including story 1.1 health test)
- All linting passes (container, YAML, Twig)
- Login page at /admin/login with username/password fields
- Protected /admin routes redirect to login
- Successful login redirects to /admin dashboard
- Invalid credentials show "Identifiants incorrects" message
- Logout at /admin/logout redirects to login page

### DoD Checklist Validation

**1. Requirements Met:**
- [x] All 9 acceptance criteria implemented and tested

**2. Coding Standards & Project Structure:**
- [x] Follows Symfony conventions
- [x] Security best practices (CSRF enabled, password hashing)
- [x] No linter errors

**3. Testing:**
- [x] 5 authentication tests (login page, protected routes, invalid/valid login, logout)
- [x] All 6 tests pass (18 assertions)

**4. Functionality & Verification:**
- [x] All routes verified via debug:router
- [x] Authentication flow tested

**5. Story Administration:**
- [x] All DoD items checked
- [x] Dev Agent Record completed

**6. Dependencies, Build & Configuration:**
- [x] Project builds and lints successfully
- [x] Migration created and applied

**Final Confirmation:**
- [x] Story ready for review
