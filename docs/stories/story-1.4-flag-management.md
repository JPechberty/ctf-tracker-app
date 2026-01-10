# Story 1.4 : Flag Management

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P0 - Critical Path
**Status:** Ready for Review

---

## User Story

**As an** administrator,
**I want** to create and manage flags for each challenge,
**so that** teams have objectives to capture during the CTF.

---

## Acceptance Criteria

1. `Flag` entity exists with fields: `id`, `name`, `value`, `points` (default 0), `challenge_id` (FK)
2. Flag has `ManyToOne` relationship with Challenge (cascade persist/remove on Challenge side)
3. Flag CRUD is available in EasyAdmin
4. Flag form includes dropdown to select parent Challenge
5. Flag list displays: name, points, associated challenge name
6. Flag `value` field is displayed as password/hidden in list view (security)
7. Flags are automatically deleted when parent Challenge is deleted (cascade)
8. Index `idx_flag_challenge_value` exists on `(challenge_id, value)` columns

---

## Technical Notes

**Architecture Reference:** `docs/architecture/4-data-models.md`, `docs/architecture/9-database-schema.md`

**Entity Structure:**
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
}
```

**Challenge Entity Update (OneToMany):**
```php
#[ORM\OneToMany(targetEntity: Flag::class, mappedBy: 'challenge', cascade: ['persist', 'remove'])]
private Collection $flags;
```

**EasyAdmin CrudController:**
```php
class FlagCrudController extends AbstractCrudController
{
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('value')
            ->setFormTypeOption('attr', ['autocomplete' => 'off'])
            ->hideOnIndex(); // Security: hide value in list
        yield IntegerField::new('points');
        yield AssociationField::new('challenge');
    }
}
```

---

## Dependencies

- Story 1.3 (Challenge Management)

---

## Definition of Done

- [x] Flag entity created with all fields
- [x] ManyToOne relationship with Challenge configured
- [x] Index idx_flag_challenge_value created
- [x] FlagCrudController configured
- [x] Value field hidden in list view
- [x] Cascade delete works
- [x] Migration created and applied

---

## Dev Agent Record

### Agent Model Used
Claude Opus 4.5

### File List
- `src/Entity/Flag.php` - Flag entity with all fields and relationship
- `src/Repository/FlagRepository.php` - Flag repository
- `src/Controller/Admin/FlagCrudController.php` - Flag CRUD controller
- `src/Controller/Admin/DashboardController.php` - Updated with Flag menu
- `src/Entity/Challenge.php` - Updated with OneToMany relationship and __toString
- `migrations/Version20260110232345.php` - Flag table migration
- `tests/Entity/FlagTest.php` - Flag entity unit tests
- `tests/Controller/Admin/FlagCrudControllerTest.php` - Flag CRUD tests

### Change Log
- Created Flag entity with id, name, value, points, challenge_id fields
- Added ManyToOne relationship from Flag to Challenge
- Added OneToMany relationship from Challenge to Flag with cascade persist/remove
- Created idx_flag_challenge_value index on (challenge_id, value)
- Created FlagCrudController with value field hidden in list view
- Added __toString() method to Challenge entity for dropdown display
- Updated Dashboard menu with Flags link
- Created and applied migration for flag table
- Created 4 unit tests for Flag entity
- Created 5 CRUD controller tests including cascade delete test

### Completion Notes
- All 30 tests pass (54 assertions)
- All linting passes (container, YAML, Twig)
- Flag list accessible at /admin/flag
- Flag form includes Challenge dropdown
- Flag value hidden in list view for security
- Cascade delete verified: removing Challenge deletes associated Flags
- Default points is 0

### DoD Checklist Validation

**1. Requirements Met:**
- [x] All 8 acceptance criteria implemented and tested

**2. Coding Standards & Project Structure:**
- [x] Follows Symfony and EasyAdmin conventions
- [x] Proper relationship mapping with cascade
- [x] No linter errors

**3. Testing:**
- [x] 4 unit tests for Flag entity (defaults, getters/setters, relationships)
- [x] 5 CRUD controller tests (list, form, create, hidden value, cascade delete)
- [x] All 30 tests pass

**4. Functionality & Verification:**
- [x] All routes verified via debug:router
- [x] CRUD operations tested
- [x] Cascade delete verified

**5. Story Administration:**
- [x] All DoD items checked
- [x] Dev Agent Record completed

**6. Dependencies, Build & Configuration:**
- [x] Migration created and applied
- [x] Index idx_flag_challenge_value created

**Final Confirmation:**
- [x] Story ready for review
