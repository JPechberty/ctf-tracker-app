# Story 2.5 : Team Fixtures

**Epic:** 2 - Team Authentication & Dashboard
**Priority:** P1 - High
**Status:** Ready for Development

---

## User Story

**As a** developer,
**I want** test team accounts in fixtures,
**so that** I can test the team login and dashboard experience.

---

## Acceptance Criteria

1. `AppFixtures` is extended to create 3 test teams:
   - "Les Hackers" (username: `team1`, password: `team1pass`)
   - "Cyber Squad" (username: `team2`, password: `team2pass`)
   - "Binary Breakers" (username: `team3`, password: `team3pass`)
2. All teams are associated with the test challenge created in Story 1.5
3. Team passwords are hashed using Symfony PasswordHasher
4. Teams have initial score of 0
5. Fixture loading still works with single command `bin/console doctrine:fixtures:load`

---

## Technical Notes

**Architecture Reference:** `docs/architecture/3-tech-stack.md`

**Updated AppFixtures:**
```php
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Admin (from Story 1.5)
        $admin = new Admin();
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Challenge (from Story 1.5)
        $challenge = new Challenge();
        $challenge->setName('Hackathon Red Team Cyber 2026');
        $challenge->setDescription('Challenge de cybersécurité pour les étudiants');
        $challenge->setPrefix('FLAG');
        $challenge->setStartDate(new \DateTimeImmutable('2026-02-01 09:00:00'));
        $challenge->setEndDate(new \DateTimeImmutable('2026-02-01 18:00:00'));
        $manager->persist($challenge);

        // Flags (from Story 1.5)
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

        // Teams (NEW in this story)
        $teams = [
            ['name' => 'Les Hackers', 'username' => 'team1', 'password' => 'team1pass'],
            ['name' => 'Cyber Squad', 'username' => 'team2', 'password' => 'team2pass'],
            ['name' => 'Binary Breakers', 'username' => 'team3', 'password' => 'team3pass'],
        ];

        foreach ($teams as $teamData) {
            $team = new Team();
            $team->setName($teamData['name']);
            $team->setUsername($teamData['username']);
            $team->setPassword($this->passwordHasher->hashPassword($team, $teamData['password']));
            $team->setChallenge($challenge);
            // score defaults to 0
            $manager->persist($team);
        }

        $manager->flush();
    }
}
```

**Test Credentials:**
| Team | Username | Password |
|------|----------|----------|
| Les Hackers | team1 | team1pass |
| Cyber Squad | team2 | team2pass |
| Binary Breakers | team3 | team3pass |

---

## Dependencies

- Story 2.1 (Team Entity)
- Story 1.5 (Base Fixtures)

---

## Definition of Done

- [ ] 3 test teams added to AppFixtures
- [ ] All teams associated with test challenge
- [ ] Passwords properly hashed
- [ ] Initial score is 0
- [ ] `doctrine:fixtures:load` works correctly
- [ ] Can login with test credentials
