# 1. Introduction

Ce document décrit l'architecture technique complète pour **CTF Tracker**, une plateforme de suivi de progression pour hackathon CTF Red Team. Son objectif est de servir de référence pour le développement AI-driven, garantissant cohérence et respect des patterns choisis.

**Relation avec le Frontend :**
L'architecture frontend (Twig + Bootstrap 5.3 + Stimulus) est intégrée dans ce document car il s'agit d'un monolithe Symfony classique. Les templates sont servis par le même applicatif.

## 1.1 Starter Template

**Décision :** Symfony Skeleton avec option `--webapp`

```bash
symfony new ctf-tracker --webapp
```

**Rationale :**
- Inclut Twig, Doctrine, Sessions, Forms out-of-the-box
- Structure standard reconnue par la communauté
- Gain de temps vs installation bundle par bundle

## 1.2 Change Log

| Date | Version | Description | Author |
|------|---------|-------------|--------|
| 2026-01-09 | 1.0 | Création initiale | Winston (Architect) |

---
