# 5. Components

## 5.1 SecurityController

**Responsibility:** Authentification des équipes (login/logout)

**Routes:**
- `GET /login` → Formulaire connexion
- `POST /login` → Traitement auth
- `GET /logout` → Déconnexion

## 5.2 TeamController

**Responsibility:** Dashboard équipe + soumission flags

**Routes:**
- `GET /dashboard` → Affiche dashboard
- `POST /dashboard` → Traite soumission flag

**Dependencies:** FlagValidationService, RankingService, SubmissionRepository

## 5.3 LeaderboardController

**Responsibility:** Classement public

**Routes:**
- `GET /leaderboard` → Affiche classement

**Dependencies:** TeamRepository, ChallengeRepository

## 5.4 FlagValidationService

**Responsibility:** Cœur métier — Validation flags selon 6 contrôles

```php
public function validate(Team $team, string $submittedValue): ValidationResult
```

**6 Contrôles séquentiels:**
1. Challenge actif?
2. Format valide? (regex `^{prefix}\{.+\}$`)
3. Flag existe?
4. Flag appartient au challenge?
5. Pas de double soumission?
6. Valeur exacte? (case-sensitive)

## 5.5 RankingService

**Responsibility:** Calcul rang équipe

```php
public function getTeamRank(Team $team): int
```

## 5.6 ValidationResult DTO

```php
class ValidationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly int $points = 0,
    ) {}

    public static function success(int $points, string $message): self
    public static function failure(string $message): self
}
```

## 5.7 Stimulus Timer Controller

```javascript
// assets/controllers/timer_controller.js
export default class extends Controller {
    static values = { endTime: Number, startTime: Number }

    connect() {
        this.update()
        this.interval = setInterval(() => this.update(), 1000)
    }

    update() {
        // Countdown logic...
    }
}
```

---
