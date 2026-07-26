-- 1999 WMFFL draft picks, transcribed from football/history/1999Season/draftpicks.php.
--
-- Only season/round/pick/teamid/playerid are populated (id is auto-increment,
-- orgTeam and pickTime are left for Josh to fill in as needed).
-- Player ids were matched by lastname/firstname against the players table;
-- picks that were streaming placeholders ("team QB", "team kicker", "team
-- offense") or explicitly "(NONE)" in the source have no real player and are
-- listed instead in insert_1999-2004_draftpicks_unresolved.sql, not here.

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(1999, 1, 1, 1, 1624),  -- Archers Who Say Ni: WR OWENS,TERRELL (SF)
(1999, 1, 2, 4, 2344),  -- Hempaholics: RB WILLIAMS,RICKY (NO)
(1999, 1, 3, 3, 1548),  -- Norsemen: WR MOULDS,ERIC (BUF)
(1999, 1, 4, 2, 2027),  -- Werewolves: RB STALEY,DUCE (PHI)
(1999, 1, 5, 10, 1989),  -- Barbarians: WR SMITH,ROD (DEN)
(1999, 1, 6, 9, 536),  -- Green Wave: TE DAVIS,TYRONE (GB)
(1999, 1, 7, 8, 1973),  -- ZEN: WR SMITH,JIMMY (JAX)
(1999, 1, 8, 6, 1955),  -- Crusaders: RB SMITH,ANTOWAIN (BUF)
-- Round 2
(1999, 2, 1, 1, 1554),  -- Archers Who Say Ni: RB MURRELL,ADRIAN (ARI)
(1999, 2, 2, 8, 240),  -- ZEN: LB BROOKS,DERRICK (TB)
(1999, 2, 3, 3, 113),  -- Norsemen: LB BARROW,MICHEAL (CAR)
(1999, 2, 4, 2, 162),  -- Werewolves: LB BIEKERT,GREG (OAK)
(1999, 2, 5, 10, 882),  -- Barbarians: LB HARDY,KEVIN (JAX)
(1999, 2, 7, 4, 1404),  -- Hempaholics: WR MCDUFFIE,O.J. (MIA)
(1999, 2, 8, 6, 1469),  -- Crusaders: DB MILLOY,LAWYER (NE)
-- Round 3
(1999, 3, 2, 4, 2012),  -- Hempaholics: LB SPIKES,TAKEO (CIN)
(1999, 3, 3, 3, 779),  -- Norsemen: DL GLOVER,LA' ROI (NO)
(1999, 3, 4, 2, 227),  -- Werewolves: DL BRATZKE,CHAD (IND)
(1999, 3, 6, 9, 1772),  -- Green Wave: WR RICE,JERRY (SF)
(1999, 3, 7, 8, 355),  -- ZEN: DB CARTER,MARTY (ATL)
(1999, 3, 8, 6, 1897),  -- Crusaders: LB SEAU,JUNIOR (SD)
-- Round 4
(1999, 4, 1, 1, 567),  -- Archers Who Say Ni: RB DILLON,COREY (CIN)
(1999, 4, 2, 8, 3463),  -- ZEN: RB MORRIS,BAM (KC)
(1999, 4, 3, 3, 1066),  -- Norsemen: RB JAMES,EDGERRIN (IND)
(1999, 4, 4, 2, 2250),  -- Werewolves: DB WASHINGTON,DEWAYNE (PIT)
(1999, 4, 5, 10, 1137),  -- Barbarians: TE JONES,DAMON (JAX)
(1999, 4, 6, 9, 1420),  -- Green Wave: DL MCKENZIE,KEITH (GB)
(1999, 4, 7, 4, 1774),  -- Hempaholics: DL RICE,SIMEON (ARI)
(1999, 4, 8, 6, 649),  -- Crusaders: RB ENIS,CURTIS (CHI)
-- Round 5
(1999, 5, 1, 6, 1110),  -- Crusaders: DL JOHNSON,JOE (NO)
(1999, 5, 2, 4, 993),  -- Hempaholics: RB HOLMES,PRIEST (BAL)
(1999, 5, 4, 10, 388),  -- Barbarians: WR CHREBET,WAYNE (NYJ)
(1999, 5, 5, 2, 971),  -- Werewolves: DB HITCHCOCK,JIMMY (MIN)
(1999, 5, 6, 3, 834),  -- Norsemen: DB GRIFFITH,ROBERT (MIN)
(1999, 5, 7, 8, 1960),  -- ZEN: DL SMITH,CHUCK (ATL)
(1999, 5, 8, 1, 2383),  -- Archers Who Say Ni: DB WOODSON,ROD (BAL)
-- Round 6
(1999, 6, 2, 4, 2052),  -- Hempaholics: WR STOKES,J.J. (SF)
(1999, 6, 4, 10, 1978),  -- Barbarians: DL SMITH,MARK (ARI)
(1999, 6, 5, 2, 1214),  -- Werewolves: LB KIRKLAND,LEVON (PIT)
(1999, 6, 6, 3, 1823),  -- Norsemen: DL ROYE,ORPHEUS (PIT)
(1999, 6, 7, 8, 777),  -- ZEN: WR GLENN,TERRY (NE)
(1999, 6, 8, 1, 1826),  -- Archers Who Say Ni: LB RUDD,DWAYNE (MIN)
-- Round 7
(1999, 7, 1, 6, 25),  -- Crusaders: TE ALEXANDER,STEPHEN (WAS)
(1999, 7, 2, 4, 84),  -- Hempaholics: DB BAILEY,CHAMP (WAS)
(1999, 7, 3, 9, 1394),  -- Green Wave: LB MCDANIEL,ED (MIN)
(1999, 7, 4, 10, 145),  -- Barbarians: DB BELLAMY,JAY (SEA)
(1999, 7, 5, 2, 274),  -- Werewolves: WR BROWN,TIM (OAK)
(1999, 7, 7, 8, 3772),  -- ZEN: LB THOMAS,DERRICK (KC)
(1999, 7, 8, 1, 1831),  -- Archers Who Say Ni: DL RUSSELL,DARRELL (OAK)
-- Round 8
(1999, 8, 1, 6, 2124),  -- Crusaders: WR THIGPEN,YANCEY (TEN)
(1999, 8, 2, 4, 2405),  -- Hempaholics: DL YOUNG,BRYANT (SF)
(1999, 8, 3, 9, 523),  -- Green Wave: DB DAVIS,ERIC (CAR)
(1999, 8, 4, 10, 1860),  -- Barbarians: TE SANTIAGO,O.J. (ATL)
(1999, 8, 5, 2, 858),  -- Werewolves: DL HALL,TRAVIS (ATL)
(1999, 8, 6, 3, 784),  -- Norsemen: TE GONZALEZ,TONY (KC)
(1999, 8, 7, 8, 1906),  -- ZEN: DB SHADE,SAM (WAS)
(1999, 8, 8, 1, 878),  -- Archers Who Say Ni: DL HANSEN,PHIL (BUF)
-- Round 9
(1999, 9, 1, 9, 1684),  -- Green Wave: WR PICKENS,CARL (CIN)
(1999, 9, 3, 8, 51),  -- ZEN: DL ARCHAMBEAU,LESTER (ATL)
(1999, 9, 4, 3, 741),  -- Norsemen: LB GARDNER,BARRY (PHI)
(1999, 9, 5, 4, 1241),  -- Hempaholics: DB LAKE,CARNELL (JAX)
(1999, 9, 6, 10, 1),  -- Barbarians: RB ABDUL-JABBAR,KARIM (MIA)
(1999, 9, 7, 6, 5080),  -- Crusaders: LB SMITH,DEREK (WAS)
(1999, 9, 8, 2, 262),  -- Werewolves: RB BROWN,GARY (NYG)
-- Round 10
(1999, 10, 1, 9, 353),  -- Green Wave: DL CARTER,KEVIN (STL)
(1999, 10, 2, 1, 678),  -- Archers Who Say Ni: LB FIELDS,MARK (NO)
(1999, 10, 3, 8, 631),  -- ZEN: WR EDWARDS,TROY (PIT)
(1999, 10, 4, 3, 199),  -- Norsemen: WR BOSTON,DAVID (ARI)
(1999, 10, 5, 4, 1481),  -- Hempaholics: LB MINTER,BARRY (CHI)
(1999, 10, 6, 10, 972),  -- Barbarians: RB HOARD,LEROY (MIN)
(1999, 10, 7, 6, 1439),  -- Crusaders: RB MEANS,NATRONE (SD)
(1999, 10, 8, 2, 1781),  -- Werewolves: TE RIEMERSMA,JAY (BUF)
-- Round 11
(1999, 11, 1, 9, 696),  -- Green Wave: RB FLETCHER,TERRELL (SD)
(1999, 11, 2, 1, 1402),  -- Archers Who Say Ni: DB MCDONALD,TIM (SF)
(1999, 11, 3, 8, 1466),  -- ZEN: LB MILLER,JAMIR (CLE)
(1999, 11, 4, 3, 278),  -- Norsemen: WR BRUCE,ISAAC (STL)
(1999, 11, 6, 10, 1281),  -- Barbarians: LB LEWIS,MO (NYJ)
-- Round 12
(1999, 12, 1, 9, 160),  -- Green Wave: RB BETTIS,JEROME (PIT)
(1999, 12, 2, 1, 2393),  -- Archers Who Say Ni: TE WYCHECK,FRANK (TEN)
(1999, 12, 4, 3, 821),  -- Norsemen: DB GREEN,VICTOR (NYJ)
(1999, 12, 5, 4, 612),  -- Hempaholics: RB DUNN,WARRICK (TB)
(1999, 12, 7, 6, 1220),  -- Crusaders: DB KNIGHT,SAMMY (---)
(1999, 12, 8, 2, 161),  -- Werewolves: RB BIAKABUTUKA,TIM (CAR)
-- Round 13
(1999, 13, 1, 2, 1376),  -- Werewolves: WR MCCARDELL,KEENAN (JAX)
(1999, 13, 2, 6, 584),  -- Crusaders: DL DOUGLAS,HUGH (PHI)
(1999, 13, 3, 10, 1257),  -- Barbarians: RB LEE,AMP (STL)
(1999, 13, 7, 1, 959),  -- Archers Who Say Ni: RB HICKS,SKIP (WAS)
-- Round 14
(1999, 14, 2, 6, 1854),  -- Crusaders: WR SANDERS,FRANK (ARI)
(1999, 14, 5, 3, 259),  -- Norsemen: DB BROWN,ERIC (DEN)
(1999, 14, 7, 1, 1037),  -- Archers Who Say Ni: WR ISMAIL,RAGHIB (DAL)
-- Round 15
(1999, 15, 1, 2, 1179),  -- Werewolves: RB KAUFMAN,NAPOLEON (OAK)
(1999, 15, 3, 10, 1708),  -- Barbarians: DL PORCHER,ROBERT (DET)
(1999, 15, 4, 4, 1142),  -- Hempaholics: TE JONES,FREDDIE (SD)
(1999, 15, 6, 8, 814),  -- ZEN: TE GREEN,ERIC (NYJ)
(1999, 15, 7, 1, 1961);  -- Archers Who Say Ni: LB SMITH,DARRIN (SEA)
