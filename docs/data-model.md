# Modèle de Données — CTF Tracker

**Date de création:** 2026-01-09
**Version:** 1.0
**Statut:** Validé, prêt à coder

---

## Vue d'ensemble

Ce document définit le modèle de données pour la plateforme CTF Tracker, dérivé d'une analyse par use-cases.

### Stack technique
- **Framework:** Symfony 7.4
- **ORM:** Doctrine
- **Base de données:** SQLite

### Entités
- `Admin` — Compte administrateur
- `Challenge` — Événement CTF avec dates
- `Flag` — Flag à capturer (appartient à un Challenge)
- `Team` — Équipe participante (appartient à un Challenge)
- `Submission` — Soumission de flag par une équipe

---

## Diagramme des Relations

```
┌─────────┐
│  Admin  │ (standalone - pas de relation)
└─────────┘

┌───────────┐       1:N       ┌────────┐
│ Challenge │────────────────▶│  Flag  │
└───────────┘                 └────────┘
      │                            │
      │ 1:N                        │ 1:N
      ▼                            ▼
┌──────────┐       N:1       ┌────────────┐
│   Team   │◀────────────────│ Submission │
└──────────┘                 └────────────┘
```

---

## Entités détaillées

### Admin

Compte administrateur pour gérer les challenges, flags et équipes via EasyAdmin.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | integer | PK, auto-increment | Identifiant unique |
| `username` | string(180) | unique, not null | Identifiant de connexion |
| `password` | string(255) | not null | Hash du mot de passe (Symfony PasswordHasher) |

**Rôle Symfony:** `ROLE_ADMIN`

**Doctrine Entity:**
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

    // ... getters/setters
}
```

---

### Challenge

Événement CTF avec période de validité et préfixe de flag personnalisable.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | integer | PK, auto-increment | Identifiant unique |
| `name` | string(255) | not null | Nom du challenge |
| `description` | text | nullable | Description affichée aux équipes |
| `prefix` | string(50) | not null, default "FLAG" | Préfixe des flags (ex: FLAG, CYBER) |
| `startDate` | datetime_immutable | not null | Date/heure de début |
| `endDate` | datetime_immutable | not null | Date/heure de fin |

**Relations:**
- `OneToMany` → Flag (cascade: persist, remove)
- `OneToMany` → Team (cascade: persist, remove)

**Doctrine Entity:**
```php
#[ORM\Entity]
#[ORM\Table(name: 'challenge')]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private string $prefix = 'FLAG';

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\OneToMany(targetEntity: Flag::class, mappedBy: 'challenge', cascade: ['persist', 'remove'])]
    private Collection $flags;

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'challenge', cascade: ['persist', 'remove'])]
    private Collection $teams;

    // ... getters/setters
}
```

**Méthodes utilitaires suggérées:**
```php
public function isActive(): bool
{
    $now = new \DateTimeImmutable();
    return $now >= $this->startDate && $now <= $this->endDate;
}
```

---

### Flag

Flag à capturer, appartenant à un challenge.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | integer | PK, auto-increment | Identifiant unique |
| `name` | string(255) | not null | Nom/label du flag (ex: "Web Exploitation") |
| `value` | string(255) | not null | Valeur secrète (ex: "s3cr3t_c0d3") |
| `points` | integer | not null, default 0 | Points attribués à la validation |
| `challenge_id` | integer | FK → Challenge, not null | Challenge parent |

**Note:** Le flag complet à soumettre = `{challenge.prefix}{flag.value}` (ex: `FLAG{s3cr3t_c0d3}`)

**Relations:**
- `ManyToOne` → Challenge (inversedBy: flags)
- `OneToMany` → Submission (cascade: persist)

**Index:**
- `idx_flag_challenge_value` sur `(challenge_id, value)` — recherche de flag par valeur

**Doctrine Entity:**
```php
#[ORM\Entity]
#[ORM\Table(name: 'flag')]
#[ORM\Index(columns: ['challenge_id', 'value'], name: 'idx_flag_challenge_value')]
class Flag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\Column]
    private int $points = 0;

    #[ORM\ManyToOne(inversedBy: 'flags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Challenge $challenge = null;

    #[ORM\OneToMany(targetEntity: Submission::class, mappedBy: 'flag', cascade: ['persist'])]
    private Collection $submissions;

    // ... getters/setters
}
```

---

### Team

Équipe participante à un challenge.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | integer | PK, auto-increment | Identifiant unique |
| `name` | string(255) | not null | Nom d'équipe (affiché au leaderboard) |
| `username` | string(180) | unique, not null | Identifiant de connexion |
| `password` | string(255) | not null | Hash du mot de passe |
| `score` | integer | not null, default 0 | Score total (dénormalisé pour performance) |
| `challenge_id` | integer | FK → Challenge, not null | Challenge auquel l'équipe participe |

**Rôle Symfony:** `ROLE_TEAM`

**Relations:**
- `ManyToOne` → Challenge (inversedBy: teams)
- `OneToMany` → Submission (cascade: persist)

**Index:**
- `idx_team_challenge` sur `(challenge_id)` — requêtes leaderboard

**Doctrine Entity:**
```php
#[ORM\Entity]
#[ORM\Table(name: 'team')]
#[ORM\Index(columns: ['challenge_id'], name: 'idx_team_challenge')]
class Team implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private int $score = 0;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Challenge $challenge = null;

    #[ORM\OneToMany(targetEntity: Submission::class, mappedBy: 'team', cascade: ['persist'])]
    private Collection $submissions;

    public function getRoles(): array
    {
        return ['ROLE_TEAM'];
    }

    // ... getters/setters
}
```

**Méthodes utilitaires suggérées:**
```php
public function addPoints(int $points): void
{
    $this->score += $points;
}
```

---

### Submission

Enregistrement d'une soumission de flag par une équipe.

| Champ | Type | Contrainte | Description |
|-------|------|------------|-------------|
| `id` | integer | PK, auto-increment | Identifiant unique |
| `team_id` | integer | FK → Team, not null | Équipe qui soumet |
| `flag_id` | integer | FK → Flag, not null | Flag ciblé |
| `submittedValue` | string(255) | not null | Valeur exacte soumise (pour audit) |
| `success` | boolean | not null | Résultat de la validation |
| `submittedAt` | datetime_immutable | not null | Timestamp de soumission |

**Note:** Une Submission n'est créée que si le flag existe en base. Les soumissions pour des flags inexistants retournent une erreur sans enregistrement.

**Relations:**
- `ManyToOne` → Team (inversedBy: submissions)
- `ManyToOne` → Flag (inversedBy: submissions)

**Index:**
- `idx_submission_team_flag_success` sur `(team_id, flag_id, success)` — vérification double soumission

**Doctrine Entity:**
```php
#[ORM\Entity]
#[ORM\Table(name: 'submission')]
#[ORM\Index(columns: ['team_id', 'flag_id', 'success'], name: 'idx_submission_team_flag_success')]
class Submission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Flag $flag = null;

    #[ORM\Column(length: 255)]
    private ?string $submittedValue = null;

    #[ORM\Column]
    private bool $success = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $submittedAt = null;

    public function __construct()
    {
        $this->submittedAt = new \DateTimeImmutable();
    }

    // ... getters/setters
}
```

---

## Logique de validation de flag

Le `FlagValidationService` effectue 6 contrôles dans cet ordre :

| # | Contrôle | Requête/Logique | Message d'erreur |
|---|----------|-----------------|------------------|
| 1 | Challenge actif | `challenge.isActive()` | "Le challenge n'est pas actif" |
| 2 | Format valide | Regex `^{prefix}\{.+\}$` | "Format de flag invalide" |
| 3 | Flag existe | `SELECT FROM Flag WHERE challenge_id = ? AND value = ?` | "Flag incorrect" |
| 4 | Flag appartient au challenge | `flag.challenge_id = team.challenge_id` | "Flag incorrect" |
| 5 | Pas de double soumission | `SELECT FROM Submission WHERE team_id = ? AND flag_id = ? AND success = true` | "Flag déjà validé" |
| 6 | Valeur correcte | Match exact `submittedValue` vs `{prefix}{flag.value}` | "Flag incorrect" |

**Note:** Les messages "Flag incorrect" sont volontairement vagues pour éviter de donner des indices.

---

## Requêtes clés

### Leaderboard
```sql
SELECT name, score
FROM team
WHERE challenge_id = :challengeId
ORDER BY score DESC
```

### Flags validés par une équipe
```sql
SELECT f.name, f.points, s.submittedAt
FROM submission s
JOIN flag f ON s.flag_id = f.id
WHERE s.team_id = :teamId AND s.success = true
ORDER BY s.submittedAt ASC
```

### Vérification double soumission
```sql
SELECT COUNT(*)
FROM submission
WHERE team_id = :teamId AND flag_id = :flagId AND success = true
```

---

## Fixtures de test

Données minimales pour tester l'application :

```yaml
Admin:
  - username: admin
    password: admin123  # hashé via PasswordHasher

