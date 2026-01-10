# 3. User Interface Design Goals

## 3.1 Overall UX Vision

L'interface CTF Tracker doit être **simple, épurée et fonctionnelle**, inspirée de PicoCTF. L'objectif est de minimiser la friction cognitive pour les équipes étudiantes : elles doivent pouvoir soumettre un flag, voir leur score et consulter le leaderboard en quelques secondes. Aucune fonctionnalité superflue, focus total sur l'action principale de chaque écran.

## 3.2 Key Interaction Paradigms

- **Dashboard-centric** : Toutes les informations équipe sur un seul écran (score, rang, formulaire, flags validés)
- **Feedback inline** : Messages de succès/erreur directement sous le champ de saisie (pas de modals ou toasts)
- **Refresh manuel** : Le leaderboard se rafraîchit par action utilisateur (bouton "Actualiser"), pas d'auto-refresh
- **Timer passif** : Countdown visible mais non-intrusif, mis à jour côté client sans interaction serveur

## 3.3 Core Screens and Views

| Écran | Route | Accès | Fonction principale |
|-------|-------|-------|---------------------|
| Login Équipe | `/login` | Public | Authentification des équipes |
| Dashboard Équipe | `/dashboard` | ROLE_TEAM | Soumission de flags + suivi progression |
| Leaderboard Public | `/leaderboard` | Public | Classement en temps réel (projetable) |

*Note : L'interface Admin est gérée par EasyAdmin (hors périmètre design custom)*

## 3.4 Accessibility

**WCAG AA** - Niveau d'accessibilité cible

- Contraste suffisant pour lisibilité sur projection grand écran
- Focus visible sur les éléments interactifs
- Messages d'erreur associés aux champs (aria-describedby)
- Police lisible (system fonts, sans-serif)

## 3.5 Branding

- **Style visuel** : Minimaliste, light theme (fond blanc/gris clair)
- **Palette couleurs** :
  - Succès : Vert (#28a745)
  - Erreur : Rouge (#dc3545)
  - Info : Bleu (#17a2b8)
- **Icônes** : Emojis pour médailles (🥇🥈🥉) et indicateurs (✓ ❌ 🏴 🏆 ⏱️)
- **Typographie** : Timer en monospace, scores alignés à droite

## 3.6 Target Device and Platforms

**Web Responsive** (Desktop + Mobile)

| Breakpoint | Adaptations |
|------------|-------------|
| Desktop (>768px) | Layout 2 colonnes pour score/rang, cards larges |
| Mobile (<768px) | Layout 1 colonne, cards empilées, textes condensés |

*Optimisation particulière pour projection grand écran (leaderboard)*

---
