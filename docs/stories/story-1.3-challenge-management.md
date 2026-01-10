# Story 1.3 : EasyAdmin Dashboard & Challenge Management

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P0 - Critical Path
**Status:** Ready for Development

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

- [ ] EasyAdminBundle installed
- [ ] Challenge entity created with all fields
- [ ] isActive(), isUpcoming(), isEnded() methods work
- [ ] ChallengeCrudController configured
- [ ] CRUD operations work in admin
- [ ] Date validation (end > start) implemented
- [ ] Migration created and applied