Challenge:
  - name: "Hackathon Red Team Cyber 2026"
    description: "Challenge de cybersécurité pour les étudiants"
    prefix: "FLAG"
    startDate: "2026-02-01 09:00:00"
    endDate: "2026-02-01 18:00:00"

Flag:
  - name: "Web Exploitation"
    value: "w3b_m4st3r"
    points: 100
  - name: "Crypto Challenge"
    value: "cr4ck3d_1t"
    points: 250
  - name: "Reverse Engineering"
    value: "r3v3rs3d"
    points: 500

Team:
  - name: "Les Hackers"
    username: "team1"
    password: "team1pass"  # hashé
  - name: "Cyber Squad"
    username: "team2"
    password: "team2pass"  # hashé
  - name: "Binary Breakers"
    username: "team3"
    password: "team3pass"  # hashé
```

---

## Historique des décisions

| Date | Décision | Rationale |
|------|----------|-----------|
| 2026-01-09 | Entités Admin et Team séparées | Séparation claire des responsabilités, pas d'héritage complexe |
| 2026-01-09 | Score dénormalisé sur Team | Performance pour l'affichage du leaderboard |
| 2026-01-09 | flag_id NOT NULL dans Submission | Modèle strict, on ne log que les soumissions pour flags existants |
| 2026-01-09 | Préfixe personnalisable par Challenge | Flexibilité pour différents thèmes de challenges |
| 2026-01-09 | Points statiques sur Flag | Simplicité MVP, pas de formule dynamique |

---

*Document généré lors de la session de brainstorming Architecture/Modèle de données*
*Facilité par Business Analyst Mary — BMAD-METHOD™*
