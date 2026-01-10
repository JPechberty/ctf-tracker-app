# Story 3.1 : Submission Entity

**Epic:** 3 - Flag Submission, Scoring & Leaderboard
**Priority:** P0 - Critical Path
**Status:** Ready for Development

---

## User Story

**As a** developer,
**I want** a Submission entity to record all flag submission attempts,
**so that** we have a complete audit trail for analysis and cheat detection.

---

## Acceptance Criteria

1. `Submission` entity exists with fields: `id`, `team_id` (FK), `flag_id` (FK), `submittedValue`, `success` (boolean), `submittedAt` (DateTimeImmutable)
2. Submission has `ManyToOne` relationship with Team
3. Submission has `ManyToOne` relationship with Flag
4. `submittedAt` is automatically set to current time in constructor
5. Index `idx_submission_team_flag_success` exists on `(team_id, flag_id, success)` columns
6. Submission CRUD is available in EasyAdmin (read-only for admin audit)
7. EasyAdmin Submission list displays: team name, flag name, submitted value, success status, timestamp
8. EasyAdmin allows filtering submissions by team, by flag, by success status

---

## Technical Notes

**Architecture Reference:** `docs/architecture/4-data-models.md`

**Entity Structure:**
```php
#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
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
    private \DateTimeImmutable $submittedAt;

    public function __construct()
    {
        $this->submittedAt = new \DateTimeImmutable();
    }
}
```

**EasyAdmin CrudController:**
```php
class SubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Submission')
            ->setEntityLabelInPlural('Submissions')
            ->setDefaultSort(['submittedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('team');
        yield AssociationField::new('flag');
        yield TextField::new('submittedValue');
        yield BooleanField::new('success');
        yield DateTimeField::new('submittedAt');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('team'))
            ->add(EntityFilter::new('flag'))
            ->add(BooleanFilter::new('success'));
    }
}
```

---

## Dependencies

- Story 2.1 (Team Entity)
- Story 1.4 (Flag Entity)

---

## Definition of Done

- [ ] Submission entity created with all fields
- [ ] ManyToOne relationships with Team and Flag configured
- [ ] submittedAt auto-set in constructor
- [ ] Index idx_submission_team_flag_success created
- [ ] SubmissionCrudController created (read-only)
- [ ] Filtering works in EasyAdmin
- [ ] Migration created and applied
