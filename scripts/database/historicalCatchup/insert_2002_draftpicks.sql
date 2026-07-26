-- 2002 WMFFL draft picks, transcribed from football/history/2002Season/draftpicks.php.
--
-- Only season/round/pick/teamid/playerid are populated (id is auto-increment,
-- orgTeam and pickTime are left for Josh to fill in as needed).
-- Player ids were matched by lastname/firstname against the players table;
-- picks that were streaming placeholders ("team QB", "team kicker", "team
-- offense") or explicitly "(NONE)" in the source have no real player and are
-- listed instead in insert_1999-2004_draftpicks_unresolved.sql, not here.

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(2002, 1, 1, 10, 2635),  -- Barbarians: QB Gannon,Rich (OAK)
(2002, 1, 2, 7, 754),  -- MeggaMen: RB George,Eddie (TEN)
(2002, 1, 3, 8, 2588),  -- Rednecks: QB Bledsoe,Drew (BUF)
(2002, 1, 4, 9, 152),  -- Green Wave: RB Bennett,Michael (MIN)
(2002, 1, 5, 3, 190),  -- Norsemen: WR Booker,Marty (CHI)
(2002, 1, 6, 8, 1690),  -- Rednecks: RB Pittman,Michael (TB)
(2002, 1, 7, 2, 1955),  -- Werewolves: RB Smith,Antowain (NE)
(2002, 1, 8, 6, 2027),  -- Crusaders: RB Staley,Duce (PHI)
(2002, 1, 9, 4, 1110),  -- Illuminati: DL Johnson,Joe (GB)
(2002, 1, 10, 5, 2822),  -- Freezer Burn: RB Green,William (CLE)
-- Round 2
(2002, 2, 1, 10, 1282),  -- Barbarians: LB Lewis,Ray (BAL)
(2002, 2, 2, 7, 2103),  -- MeggaMen: RB Taylor,Fred (JAX)
(2002, 2, 3, 1, 410),  -- War Eagles: LB Clemons,Charlie (NO)
(2002, 2, 4, 9, 303),  -- Green Wave: WR Burress,Plaxico (PIT)
(2002, 2, 5, 3, 2889),  -- Norsemen: DL Peppers,Julius (CAR)
(2002, 2, 6, 1, 251),  -- War Eagles: DL Brown,Courtney (CLE)
(2002, 2, 7, 2, 1973),  -- Werewolves: WR Smith,Jimmy (JAX)
(2002, 2, 8, 6, 2012),  -- Crusaders: LB Spikes,Takeo (CIN)
(2002, 2, 9, 4, 2915),  -- Illuminati: TE Shockey,Jeremy (NYG)
(2002, 2, 10, 5, 1356),  -- Freezer Burn: WR Mason,Derrick (TEN)
-- Round 3
(2002, 3, 1, 10, 3043),  -- Barbarians: K Vanderjagt,Mike (IND)
(2002, 3, 2, 7, 1548),  -- MeggaMen: WR Moulds,Eric (BUF)
(2002, 3, 3, 1, 1646),  -- War Eagles: LB Patton,Marvcus (KC)
(2002, 3, 4, 9, 435),  -- Green Wave: WR Coles,Laveranues (NYJ)
(2002, 3, 5, 3, 1466),  -- Norsemen: LB Miller,Jamir (CLE)
(2002, 3, 6, 8, 983),  -- Rednecks: DL Holliday,Vonnie (GB)
(2002, 3, 7, 2, 2105),  -- Werewolves: DL Taylor,Jason (MIA)  [manually resolved - see note below]
(2002, 3, 8, 6, 1974),  -- Crusaders: DL Smith,Justin (CIN)
(2002, 3, 9, 4, 452),  -- Illuminati: WR Conway,Curtis (SD)
(2002, 3, 10, 5, 2304),  -- Freezer Burn: DL Wiley,Marcellus (SD)
-- Round 4
(2002, 4, 1, 10, 936),  -- Barbarians: RB Hearst,Garrison (SF)
(2002, 4, 2, 7, 1598),  -- MeggaMen: DB O'Neal,Deltha (DEN)
(2002, 4, 3, 8, 2951),  -- Rednecks: DB Williams,Roy (DAL)
(2002, 4, 4, 9, 2718),  -- Green Wave: QB Stewart,Kordell (PIT)
(2002, 4, 5, 3, 1425),  -- Norsemen: LB McKinnon,Ronald (ARI)
(2002, 4, 6, 1, 2808),  -- War Eagles: WR Gaffney,Jabar (HOU)
(2002, 4, 7, 2, 1274),  -- Werewolves: TE Lewis,Chad (PHI)
(2002, 4, 8, 6, 2787),  -- Crusaders: RB Duckett,T.J. (ATL)
(2002, 4, 9, 4, 1169),  -- Illuminati: WR Jurevicius,Joe (TB)
(2002, 4, 10, 5, 2802),  -- Freezer Burn: RB Foster,De'shaun (CAR)
-- Round 5
(2002, 5, 1, 10, 612),  -- Barbarians: RB Dunn,Warrick (ATL)
(2002, 5, 2, 7, 1774),  -- MeggaMen: DL Rice,Simeon (TB)
(2002, 5, 3, 8, 1374),  -- Rednecks: WR McCaffrey,Ed (DEN)
(2002, 5, 4, 9, 365),  -- Green Wave: TE Chamberlain,Byron (MIN)
(2002, 5, 5, 6, 275),  -- Crusaders: WR Brown,Troy (NE)
(2002, 5, 6, 1, 934),  -- War Eagles: TE Heap,Todd (BAL)
(2002, 5, 7, 2, 1910),  -- Werewolves: DB Sharper,Darren (GB)
(2002, 5, 8, 6, 834),  -- Crusaders: DB Griffith,Robert (CLE)
(2002, 5, 9, 4, 2898),  -- Illuminati: DB Reed,Edward (BAL)
(2002, 5, 10, 5, 729),  -- Freezer Burn: DB Fuller,Corey (CLE)
-- Round 6
(2002, 6, 1, 10, 1772),  -- Barbarians: WR Rice,Jerry (OAK)
(2002, 6, 2, 7, 1433),  -- MeggaMen: DB McNeil,Ryan (SD)
(2002, 6, 3, 8, 2893),  -- Rednecks: RB Portis,Clinton (DEN)
(2002, 6, 4, 9, 1897),  -- Green Wave: LB Seau,Junior (SD)
(2002, 6, 5, 3, 821),  -- Norsemen: DB Green,Victor (NE)
(2002, 6, 6, 1, 214),  -- War Eagles: DL Brackens,Tony (JAX)
(2002, 6, 7, 2, 2203),  -- Werewolves: DB Vincent,Troy (PHI)
(2002, 6, 8, 6, 105),  -- Crusaders: RB Barlow,Kevan (SF)
(2002, 6, 9, 4, 2906),  -- Illuminati: DB Rumph,Mike (SF)
(2002, 6, 10, 7, 2831),  -- MeggaMen: DL Haynesworth,Albert (TEN)
-- Round 7
(2002, 7, 1, 10, 1949),  -- Barbarians: TE Sloan,David (NO)
(2002, 7, 2, 7, 268),  -- MeggaMen: DB Brown,Mike (CHI)  [manually resolved - see note below]
(2002, 7, 3, 8, 1376),  -- Rednecks: WR McCardell,Keenan (TB)
(2002, 7, 4, 9, 1579),  -- Green Wave: LB Nguyen,Dat (DAL)
(2002, 7, 5, 3, 2372),  -- Norsemen: DL Wistrom,Grant (STL)
(2002, 7, 6, 1, 1469),  -- War Eagles: DB Milloy,Lawyer (NE)
(2002, 7, 7, 2, 744),  -- Werewolves: RB Garner,Charlie (OAK)
(2002, 7, 8, 6, 396),  -- Crusaders: LB Claiborne,Chris (DET)
(2002, 7, 9, 4, 201),  -- Illuminati: LB Boulware,Peter (BAL)
(2002, 7, 10, 7, 2922),  -- MeggaMen: WR Stallworth,Donte' (NO)
-- Round 8
(2002, 8, 1, 10, 663),  -- Barbarians: LB Farrior,James (PIT)
(2002, 8, 2, 7, 2925),  -- MeggaMen: TE Stevens,Jerramy (SEA)
(2002, 8, 3, 8, 2699),  -- Rednecks: QB Plummer,Jake (ARI)
(2002, 8, 4, 9, 584),  -- Green Wave: DL Douglas,Hugh (PHI)
(2002, 8, 5, 3, 652),  -- Norsemen: DB Evans,Doug (SEA)
(2002, 8, 6, 1, 1482),  -- War Eagles: DB Minter,Mike (CAR)
(2002, 8, 7, 2, 2647),  -- Werewolves: QB Griese,Brian (DEN)
(2002, 8, 8, 6, 409),  -- Crusaders: DB Clements,Nate (BUF)
(2002, 8, 9, 4, 2628),  -- Illuminati: QB Fiedler,Jay (MIA)
(2002, 8, 10, 5, 512),  -- Freezer Burn: DL Daniels,Philip (CHI)
-- Round 9
(2002, 9, 1, 10, 236),  -- Barbarians: DB Bronson,Zack (SF)
(2002, 9, 2, 7, 2661),  -- MeggaMen: QB Johnson,Brad (TB)
(2002, 9, 3, 8, 1702),  -- Rednecks: LB Polley,Tommy (STL)
(2002, 9, 4, 9, 2159),  -- Green Wave: DB Tillman,Pat (---)
(2002, 9, 7, 2, 1153),  -- Werewolves: LB Jones,Marvin (NYJ)
(2002, 9, 8, 6, 1708),  -- Crusaders: DL Porcher,Robert (DET)
(2002, 9, 10, 5, 2593),  -- Freezer Burn: QB Brady,Tom (NE)
-- Round 10
(2002, 10, 1, 10, 2352),  -- Barbarians: DB Williams,Tyrone (GB)
(2002, 10, 2, 7, 3020),  -- MeggaMen: K  (Janikowski,SebastianOAK)  [manually resolved - see note below]
(2002, 10, 3, 8, 3001),  -- Rednecks: K Elam,Jason (DEN)
(2002, 10, 4, 9, 1825),  -- Green Wave: DL Rucker,Mike (CAR)
(2002, 10, 5, 3, 1546),  -- Norsemen: WR Moss,Santana (NYJ)
(2002, 10, 6, 1, 2167),  -- War Eagles: WR Toomer,Amani (NYG)
(2002, 10, 7, 2, 1159),  -- Werewolves: RB Jones,Thomas (ARI)
(2002, 10, 8, 6, 2235),  -- Crusaders: WR Ward,Hines (PIT)
(2002, 10, 9, 4, 1998),  -- Illuminati: DB Smoot,Fred (WAS)
(2002, 10, 10, 5, 980),  -- Freezer Burn: LB Holdman,Warrick (CHI)
-- Round 11
(2002, 11, 1, 10, 1244),  -- Barbarians: DL Lang,Kenard (CLE)
(2002, 11, 2, 7, 1318),  -- MeggaMen: RB Mack,Stacey (JAX)
(2002, 11, 3, 8, 59),  -- Rednecks: LB Armstead,Jessie (WAS)
(2002, 11, 4, 9, 453),  -- Green Wave: TE Conwell,Ernie (STL)
(2002, 11, 5, 3, 2988),  -- Norsemen: K Brown,Kris (HOU)
(2002, 11, 6, 1, 1163),  -- War Eagles: RB Jordan,Lamont (NYJ)
(2002, 11, 7, 2, 2228),  -- Werewolves: TE Walls,Wesley (CAR)
(2002, 11, 8, 6, 133),  -- Crusaders: TE Becht,Anthony (NYJ)
(2002, 11, 9, 4, 248),  -- Illuminati: LB Brown,Chad (SEA)
(2002, 11, 10, 5, 2310),  -- Freezer Burn: DB Williams,Aeneas (STL)
-- Round 12
(2002, 12, 1, 10, 636),  -- Barbarians: DL Ellis,Greg (DAL)
(2002, 12, 2, 7, 2943),  -- MeggaMen: RB Wells,Jonathan (HOU)
(2002, 12, 3, 8, 492),  -- Rednecks: TE Crumpler,Alge (ATL)
(2002, 12, 5, 3, 2600),  -- Norsemen: QB Brunell,Mark (JAX)
(2002, 12, 6, 1, 991),  -- War Eagles: LB Holmes,Earl (CLE)
(2002, 12, 7, 2, 2157),  -- Werewolves: WR Thrash,James (PHI)
(2002, 12, 8, 6, 2678),  -- Crusaders: QB Martin,Jamie (STL)
(2002, 12, 9, 4, 1936),  -- Illuminati: DL Simon,Corey (PHI)
(2002, 12, 10, 5, 2806);  -- Freezer Burn: DL Freeney,Dwight (IND)

-- Manually resolved ambiguous names (multiple players table matches for the
-- same last/first name; resolved using the NFL team on the pick line, the
-- player's real draft era, and/or retirement year):
--   Taylor,Jason (DL, MIA) -> playerid 2105, Jason Taylor, Miami Dolphins DE/LB drafted 1997 rd3 (not playerid 14847, drafted 2023)
--   Brown,Mike (DB, CHI) -> playerid 268, Mike Brown, Chicago Bears S drafted 2000 rd2 (not playerid 10210 [WR] or 14620 [DB, no draft info, team TEN])
--   Janikowski,Sebastian (K, OAK) -> playerid 3020; source line has no space before the trailing NFL code ("SebastianOAK")
