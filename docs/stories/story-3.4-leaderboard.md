# Story 3.4 : Public Leaderboard

**Epic:** 3 - Flag Submission, Scoring & Leaderboard
**Priority:** P0 - Critical Path
**Status:** Ready for Development

---

## User Story

**As a** visitor (spectator, teacher, or team member),
**I want** to view the public leaderboard without authentication,
**so that** I can see the current rankings and project it on a big screen.

---

## Acceptance Criteria

1. Leaderboard page exists at `/leaderboard` (public, no authentication required)
2. Leaderboard displays challenge name as title
3. Leaderboard displays countdown timer (same JavaScript logic as dashboard)
4. Leaderboard lists all teams ordered by score descending
5. Top 3 teams display medal icons (gold, silver, bronze)
6. Each row shows: rank, team name, score (formatted with spacing/dots)
7. Teams with equal scores share the same rank
8. Footer displays total number of flags available (e.g., "10 flags disponibles")
9. "Actualiser" button refreshes the page
10. Layout matches wireframe E3, optimized for projection on large screen
11. Leaderboard is responsive (mobile layout with stacked entries)

---

## Technical Notes

**Architecture Reference:** `docs/architecture/5-components.md`

**LeaderboardController:**
```php
class LeaderboardController extends AbstractController
{
    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function index(
        ChallengeRepository $challengeRepo,
        TeamRepository $teamRepo,
        RankingService $rankingService,
    ): Response {
        // For MVP, assume single active challenge
        $challenge = $challengeRepo->findOneBy([], ['id' => 'DESC']);

        if (!$challenge) {
            throw $this->createNotFoundException('No challenge found');
        }

        $teams = $teamRepo->findByChallengeSortedByScore($challenge);
        $rankedTeams = $rankingService->getRankedTeams($teams);

        return $this->render('leaderboard/index.html.twig', [
            'challenge' => $challenge,
            'rankedTeams' => $rankedTeams,
            'totalFlags' => count($challenge->getFlags()),
        ]);
    }
}
```

**RankingService Addition:**
```php
public function getRankedTeams(array $teams): array
{
    $result = [];
    $rank = 1;
    $previousScore = null;
    $sameRankCount = 0;

    foreach ($teams as $team) {
        if ($previousScore !== null && $team->getScore() < $previousScore) {
            $rank += $sameRankCount;
            $sameRankCount = 1;
        } else {
            $sameRankCount++;
        }

        $result[] = [
            'rank' => $rank,
            'team' => $team,
        ];

        $previousScore = $team->getScore();
    }

    return $result;
}
```

**Template (leaderboard/index.html.twig):**
```twig
{% extends 'base.html.twig' %}

{% block body %}
<div class="container py-4">
  <div class="text-center mb-4">
    <h1 class="display-5">{{ challenge.name }}</h1>
    <span class="badge bg-dark fs-5 font-monospace"
          data-controller="timer"
          data-timer-end-time-value="{{ challenge.endDate.timestamp }}"
          data-timer-start-time-value="{{ challenge.startDate.timestamp }}">
      --:--:--
    </span>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th class="text-center" style="width: 80px">Rang</th>
            <th>Equipe</th>
            <th class="text-end" style="width: 120px">Score</th>
          </tr>
        </thead>
        <tbody>
          {% for entry in rankedTeams %}
          <tr>
            <td class="text-center fs-4">
              {% if entry.rank == 1 %}🥇
              {% elseif entry.rank == 2 %}🥈
              {% elseif entry.rank == 3 %}🥉
              {% else %}#{{ entry.rank }}
              {% endif %}
            </td>
            <td class="fs-5">{{ entry.team.name }}</td>
            <td class="text-end font-monospace fs-5">{{ entry.team.score }} pts</td>
          </tr>
          {% else %}
          <tr>
            <td colspan="3" class="text-center text-muted py-4">
              Aucune equipe inscrite
            </td>
          </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mt-4">
    <span class="text-muted">🚩 {{ totalFlags }} flags disponibles</span>
    <a href="{{ path('app_leaderboard') }}" class="btn btn-outline-secondary">
      🔄 Actualiser
    </a>
  </div>
</div>
{% endblock %}
```

**Large Screen Optimization:**
```css
/* For projection mode */
@media (min-width: 1200px) {
  .leaderboard-table {
    font-size: 1.5rem;
  }
  .leaderboard-table td, .leaderboard-table th {
    padding: 1rem 1.5rem;
  }
}
```

---

## Dependencies

- Story 2.4 (Timer Stimulus controller)
- Story 2.3 (RankingService)

---

## Definition of Done

- [ ] Leaderboard route at /leaderboard (public)
- [ ] Challenge name displayed as title
- [ ] Timer countdown displayed
- [ ] Teams listed by score descending
- [ ] Medal icons for top 3
- [ ] Tied teams share rank
- [ ] Total flags count in footer
- [ ] Actualiser button works
- [ ] Layout optimized for projection
- [ ] Responsive on mobile
