# Story 3.5 : Challenge States & Final Polish

**Epic:** 3 - Flag Submission, Scoring & Leaderboard
**Priority:** P1 - High
**Status:** Ready for Review

---

## User Story

**As a** user,
**I want** the interface to properly reflect challenge states,
**so that** I understand whether the challenge is upcoming, active, or ended.

---

## Acceptance Criteria

1. **Before challenge starts:**
   - Dashboard shows "CHALLENGE A VENIR" message with countdown to start
   - Submission form is hidden/disabled
   - Leaderboard shows "Le classement sera affiche au demarrage du challenge"
2. **During active challenge:**
   - Full functionality available (submission, scoring, leaderboard)
   - Timer shows remaining time
3. **After challenge ends:**
   - Dashboard shows "CHALLENGE TERMINE" message
   - Submission form is hidden/disabled
   - Dashboard displays "Consultez le classement final" with leaderboard link
   - Leaderboard shows "CHALLENGE TERMINE - Classement final"
   - Leaderboard data is frozen (reflects final scores)
4. All pages are responsive and display correctly on mobile (< 768px)
5. All feedback messages use consistent styling (green success, red error)
6. Timer displays "TERMINE" when challenge has ended
7. All error states display user-friendly messages (no technical errors exposed)

---

## Technical Notes

**Architecture Reference:** `docs/architecture/7-core-workflows.md`

**Challenge States:**
```
now < startDate     → Upcoming (form disabled, countdown to start)
startDate <= now <= endDate → Active (full functionality)
now > endDate       → Ended (form disabled, final results)
```

**Challenge Entity Methods:**
```php
public function isUpcoming(): bool
{
    return new \DateTimeImmutable() < $this->startDate;
}

public function isActive(): bool
{
    $now = new \DateTimeImmutable();
    return $now >= $this->startDate && $now <= $this->endDate;
}

public function isEnded(): bool
{
    return new \DateTimeImmutable() > $this->endDate;
}
```

**Dashboard Template States:**
```twig
{% if challenge.isUpcoming %}
  <div class="alert alert-info text-center">
    <h4>⏳ CHALLENGE A VENIR</h4>
    <p>Le challenge commence dans:</p>
    <span class="badge bg-dark fs-4 font-monospace"
          data-controller="timer"
          data-timer-end-time-value="{{ challenge.startDate.timestamp }}"
          data-timer-start-time-value="{{ challenge.startDate.timestamp }}">
      --:--:--
    </span>
  </div>

{% elseif challenge.isEnded %}
  <div class="alert alert-secondary text-center">
    <h4>⏱️ CHALLENGE TERMINE</h4>
    <p>Consultez le classement final</p>
    <a href="{{ path('app_leaderboard') }}" class="btn btn-primary">
      🏆 Voir le leaderboard
    </a>
  </div>

{% else %}
  {# Active challenge - show submission form #}
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">SOUMETTRE UN FLAG</h5>
      {# ... form ... #}
    </div>
  </div>
{% endif %}
```

**Leaderboard Template States:**
```twig
{% if challenge.isUpcoming %}
  <div class="alert alert-info text-center">
    <h4>⏳ Challenge a venir</h4>
    <p>Le classement sera affiche au demarrage du challenge</p>
  </div>
{% else %}
  {% if challenge.isEnded %}
    <div class="alert alert-secondary text-center mb-4">
      <strong>CHALLENGE TERMINE - Classement final</strong>
    </div>
  {% endif %}

  {# Show leaderboard table #}
  <table class="table">
    {# ... #}
  </table>
{% endif %}
```

**Responsive CSS:**
```css
/* Mobile optimizations */
@media (max-width: 767px) {
  .display-4 {
    font-size: 2rem;
  }
  .card {
    margin-bottom: 1rem;
  }
  .table td, .table th {
    padding: 0.5rem;
  }
}
```

---

## Dependencies

- Story 3.4 (Leaderboard)
- Story 3.3 (Submission Form)

---

## Definition of Done

- [x] Challenge.isUpcoming(), isActive(), isEnded() methods work
- [x] Dashboard shows appropriate state message
- [x] Submission form hidden when challenge not active
- [x] Leaderboard shows "a venir" message before start
- [x] Leaderboard shows "termine" banner after end
- [x] Timer shows countdown to start when upcoming
- [x] Timer shows "TERMINE" when ended
- [x] All pages responsive on mobile
- [x] Consistent success/error styling
- [x] No technical errors exposed to users

---

## Dev Agent Record

### Agent Model Used
Claude Opus 4.5

### File List

**Modified:**
- `templates/dashboard/index.html.twig` - Added challenge state handling (upcoming/active/ended), responsive CSS
- `templates/leaderboard/index.html.twig` - Added challenge state handling, mobile responsive CSS
- `tests/Controller/DashboardControllerTest.php` - Added 8 tests for challenge states
- `tests/Controller/LeaderboardControllerTest.php` - Added 6 tests for challenge states

### Change Log

- Updated Dashboard template to show:
  - "CHALLENGE A VENIR" message with countdown when upcoming
  - "CHALLENGE TERMINE" message with leaderboard link when ended
  - Submission form only when challenge is active
- Updated Leaderboard template to show:
  - "Challenge a venir" message when upcoming (hides table)
  - "CHALLENGE TERMINE - Classement final" banner when ended (shows table)
- Added mobile responsive CSS to both templates
- Timer controller already handles "TERMINE" state (from Story 2.4)
- Challenge entity already has isUpcoming(), isActive(), isEnded() methods
- Added 14 new tests for challenge state functionality

### Debug Log References
None

### Completion Notes
- All 136 Controller/Service/Entity tests pass
- Pre-existing fixture test failures are infrastructure issues unrelated to this story
- Timer controller already implemented "TERMINE" display in Story 2.4
