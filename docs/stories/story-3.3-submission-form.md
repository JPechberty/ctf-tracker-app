# Story 3.3 : Flag Submission Form & Scoring

**Epic:** 3 - Flag Submission, Scoring & Leaderboard
**Priority:** P0 - Critical Path
**Status:** Ready for Development

---

## User Story

**As a** team member,
**I want** to submit flags via a form on my dashboard and see immediate feedback,
**so that** I know if my submission was correct and see my updated score.

---

## Acceptance Criteria

1. Dashboard displays "SOUMETTRE UN FLAG" card with input field and "Valider" button
2. Form displays expected format hint (e.g., "Format attendu: FLAG{...}") based on challenge prefix
3. Form submission calls `FlagValidationService`
4. All submissions (success and failure) are persisted as Submission entities
5. On successful validation:
   - Team score is updated synchronously (`team.addPoints(flag.points)`)
   - Success message displays: "Flag valide ! +{points} points"
   - Input field is cleared
   - Validated flags list updates to show new flag
   - Score and rank cards update with new values
6. On failed validation:
   - Error message displays below input (e.g., "Flag incorrect")
   - Input field retains submitted value for correction
7. Feedback is displayed inline below the input field (not modal/toast)
8. Form can be submitted via Enter key or button click

---

## Technical Notes

**Architecture Reference:** `docs/architecture/5-components.md`

**TeamController Update:**
```php
#[Route('/dashboard', name: 'app_dashboard', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_TEAM')]
public function dashboard(
    Request $request,
    FlagValidationService $flagValidator,
    RankingService $rankingService,
    SubmissionRepository $submissionRepo,
    EntityManagerInterface $em,
): Response {
    /** @var Team $team */
    $team = $this->getUser();
    $challenge = $team->getChallenge();

    $feedback = null;
    $lastValue = '';

    if ($request->isMethod('POST')) {
        $submittedValue = trim($request->request->get('flag', ''));
        $lastValue = $submittedValue;

        $result = $flagValidator->validateSubmission($team, $submittedValue);

        // Persist submission (success or failure)
        $submission = new Submission();
        $submission->setTeam($team);
        $submission->setSubmittedValue($submittedValue);
        $submission->setSuccess($result->success);

        if ($result->flag) {
            $submission->setFlag($result->flag);
        }

        if ($result->success) {
            $team->addPoints($result->points);
            $lastValue = ''; // Clear on success
        }

        $em->persist($submission);
        $em->flush();

        $feedback = $result;
    }

    return $this->render('team/dashboard.html.twig', [
        'team' => $team,
        'challenge' => $challenge,
        'rank' => $rankingService->getTeamRank($team),
        'validatedFlags' => $submissionRepo->findValidatedByTeam($team),
        'totalFlags' => count($challenge->getFlags()),
        'feedback' => $feedback,
        'lastValue' => $lastValue,
    ]);
}
```

**Template Submission Form:**
```twig
<div class="card">
  <div class="card-body">
    <h5 class="card-title">SOUMETTRE UN FLAG</h5>
    <form method="post">
      <div class="input-group mb-2">
        <input type="text"
               name="flag"
               class="form-control font-monospace"
               placeholder="{{ challenge.prefix }}{...}"
               value="{{ lastValue }}"
               autofocus>
        <button type="submit" class="btn btn-primary">Valider</button>
      </div>
      <small class="text-muted">Format attendu: {{ challenge.prefix }}{...}</small>

      {% if feedback %}
        {% if feedback.success %}
          <div class="alert alert-success mt-3 mb-0">
            ✅ {{ feedback.message }} +{{ feedback.points }} points
          </div>
        {% else %}
          <div class="alert alert-danger mt-3 mb-0">
            ❌ {{ feedback.message }}
          </div>
        {% endif %}
      {% endif %}
    </form>
  </div>
</div>
```

---

## Dependencies

- Story 3.2 (FlagValidationService)
- Story 2.3 (Dashboard with score/rank)

---

## Definition of Done

- [ ] Submission form added to dashboard
- [ ] Format hint displays challenge prefix
- [ ] FlagValidationService called on POST
- [ ] Submissions persisted (success and failure)
- [ ] Team score updated on success
- [ ] Success message with points displayed
- [ ] Error message displayed on failure
- [ ] Input cleared on success, retained on failure
- [ ] Score and rank cards update after submission
- [ ] Enter key submits form
