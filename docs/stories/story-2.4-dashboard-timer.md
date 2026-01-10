# Story 2.4 : Team Dashboard - Timer & Validated Flags

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P1 - High
**Status:** Ready for Development

---

## User Story

**As a** team member,
**I want** to see the countdown timer and my list of validated flags,
**so that** I can track time remaining and review my achievements.

---

## Acceptance Criteria

1. Dashboard displays countdown timer showing time remaining until challenge `endDate`
2. Timer updates every second via JavaScript (no server polling)
3. Timer format is `HH:MM:SS`
4. Timer displays "TERMINÉ" when challenge has ended
5. Timer displays countdown to start if challenge hasn't begun yet
6. Dashboard displays "FLAGS VALIDÉS (X/Y)" section where X = validated, Y = total flags in challenge
7. Validated flags list shows: flag name and points for each validated flag
8. If no flags validated, display message "Aucun flag validé pour le moment"
9. Validated flags are ordered by validation time (earliest first)
10. Dashboard includes link/button "Voir le leaderboard" navigating to `/leaderboard`

---

## Technical Notes

**Architecture Reference:** `docs/architecture/5-components.md`

**Stimulus Timer Controller (assets/controllers/timer_controller.js):**
```javascript
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        endTime: Number,
        startTime: Number
    }

    connect() {
        this.update()
        this.interval = setInterval(() => this.update(), 1000)
    }

    disconnect() {
        clearInterval(this.interval)
    }

    update() {
        const now = Date.now()
        const end = this.endTimeValue * 1000
        const start = this.startTimeValue * 1000

        if (now < start) {
            // Challenge not started
            this.element.textContent = '⏳ ' + this.formatTime(start - now)
            this.element.dataset.state = 'upcoming'
        } else if (now >= end) {
            // Challenge ended
            this.element.textContent = '⏱️ TERMINÉ'
            this.element.classList.add('bg-danger', 'text-white')
            clearInterval(this.interval)
        } else {
            // Challenge active
            this.element.textContent = '⏱️ ' + this.formatTime(end - now)
        }
    }

    formatTime(ms) {
        const h = Math.floor(ms / 3600000)
        const m = Math.floor((ms % 3600000) / 60000)
        const s = Math.floor((ms % 60000) / 1000)
        return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
    }
}
```

**SubmissionRepository Method:**
```php
public function findValidatedByTeam(Team $team): array
{
    return $this->createQueryBuilder('s')
        ->join('s.flag', 'f')
        ->andWhere('s.team = :team')
        ->andWhere('s.success = true')
        ->setParameter('team', $team)
        ->orderBy('s.submittedAt', 'ASC')
        ->getQuery()
        ->getResult();
}
```

**Controller Update:**
```php
return $this->render('team/dashboard.html.twig', [
    'team' => $team,
    'challenge' => $team->getChallenge(),
    'rank' => $rankingService->getTeamRank($team),
    'validatedFlags' => $submissionRepo->findValidatedByTeam($team),
    'totalFlags' => count($team->getChallenge()->getFlags()),
]);
```

**Template Timer:**
```twig
<span class="badge bg-dark fs-6 font-monospace"
      data-controller="timer"
      data-timer-end-time-value="{{ challenge.endDate.timestamp }}"
      data-timer-start-time-value="{{ challenge.startDate.timestamp }}">
    ⏱️ --:--:--
</span>
```

**Template Validated Flags:**
```twig
<div class="card">
  <div class="card-body">
    <h5 class="card-title">FLAGS VALIDÉS ({{ validatedFlags|length }}/{{ totalFlags }})</h5>
    {% if validatedFlags is empty %}
      <p class="text-muted">Aucun flag validé pour le moment</p>
    {% else %}
      <ul class="list-group list-group-flush">
        {% for submission in validatedFlags %}
          <li class="list-group-item d-flex justify-content-between">
            <span>✅ {{ submission.flag.name }}</span>
            <span class="badge bg-success">+{{ submission.flag.points }} pts</span>
          </li>
        {% endfor %}
      </ul>
    {% endif %}
  </div>
</div>

<a href="{{ path('app_leaderboard') }}" class="btn btn-outline-secondary mt-3">
  🏆 Voir le leaderboard
</a>
```

---

## Dependencies

- Story 2.3 (Dashboard Score & Rank)

---

## Definition of Done

- [ ] Stimulus timer controller created
- [ ] Timer updates every second
- [ ] Timer shows correct state (upcoming/active/ended)
- [ ] Timer format is HH:MM:SS
- [ ] Validated flags list displays correctly
- [ ] Empty state message shown when no flags
- [ ] Flags ordered by validation time
- [ ] Leaderboard link works
