# Story 1.5 : Development Fixtures

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P1 - High
**Status:** Ready for Review

---

## User Story

**As a** developer,
**I want** pre-configured test data loaded via fixtures,
**so that** I can develop and test features efficiently without manual data entry.

---

## Acceptance Criteria

1. DoctrineFixturesBundle is installed
2. `AppFixtures` class creates:
   - 1 admin account (username: `admin`, password: `admin123` hashed)
   - 1 challenge "Hackathon Red Team Cyber 2026" with appropriate dates and prefix "FLAG"
   - 3 flags with varying points (100, 250, 500)
3. Fixtures can be loaded with `bin/console doctrine:fixtures:load`
4. Fixtures use Symfony PasswordHasher to hash admin password
5. Fixtures are idempotent (can be re-run safely with `--purge-with-truncate`)
6. README or comment documents the fixture loading command

---

## Technical Notes

**Architecture Reference:** `docs/architecture/3-tech-stack.md`

**Fixture Implementation:**
```php
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new Admin();
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Challenge
        $challenge = new Challenge();
        $challenge->setName('Hackathon Red Team Cyber 2026');
        $challenge->setDescription('Challenge de cybersécurité pour les étudiants');
        $challenge->setPrefix('FLAG');
        $challenge->setStartDate(new \DateTimeImmutable('2026-02-01 09:00:00'));
        $challenge->setEndDate(new \DateTimeImmutable('2026-02-01 18:00:00'));
        $manager->persist($challenge);

        // Flags
        $flags = [
            ['name' => 'Web Exploitation', 'value' => 'w3b_m4st3r', 'points' => 100],
            ['name' => 'Crypto Challenge', 'value' => 'cr4ck3d_1t', 'points' => 250],
            ['name' => 'Reverse Engineering', 'value' => 'r3v3rs3d', 'points' => 500],
        ];

        foreach ($flags as $flagData) {
            $flag = new Flag();
            $flag->setName($flagData['name']);
            $flag->setValue($flagData['value']);
            $flag->setPoints($flagData['points']);
            $flag->setChallenge($challenge);
            $manager->persist($flag);
        }

        $manager->flush();
    }
}
```

**Commands:**
```bash
composer require --dev doctrine/doctrine-fixtures-bundle
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Dependencies

- Story 1.4 (Flag Management)

---

## Definition of Done

- [x] DoctrineFixturesBundle installed
- [x] AppFixtures class created
- [x] Admin account created with hashed password
- [x] Challenge created with proper dates
- [x] 3 flags created with varying points
- [x] `doctrine:fixtures:load` works without errors
- [x] Fixtures documented in README

---

## Dev Agent Record

### Agent Model Used
Claude Opus 4.5

### File List
- `src/DataFixtures/AppFixtures.php` - Development fixtures
- `README.md` - Project documentation with fixture commands

### Change Log
- Installed DoctrineFixturesBundle v4.3
- Created AppFixtures with password hasher injection
- Added admin account (username: admin, password: admin123)
- Added challenge "Hackathon Red Team Cyber 2026" with dates
- Added 3 flags: Web Exploitation (100pts), Crypto Challenge (250pts), Reverse Engineering (500pts)
- Created README.md with fixture documentation

### Completion Notes
- All 30 tests pass (54 assertions)
- Container linting passes
- Fixtures load successfully with `doctrine:fixtures:load`
- Fixtures are idempotent with `--purge-with-truncate`
- Admin password properly hashed using UserPasswordHasherInterface

### Fixture Data Summary
| Entity | Data |
|--------|------|
| Admin | username: `admin`, password: `admin123` |
| Challenge | "Hackathon Red Team Cyber 2026", Feb 1 2026 09:00-18:00 |
| Flag 1 | Web Exploitation, 100 points |
| Flag 2 | Crypto Challenge, 250 points |
| Flag 3 | Reverse Engineering, 500 points |

### DoD Checklist Validation

**1. Requirements Met:**
- [x] All 6 acceptance criteria implemented

**2. Coding Standards & Project Structure:**
- [x] Follows Symfony fixtures conventions
- [x] Password properly hashed
- [x] Container linting passes

**3. Testing:**
- [x] All 30 existing tests still pass
- [x] Fixtures load without errors

**4. Functionality & Verification:**
- [x] Fixtures verified via database queries
- [x] Admin, Challenge, and Flags all created correctly

**5. Story Administration:**
- [x] All DoD items checked
- [x] Dev Agent Record completed

**6. Dependencies, Build & Configuration:**
- [x] DoctrineFixturesBundle installed as dev dependency

**Final Confirmation:**
- [x] Story ready for review
