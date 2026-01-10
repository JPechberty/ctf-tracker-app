# Story 1.4 : Flag Management

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P0 - Critical Path
**Status:** Ready for Development

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

- [ ] Flag entity created with all fields
- [ ] ManyToOne relationship with Challenge configured
- [ ] Index idx_flag_challenge_value created
- [ ] FlagCrudController configured
- [ ] Value field hidden in list view
- [ ] Cascade delete works
- [ ] Migration created and applied
