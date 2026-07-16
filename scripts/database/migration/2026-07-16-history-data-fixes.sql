-- Phase 9a data fixes (specs/2026-07-16-history-phase9a/): corrections
-- found while making /history/pastchamps data-driven. Sources of truth:
-- the frozen per-season pages (2009/2010/2011Season.php) and the 2022
-- standings computed from schedule. Run once on prod at deploy.

-- titles: the 2022 White Division title belongs to MeggaMen (teamid 7,
-- 9-4 atop division 3), not Fighting Squirrels (teamid 8, who played in
-- Gold). No (2022, Division, 7) row exists, so the PK update is safe.
UPDATE titles SET teamid = 7
WHERE season = 2022 AND type = 'Division' AND teamid = 8;

-- schedule: three championship games carried placeholder/garbled scores.
-- Winner sides were already correct, so W/L records never suffered.
-- 2009 Championship XVIII: Norsemen 60 - MeggaMen 44 (OT)
UPDATE schedule SET scorea = 60, scoreb = 44 WHERE gameid = 1179;
-- 2010 Championship XIX: Werewolves 21 - Norsemen 64
UPDATE schedule SET scorea = 21, scoreb = 64 WHERE gameid = 1267;
-- 2011 Championship XX: Crusaders 54 - Whiskey Tango 62
UPDATE schedule SET scorea = 54, scoreb = 62 WHERE gameid = 1355;
