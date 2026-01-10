# Brainstorming Session Results

**Session Date:** 2026-01-08
**Facilitator:** Business Analyst Mary 📊
**Participant:** Enseignant/Organisateur Hackathon

---

## Executive Summary

**Topic:** Plateforme de suivi CTF pour Hackathon Red Team Cyber

**Session Goals:** Concevoir l'architecture et définir les fonctionnalités prioritaires pour une première version de plateforme permettant aux étudiants de valider leur avancement, surveiller leur score et classement, et suivre le temps restant lors d'un hackathon technique.

**Techniques Used:**
1. First Principles Thinking (décomposition en besoins fondamentaux)
2. Morphological Analysis (exploration des composants techniques)
3. Resource Constraints / MVP Thinking (priorisation)

**Total Ideas Generated:** 25+ éléments architecturaux et fonctionnels identifiés

### Key Themes Identified:
- Simplicité et robustesse pour une livraison rapide
- Architecture Symfony classique éprouvée (sessions, Twig, EasyAdmin)
- Validation stricte des flags avec 5 contrôles critiques
- MVP complet avec toutes fonctionnalités critiques pour le jour J
- Nice-to-have identifiés pour itérations futures

---

## Technique Sessions

### First Principles Thinking - 25 minutes

**Description:** Décomposition de la plateforme en ses éléments fondamentaux pour identifier l'essence du système

#### Ideas Generated:

1. **Phrase mission** : "Plateforme permettant de valider votre avancement, surveiller votre score et rang par rapport aux autres équipes, et garder un œil sur le temps restant"

2. **6 Piliers fondamentaux identifiés** :
   - Authentification avec 2 niveaux (Admin et Étudiant)
   - Gestion des équipes avec création de comptes par l'admin
   - Création de challenges (nom, dates début/fin) et flags associés (nom, points)
   - Soumission de flags par les équipes
   - Gestion du score de chaque équipe
   - Leaderboard public

3. **Chaîne de dépendance établie** :
   ```
   Auth → Challenges+Flags → Équipes(challenge) → Soumissions → Scores+Leaderboard
   ```

4. **Découverte architecturale clé** : Une équipe est créée spécifiquement pour un challenge (relation 1-N)

5. **Logique de validation de flag - 5 contrôles critiques** :
   - Le flag existe en base de données
   - Le flag est correct (match exact)
   - Le flag appartient au challenge concerné
   - L'équipe n'a pas déjà validé ce flag (pas de double soumission)
   - Le challenge est actif (timestamp actuel entre date début et date fin)

#### Insights Discovered:
- La validation de flag est le cœur métier critique de l'application - elle nécessite 5 vérifications distinctes
- La relation Challenge → Équipes est importante : une équipe existe pour un challenge spécifique
- La gestion du temps (dates début/fin de challenge) est un élément de validation, pas juste d'affichage
- Les dépendances sont linéaires et claires, facilitant l'ordre de développement

#### Notable Connections:
- Le leaderboard dépend directement de la gestion des scores
- Les scores dépendent des soumissions validées
- Tout repose sur une authentification solide en amont

---

### Morphological Analysis - 30 minutes

**Description:** Exploration systématique des options architecturales pour chaque composant technique clé

#### Ideas Generated:

**Dimension 1 - Authentification**
1. Options explorées : Session Symfony / JWT / API tokens / Mix
2. **Choix retenu** : Session Symfony classique pour tous (Admin + Équipes)
3. Rationale : Simplicité, éprouvé, suffisant pour 10 équipes

**Dimension 2 - Validation de Flag**
4. Options explorées : String match exact / flexible / hash / regex / format imposé
5. **Choix retenu** : String match exact (case-sensitive)
6. Rationale : Pas d'ambiguïté, le flag doit être exactement correct

**Dimension 3 - Calcul de Score**
7. Options explorées : Temps réel synchrone / event-driven / cache / vue matérialisée
8. **Choix retenu** : Temps réel synchrone (UPDATE immédiat en BD)
9. Rationale : Simplicité d'implémentation, performance suffisante pour 10 équipes, fiabilité maximale

**Dimension 4 - Leaderboard**
10. Options explorées : Refresh manuel / auto-refresh / polling AJAX / WebSocket / SSE
11. **Choix retenu** : Refresh manuel par les équipes
12. Rationale : Simplicité maximale, charge serveur minimale

