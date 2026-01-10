# 10. Source Tree

```
ctf-tracker/
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── importmap.php
│
├── assets/
│   ├── app.js
│   ├── controllers/
│   │   └── timer_controller.js
│   └── styles/
│       └── app.css
│
├── config/
│   ├── bundles.php
│   ├── packages/
│   │   ├── doctrine.yaml
│   │   ├── security.yaml
│   │   └── ...
│   ├── routes.yaml
│   └── services.yaml
│
├── migrations/
│
├── public/
│   └── index.php
│
├── src/
│   ├── Controller/
│   │   ├── Admin/
│   │   │   ├── ChallengeCrudController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FlagCrudController.php
│   │   │   ├── SubmissionCrudController.php
│   │   │   └── TeamCrudController.php
│   │   ├── LeaderboardController.php
│   │   ├── SecurityController.php
│   │   └── TeamController.php
│   │
│   ├── DataFixtures/
│   │   └── AppFixtures.php
│   │
│   ├── DTO/
│   │   └── ValidationResult.php
│   │
│   ├── Entity/
│   │   ├── Admin.php
│   │   ├── Challenge.php
│   │   ├── Flag.php
│   │   ├── Submission.php
│   │   └── Team.php
│   │
│   ├── Repository/
│   │   ├── ChallengeRepository.php
│   │   ├── FlagRepository.php
│   │   ├── SubmissionRepository.php
│   │   └── TeamRepository.php
│   │
│   ├── Service/
│   │   ├── FlagValidationService.php
│   │   └── RankingService.php
│   │
│   └── Kernel.php
│
├── templates/
│   ├── base.html.twig
│   ├── leaderboard/
│   │   └── index.html.twig
│   ├── security/
│   │   └── login.html.twig
│   └── team/
│       └── dashboard.html.twig
│
├── tests/
│   ├── Controller/
│   │   └── ...
│   └── Service/
│       └── FlagValidationServiceTest.php
│
└── var/
    ├── cache/
    ├── data/
    │   └── ctf.db
    └── log/
```

---
