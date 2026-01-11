# Story 2.2 : Team Authentication

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P0 - Critical Path
**Status:** Ready for Review

---

## User Story

**As a** team member,
**I want** to log in with my team credentials,
**so that** I can access our team dashboard.

---

## Acceptance Criteria

1. Separate security firewall configured for team authentication (`main` firewall)
2. Team login page exists at `/login` (public route)
3. Login page matches wireframe E1: centered form with logo, challenge name, username/password fields
4. Login form displays challenge name dynamically (or generic title if multiple challenges)
5. Successful login redirects to `/dashboard`
6. Invalid credentials display error message "Identifiants incorrects" below the form
7. `/dashboard` route is protected and redirects unauthenticated users to `/login`
8. Logout functionality works at `/logout`
9. Admin and Team authentication are completely separated (different firewalls, different login pages)

---

## Technical Notes

**Architecture Reference:** `docs/architecture/15-security.md`, `docs/architecture/5-components.md`

**Security Configuration (security.yaml):**
```yaml
security:
    providers:
        team_provider:
            entity:
                class: App\Entity\Team
                property: username

    firewalls:
        main:
            lazy: true
            provider: team_provider
            form_login:
                login_path: app_login
                check_path: app_login
                default_target_path: app_dashboard
            logout:
                path: app_logout
                target: app_login

    access_control:
        - { path: ^/login$, roles: PUBLIC_ACCESS }
        - { path: ^/dashboard, roles: ROLE_TEAM }
```

**SecurityController:**
```php
class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Handled by Symfony Security
    }
}
```

**Template (login.html.twig):**
```twig
{% extends 'base.html.twig' %}

{% block body %}
<div class="container">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body text-center">
          <h1 class="mb-4">🏴 CTF TRACKER</h1>
          {% if error %}
            <div class="alert alert-danger">❌ Identifiants incorrects</div>
          {% endif %}
          <form method="post">
            <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">
            <div class="mb-3">
              <input type="text" name="_username" class="form-control" placeholder="Identifiant" value="{{ last_username }}" required autofocus>
            </div>
            <div class="mb-3">
              <input type="password" name="_password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
{% endblock %}
```

---

## Dependencies

- Story 2.1 (Team Entity)

---

## Definition of Done

- [x] Team firewall configured in security.yaml
- [x] SecurityController with login/logout routes
- [x] Login template matching wireframe E1
- [x] Error message displays correctly
- [x] Redirect to /dashboard on success
- [x] /dashboard protected (redirects to /login)
- [x] Admin and Team firewalls completely separated

---

## Dev Agent Record

### File List

| File | Action |
|------|--------|
| config/packages/security.yaml | Modified |
| src/Controller/SecurityController.php | Modified |
| src/Controller/DashboardController.php | Created |
| templates/base.html.twig | Modified |
| templates/security/login.html.twig | Created |
| templates/security/admin_login.html.twig | Renamed from login.html.twig |
| templates/dashboard/index.html.twig | Created |
| tests/Controller/SecurityControllerTest.php | Modified |

### Change Log

- Added `team_provider` to security.yaml for Team entity authentication
- Configured `main` firewall with form_login for team authentication at `/login`
- Added access_control rules for `/login` (PUBLIC_ACCESS) and `/dashboard` (ROLE_TEAM)
- Added team `login()` and `logout()` routes to SecurityController
- Renamed admin template to `admin_login.html.twig` to separate from team login
- Created team login template with Bootstrap styling matching wireframe E1
- Added Bootstrap CSS via CDN to base.html.twig
- Created DashboardController with protected `/dashboard` route
- Created dashboard template with logout button
- Added 7 team authentication tests covering all acceptance criteria

### Completion Notes

- All 50 tests pass (43 existing + 7 new team auth tests)
- Admin and Team firewalls are completely separated
- Team login uses `main` firewall, Admin login uses `admin` firewall

### Agent Model Used

Claude Opus 4.5
