# Story 2.2 : Team Authentication

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P0 - Critical Path
**Status:** Ready for Development

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

- [ ] Team firewall configured in security.yaml
- [ ] SecurityController with login/logout routes
- [ ] Login template matching wireframe E1
- [ ] Error message displays correctly
- [ ] Redirect to /dashboard on success
- [ ] /dashboard protected (redirects to /login)
- [ ] Admin and Team firewalls completely separated
