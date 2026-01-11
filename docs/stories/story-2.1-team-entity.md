# Story 2.1 : Team Entity & Admin Management

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P0 - Critical Path
**Status:** Ready for Review

---

## User Story

**As an** administrator,
**I want** to create and manage team accounts via EasyAdmin,
**so that** teams can participate in the CTF challenge.

---

## Acceptance Criteria

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

## Technical Notes

**Architecture Reference:** `docs/architecture/4-data-models.md`

**Entity Structure:**
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

    public function getRoles(): array
    {
        return ['ROLE_TEAM'];
    }

    public function addPoints(int $points): void
    {
        $this->score += $points;
    }
}
```

**EasyAdmin Password Hashing:**
```php
class TeamCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('username');
        yield TextField::new('password')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW);
        yield IntegerField::new('score')->hideOnForm();
        yield AssociationField::new('challenge');
    }
}
```

---

## Dependencies

- Story 1.5 (Fixtures - Challenge exists)

---

## Definition of Done

- [x] Team entity created with all fields
- [x] UserInterface and PasswordAuthenticatedUserInterface implemented
- [x] ManyToOne relationship with Challenge configured
- [x] Index idx_team_challenge created
- [x] addPoints() method works correctly
- [x] TeamCrudController with password hashing
- [x] Migration created and applied

---

## Dev Agent Record

**Date:** 2026-01-11
**Agent:** James (Dev Agent)

### Implementation Summary

Implemented Team entity with full UserInterface support for authentication. Created TeamCrudController with automatic password hashing on create/update operations.

### Files Created/Modified

- `src/Entity/Team.php` - Team entity with UserInterface, score management
- `src/Repository/TeamRepository.php` - Repository with PasswordUpgraderInterface
- `src/Entity/Challenge.php` - Added teams OneToMany relationship
- `src/Controller/Admin/TeamCrudController.php` - CRUD with password hashing
- `src/Controller/Admin/DashboardController.php` - Added Teams menu item
- `migrations/Version20260111000545.php` - Team table migration
- `tests/Entity/TeamTest.php` - 9 unit tests
- `tests/Controller/Admin/TeamCrudControllerTest.php` - 5 functional tests

### Testing Summary

- **Total Tests:** 43 (suite complète)
- **Assertions:** 79
- **Status:** All passing

### Validation

- Container lint: OK
- All acceptance criteria met