**Dimension 5 - Interface Admin**
13. Options explorées : Twig custom / EasyAdmin / Sonata / API + Front séparé
14. **Choix retenu** : EasyAdmin
15. Rationale : Génération automatique des CRUD pour challenges, flags, équipes - vitesse de développement maximale

**Dimension 6 - Interface Équipes**
16. Options explorées : Twig pur / Twig + JS / SPA / Mobile-first
17. **Choix retenu** : Symfony + Twig + JavaScript léger
18. Rationale : Base serveur solide avec améliorations progressives pour feedback visuel

#### Insights Discovered:
- Architecture "boring technology" volontairement choisie : Symfony classique, sessions, Twig
- Chaque choix privilégie la simplicité et la robustesse sur la sophistication technique
- EasyAdmin permet de gagner un temps considérable sur l'interface admin
- Le JavaScript reste léger et progressif (amélioration, pas dépendance)

#### Notable Connections:
- Le choix de sessions Symfony s'harmonise parfaitement avec Twig et EasyAdmin
- Le calcul de score synchrone simplifie l'affichage du leaderboard
- Toute la stack est cohérente : Symfony 7.4 + PHP 8.4 + SQLite + EasyAdmin + Twig

---

### Resource Constraints (MVP Thinking) - 20 minutes

**Description:** Définition du MVP et priorisation pour livraison rapide avant le jour du hackathon

#### Ideas Generated:

1. **Validation critique** : TOUTES les 6 fonctionnalités identifiées sont critiques pour le jour J
   - Pas de "nice-to-have" dans le MVP
   - Le hackathon ne peut pas avoir lieu sans ces fonctionnalités

2. **Ordre de développement optimal** :
   ```
   1. Authentification
   2. Gestion Challenges + Flags
   3. Gestion Équipes
   4. Soumission de Flags
   5. Calcul de Score
   6. Leaderboard
   ```

3. **Rationale de l'ordre** : Construire le "contenu" (challenges) avant les "participants" (équipes), permettant de tester bout-en-bout progressivement

4. **Seeds/Fixtures requis pour tests et démo** :
   - Compte admin par défaut
   - Challenges de test avec flags prédéfinis
   - Équipes de test

5. **Nice-to-have identifiés (post-MVP)** :
   - Hints/indices pour les challenges
   - Export des résultats (CSV/JSON)
   - Timer countdown visible sur l'interface

#### Insights Discovered:
- Le MVP est "complet" - toutes les fonctionnalités sont nécessaires (pas de scopage possible)
- L'ordre de développement permet de tester incrémentalement chaque couche
- Les fixtures sont essentielles pour accélérer le développement et les tests
- Les nice-to-have sont vraiment "bonus" et peuvent attendre après le jour J

#### Notable Connections:
- L'ordre de développement suit exactement la chaîne de dépendance identifiée en First Principles
- Les fixtures permettront de tester la validation de flag dès l'implémentation de la soumission
- Le timer countdown (nice-to-have) pourrait être ajouté facilement en JavaScript côté client

---

## Idea Categorization

### Immediate Opportunities
*Ideas ready to implement now*

1. **Utiliser EasyAdmin pour l'interface admin**
   - Description: Bundle Symfony qui génère automatiquement les interfaces CRUD
   - Why immediate: Gain de temps énorme sur le développement, documentation excellente, compatible Symfony 7.4
   - Resources needed: Installation du bundle EasyAdmin, configuration des entités

2. **Créer des Symfony Fixtures pour les données de test**
   - Description: Utiliser DoctrineFixturesBundle pour seeder admin, challenges et équipes
   - Why immediate: Permet de tester immédiatement chaque fonctionnalité développée sans saisie manuelle
   - Resources needed: Installation du bundle Fixtures, création des classes de fixtures

3. **Implémenter la validation de flag comme service métier**
   - Description: Créer un FlagValidationService avec les 5 contrôles identifiés
   - Why immediate: Cœur métier de l'application, logique bien définie, réutilisable, testable
   - Resources needed: Classe service + tests unitaires

### Future Innovations
*Ideas requiring development/research*

