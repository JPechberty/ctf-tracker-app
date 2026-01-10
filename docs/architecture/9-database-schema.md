# 9. Database Schema

## 9.1 DDL SQLite

```sql
CREATE TABLE admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE challenge (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    prefix VARCHAR(50) NOT NULL DEFAULT 'FLAG',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL
);

CREATE TABLE flag (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    challenge_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    points INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (challenge_id) REFERENCES challenge(id) ON DELETE CASCADE
);

CREATE INDEX idx_flag_challenge_value ON flag(challenge_id, value);

CREATE TABLE team (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    challenge_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    score INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (challenge_id) REFERENCES challenge(id) ON DELETE CASCADE
);

CREATE INDEX idx_team_challenge ON team(challenge_id);

CREATE TABLE submission (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    team_id INTEGER NOT NULL,
    flag_id INTEGER NOT NULL,
    submitted_value VARCHAR(255) NOT NULL,
    success INTEGER NOT NULL DEFAULT 0,
    submitted_at DATETIME NOT NULL,
    FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE CASCADE,
    FOREIGN KEY (flag_id) REFERENCES flag(id) ON DELETE CASCADE
);

CREATE INDEX idx_submission_team_flag_success ON submission(team_id, flag_id, success);
```

## 9.2 Requêtes Critiques

**Leaderboard:**
```sql
SELECT id, name, score FROM team
WHERE challenge_id = :id ORDER BY score DESC;
```

**Flags validés:**
```sql
SELECT f.name, f.points, s.submitted_at
FROM submission s JOIN flag f ON s.flag_id = f.id
WHERE s.team_id = :id AND s.success = 1
ORDER BY s.submitted_at ASC;
```

**Double soumission:**
```sql
SELECT COUNT(*) FROM submission
WHERE team_id = :teamId AND flag_id = :flagId AND success = 1;
```

---
