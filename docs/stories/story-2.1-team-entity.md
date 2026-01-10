# Story 2.1 : Team Entity & Admin Management

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P0 - Critical Path
**Status:** Ready for Development

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

- [ ] Team entity created with all fields
- [ ] UserInterface and PasswordAuthenticatedUserInterface implemented
- [ ] ManyToOne relationship with Challenge configured
- [ ] Index idx_team_challenge created
- [ ] addPoints() method works correctly
- [ ] TeamCrudController with password hashing
- [ ] Migration created and applied