1. **Système de hints/indices pour les challenges**
   - Description: Permettre à l'admin de définir des indices, les équipes peuvent les débloquer (avec pénalité de points?)
   - Development needed: Entité Hint liée à Flag, logique de débloquage, UI pour affichage
   - Timeline estimate: Post-MVP, 1-2 jours de développement

2. **Export des résultats en multiple formats**
   - Description: Exporter le leaderboard final et l'historique des soumissions (CSV, JSON, PDF?)
   - Development needed: Service d'export, choix des formats, UI admin pour téléchargement
   - Timeline estimate: Post-MVP, 1 jour de développement

3. **Dashboard avec statistiques avancées**
   - Description: Graphiques de progression, taux de réussite par flag, timeline des soumissions
   - Development needed: Collecte de métriques, bibliothèque de charts (Chart.js?), vues dédiées
   - Timeline estimate: Post-MVP, 2-3 jours de développement

### Moonshots
*Ambitious, transformative concepts*

1. **Plateforme multi-événements réutilisable**
   - Description: Transformer l'application en plateforme générique pour n'importe quel hackathon/CTF
   - Transformative potential: Réutilisable chaque année, partageable avec d'autres enseignants, potentiel open-source
   - Challenges to overcome: Gestion multi-tenancy, configuration flexible, interface de gestion d'événements, migration SQLite → PostgreSQL?

2. **Leaderboard temps réel avec WebSocket + écran de projection**
   - Description: Affichage live sur grand écran avec animations, mise à jour instantanée à chaque flag validé
   - Transformative potential: Expérience immersive pour les étudiants, compétition plus engageante, aspect spectacle
   - Challenges to overcome: Infrastructure WebSocket (Mercure? Symfony UX Turbo?), design de l'écran de projection, gestion de la charge

3. **Système de collaboration inter-équipes**
   - Description: Permettre aux équipes de partager des indices, créer des alliances, échanger des points
   - Transformative potential: Dynamique sociale nouvelle, apprentissage collaboratif, scénarios de jeu complexes
   - Challenges to overcome: Conception game design, équilibrage, détection de tricherie, complexité UI

### Insights & Learnings
*Key realizations from the session*

- **Architecture over features**: Prendre le temps de définir l'architecture (Morphological Analysis) avant de coder évite les refactorisations coûteuses. Une stack simple et cohérente (Symfony classique) est préférable à des technologies à la mode.

- **Validation is king**: Dans un système CTF, la logique de validation de flag est le cœur métier absolu. Les 5 contrôles identifiés doivent être robustes, testés unitairement, et documentés.

- **MVP ≠ Features minimalistes**: Parfois le MVP doit être "complet" pour fonctionner. Dans ce cas, toutes les 6 fonctionnalités sont nécessaires - la contrainte est sur la qualité d'implémentation, pas sur le scope.

- **Seeds as productivity multiplier**: Investir dans des fixtures de qualité au début accélère drastiquement le développement et les tests. C'est un investissement initial rentable.

- **Technical debt awareness**: Les nice-to-have (hints, export, timer) ont été consciemment exclus du MVP pour éviter le scope creep. Ils sont documentés pour itérations futures.

- **Symfony ecosystem leverage**: En choisissant EasyAdmin, Fixtures, Security component, on s'appuie sur l'écosystème Symfony mature - "ne pas réinventer la roue" appliqué.

---

## Action Planning

### Top 3 Priority Ideas

#### #1 Priority: Développer le FlagValidationService (cœur métier)

- **Rationale**: C'est la logique métier critique de toute l'application. Sans validation robuste des flags, tout le reste est inutile. Ce service sera utilisé par le contrôleur de soumission et doit être parfaitement fiable.

