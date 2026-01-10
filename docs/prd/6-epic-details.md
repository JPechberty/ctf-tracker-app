# 6. Epic Details

## Epic 1 : Foundation & Admin Interface

**Goal :** Établir l'infrastructure projet avec authentification admin et gestion complète des entités métier via EasyAdmin. À la fin de cet epic, un administrateur peut se connecter, créer des challenges avec leurs flags, et préparer l'événement.

---

### Story 1.1 : Project Setup & Configuration

**As a** developer,
**I want** a properly configured Symfony 7.4 project with SQLite database,
**so that** I have a solid foundation to build the CTF Tracker application.

**Acceptance Criteria:**

1. Symfony 7.4 project is initialized with standard directory structure (`src/`, `templates/`, `public/`, `config/`)
2. SQLite database is configured in `.env` and `config/packages/doctrine.yaml`
3. Doctrine ORM is installed and configured
4. `bin/console` commands work correctly (`doctrine:database:create`, `doctrine:schema:create`)
5. A basic health check route (`/health`) returns HTTP 200 with "OK" response
6. `.gitignore` is properly configured (excludes `var/`, `vendor/`, `.env.local`, `*.db`)
7. `composer.json` includes all required dependencies (symfony/framework-bundle, doctrine/orm, etc.)

---

### Story 1.2 : Admin Entity & Authentication

**As an** administrator,
**I want** to log in with my credentials,
**so that** I can securely access the administration interface.

**Acceptance Criteria:**

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

### Story 1.3 : EasyAdmin Dashboard & Challenge Management

**As an** administrator,
**I want** to create and manage challenges via EasyAdmin,
**so that** I can set up CTF events with defined time periods.

**Acceptance Criteria:**

1. EasyAdminBundle is installed and configured
2. Admin dashboard is accessible at `/admin` after login
3. `Challenge` entity exists with fields: `id`, `name`, `description` (nullable), `prefix` (default "FLAG"), `startDate`, `endDate`
4. Challenge CRUD is available in EasyAdmin with all fields editable
5. Challenge list displays: name, prefix, start date, end date
6. Challenge form validates that `endDate` is after `startDate`
7. Challenge can be created, edited, and deleted from the admin interface
8. `isActive()` method on Challenge returns true only when current time is between startDate and endDate

---

### Story 1.4 : Flag Management

**As an** administrator,
**I want** to create and manage flags for each challenge,
**so that** teams have objectives to capture during the CTF.

**Acceptance Criteria:**

1. `Flag` entity exists with fields: `id`, `name`, `value`, `points` (default 0), `challenge_id` (FK)
2. Flag has `ManyToOne` relationship with Challenge (cascade persist/remove on Challenge side)
3. Flag CRUD is available in EasyAdmin
4. Flag form includes dropdown to select parent Challenge
5. Flag list displays: name, points, associated challenge name
6. Flag `value` field is displayed as password/hidden in list view (security)
7. Flags are automatically deleted when parent Challenge is deleted (cascade)
8. Index `idx_flag_challenge_value` exists on `(challenge_id, value)` columns

---

### Story 1.5 : Development Fixtures

**As a** developer,
**I want** pre-configured test data loaded via fixtures,
**so that** I can develop and test features efficiently without manual data entry.

**Acceptance Criteria:**

1. DoctrineFixturesBundle is installed
2. `AppFixtures` class creates:
   - 1 admin account (username: `admin`, password: `admin123` hashed)
   - 1 challenge "Hackathon Red Team Cyber 2026" with appropriate dates and prefix "FLAG"
   - 3 flags with varying points (100, 250, 500)
3. Fixtures can be loaded with `bin/console doctrine:fixtures:load`
4. Fixtures use Symfony PasswordHasher to hash admin password
5. Fixtures are idempotent (can be re-run safely with `--purge-with-truncate`)
6. README or comment documents the fixture loading command

---

## Epic 2 : Team Authentication & Dashboard

**Goal :** Permettre aux équipes de se connecter et d'accéder à leur dashboard avec affichage du score, du rang et du timer. À la fin de cet epic, les équipes peuvent se connecter et visualiser leur progression (sans encore pouvoir soumettre de flags).

---

### Story 2.1 : Team Entity & Admin Management

**As an** administrator,
**I want** to create and manage team accounts via EasyAdmin,
**so that** teams can participate in the CTF challenge.

**Acceptance Criteria:**

