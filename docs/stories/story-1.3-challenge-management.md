# Story 1.3 : EasyAdmin Dashboard & Challenge Management

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P0 - Critical Path
**Status:** Ready for Review

---

## User Story

**As an** administrator,
**I want** to create and manage challenges via EasyAdmin,
**so that** I can set up CTF events with defined time periods.

---

## Acceptance Criteria

1. EasyAdminBundle is installed and configured
2. Admin dashboard is accessible at `/admin` after login
3. `Challenge` entity exists with fields: `id`, `name`, `description` (nullable), `prefix` (default "FLAG"), `startDate`, `endDate`
4. Challenge CRUD is available in EasyAdmin with all fields editable
5. Challenge list displays: name, prefix, start date, end date
6. Challenge form validates that `endDate` is after `startDate`
7. Challenge can be created, edited, and deleted from the admin interface
8. `isActive()` method on Challenge returns true only when current time is between startDate and endDate

---

## Technical Notes

**Architecture Reference:** `docs/architecture/4-data-models.md`, `docs/architecture/5-components.md`

**Entity Structure:**
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

    public function isActive(): bool
    {
        $now = new \DateTimeImmutable();
        return $now >= $this->startDate && $now <= $this->endDate;
    }

    public function isUpcoming(): bool
    {
        return new \DateTimeImmutable() < $this->startDate;
    }

    public function isEnded(): bool
    {
        return new \DateTimeImmutable() > $this->endDate;
    }
}
```

**EasyAdmin CrudController:**
```php
class ChallengeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Challenge::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextareaField::new('description')->hideOnIndex();
        yield TextField::new('prefix');
        yield DateTimeField::new('startDate');
        yield DateTimeField::new('endDate');
    }
}
```

---

## Dependencies

- Story 1.2 (Admin Authentication)

---

## Definition of Done

- [x] EasyAdminBundle installed
- [x] Challenge entity created with all fields
- [x] isActive(), isUpcoming(), isEnded() methods work
- [x] ChallengeCrudController configured
- [x] CRUD operations work in admin
- [x] Date validation (end > start) implemented
- [x] Migration created and applied

---

## Dev Agent Record

### Agent Model Used
Claude Opus 4.5

### File List
- `src/Entity/Challenge.php` - Challenge entity with all fields and status methods
- `src/Repository/ChallengeRepository.php` - Challenge repository
- `src/Controller/Admin/DashboardController.php` - EasyAdmin dashboard
- `src/Controller/Admin/ChallengeCrudController.php` - Challenge CRUD controller
- `migrations/Version20260110231858.php` - Challenge table migration
- `tests/Entity/ChallengeTest.php` - Challenge entity unit tests
- `tests/Controller/Admin/ChallengeCrudControllerTest.php` - EasyAdmin CRUD tests

### Change Log
- Installed EasyAdminBundle v4.27
- Created Challenge entity with id, name, description, prefix, startDate, endDate
- Implemented isActive(), isUpcoming(), isEnded() methods
- Added Assert\Callback validation for end date > start date
- Created EasyAdmin DashboardController at /admin
- Created ChallengeCrudController with all CRUD operations
- Replaced old AdminController with EasyAdmin dashboard
- Created and applied migration for challenge table
- Created 9 unit tests for Challenge entity
- Created 7 CRUD controller tests

### Completion Notes
- All 21 tests pass (38 assertions)
- All linting passes (container, YAML, Twig)
- Dashboard accessible at /admin (redirects to challenge list)
- Challenge CRUD at /admin/challenge with new/edit/delete
- Date validation shows error when end date <= start date
- Default prefix is "FLAG"

### DoD Checklist Validation

**1. Requirements Met:**
- [x] All 8 acceptance criteria implemented and tested

**2. Coding Standards & Project Structure:**
- [x] Follows Symfony and EasyAdmin conventions
- [x] Validation using Symfony constraints
- [x] No linter errors

**3. Testing:**
- [x] 9 unit tests for Challenge entity (isActive, isUpcoming, isEnded, getters/setters)
- [x] 7 CRUD controller tests (access, forms, create, validation)
- [x] All 21 tests pass

**4. Functionality & Verification:**
- [x] All routes verified via debug:router
- [x] CRUD operations tested

**5. Story Administration:**
- [x] All DoD items checked
- [x] Dev Agent Record completed

**6. Dependencies, Build & Configuration:**
- [x] EasyAdminBundle installed via composer
- [x] Migration created and applied

**Final Confirmation:**
- [x] Story ready for review
