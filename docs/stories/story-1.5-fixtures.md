# Story 1.5 : Development Fixtures

**Epic:** 1 - Foundation & Admin Interface
**Priority:** P1 - High
**Status:** Ready for Development

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

- [ ] DoctrineFixturesBundle installed
- [ ] AppFixtures class created
- [ ] Admin account created with hashed password
- [ ] Challenge created with proper dates
- [ ] 3 flags created with varying points
- [ ] `doctrine:fixtures:load` works without errors
- [ ] Fixtures documented in README