- **Next steps**:
  1. Créer la classe `FlagValidationService` dans `src/Service/`
  2. Implémenter les 5 contrôles de validation identifiés (existe, correct, bon challenge, pas de doublon, challenge actif)
  3. Écrire les tests unitaires couvrant tous les cas (succès + 5 types d'échec)
  4. Documenter les codes d'erreur retournés pour feedback utilisateur

- **Resources needed**:
  - Documentation Doctrine pour les requêtes (vérifier flag, vérifier soumission existante)
  - DateTimeImmutable PHP 8.4 pour validation des dates de challenge
  - PHPUnit pour les tests

- **Timeline**: 1ère fonctionnalité à développer après les entités et l'authentification

---

#### #2 Priority: Configurer EasyAdmin pour l'interface admin

- **Rationale**: L'interface admin est nécessaire pour créer challenges, flags et équipes AVANT de pouvoir tester la soumission. EasyAdmin permet de gagner énormément de temps vs développer des CRUD manuellement.

- **Next steps**:
  1. Installer EasyAdminBundle via Composer
  2. Créer le DashboardController admin
  3. Configurer les CRUD pour Challenge, Flag, Team (User)
  4. Personnaliser les champs (associer flags aux challenges, définir dates, générer passwords équipes)
  5. Restreindre l'accès admin via Security

- **Resources needed**:
  - Documentation EasyAdmin 4.x (compatible Symfony 7.4)
  - Symfony Security pour restriction d'accès (ROLE_ADMIN)
  - Possiblement un générateur de mots de passe pour les comptes équipes

- **Timeline**: À développer juste après l'authentification, avant la gestion manuelle des entités

---

#### #3 Priority: Créer les Fixtures complètes de test

- **Rationale**: Dès que les entités existent, les fixtures permettent de peupler la base instantanément pour tester. Elles serviront aussi de documentation (exemple de données valides) et pourront être utilisées pour démo.

- **Next steps**:
  1. Installer DoctrineFixturesBundle
  2. Créer `AppFixtures.php` avec :
     - 1 compte admin (username: admin, password: admin ou plus secure)
     - 2-3 challenges de test avec dates cohérentes
     - 5-10 flags répartis sur les challenges avec points variés
     - 3-5 équipes de test avec credentials
  3. Ajouter quelques soumissions de test pour tester le leaderboard
  4. Documenter la commande de chargement (`php bin/console doctrine:fixtures:load`)

- **Resources needed**:
  - DoctrineFixturesBundle
  - PasswordHasher Symfony pour hasher les passwords en fixtures
  - Faker (optionnel) pour générer des noms d'équipes/challenges variés

- **Timeline**: À créer dès que les entités sont finalisées, avant même l'implémentation des contrôleurs

---

## Reflection & Follow-up

### What Worked Well

- **Approche structurée en 3 techniques** : First Principles → Morphological → MVP a permis de construire progressivement une vision complète
- **Focus sur l'architecture avant le code** : Prendre le temps de définir les choix techniques évite les hésitations pendant le développement
- **Identification claire des dépendances** : La chaîne Auth → Challenges → Équipes → Soumissions → Scores → Leaderboard guide l'ordre de développement
- **Pragmatisme technique** : Choix de "boring technology" (Symfony classique) assumé pour la robustesse et la rapidité
- **Séparation MVP vs nice-to-have** : Évite le scope creep, permet de livrer une v1 fonctionnelle rapidement

### Areas for Further Exploration

- **Sécurité applicative** : Approfondir la protection contre les attaques (brute-force de flags, CSRF, injection SQL via SQLite, rate limiting sur soumissions)
- **Gestion des erreurs et feedback utilisateur** : Définir les messages d'erreur précis pour chaque échec de validation (flag incorrect, challenge inactif, déjà soumis, etc.)
- **Performance et optimisation requêtes** : Avec SQLite, vérifier les index nécessaires pour les requêtes de leaderboard et validation
- **UX de soumission de flag** : Concevoir le formulaire (input text simple? feedback visuel immédiat? son de validation?)
- **Tests de charge** : Simuler 10 équipes soumettant simultanément pour identifier les goulots d'étranglement potentiels
- **Stratégie de backup** : SQLite est un fichier unique - prévoir une stratégie de sauvegarde régulière pendant le hackathon

### Recommended Follow-up Techniques

- **Storyboarding / User Journey Mapping** : Définir le parcours exact d'une équipe (connexion → soumission → voir score → consulter leaderboard) pour affiner l'UX
- **Threat Modeling** : Session dédiée à identifier les vecteurs d'attaque possibles (triche, injection, déni de service) et les mitigations
- **Technical Spike** : Prototyper rapidement la validation de flag + calcul de score pour valider l'approche avant le développement complet
- **Reverse Brainstorming** : "Comment pourrait-on faire échouer cette plateforme le jour J?" pour identifier les risques et prévoir les plans B

### Questions That Emerged

- **Format des flags** : Allez-vous imposer un format standard (ex: FLAG{...}) ou laisser libre? Cela impacte la validation et la communication aux étudiants
- **Affichage du leaderboard** : Public pour tous (même non-connectés) ou réservé aux équipes connectées? Affichage anonyme (Team #1) ou avec noms?
- **Gestion du temps** : Le timer countdown (nice-to-have) doit-il être géré côté serveur (source de vérité) ou juste côté client (affichage)?
- **Soumissions échouées** : Faut-il logger/afficher l'historique des tentatives échouées? Limiter le nombre de tentatives par minute (anti-brute-force)?
- **Points des flags** : Statiques (définis à la création) ou dynamiques (diminuent avec le temps ou le nombre d'équipes ayant résolu)?
- **Fin de challenge** : Que se passe-t-il après la date de fin? Leaderboard figé? Possibilité de continuer en mode "entraînement"?
- **Export des résultats** : Quelles données exactement? Scores finaux seulement ou tout l'historique des soumissions avec timestamps?

---

## Answers to Emerged Questions

**Session Date:** 2026-01-09
**Facilitated by:** Business Analyst Mary 📊

Les 7 questions identifiées lors de la session précédente ont été systématiquement adressées. Voici les décisions validées avec leurs implications techniques.

---

### Question 1: Format des Flags

**Décision:** Format imposé avec **préfixe personnalisable par challenge**

**Détails:**
- Chaque challenge peut définir son propre préfixe (ex: `FLAG`, `CYBER`, `HACK`, etc.)
- Format imposé: `{préfixe}{contenu}` (ex: `FLAG{s3cr3t_c0d3}`, `CYBER{p4ssw0rd}`)
- Le préfixe est stocké dans l'entité Challenge et communiqué aux équipes

**Implications techniques:**
- Ajouter un champ `prefix: string` à l'entité Challenge (ex: "FLAG", "CYBER")
- Validation étendue dans FlagValidationService :
  - Vérifier que la soumission matche le pattern `{prefix}{...}`
  - Ou stocker le flag complet en BD et faire un match exact (plus simple pour MVP)
- Interface admin: champ "Prefix" dans le CRUD Challenge (EasyAdmin)
- Interface équipe: afficher le format attendu (ex: "Format attendu: FLAG{...}")

**Rationale:**
- Équilibre entre structure (communication claire aux étudiants) et flexibilité (personnalisation par challenge)
- Permet des thématiques différentes selon les challenges
- Validation et communication facilitées

---

### Question 2: Affichage du Leaderboard

**Décision:** Leaderboard **public** (accessible sans connexion) avec **noms d'équipes visibles**

**Détails:**
- Route `/leaderboard` accessible sans authentification
- Affichage des noms d'équipes réels (ex: "Team Hackers", "Les Cybers")
- Visible par tous : étudiants, spectateurs, enseignants

**Implications techniques:**
- Route publique dans `security.yaml` : `/leaderboard` accessible sans ROLE
- Requête SQL simple : `SELECT team.name, SUM(flag.points) GROUP BY team ORDER BY score DESC`
- Idéal pour projection sur grand écran pendant l'événement
- Pas de données sensibles exposées (juste noms d'équipes et scores)

**Rationale:**
- Favorise l'esprit de compétition et l'engagement
- Permet projection publique pour spectateurs
- Cohérent avec le contexte pédagogique (pas de pression néfaste, apprentissage)
- Simplicité technique (pas de gestion de permissions multiples)

---

### Question 3: Gestion du Temps (Timer Countdown)

**Décision:** Timer countdown géré **côté client** (JavaScript)

**Détails:**
- Le serveur envoie la date/heure de fin du challenge (DateTime)
- JavaScript calcule et affiche le countdown en temps réel côté navigateur
- Mise à jour fluide chaque seconde sans requête serveur

**Implications techniques:**
- Twig passe `challenge.endDate` au template (format ISO 8601 ou timestamp)
- JavaScript vanilla ou petit script calcule `endDate - now()` et affiche
- Exemple: `<script>const endDate = new Date("{{ challenge.endDate|date('c') }}");</script>`
- Aucune charge serveur, pas de polling nécessaire

**Rationale:**
- Cohérent avec l'approche "Symfony + Twig + JavaScript léger"
- Simplicité maximale d'implémentation
- Performance optimale (pas de requêtes répétées)
- Fonctionnalité "nice-to-have" réalisée avec effort minimal
- Précision suffisante (décalage potentiel de quelques secondes acceptable)

---

### Question 4: Soumissions Échouées

**Décision:**
- **Historique:** Logger toutes tentatives en BD, visibles **admin uniquement**
- **Anti-brute-force:** **Pas de limitation** (MVP simple)

**Détails:**
- Toutes les soumissions (réussies ET échouées) sont stockées dans l'entité Submission
- Champ `success: boolean` pour distinguer succès/échec
- Interface équipe affiche uniquement les validations réussies
- Interface admin (EasyAdmin) peut consulter tout l'historique (analyse, détection triche)

**Implications techniques:**
- Entité `Submission` avec champs:
  - `team: Team`
  - `flag: Flag`
  - `submittedValue: string` (ce que l'équipe a soumis)
  - `success: boolean`
  - `submittedAt: DateTimeImmutable`
- FlagValidationService persiste TOUTES les soumissions
- Pas de rate limiting pour MVP (peut être ajouté post-MVP si abus détectés)
- EasyAdmin CRUD pour Submission (filtres par équipe, par flag, par succès)

**Rationale:**
- Simplicité pour MVP (pas de système de rate limiting complexe)
- Traçabilité complète pour l'admin (détection triche, analyse pédagogique)
- Pas de pollution de l'interface équipe avec les échecs
- Flexibilité pour ajouter rate limiting en post-MVP si nécessaire

---

### Question 5: Points des Flags

**Décision:** Points **statiques** (définis à la création du flag)

**Détails:**
- Chaque flag a une valeur en points fixe (ex: 100, 250, 500)
- L'admin définit les points lors de la création du flag
- Les points ne changent pas dans le temps ni selon le nombre de résolutions

**Implications techniques:**
- Champ `points: int` sur l'entité Flag (NOT NULL, default 0)
- Calcul de score simple: `SELECT SUM(flags.points) FROM submissions WHERE team_id = ? AND success = true`
- Pas de formule de dégradation temporelle ou dynamique
- Mise à jour du score de l'équipe synchrone (UPDATE immédiat)

**Rationale:**
- Simplicité maximale (cohérent avec philosophie MVP)
- Prévisible et équitable pour les étudiants
- L'admin contrôle la difficulté via l'attribution des points
- Pas de complexité algorithmique inutile
- Focus sur l'apprentissage plutôt que sur la course à la vitesse

---

### Question 6: Fin de Challenge

**Décision:** Leaderboard **figé** + soumissions **bloquées** APRÈS date de fin, avec possibilité pour l'admin de **prolonger manuellement**

**Détails:**
- Après `challenge.endDate`, les soumissions sont refusées (validation "challenge actif" échoue)
- Le leaderboard reste accessible en lecture mais affiche les résultats finaux figés
- L'admin peut modifier `endDate` via EasyAdmin pour prolonger en cas d'imprévu

**Implications techniques:**
- FlagValidationService vérifie: `now() >= challenge.startDate AND now() <= challenge.endDate`
- Si validation échoue après endDate: message "Le challenge est terminé"
- Leaderboard public continue d'afficher le classement final
- EasyAdmin permet édition simple de `endDate` pour prolongation exceptionnelle

**Rationale:**
- Résultats officiels clairs et immuables (important pour hackathon académique)
- Flexibilité pour gérer problèmes techniques le jour J (serveur down, prolongation nécessaire)
- Cohérent avec la logique de validation "challenge actif"
- Pas de complexité de "mode entraînement" pour le MVP

---

### Question 7: Export des Résultats

**Décision:** Export **scores + flags résolus par équipe**, format **JSON** uniquement

**Détails:**
- Fonctionnalité "nice-to-have" (post-MVP)
- Contenu: pour chaque équipe, afficher le score total + liste des flags validés avec leurs points
- Format: JSON structuré (réutilisable programmatiquement)
- Accessible via interface admin

**Exemple de structure JSON:**
```json
{
  "challenge": "Hackathon Red Team Cyber 2026",
  "exported_at": "2026-01-15T18:00:00Z",
  "teams": [
    {
      "name": "Team Hackers",
      "total_score": 1250,
      "flags_solved": [
        {"flag_name": "Web Exploitation", "points": 500},
        {"flag_name": "Crypto Challenge", "points": 750}
      ]
    }
  ]
}
```

**Implications techniques:**
- Route `/admin/export/results` (ROLE_ADMIN requis)
- Symfony Serializer pour générer JSON
- Requêtes: récupérer toutes les équipes + leurs soumissions réussies avec flags associés
- Bouton "Export Results (JSON)" dans interface admin

**Rationale:**
- Format JSON approprié pour public technique (hackathon cyber)
- Données structurées réutilisables (analyses, visualisations, archivage)
- Plus simple à implémenter que multiple formats
- Détail suffisant (qui a résolu quoi) sans surcharge (pas toutes les tentatives échouées)

---

### Impact des Décisions sur l'Architecture

**Modifications au modèle de données:**
- **Challenge**: ajouter champ `prefix: string` (nullable ou default "FLAG")
- **Submission**: confirmer champs `submittedValue: string` et `success: boolean`
- **Flag**: confirmer champ `points: int`

**Modifications aux fonctionnalités identifiées:**
- **FlagValidationService**: ajouter validation du format de préfixe
- **Leaderboard**: confirmer route publique `/leaderboard`
- **Interface équipe**: ajouter timer countdown JavaScript + affichage format flag attendu
- **Nice-to-have confirmés**: Export JSON, Timer countdown (mais très simple)

**Pas de changement majeur:**
- L'architecture globale reste identique (session, Twig, EasyAdmin, SQLite)
- Les 6 piliers fondamentaux restent inchangés
- L'ordre de développement reste valide

**Nouvelles contraintes techniques:**
- Validation de format de flag légèrement plus complexe (regex pour préfixe)
- Route publique à sécuriser (pas de données sensibles dans leaderboard)
- Timer JavaScript à implémenter (mais simple)

---

### Clarifications Obtenues

Ces décisions permettent maintenant de:

1. ✅ **Définir précisément le modèle de données Doctrine** (entités Challenge, Flag, Submission avec tous les champs)
2. ✅ **Spécifier la logique exacte de FlagValidationService** (6 contrôles: existe, correct, format préfixe, bon challenge, pas doublon, challenge actif)
3. ✅ **Concevoir l'interface équipe** (affichage format attendu, timer countdown, liste flags validés uniquement)
4. ✅ **Configurer la sécurité Symfony** (route publique /leaderboard, routes admin protégées)
5. ✅ **Prioriser le développement** (export JSON et timer confirmés comme nice-to-have post-MVP)

**Prochaines étapes recommandées:**
- Session de modélisation détaillée des entités Doctrine (relations, contraintes, index)
- Wireframes de l'interface équipe (formulaire soumission, affichage flags, timer, leaderboard)
- Définition des messages d'erreur utilisateur pour chaque cas de validation échouée

### Next Session Planning

- **Suggested topics**:
  - Session de conception UX/UI (wireframes pour interface équipes et leaderboard)
  - Session d'architecture technique détaillée (modèle de données Doctrine, relations entre entités)
  - Session de planification projet (découpage en tâches, estimation, milestones jusqu'au jour J)
  - Session de stratégie de test (tests unitaires, fonctionnels, end-to-end)

- **Recommended timeframe**:
  - Session architecture/modèle de données : avant de coder (1-2h)
  - Session UX/wireframes : en parallèle ou juste après l'architecture (1h)
  - Session planification : une fois l'architecture validée (30min-1h)

- **Preparation needed**:
  - Installer Symfony 7.4 + PHP 8.4 + SQLite pour valider l'environnement
  - Lister les entités pressenties (User, Team, Challenge, Flag, Submission, Score?)
  - Préparer des exemples de flags réels du hackathon (pour valider le format)
  - Réfléchir à la structure des équipes (taille? noms prédéfinis ou choisis?)

---

*Session facilitated using the BMAD-METHOD™ brainstorming framework*
