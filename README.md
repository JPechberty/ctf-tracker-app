# CTF Tracker

Application de suivi pour Capture The Flag (CTF) events.

## Requirements

- PHP 8.4+
- Composer
- Symfony CLI (optional, for local development)

## Installation

```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

## Development Fixtures

Load test data for development:

```bash
# Load fixtures (purges existing data)
php bin/console doctrine:fixtures:load --no-interaction

# Reload fixtures with truncate
php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction
```

### Fixture Data

| Entity | Data |
|--------|------|
| Admin | username: `admin`, password: `admin123` |
| Challenge | "Hackathon Red Team Cyber 2026" |
| Flags | Web Exploitation (100pts), Crypto Challenge (250pts), Reverse Engineering (500pts) |

## Running the Application

```bash
# Start Symfony development server
symfony server:start

# Or use PHP built-in server
php -S localhost:8000 -t public/
```

Access the admin interface at: http://localhost:8000/admin

## Running Tests

```bash
php bin/phpunit
```

## Health Check

The application exposes a health check endpoint at `/health` that returns `OK` with HTTP 200.
