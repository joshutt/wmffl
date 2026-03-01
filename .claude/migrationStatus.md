# Symfony Migration Status

*Last updated: February 2026*

The migration from legacy PHP to Symfony is in **early stages**.

## What's Been Migrated

- **1 controller**: `StandingsController` - the only fully Symfony-powered feature
- **2 services**: `SeasonWeekService` and `AuthenticationService` (abstractions over legacy globals)
- **1 template**: Standings page in Twig
- **4 repositories**: Only `StandingsRepository` is actively used

## What's Ready But Unused

- **62 Doctrine entities** in `symfony-app/src/Entity/` - the entire database schema is mapped and ready
- **6 shared entities** in `src/orm/` for cross-app compatibility
- Full Symfony infrastructure (routing, Twig, Doctrine) is configured

## What Remains in Legacy

**~905 PHP files** in `/football/` covering:

| Area | Files | Description |
|------|-------|-------------|
| Rules | 53 | League rules documentation |
| History | 34 | Historical season archives |
| Admin | 18 | Commissioner tools |
| Stats | 18 | Scoring and statistics |
| Teams | 16 | Team management |
| Transactions | 13 | Trades, waivers, IR, protections |
| Login | 12 | Authentication system |
| Article | 10 | News articles |
| Activate | 10 | Player activations |
| Utils | 8 | Utility functions |
| Forum | 5 | Discussions |
| Base | 9 | Core includes (menu, CSS, auth) |

## Current Architecture

```
Request
  ↓
Symfony Kernel (handles routing)
  ↓
If route found → Symfony Controller → Response
  ↓ (if 404)
LegacyBridge::handleRequest()
  ↓
Maps path to legacy PHP file in /football/
  ↓
Includes legacy file with Symfony services available as globals
```

The `LegacyBridge` (`symfony-app/src/LegacyBridge.php`) makes these Symfony services available to legacy code:
- `$symEntityManager` - Doctrine EntityManager
- `$seasonWeekService` - Current season/week data
- `$authService` - Authentication wrapper

## Migration Progress

| Category | Status |
|----------|--------|
| Infrastructure | Complete |
| Entity Mapping | Complete (62 entities) |
| Controllers | ~0.1% (1 of many) |
| Templates | ~0.1% |
| Business Logic | Mostly legacy |

## Recommended Next Steps

1. Migrate other high-traffic, read-heavy pages (similar complexity to Standings)
2. Gradually move authentication to Symfony Security component
3. Convert transaction pages as they need updates
4. Eventually deprecate LegacyBridge once critical mass is reached
