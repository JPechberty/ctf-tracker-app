# Story 2.3 : Team Dashboard - Score & Rank Display

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P0 - Critical Path
**Status:** Ready for Development

---

## User Story

**As a** team member,
**I want** to see my current score and ranking position,
**so that** I know how I'm performing compared to other teams.

---

## Acceptance Criteria

1. Dashboard page exists at `/dashboard` (requires ROLE_TEAM)
2. Dashboard displays team name in header
3. Dashboard displays current score in a prominent card (e.g., "450 pts")
4. Dashboard displays current rank position (e.g., "#3")
5. Rank is calculated dynamically: position among all teams of the same challenge, ordered by score descending
6. Teams with equal scores share the same rank (ties handled)
7. Dashboard layout matches wireframe E2 structure (header + score card + rank card)
8. Logout button is visible in the header

---

## Technical Notes

**Architecture Reference:** `docs/architecture/5-components.md`

**RankingService:**
```php
class RankingService
{
    public function __construct(
        private TeamRepository $teamRepo
    ) {}

    public function getTeamRank(Team $team): int
    {
        $teams = $this->teamRepo->findByChallengeSortedByScore($team->getChallenge());

        $rank = 1;
        $previousScore = null;
        $sameRankCount = 0;

        foreach ($teams as $t) {
            if ($previousScore !== null && $t->getScore() < $previousScore) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            if ($t->getId() === $team->getId()) {
                return $rank;
            }

            $previousScore = $t->getScore();
        }

        return $rank;
    }
}
```

**TeamRepository Method:**
```php
public function findByChallengeSortedByScore(Challenge $challenge): array
{
    return $this->createQueryBuilder('t')
        ->andWhere('t.challenge = :challenge')
        ->setParameter('challenge', $challenge)
        ->orderBy('t.score', 'DESC')
        ->getQuery()
        ->getResult();
}
```

**TeamController:**
```php
#[Route('/dashboard', name: 'app_dashboard')]
#[IsGranted('ROLE_TEAM')]
public function dashboard(RankingService $rankingService): Response
{
    /** @var Team $team */
    $team = $this->getUser();

    return $this->render('team/dashboard.html.twig', [
        'team' => $team,
        'challenge' => $team->getChallenge(),
        'rank' => $rankingService->getTeamRank($team),
    ]);
}
```

**Template Structure:**
```twig
<nav class="navbar navbar-light bg-light sticky-top">
  <div class="container">
    <span class="navbar-brand">🏴 CTF TRACKER</span>
    <span>{{ team.name }}</span>
    <a href="{{ path('app_logout') }}" class="btn btn-outline-danger btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">VOTRE SCORE</h5>
          <p class="display-4 fw-bold font-monospace">{{ team.score }} pts</p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">VOTRE RANG</h5>
          <p class="display-4 fw-bold">#{{ rank }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## Dependencies

- Story 2.2 (Team Authentication)

---

## Definition of Done

- [ ] Dashboard route at /dashboard with ROLE_TEAM protection
- [ ] RankingService created and tested
- [ ] TeamRepository.findByChallengeSortedByScore() works
- [ ] Score card displays correctly
- [ ] Rank card displays correctly
- [ ] Ties handled (equal scores = same rank)
- [ ] Logout button in header works
- [ ] Layout matches wireframe E2