1. `Team` entity exists with fields: `id`, `name`, `username` (unique), `password` (hashed), `score` (default 0), `challenge_id` (FK)
2. Team implements `UserInterface` and `PasswordAuthenticatedUserInterface`
3. Team has role `ROLE_TEAM` returned by `getRoles()`
4. Team has `ManyToOne` relationship with Challenge
5. Team CRUD is available in EasyAdmin
6. Team form includes: name, username, password field, dropdown to select Challenge
7. Team list displays: name, username, score, associated challenge name
8. Password is hashed automatically when creating/updating a team via EasyAdmin
9. Index `idx_team_challenge` exists on `(challenge_id)` column
10. `addPoints(int $points)` method exists to increment team score

---

### Story 2.2 : Team Authentication

**As a** team member,
**I want** to log in with my team credentials,
**so that** I can access our team dashboard.

**Acceptance Criteria:**

1. Separate security firewall configured for team authentication (`team` firewall)
2. Team login page exists at `/login` (public route)
3. Login page matches wireframe E1: centered form with logo, challenge name, username/password fields
4. Login form displays challenge name dynamically (or generic title if multiple challenges)
5. Successful login redirects to `/dashboard`
6. Invalid credentials display error message "Identifiants incorrects" below the form
7. `/dashboard` route is protected and redirects unauthenticated users to `/login`
8. Logout functionality works at `/logout`
9. Admin and Team authentication are completely separated (different firewalls, different login pages)

---

### Story 2.3 : Team Dashboard - Score & Rank Display

**As a** team member,
**I want** to see my current score and ranking position,
**so that** I know how I'm performing compared to other teams.

**Acceptance Criteria:**

1. Dashboard page exists at `/dashboard` (requires ROLE_TEAM)
2. Dashboard displays team name in header
3. Dashboard displays current score in a prominent card (e.g., "450 pts")
4. Dashboard displays current rank position (e.g., "#3")
5. Rank is calculated dynamically: position among all teams of the same challenge, ordered by score descending
6. Teams with equal scores share the same rank (ties handled)
7. Dashboard layout matches wireframe E2 structure (header + score card + rank card)
8. Logout button is visible in the header

---

### Story 2.4 : Team Dashboard - Timer & Validated Flags

**As a** team member,
**I want** to see the countdown timer and my list of validated flags,
**so that** I can track time remaining and review my achievements.

**Acceptance Criteria:**

1. Dashboard displays countdown timer showing time remaining until challenge `endDate`
2. Timer updates every second via JavaScript (no server polling)
3. Timer format is `HH:MM:SS`
4. Timer displays "TERMINÉ" when challenge has ended
5. Timer displays countdown to start if challenge hasn't begun yet
6. Dashboard displays "FLAGS VALIDÉS (X/Y)" section where X = validated, Y = total flags in challenge
7. Validated flags list shows: flag name and points for each validated flag
8. If no flags validated, display message "Aucun flag validé pour le moment"
9. Validated flags are ordered by validation time (earliest first)
10. Dashboard includes link/button "Voir le leaderboard" navigating to `/leaderboard`

---

### Story 2.5 : Team Fixtures

**As a** developer,
**I want** test team accounts in fixtures,
**so that** I can test the team login and dashboard experience.

**Acceptance Criteria:**

1. `AppFixtures` is extended to create 3 test teams:
   - "Les Hackers" (username: `team1`, password: `team1pass`)
   - "Cyber Squad" (username: `team2`, password: `team2pass`)
   - "Binary Breakers" (username: `team3`, password: `team3pass`)
2. All teams are associated with the test challenge created in Story 1.5
3. Team passwords are hashed using Symfony PasswordHasher
4. Teams have initial score of 0
5. Fixture loading still works with single command `bin/console doctrine:fixtures:load`

---

## Epic 3 : Flag Submission, Scoring & Leaderboard

**Goal :** Implémenter le cœur CTF (validation de flags, calcul de score) et le leaderboard public. À la fin de cet epic, le MVP est complet : les équipes soumettent des flags, gagnent des points, et le classement est visible publiquement.

---

### Story 3.1 : Submission Entity

**As a** developer,
**I want** a Submission entity to record all flag submission attempts,
**so that** we have a complete audit trail for analysis and cheat detection.

**Acceptance Criteria:**

1. `Submission` entity exists with fields: `id`, `team_id` (FK), `flag_id` (FK), `submittedValue`, `success` (boolean), `submittedAt` (DateTimeImmutable)
2. Submission has `ManyToOne` relationship with Team
3. Submission has `ManyToOne` relationship with Flag
4. `submittedAt` is automatically set to current time in constructor
5. Index `idx_submission_team_flag_success` exists on `(team_id, flag_id, success)` columns
6. Submission CRUD is available in EasyAdmin (read-only for admin audit)
7. EasyAdmin Submission list displays: team name, flag name, submitted value, success status, timestamp
8. EasyAdmin allows filtering submissions by team, by flag, by success status

