-- 2001 WMFFL draft picks, transcribed from football/history/2001Season/draftpicks.php.
--
-- Only season/round/pick/teamid/playerid are populated (id is auto-increment,
-- orgTeam and pickTime are left for Josh to fill in as needed).
-- Player ids were matched by lastname/firstname against the players table;
-- picks that were streaming placeholders ("team QB", "team kicker", "team
-- offense") or explicitly "(NONE)" in the source have no real player and are
-- listed instead in insert_1999-2004_draftpicks_unresolved.sql, not here.

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(2001, 1, 1, 8, 152),  -- ZEN: RB BENNETT,MICHAEL (MIN)
(2001, 1, 2, 9, 2260),  -- Green Wave: RB WATTERS,RICKY (SEA)
(2001, 1, 3, 5, 612),  -- Freezer Burn: RB DUNN,WARRICK (TB)
(2001, 1, 5, 2, 1989),  -- Werewolves: WR SMITH,ROD (DEN)
(2001, 1, 6, 8, 20),  -- ZEN: WR ALEXANDER,DERRICK (KC)
(2001, 1, 7, 10, 999),  -- Barbarians: WR HORN,JOE (NO)
(2001, 1, 8, 7, 42),  -- MeggaMen: RB ANDERSON,JAMAL (ATL)
(2001, 1, 9, 4, 278),  -- Hempaholics: WR BRUCE,ISAAC (STL)
(2001, 1, 10, 6, 2281),  -- Crusaders: RB WHEATLEY,TYRONE (OAK)
-- Round 2
(2001, 2, 1, 8, 160),  -- ZEN: RB BETTIS,JEROME (PIT)
(2001, 2, 2, 9, 1549),  -- Green Wave: WR MUHAMMAD,MUHSIN (CAR)
(2001, 2, 3, 5, 781),  -- Freezer Burn: LB GODFREY,RANDALL (TEN)
(2001, 2, 5, 2, 2165),  -- Werewolves: RB TOMLINSON,LADAINIAN (SD)
(2001, 2, 6, 1, 950),  -- War Eagles: RB HENRY,TRAVIS (BUF)
(2001, 2, 8, 7, 1469),  -- MeggaMen: DB MILLOY,LAWYER (NE)
(2001, 2, 9, 4, 1897),  -- Hempaholics: LB SEAU,JUNIOR (SD)
(2001, 2, 10, 6, 162),  -- Crusaders: LB BIEKERT,GREG (OAK)
-- Round 3
(2001, 3, 1, 8, 1732),  -- ZEN: DL PRYCE,TREVOR (DEN)
(2001, 3, 3, 5, 1998),  -- Freezer Burn: DB SMOOT,FRED (WAS)
(2001, 3, 5, 2, 2159),  -- Werewolves: DB TILLMAN,PAT (ARI)
(2001, 3, 6, 1, 1931),  -- War Eagles: LB SIMMONS,BRIAN (CIN)
(2001, 3, 7, 10, 1646),  -- Barbarians: LB PATTON,MARVCUS (KC)
(2001, 3, 8, 7, 721),  -- MeggaMen: WR FREEMAN,ANTONIO (GB)
(2001, 3, 9, 4, 2381),  -- Hempaholics: DB WOODSON,CHARLES (OAK)
(2001, 3, 10, 6, 1482),  -- Crusaders: DB MINTER,MIKE (CAR)
-- Round 4
(2001, 4, 1, 6, 1720),  -- Crusaders: RB PRENTICE,TRAVIS (CLE)
(2001, 4, 2, 4, 1110),  -- Hempaholics: DL JOHNSON,JOE (NO)
(2001, 4, 3, 7, 353),  -- MeggaMen: DL CARTER,KEVIN (TEN)
(2001, 4, 4, 10, 1153),  -- Barbarians: LB JONES,MARVIN (NYJ)
(2001, 4, 5, 1, 532),  -- War Eagles: RB DAVIS,TERRELL (DEN)
(2001, 4, 6, 2, 2058),  -- Werewolves: DL STRAHAN,MICHAEL (NYG)
(2001, 4, 7, 3, 1987),  -- Norsemen: RB SMITH,ROBERT (---)
(2001, 4, 8, 5, 301),  -- Freezer Burn: DL BURNETT,ROB (BAL)
(2001, 4, 9, 9, 1958),  -- Green Wave: DL SMITH,BRUCE (WAS)
(2001, 4, 10, 1, 2203),  -- War Eagles: DB VINCENT,TROY (PHI)
-- Round 5
(2001, 5, 1, 6, 2372),  -- Crusaders: DL WISTROM,GRANT (STL)
(2001, 5, 3, 7, 2157),  -- MeggaMen: WR THRASH,JAMES (PHI)
(2001, 5, 4, 10, 274),  -- Barbarians: WR BROWN,TIM (OAK)
(2001, 5, 5, 8, 1812),  -- ZEN: DB ROLLE,SAMARI (TEN)
(2001, 5, 6, 2, 1668),  -- Werewolves: LB PETERSON,MIKE (IND)
(2001, 5, 7, 6, 2108),  -- Crusaders: WR TAYLOR,TRAVIS (BAL)
(2001, 5, 8, 5, 1376),  -- Freezer Burn: WR MCCARDELL,KEENAN (JAX)
(2001, 5, 9, 9, 28),  -- Green Wave: DB ALLEN,ERIC (OAK)
-- Round 6
(2001, 6, 1, 6, 161),  -- Crusaders: RB BIAKABUTUKA,TIM (CAR)
(2001, 6, 2, 4, 25),  -- Hempaholics: TE ALEXANDER,STEPHEN (WAS)
(2001, 6, 3, 7, 2215),  -- MeggaMen: DB WALKER,BRIAN (MIA)
(2001, 6, 4, 10, 1277),  -- Barbarians: RB LEWIS,JAMAL (BAL)
(2001, 6, 6, 2, 1080),  -- Werewolves: DB JENKINS,BILLY (DEN)
(2001, 6, 8, 5, 35),  -- Freezer Burn: RB ALSTOTT,MIKE (TB)
(2001, 6, 9, 9, 768),  -- Green Wave: LB GILDON,JASON (PIT)
(2001, 6, 10, 4, 1356),  -- Hempaholics: WR MASON,DERRICK (TEN)
-- Round 7
(2001, 7, 1, 10, 345),  -- Barbarians: TE CARSWELL,DWAYNE (DEN)
(2001, 7, 3, 2, 1008),  -- Werewolves: DL HOWARD,DARREN (NO)
(2001, 7, 4, 9, 2146),  -- Green Wave: LB THOMAS,WILLIAM (OAK)
(2001, 7, 5, 6, 598),  -- Crusaders: TE DUDLEY,RICKEY (CLE)
(2001, 7, 6, 7, 1159),  -- MeggaMen: RB JONES,THOMAS (ARI)
(2001, 7, 7, 8, 1690),  -- ZEN: RB PITTMAN,MICHAEL (ARI)
(2001, 7, 8, 1, 53),  -- War Eagles: DB ARCHULETA,ADAM (STL)
(2001, 7, 10, 4, 1711),  -- Hempaholics: LB PORTER,JOEY (PIT)
-- Round 8
(2001, 8, 2, 1, 921),  -- War Eagles: WR HATCHETTE,MATTHEW (NYJ)
(2001, 8, 3, 2, 2262),  -- Werewolves: WR WAYNE,REGGIE (IND)
(2001, 8, 4, 9, 1493),  -- Green Wave: LB MITCHELL,KEITH (NO)
(2001, 8, 5, 6, 2245),  -- Crusaders: WR WARRICK,PETER (CIN)
(2001, 8, 6, 7, 63),  -- MeggaMen: LB ARRINGTON,LAVAR (WAS)
(2001, 8, 8, 1, 24),  -- War Eagles: RB ALEXANDER,SHAUN (SEA)
(2001, 8, 9, 3, 248),  -- Norsemen: LB BROWN,CHAD (SEA)
(2001, 8, 10, 8, 1800),  -- ZEN: WR ROBINSON,KOREN (SEA)
-- Round 9
(2001, 9, 1, 10, 1048),  -- Barbarians: DL JACKSON,GRADY (OAK)
(2001, 9, 2, 5, 1531),  -- Freezer Burn: LB MORGAN,DAN (CAR)
(2001, 9, 3, 2, 60),  -- Werewolves: DL ARMSTRONG,TRACE (OAK)
(2001, 9, 4, 9, 304),  -- Green Wave: DB BURRIS,JEFF (IND)
(2001, 9, 6, 7, 2304),  -- MeggaMen: DL WILEY,MARCELLUS (BUF)
(2001, 9, 7, 8, 339),  -- ZEN: DB CARPENTER,KEION (BUF)
(2001, 9, 8, 1, 992),  -- War Eagles: DL HOLMES,KENNY (NYG)
(2001, 9, 9, 3, 1394),  -- Norsemen: LB MCDANIEL,ED (MIN)
(2001, 9, 10, 4, 285),  -- Hempaholics: DB BUCHANAN,RAY (ATL)
-- Round 10
(2001, 10, 3, 8, 1050),  -- ZEN: RB JACKSON,JAMES (CLE)
(2001, 10, 4, 1, 1059),  -- War Eagles: WR JACKSON,WILLIE (NO)
(2001, 10, 6, 6, 1740),  -- Crusaders: LB RAINER,WALI (CLE)
(2001, 10, 7, 9, 2319),  -- Green Wave: DB WILLIAMS,DARRYL (CIN)
(2001, 10, 9, 5, 268),  -- Freezer Burn: DB BROWN,MIKE (CHI)  [manually resolved - see note below]
(2001, 10, 10, 10, 259),  -- Barbarians: DB BROWN,ERIC (DEN)
-- Round 11
(2001, 11, 1, 4, 993),  -- Hempaholics: RB HOLMES,PRIEST (BAL)
(2001, 11, 2, 3, 1906),  -- Norsemen: DB SHADE,SAM (WAS)
(2001, 11, 3, 8, 1491),  -- ZEN: WR MITCHELL,FREDDIE (PHI)
(2001, 11, 4, 1, 1111),  -- War Eagles: WR JOHNSON,KEVIN (CLE)
(2001, 11, 6, 6, 834),  -- Crusaders: DB GRIFFITH,ROBERT (MIN)
(2001, 11, 7, 9, 1967),  -- Green Wave: RB SMITH,EMMITT (DAL)
(2001, 11, 8, 2, 59),  -- Werewolves: LB ARMSTEAD,JESSIE (NYG)
(2001, 11, 9, 5, 1420),  -- Freezer Burn: DL MCKENZIE,KEITH (GB)
(2001, 11, 10, 10, 546),  -- Barbarians: RB DAYNE,RON (NYG)
-- Round 12
(2001, 12, 2, 3, 1281),  -- Norsemen: LB LEWIS,MO (NYJ)
(2001, 12, 3, 8, 490),  -- ZEN: WR CROWELL,GERMANE (DET)
(2001, 12, 5, 7, 716),  -- MeggaMen: TE FRANKS,BUBBA (GB)
(2001, 12, 6, 6, 227),  -- Crusaders: DL BRATZKE,CHAD (IND)
(2001, 12, 7, 9, 1640),  -- Green Wave: DL PARRELLA,JOHN (SD)
(2001, 12, 8, 2, 2114),  -- Werewolves: WR TERRELL,DAVID (CHI)
(2001, 12, 9, 5, 934),  -- Freezer Burn: TE HEAP,TODD (BAL)
(2001, 12, 10, 10, 147);  -- Barbarians: DB BELSER,JASON (IND)

-- Manually resolved ambiguous names (multiple players table matches for the
-- same last/first name; resolved using the NFL team on the pick line, the
-- player's real draft era, and/or retirement year):
--   Brown,Mike (DB, CHI) -> playerid 268, Mike Brown, Chicago Bears S drafted 2000 rd2 (not playerid 10210 [WR] or 14620 [DB, no draft info, team TEN])
