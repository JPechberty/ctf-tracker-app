# Story 3.2 : Flag Validation Service

**Epic:** 3 - Flag Submission, Scoring & Leaderboard
**Priority:** P0 - Critical Path
**Status:** Ready for Review

---

## User Story

**As a** developer,
**I want** a FlagValidationService implementing 6 validation controls,
**so that** flag submissions are properly validated with appropriate error messages.

---

## Acceptance Criteria

1. `FlagValidationService` class exists in `src/Service/`
2. Service implements `validateSubmission(Team $team, string $submittedValue): ValidationResult`
3. `ValidationResult` contains: `success` (bool), `message` (string), `points` (int, 0 if failed), `flag` (Flag|null)
4. **Control 1 - Challenge Active:** Returns error "Le challenge n'est pas actif" if challenge is not active
5. **Control 2 - Format Valid:** Returns error "Format de flag invalide" if submitted value doesn't match pattern `{prefix}{...}` (e.g., `FLAG{...}`)
6. **Control 3 - Flag Exists:** Returns error "Flag incorrect" if no flag matches the submitted value
7. **Control 4 - Flag Belongs to Challenge:** Returns error "Flag incorrect" if flag doesn't belong to team's challenge
8. **Control 5 - Not Already Submitted:** Returns error "Flag deja valide" if team already has a successful submission for this flag
9. **Control 6 - Value Correct:** Returns error "Flag incorrect" if exact match fails (case-sensitive)
10. Validation controls are executed in order 1-6, stopping at first failure
11. On success, returns message "Flag valide !" with flag points
12. Unit tests cover all 6 failure cases plus success case (minimum 7 tests)

---

## Technical Notes

**Architecture Reference:** `docs/architecture/5-components.md`, `docs/architecture/7-core-workflows.md`

**ValidationResult DTO:**
```php
readonly class ValidationResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $points = 0,
        public ?Flag $flag = null,
    ) {}

    public static function success(Flag $flag): self
    {
        return new self(true, 'Flag valide !', $flag->getPoints(), $flag);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
```

**FlagValidationService:**
```php
class FlagValidationService
{
    public function __construct(
        private FlagRepository $flagRepo,
        private SubmissionRepository $submissionRepo,
    ) {}

    public function validateSubmission(Team $team, string $submittedValue): ValidationResult
    {
        $challenge = $team->getChallenge();

        // Control 1: Challenge actif
        if (!$challenge->isActive()) {
            return ValidationResult::failure('Le challenge n\'est pas actif');
        }

        // Control 2: Format valide
        $prefix = $challenge->getPrefix();
        if (!str_starts_with($submittedValue, $prefix . '{') || !str_ends_with($submittedValue, '}')) {
            return ValidationResult::failure('Format de flag invalide');
        }

        // Extract value between PREFIX{ and }
        $extractedValue = substr($submittedValue, strlen($prefix) + 1, -1);

        // Control 3: Flag existe
        $flag = $this->flagRepo->findOneBy([
            'value' => $extractedValue,
            'challenge' => $challenge,
        ]);

        if (!$flag) {
            return ValidationResult::failure('Flag incorrect');
        }

        // Control 4 already covered by findOneBy with challenge parameter

        // Control 5: Pas deja soumis
        $existingSubmission = $this->submissionRepo->findOneBy([
            'team' => $team,
            'flag' => $flag,
            'success' => true,
        ]);

        if ($existingSubmission) {
            return ValidationResult::failure('Flag deja valide');
        }

        // Control 6: Valeur exacte (deja verifiee par findOneBy avec value exacte)

        return ValidationResult::success($flag);
    }
}
```

**Validation Flow:**
```
1. Challenge actif?         → "Le challenge n'est pas actif"
2. Format PREFIX{...}?      → "Format de flag invalide"
3. Flag existe?             → "Flag incorrect"
4. Flag du bon challenge?   → "Flag incorrect"
5. Pas deja valide?         → "Flag deja valide"
6. Valeur exacte?           → "Flag incorrect"
✓ Success                   → "Flag valide !"
```

---

## Dependencies

- Story 3.1 (Submission Entity)
- Story 1.3 (Challenge.isActive())

---

## Definition of Done

- [x] ValidationResult DTO created
- [x] FlagValidationService created in src/Service/
- [x] All 6 controls implemented in order
- [x] Unit test for Control 1 (challenge not active)
- [x] Unit test for Control 2 (invalid format)
- [x] Unit test for Control 3 (flag not found)
- [x] Unit test for Control 4 (flag wrong challenge)
- [x] Unit test for Control 5 (already validated)
- [x] Unit test for Control 6 (value mismatch)
- [x] Unit test for success case

---

## Dev Agent Record

### Agent Model Used
Claude Opus 4.5

### File List
| File | Action |
|------|--------|
| src/DTO/ValidationResult.php | Created |
| src/Service/FlagValidationService.php | Created |
| tests/Service/FlagValidationServiceTest.php | Created |

### Change Log
- Created ValidationResult DTO with success/failure factory methods
- Created FlagValidationService with 6 validation controls in order
- Control 1: Challenge active check
- Control 2: Format validation (PREFIX{...})
- Control 3: Flag exists check
- Control 4: Flag belongs to challenge check (combined with Control 3)
- Control 5: Not already validated check
- Control 6: Exact value match (handled by findOneBy)
- Added 11 unit tests covering all failure cases + success cases + custom prefix