---

### Story 3.2 : Flag Validation Service

**As a** developer,
**I want** a FlagValidationService implementing 6 validation controls,
**so that** flag submissions are properly validated with appropriate error messages.

**Acceptance Criteria:**

1. `FlagValidationService` class exists in `src/Service/`
2. Service implements `validateSubmission(Team $team, string $submittedValue): ValidationResult`
3. `ValidationResult` contains: `success` (bool), `message` (string), `points` (int, 0 if failed), `flag` (Flag|null)
4. **Control 1 - Challenge Active:** Returns error "Le challenge n'est pas actif" if challenge is not active
5. **Control 2 - Format Valid:** Returns error "Format de flag invalide" if submitted value doesn't match pattern `{prefix}{...}` (e.g., `FLAG{...}`)
6. **Control 3 - Flag Exists:** Returns error "Flag incorrect" if no flag matches the submitted value
7. **Control 4 - Flag Belongs to Challenge:** Returns error "Flag incorrect" if flag doesn't belong to team's challenge
8. **Control 5 - Not Already Submitted:** Returns error "Flag déjà validé" if team already has a successful submission for this flag
9. **Control 6 - Value Correct:** Returns error "Flag incorrect" if exact match fails (case-sensitive)
10. Validation controls are executed in order 1→6, stopping at first failure
11. On success, returns message "Flag validé !" with flag points
12. Unit tests cover all 6 failure cases plus success case (minimum 7 tests)

---

### Story 3.3 : Flag Submission Form & Scoring

**As a** team member,
**I want** to submit flags via a form on my dashboard and see immediate feedback,
**so that** I know if my submission was correct and see my updated score.

**Acceptance Criteria:**

1. Dashboard displays "SOUMETTRE UN FLAG" card with input field and "Valider" button
2. Form displays expected format hint (e.g., "Format attendu: FLAG{...}") based on challenge prefix
3. Form submission calls `FlagValidationService`
4. All submissions (success and failure) are persisted as Submission entities
5. On successful validation:
   - Team score is updated synchronously (`team.addPoints(flag.points)`)
   - Success message displays: "✅ Flag validé ! +{points} points"
   - Input field is cleared
   - Validated flags list updates to show new flag
   - Score and rank cards update with new values
6. On failed validation:
   - Error message displays below input (e.g., "❌ Flag incorrect")
   - Input field retains submitted value for correction
7. Feedback is displayed inline below the input field (not modal/toast)
8. Form can be submitted via Enter key or button click

---

### Story 3.4 : Public Leaderboard

**As a** visitor (spectator, teacher, or team member),
**I want** to view the public leaderboard without authentication,
**so that** I can see the current rankings and project it on a big screen.

**Acceptance Criteria:**

1. Leaderboard page exists at `/leaderboard` (public, no authentication required)
2. Leaderboard displays challenge name as title
3. Leaderboard displays countdown timer (same JavaScript logic as dashboard)
4. Leaderboard lists all teams ordered by score descending
5. Top 3 teams display medal icons (🥇 🥈 🥉)
6. Each row shows: rank, team name, score (formatted with spacing/dots)
7. Teams with equal scores share the same rank
8. Footer displays total number of flags available (e.g., "🚩 10 flags disponibles")
9. "Actualiser" button refreshes the page
10. Layout matches wireframe E3, optimized for projection on large screen
11. Leaderboard is responsive (mobile layout with stacked entries)

---

### Story 3.5 : Challenge States & Final Polish

**As a** user,
**I want** the interface to properly reflect challenge states,
**so that** I understand whether the challenge is upcoming, active, or ended.

**Acceptance Criteria:**

1. **Before challenge starts:**
   - Dashboard shows "⏳ CHALLENGE À VENIR" message with countdown to start
   - Submission form is hidden/disabled
   - Leaderboard shows "Le classement sera affiché au démarrage du challenge"
2. **During active challenge:**
   - Full functionality available (submission, scoring, leaderboard)
   - Timer shows remaining time
3. **After challenge ends:**
   - Dashboard shows "⏱️ CHALLENGE TERMINÉ" message
   - Submission form is hidden/disabled
   - Dashboard displays "Consultez le classement final" with leaderboard link
   - Leaderboard shows "CHALLENGE TERMINÉ - Classement final"
   - Leaderboard data is frozen (reflects final scores)
4. All pages are responsive and display correctly on mobile (< 768px)
5. All feedback messages use consistent styling (green success, red error)
6. Timer displays "TERMINÉ" when challenge has ended
7. All error states display user-friendly messages (no technical errors exposed)

---
