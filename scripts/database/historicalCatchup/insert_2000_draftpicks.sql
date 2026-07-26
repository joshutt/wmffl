-- 2000 WMFFL draft picks, transcribed from football/history/2000Season/draftpicks.php.
--
-- Only season/round/pick/teamid/playerid are populated (id is auto-increment,
-- orgTeam and pickTime are left for Josh to fill in as needed).
-- Player ids were matched by lastname/firstname against the players table;
-- picks that were streaming placeholders ("team QB", "team kicker", "team
-- offense") or explicitly "(NONE)" in the source have no real player and are
-- listed instead in insert_1999-2004_draftpicks_unresolved.sql, not here.
--
-- The source page still calls team 7 "Expansion Team 2" (Bryan Jones' new
-- franchise, awarded July 2000); it was renamed MeggaMen before the season
-- started and is used under that name everywhere else in the 2000 history
-- pages, so its picks here use teamid 7.

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(2000, 1, 1, 4, 450),  -- Hempaholics: WR CONNELL,ALBERT (WAS)
(2000, 1, 2, 1, 2281),  -- Archers Who Say Ni: RB WHEATLEY,TYRONE (OAK)
(2000, 1, 3, 5, 4),  -- Freezer Burn: DB ABRAHAM,DONNIE (TB)
(2000, 1, 4, 10, 744),  -- Barbarians: RB GARNER,CHARLIE (SF)
(2000, 1, 6, 9, 1075),  -- Green Wave: WR JEFFERS,PATRICK (CAR)
(2000, 1, 7, 6, 1481),  -- Crusaders: LB MINTER,BARRY (CHI)
(2000, 1, 8, 2, 1549),  -- Werewolves: WR MUHAMMAD,MUHSIN (CAR)
(2000, 1, 9, 8, 546),  -- ZEN: RB DAYNE,RON (NYG)
(2000, 1, 11, 5, 1154),  -- Freezer Burn: LB JONES,MIKE A. (STL)
-- Round 2
(2000, 2, 1, 4, 716),  -- Hempaholics: TE FRANKS,BUBBA (GB)
(2000, 2, 2, 1, 1826),  -- Archers Who Say Ni: LB RUDD,DWAYNE (MIN)
(2000, 2, 3, 5, 2167),  -- Freezer Burn: WR TOOMER,AMANI (NYG)
(2000, 2, 4, 10, 1949),  -- Barbarians: TE SLOAN,DAVID (DET)
(2000, 2, 5, 7, 720),  -- MeggaMen (Expansion Team 2): LB FREDRICKSON,ROB (ARI)
(2000, 2, 6, 9, 1214),  -- Green Wave: LB KIRKLAND,LEVON (PIT)
(2000, 2, 7, 6, 1192),  -- Crusaders: DL KENNEDY,CORTEZ (SEA)
(2000, 2, 8, 2, 540),  -- Werewolves: DB DAWKINS,BRIAN (PHI)
(2000, 2, 9, 8, 1425),  -- ZEN: LB MCKINNON,RONALD (ARI)
(2000, 2, 11, 7, 2166),  -- MeggaMen (Expansion Team 2): DB TONGUE,REGGIE (SEA)
(2000, 2, 12, 5, 432),  -- Freezer Burn: DL COLEMAN,MARCO (WAS)
-- Round 3
(2000, 3, 1, 4, 567),  -- Hempaholics: RB DILLON,COREY (CIN)
(2000, 3, 2, 1, 2347),  -- Archers Who Say Ni: TE WILLIAMS,ROLAND (STL)
(2000, 3, 3, 5, 2027),  -- Freezer Burn: RB STALEY,DUCE (PHI)
(2000, 3, 4, 10, 747),  -- Barbarians: RB GARY,OLANDIS (DEN)
(2000, 3, 5, 7, 1773),  -- MeggaMen (Expansion Team 2): DB RICE,RON (DET)
(2000, 3, 7, 6, 1542),  -- Crusaders: WR MORTON,JOHNNIE (DET)
(2000, 3, 8, 2, 248),  -- Werewolves: LB BROWN,CHAD (SEA)
(2000, 3, 9, 8, 1548),  -- ZEN: WR MOULDS,ERIC (BUF)
(2000, 3, 10, 3, 2012),  -- Norsemen: LB SPIKES,TAKEO (CIN)
(2000, 3, 11, 5, 2175),  -- Freezer Burn: LB TROTTER,JEREMIAH (PHI)
-- Round 4
(2000, 4, 2, 8, 251),  -- ZEN: DL BROWN,COURTNEY (CLE)
(2000, 4, 3, 4, 2058),  -- Hempaholics: DL STRAHAN,MICHAEL (NYG)
(2000, 4, 4, 6, 161),  -- Crusaders: RB BIAKABUTUKA,TIM (CAR)
(2000, 4, 5, 9, 1212),  -- Green Wave: RB KIRBY,TERRY (CLE)
(2000, 4, 6, 7, 777),  -- MeggaMen (Expansion Team 2): WR GLENN,TERRY (NE)
(2000, 4, 7, 10, 1885),  -- Barbarians: WR SCOTT,DARNAY (CIN)
(2000, 4, 8, 5, 1511),  -- Freezer Burn: TE MOORE,DAVE (TB)
(2000, 4, 9, 1, 1910),  -- Archers Who Say Ni: DB SHARPER,DARREN (GB)
(2000, 4, 10, 4, 2203),  -- Hempaholics: DB VINCENT,TROY (PHI)
-- Round 5
(2000, 5, 1, 6, 909),  -- Crusaders: DB HARRISON,RODNEY (SD)
(2000, 5, 2, 8, 355),  -- ZEN: DB CARTER,MARTY (ATL)
(2000, 5, 3, 2, 353),  -- Werewolves: DL CARTER,KEVIN (STL)
(2000, 5, 4, 3, 102),  -- Norsemen: LB BARBER,SHAWN (WAS)
(2000, 5, 5, 9, 1732),  -- Green Wave: DL PRYCE,TREVOR (DEN)
(2000, 5, 8, 5, 570),  -- Freezer Burn: DB DISHMAN,CRIS (KC)
(2000, 5, 9, 1, 2372),  -- Archers Who Say Ni: DL WISTROM,GRANT (STL)
(2000, 5, 10, 4, 1111),  -- Hempaholics: WR JOHNSON,KEVIN (CLE)
-- Round 6
(2000, 6, 1, 3, 2017),  -- Norsemen: DB SPRINGS,SHAWN (SEA)
(2000, 6, 2, 8, 1987),  -- ZEN: RB SMITH,ROBERT (MIN)
(2000, 6, 3, 2, 1310),  -- Werewolves: DB LYGHT,TODD (STL)
(2000, 6, 5, 9, 1495),  -- Green Wave: TE MITCHELL,PETE (NYG)
(2000, 6, 6, 7, 1374),  -- MeggaMen (Expansion Team 2): WR MCCAFFREY,ED (DEN)
(2000, 6, 7, 10, 2354),  -- Barbarians: DB WILLIAMS,WILLIE (SEA)
(2000, 6, 8, 5, 1708),  -- Freezer Burn: DL PORCHER,ROBERT (DET)
(2000, 6, 9, 1, 1769),  -- Archers Who Say Ni: RB RHETT,ERRICT (CLE)
(2000, 6, 10, 4, 2194),  -- Hempaholics: LB URLACHER,BRIAN (CHI)
-- Round 7
(2000, 7, 1, 1, 1493),  -- Archers Who Say Ni: LB MITCHELL,KEITH (NO)
(2000, 7, 2, 9, 1641),  -- Green Wave: DB PARRISH,TONY (CHI)
(2000, 7, 3, 10, 1466),  -- Barbarians: LB MILLER,JAMIR (CLE)
(2000, 7, 4, 7, 1326),  -- MeggaMen (Expansion Team 2): DL MAMULA,MIKE (PHI)
(2000, 7, 6, 4, 2405),  -- Hempaholics: DL YOUNG,BRYANT (SF)
(2000, 7, 7, 2, 1863),  -- Werewolves: DL SAPP,WARREN (TB)
(2000, 7, 8, 6, 149),  -- Crusaders: LB BENNETT,CORNELIUS (IND)
(2000, 7, 10, 8, 983),  -- ZEN: DL HOLLIDAY,VONNIE (GB)
-- Round 8
(2000, 8, 1, 1, 614),  -- Archers Who Say Ni: WR DWIGHT,TIM (ATL)
(2000, 8, 4, 7, 160),  -- MeggaMen (Expansion Team 2): RB BETTIS,JEROME (PIT)
(2000, 8, 5, 5, 737),  -- Freezer Burn: WR GALLOWAY,JOEY (DAL)
(2000, 8, 7, 2, 996),  -- Werewolves: WR HOLT,TORRY (STL)
(2000, 8, 8, 6, 1314),  -- Crusaders: DB LYNCH,JOHN (TB)
(2000, 8, 9, 3, 1350),  -- Norsemen: WR MARTIN,TONY (MIA)
(2000, 8, 10, 8, 36),  -- ZEN: DB AMBROSE,ASHLEY (ATL)
-- Round 9
(2000, 9, 1, 1, 1322),  -- Archers Who Say Ni: DB MADISON,SAM (MIA)
(2000, 9, 2, 9, 1249),  -- Green Wave: DB LASSITER,KWAMIE (ARI)
(2000, 9, 4, 7, 1133),  -- MeggaMen (Expansion Team 2): DL JONES,CEDRIC (NYG)
(2000, 9, 7, 2, 1897),  -- Werewolves: LB SEAU,JUNIOR (SD)
(2000, 9, 8, 6, 1960),  -- Crusaders: DL SMITH,CHUCK (CAR)
(2000, 9, 9, 3, 227),  -- Norsemen: DL BRATZKE,CHAD (IND)
(2000, 9, 10, 8, 1931),  -- ZEN: LB SIMMONS,BRIAN (CIN)
-- Round 10
(2000, 10, 2, 3, 580),  -- Norsemen: DL DOLEMAN,CHRIS (MIN)
(2000, 10, 4, 2, 1159),  -- Werewolves: RB JONES,THOMAS (ARI)
(2000, 10, 5, 4, 1722),  -- Hempaholics: WR PRICE,PEERLESS (BUF)
(2000, 10, 7, 7, 649),  -- MeggaMen (Expansion Team 2): RB ENIS,CURTIS (CHI)
(2000, 10, 10, 1, 779),  -- Archers Who Say Ni: DL GLOVER,LA' ROI (NO)
-- Round 11
(2000, 11, 1, 8, 133),  -- ZEN: TE BECHT,ANTHONY (NYJ)
(2000, 11, 2, 3, 972),  -- Norsemen: RB HOARD,LEROY (MIN)
(2000, 11, 3, 6, 612),  -- Crusaders: RB DUNN,WARRICK (TB)
(2000, 11, 5, 4, 1025),  -- Hempaholics: RB HUNTLEY,RICHARD (PIT)
(2000, 11, 6, 5, 303),  -- Freezer Burn: WR BURRESS,PLAXICO (PIT)
(2000, 11, 7, 7, 1701),  -- MeggaMen (Expansion Team 2): TE POLLARD,MARCUS (IND)
(2000, 11, 9, 9, 1036),  -- Green Wave: WR ISMAIL,QADRY (BAL)
-- Round 12
(2000, 12, 2, 3, 31),  -- Norsemen: RB ALLEN,TERRY (---)
(2000, 12, 3, 6, 1194),  -- Crusaders: WR KENNISON,EDDIE (CHI)
(2000, 12, 4, 2, 1598),  -- Werewolves: DB O'NEAL,DELTHA (DEN)
(2000, 12, 5, 4, 63),  -- Hempaholics: LB ARRINGTON,LAVAR (WAS)
(2000, 12, 6, 5, 1695),  -- Freezer Burn: DB PLUMMER,AHMED (SF)
(2000, 12, 7, 7, 1797),  -- MeggaMen (Expansion Team 2): LB ROBINSON,EDDIE (TEN)
(2000, 12, 9, 9, 1123);  -- Green Wave: DL JOHNSON,RAYLEE (SD)
