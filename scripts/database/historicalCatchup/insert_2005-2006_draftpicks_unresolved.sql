-- Draft picks from the 2005-2006 seasons that could NOT be turned into
-- draftpicks rows, because the source page doesn't name a real player to
-- look up in the players table. There is nothing to run here - this file
-- is a reference list, not a set of INSERT statements.
--
-- All of these are "Off <city>" streaming-defense placeholders: these
-- seasons let a team draft a matchup-streamed NFL team's defense as a
-- roster slot instead of a specific player, so there is no individual to
-- match in players.
--
-- If any of these should actually become draftpicks rows (e.g. by adding
-- placeholder rows with playerid NULL, or a synthetic "team defense"
-- player), Josh will need to decide how - the sibling insert_<season>_
-- draftpicks.sql files only cover the picks with a real matched playerid.

-- ==== 2005 (from football/history/2005Season/draftsummary.txt) ====
--   Round 10, Pick 3: Crusaders - Off 'Kansas City' (KC)
--   Round 10, Pick 4: Sacks on the Beach - Off 'San Diego' (SD)
--   Round 12, Pick 2: Bug Stompers - Off 'Seattle' (SEA)

-- ==== 2006 (from football/history/2006Season/draftsummary.txt) ====
--   Round 9, Pick 3: Lindbergh Baby Casserole - Off 'Denver' (DEN)
--   Round 9, Pick 7: Werewolves - Off 'Atlanta' (ATL)
--   Round 9, Pick 10: MeggaMen - Off 'Cincinnati' (CIN)
--   Round 11, Pick 4: Go Balls Deep - Off 'Pittsburgh' (PIT)
--   Round 12, Pick 2: Gallic Warriors - Off 'New York' (NYG)

