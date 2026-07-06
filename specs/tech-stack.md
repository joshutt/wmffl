# Tech Stack

## Target end state

A single Symfony application, backed by Doctrine ORM, with server-rendered
Twig templates. No legacy PHP, no mysqli, no LegacyBridge.

## Current stack

- **Framework:** Symfony 7.4
- **Language:** PHP 8.2+
- **ORM:** Doctrine ORM ^3.5 / DBAL ^3, via `doctrine/doctrine-bundle` and
  `doctrine/doctrine-migrations-bundle`
- **Templating:** Twig (`symfony/twig-bundle`)
- **Entry point:** `symfony-app/public/index.php`
- **Testing:** PHPUnit (`symfony-app/vendor/bin/phpunit tests/`)

## Frontend

Stay with server-rendered Twig. No JS framework (React/Vue/etc.) is planned —
interactivity, where needed, should be handled with small, targeted vanilla
JS or Stimulus-style progressive enhancement rather than a SPA framework.

## Legacy stack (being phased out)

- Procedural PHP in `/football/`, manual routing via `front_controller.php`
- Direct `mysqli` connections (`football/base/conn.php`)
- A separate legacy Doctrine EntityManager for entities under `src/orm/`,
  configured via `football/bootstrap.php` and `conf/db.ini`

This legacy stack exists only to be migrated away from — do not add new
functionality to it, and do not add new dependencies to the root
`composer.json` beyond what's needed to keep it running during the
migration.

## Database

Single MySQL database, shared by both ORM setups during the migration:
- Legacy credentials: `conf/db.ini`
- Symfony credentials: `symfony-app/.env.local` (`DATABASE_URL`)

These must stay in sync until the legacy Doctrine setup is removed.
