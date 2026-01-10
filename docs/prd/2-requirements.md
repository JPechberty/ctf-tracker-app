# 2. Requirements

## 2.1 Functional Requirements

| ID | Requirement |
|----|-------------|
| FR1 | Le système doit permettre à un administrateur de se connecter avec un compte dédié (ROLE_ADMIN) |
| FR2 | L'administrateur doit pouvoir créer, modifier et supprimer des challenges via EasyAdmin (nom, description, préfixe de flag, dates début/fin) |
| FR3 | L'administrateur doit pouvoir créer, modifier et supprimer des flags associés à un challenge (nom, valeur secrète, points) |
| FR4 | L'administrateur doit pouvoir créer, modifier et supprimer des équipes associées à un challenge (nom, identifiant, mot de passe) |
| FR5 | Une équipe doit pouvoir se connecter avec ses identifiants (ROLE_TEAM) |
| FR6 | Une équipe connectée doit voir son score actuel et son rang dans le classement |
| FR7 | Une équipe connectée doit pouvoir soumettre un flag via un formulaire dédié |
| FR8 | Le système doit valider chaque soumission selon 6 contrôles : challenge actif, format valide, flag existe, flag appartient au challenge, pas de double soumission, valeur exacte |
| FR9 | Le système doit afficher un feedback immédiat après soumission (succès avec points, ou erreur appropriée) |
| FR10 | Le système doit enregistrer toutes les soumissions (réussies et échouées) avec timestamp pour audit |
| FR11 | Une équipe connectée doit voir la liste des flags qu'elle a validés avec les points associés |
| FR12 | Le leaderboard doit être accessible publiquement sans authentification (route `/leaderboard`) |
| FR13 | Le leaderboard doit afficher toutes les équipes du challenge triées par score décroissant |
| FR14 | Le système doit afficher un timer countdown indiquant le temps restant du challenge |
| FR15 | Après la date de fin du challenge, les soumissions doivent être refusées et le leaderboard figé |

## 2.2 Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR1 | L'application doit utiliser Symfony 7.4 avec PHP 8.4 |
| NFR2 | La base de données doit être SQLite (fichier unique, simplicité de déploiement) |
| NFR3 | L'interface admin doit être générée via EasyAdmin pour minimiser le temps de développement |
| NFR4 | L'authentification doit utiliser le système de sessions Symfony (pas de JWT) |
| NFR5 | L'interface équipe doit être responsive (Desktop + Mobile) |
| NFR6 | Le timer countdown doit être géré côté client en JavaScript (pas de polling serveur) |
| NFR7 | La comparaison des flags doit être case-sensitive (match exact) |
| NFR8 | Le score doit être mis à jour de manière synchrone en base de données (pas de file d'attente) |
| NFR9 | L'application doit supporter au minimum 10 équipes simultanées sans dégradation de performance |
| NFR10 | Le style de l'interface doit être simple et épuré (inspiration PicoCTF) |

---
