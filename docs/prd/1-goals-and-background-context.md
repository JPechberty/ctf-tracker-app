# 1. Goals and Background Context

## 1.1 Goals

- Permettre aux équipes étudiantes de **soumettre et valider des flags** pendant un hackathon CTF Red Team
- Offrir un **leaderboard public en temps réel** pour stimuler la compétition
- Fournir aux administrateurs un **back-office simple** pour gérer challenges, flags et équipes
- Garantir une **validation robuste** des flags (6 contrôles : challenge actif, format, existence, appartenance, unicité, exactitude)
- Livrer une application **fonctionnelle et fiable** pour le jour J du hackathon

## 1.2 Background Context

Cette plateforme CTF Tracker est développée dans le cadre d'un **Hackathon Red Team Cyber** destiné à des étudiants. L'outil répond à un besoin pédagogique : permettre aux équipes de valider leur progression technique, visualiser leur classement, et maintenir l'engagement grâce à un timer et un leaderboard projetable.

L'architecture choisie privilégie la **simplicité et la robustesse** (Symfony 7.4, SQLite, EasyAdmin, sessions classiques) plutôt que des technologies modernes complexes. Le MVP inclut toutes les fonctionnalités critiques sans compromis sur le scope, les nice-to-have (hints, export JSON, timer avancé) étant reportés post-événement.

## 1.3 Change Log

| Date | Version | Description | Author |
|------|---------|-------------|--------|
| 2026-01-09 | 1.0 | Création initiale du PRD | John (PM) |

---
