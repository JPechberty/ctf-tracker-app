# 12. Error Handling Strategy

## 12.1 Approach

| Aspect | Implementation |
|--------|----------------|
| **User-Facing** | Messages français, non-techniques |
| **Technical** | Logs Monolog uniquement |
| **Validation** | ValidationResult DTO |

## 12.2 Error Messages

| Situation | Message |
|-----------|---------|
| Challenge non actif | "Le challenge n'est pas actif" |
| Format invalide | "Format de flag invalide" |
| Flag incorrect | "Flag incorrect" |
| Flag déjà validé | "Flag déjà validé" |
| Identifiants incorrects | "Identifiants incorrects" |

## 12.3 Logging

```yaml
# config/packages/monolog.yaml
when@prod:
    monolog:
        handlers:
            main:
                type: rotating_file
                path: "%kernel.logs_dir%/%kernel.environment%.log"
                level: warning
                max_files: 7
```

---
