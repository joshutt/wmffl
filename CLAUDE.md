# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WMFFL is a fantasy football league management system undergoing migration from a legacy PHP application to Symfony. The codebase currently runs both systems in parallel, with Symfony handling new features and a LegacyBridge routing unhandled requests to the legacy code.

## Architecture

### Dual Application Structure

The project consists of two applications:

1. **Legacy PHP Application** (`/football/`)
   - Traditional procedural PHP files with manual routing
   - Uses mysqli for database connections
   - Entry point: `football/index.php`, `football/front_controller.php`
   - Database connection via `football/base/conn.php`
   - Bootstrap file: `football/bootstrap.php` (sets up Doctrine EntityManager)

2. **Symfony Application** (`/symfony-app/`)
   - Modern Symfony 7.4 application
   - Entry point: `symfony-app/public/index.php`
   - Symfony handles requests first; if route not found (404), falls back to legacy via LegacyBridge

### LegacyBridge Pattern

Located at `symfony-app/src/LegacyBridge.php`, this bridge:
- Maps incoming requests to legacy PHP files in `/football/`
- Sets up include paths and environment for legacy code
- Makes Symfony's EntityManager available to legacy code as `$symEntityManager`
- Handles special routing (e.g., `/img/` routes to `img.php`)
- Automatically appends `.php` extension to routes without file extensions

### Database & ORM

Two separate Doctrine ORM setups exist:

1. **Legacy Doctrine Setup** (via `football/bootstrap.php`):
   - Creates EntityManager for entities in `src/orm/`
   - Uses attributes for metadata configuration
   - Configured via `conf/db.ini` (database credentials)
   - CLI tool: `scripts/bin/doctrine` for migrations/schema commands

2. **Symfony Doctrine Setup** (via Doctrine Bundle):
   - Entity namespace: `App\Entity` in `symfony-app/src/Entity/`
   - Also maps shared entities from `WMFFL\` namespace in `/src/`
   - Configured via `symfony-app/config/packages/doctrine.yaml`
   - Uses `DATABASE_URL` environment variable (set in `symfony-app/.env.local`)
   - CLI via: `symfony-app/bin/console doctrine:*`

### Shared Code

`/src/` directory contains shared classes used by both applications:
- `WMFFL\Team` - Team domain model
- Enums in `src/enum/`
- ORM entities in `src/orm/`

Autoloading configured in both root `composer.json` and `symfony-app/composer.json` to handle `WMFFL\` namespace.

## Common Commands

### Symfony Application

```bash
# Navigate to symfony app directory first
cd symfony-app

# Run Symfony console commands
php bin/console list
php bin/console cache:clear

# Database migrations
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:update --dump-sql

# Create entities/controllers
php bin/console make:entity
php bin/console make:controller
```

### Legacy Doctrine ORM

```bash
# Run from project root
php scripts/bin/doctrine orm:schema-tool:update --dump-sql
php scripts/bin/doctrine orm:validate-schema
```

### Testing

```bash
# Run PHPUnit tests (root level)
vendor/bin/phpunit test/

# Run Symfony tests
cd symfony-app
vendor/bin/phpunit tests/
```

### Composer Dependencies

```bash
# Install root dependencies (includes legacy Doctrine ORM)
composer install

# Install Symfony dependencies
cd symfony-app
composer install
```

## Database Configuration

Credentials are stored in:
- Legacy: `conf/db.ini` (format: userName, password, host, dbName)
- Symfony: `symfony-app/.env.local` (DATABASE_URL variable)

Both setups point to the same MySQL database but manage different entity mappings.

## Directory Structure

- `/football/` - Legacy web application (served via LegacyBridge)
  - `/football/base/` - Shared includes (conn.php, menu.php, scoring.php)
  - `/football/admin/` - Admin functionality
  - `/football/transactions/` - Transaction management
  - `/football/history/` - Historical data
- `/symfony-app/` - Modern Symfony application
  - `/symfony-app/src/Entity/` - Symfony-managed entities
  - `/symfony-app/src/Controller/` - Symfony controllers
  - `/symfony-app/public/` - Web root with index.php
- `/src/` - Shared domain models (WMFFL namespace)
  - `/src/orm/` - Doctrine entities shared between both apps
  - `/src/enum/` - Enums
- `/scripts/` - Utility scripts
  - `/scripts/database/` - SQL scripts for maintenance
  - `/scripts/imports/` - Data import scripts
- `/lib/` - Additional libraries
- `/test/` - PHPUnit tests for legacy code
- `/vendor/` - Root Composer dependencies
- `/symfony-app/vendor/` - Symfony-specific dependencies

## Key Implementation Notes

1. When adding new features, prefer Symfony controllers over legacy PHP files
2. When modifying shared entities in `/src/orm/`, ensure compatibility with both ORM setups
3. The LegacyBridge handles routing automatically - test legacy paths work after Symfony changes
4. Database credentials must be synchronized between `conf/db.ini` and `symfony-app/.env.local`
5. The global `$kernel` variable makes Symfony services available to legacy code
6. Environment configuration uses Symfony .env files; legacy code accesses via `$ini` array from db.ini
