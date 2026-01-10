# 14. Test Strategy

## 14.1 Testing Philosophy

| Aspect | Choix |
|--------|-------|
| **Approche** | Test-After (MVP) |
| **Priorité** | Unit tests FlagValidationService |
| **Coverage** | 80% Services, 50% global |
| **E2E** | Non prévu |

## 14.2 Critical Tests

**FlagValidationServiceTest** (7+ tests obligatoires):
1. `testValidateReturnsErrorWhenChallengeNotActive`
2. `testValidateReturnsErrorWhenFormatInvalid`
3. `testValidateReturnsErrorWhenPrefixMismatch`
4. `testValidateReturnsErrorWhenFlagNotFound`
5. `testValidateReturnsErrorWhenFlagAlreadyValidated`
6. `testValidateReturnsSuccessAndAddsPoints`
7. `testValidateIsCaseSensitive`

## 14.3 Commands

```bash
php bin/phpunit                           # All tests
php bin/phpunit --testsuite=Unit          # Unit only
php bin/phpunit --coverage-html var/coverage  # Coverage
```

---
