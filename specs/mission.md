# Mission

WMFFL is a fantasy football league management system. The mission has two
parallel threads:

1. **Migrate off the legacy PHP application.** Move all functionality
   currently living in `/football/` into the Symfony application, retiring
   the legacy procedural codebase and its mysqli-based data access entirely.
2. **Keep building new features.** Migration is not a moratorium on product
   work — new fantasy football functionality (e.g. player profiles, draft
   tooling) is built directly in Symfony as it's needed, rather than waiting
   for the legacy app to be fully retired first.
3. **Keep admin tooling in step with everything else.** Any feature area,
   whether migrated from legacy or newly built, must come with sufficient
   admin tools to manage it — a migration or new feature isn't done until
   league admins can administer it without dropping into raw DB access.

New features should always be built in Symfony, never in the legacy app.
When touching an existing legacy page, prefer porting it to Symfony over
patching it in place, unless the change is a small, urgent fix.

Success looks like: the `/football/` directory and its LegacyBridge fallback
no longer exist, and every route is served by a Symfony controller — while
the league has continued to gain functionality throughout the process.
