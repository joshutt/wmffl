-- 2001 Week 1 player scores, transcribed from the newsletter at
-- football/history/2001Season/2001wk1.php ("WMFFL Scoring Breakdown").
--
-- activations already holds this week (150 rows: 15 slots x 10 teams,
-- with playerid=0 for the Norsemen's six empty slots from their illegal
-- lineup - the newsletter itself notes "The Norsemen failed to submit a
-- full line-up and therefore were fined $1"), so only playerscores is
-- inserted here. Player ids were taken from the existing activations
-- rows joined to players, and every team's offensive/defensive subtotal
-- was verified against the newsletter totals before writing this file.
-- The Norsemen/War Eagles pairing only prints War Eagles' subtotal in
-- the newsletter; Norsemen's (off 59, def 7) was derived from the
-- printed final scores (War Eagles 16, Norsemen 30) and cross-checked
-- by summing their nine printed player lines.
--
-- Column semantics (per scripts/logscores/transferscores.php):
--   pts    = the player's computed score for the week
--   active = same value when the player was activated, NULL otherwise.
-- The newsletter only records activated players, so pts = active here.

INSERT INTO playerscores (playerid, season, week, pts, active) VALUES
-- War Eagles (teamid 1) - off 23, def 29, final 16
(2438, 2001, 1,  0,  0),  -- HC Reid, Andy (PHI)
(2455, 2001, 1, -2, -2),  -- QB Cleveland Team QB
( 567, 2001, 1, 11, 11),  -- RB Dillon, Corey (CIN)
(2103, 2001, 1,  3,  3),  -- RB Taylor, Fred (JAX)
(1046, 2001, 1,  1,  1),  -- WR Jackson, Darrell (SEA)
(1111, 2001, 1,  0,  0),  -- WR Johnson, Kevin (CLE)
(1274, 2001, 1,  0,  0),  -- TE Lewis, Chad (PHI)
( 560, 2001, 1,  7,  7),  -- K  Detroit Kicker
(1572, 2001, 1,  3,  3),  -- OL New Orleans Offense
( 992, 2001, 1,  1,  1),  -- DL Holmes, Kenny (NYG)
(2105, 2001, 1,  5,  5),  -- DL Taylor, Jason (MIA)
( 474, 2001, 1,  0,  0),  -- LB Cowart, Sam (BUF)
(1931, 2001, 1,  2,  2),  -- LB Simmons, Brian (CIN)
(  53, 2001, 1,  6,  6),  -- DB Archuleta, Adam (STL)
(1910, 2001, 1, 15, 15),  -- DB Sharper, Darren (GB)
-- Norsemen (teamid 3) - off 59, def 7, final 30 (only 9 players; illegal lineup)
(2422, 2001, 1,  3,  3),  -- HC Dungy, Tony (TB)
(2460, 2001, 1, 11, 11),  -- QB Indianapolis Team QB
( 809, 2001, 1, 21, 21),  -- RB Green, Ahman (GB)
(1066, 2001, 1, 20, 20),  -- RB James, Edgerrin (IND)
(1545, 2001, 1,  0,  0),  -- WR Moss, Randy (MIN)
( 784, 2001, 1,  4,  4),  -- TE Gonzalez, Tony (KC)
(1180, 2001, 1,  2,  2),  -- DL Kearse, Jevon (TEN)
(2012, 2001, 1,  5,  5),  -- LB Spikes, Takeo (CIN)
( 821, 2001, 1,  0,  0),  -- DB Green, Victor (NYJ)
-- Green Wave (teamid 9) - off 61, def 24, final 10
(2425, 2001, 1,  0,  0),  -- HC Fisher, Jeff (TEN)
(2464, 2001, 1,  6,  6),  -- QB Minnesota Team QB
( 667, 2001, 1, 16, 16),  -- RB Faulk, Marshall (STL)
(2260, 2001, 1,  6,  6),  -- RB Watters, Ricky (SEA)
( 275, 2001, 1, 13, 13),  -- WR Brown, Troy (NE)
(1036, 2001, 1,  4,  4),  -- WR Ismail, Qadry (BAL)
(1949, 2001, 1,  1,  1),  -- TE Sloan, David (DET)
(  91, 2001, 1,  5,  5),  -- K  Baltimore Kicker
(1029, 2001, 1, 10, 10),  -- OL Indianapolis Offense
(1640, 2001, 1,  6,  6),  -- DL Parrella, John (SD)
(1742, 2001, 1,  5,  5),  -- DL Randle, John (SEA)
( 768, 2001, 1,  1,  1),  -- LB Gildon, Jason (PIT)
(2146, 2001, 1,  8,  8),  -- LB Thomas, William (OAK)
(  28, 2001, 1,  2,  2),  -- DB Allen, Eric (OAK)
( 304, 2001, 1,  2,  2),  -- DB Burris, Jeff (IND)
-- Hempaholics (teamid 4) - off 63, def 51, final 39
(2428, 2001, 1,  4,  4),  -- HC Haslett, Jim (NO)
(2459, 2001, 1,  9,  9),  -- QB Green Bay Team QB
( 754, 2001, 1,  0,  0),  -- RB George, Eddie (TEN)
(2344, 2001, 1, 11, 11),  -- RB Williams, Ricky (NO)
( 278, 2001, 1,  2,  2),  -- WR Bruce, Isaac (STL)
(1973, 2001, 1, 22, 22),  -- WR Smith, Jimmy (JAX)
(  25, 2001, 1,  1,  1),  -- TE Alexander, Stephen (WAS)
(1843, 2001, 1, 13, 13),  -- K  San Diego Kicker
(2020, 2001, 1,  1,  1),  -- OL St. Louis Offense
( 779, 2001, 1,  5,  5),  -- DL Glover, La'roi (NO)
(1110, 2001, 1, 13, 13),  -- DL Johnson, Joe (NO)
(1711, 2001, 1,  8,  8),  -- LB Porter, Joey (PIT)
(2194, 2001, 1,  5,  5),  -- LB Urlacher, Brian (CHI)
(  84, 2001, 1, 11, 11),  -- DB Bailey, Champ (WAS)
( 285, 2001, 1,  9,  9),  -- DB Buchanan, Ray (ATL)
-- Crusaders (teamid 6) - off 13, def 42, final 0 (negative reset to 0)
(2443, 2001, 1,  4,  4),  -- HC Shanahan, Mike (DEN)
(2021, 2001, 1,  5,  5),  -- QB St. Louis Team QB
( 161, 2001, 1, -2, -2),  -- RB Biakabutuka, Tim (CAR)
(2281, 2001, 1,  0,  0),  -- RB Wheatley, Tyrone (OAK)
( 907, 2001, 1,  0,  0),  -- WR Harrison, Marvin (IND)
(1113, 2001, 1,  4,  4),  -- WR Johnson, Keyshawn (TB)
( 598, 2001, 1,  0,  0),  -- TE Dudley, Rickey (CLE)
(1060, 2001, 1,  2,  2),  -- K  Jacksonville Kicker
(1591, 2001, 1,  0,  0),  -- OL NY Giants Offense
( 227, 2001, 1,  4,  4),  -- DL Bratzke, Chad (IND)
( 584, 2001, 1,  6,  6),  -- DL Douglas, Hugh (PHI)
( 162, 2001, 1,  4,  4),  -- LB Biekert, Greg (OAK)
( 695, 2001, 1,  7,  7),  -- LB Fletcher, London (STL)
( 834, 2001, 1, 13, 13),  -- DB Griffith, Robert (MIN)
( 909, 2001, 1,  8,  8),  -- DB Harrison, Rodney (SD)
-- Werewolves (teamid 2) - off 56, def 40, final 14
(2433, 2001, 1,  3,  3),  -- HC Martz, Mike (STL)
(2462, 2001, 1,  2,  2),  -- QB Kansas City Team QB
(1976, 2001, 1, 16, 16),  -- RB Smith, Lamar (MIA)
(2040, 2001, 1,  4,  4),  -- RB Stewart, James (DET)
( 996, 2001, 1,  5,  5),  -- WR Holt, Torry (STL)
(1989, 2001, 1, 18, 18),  -- WR Smith, Rod (DEN)
(2228, 2001, 1,  2,  2),  -- TE Walls, Wesley (CAR)
(2089, 2001, 1,  4,  4),  -- K  Tampa Bay Kicker
(1603, 2001, 1,  2,  2),  -- OL Oakland Offense
(1863, 2001, 1,  1,  1),  -- DL Sapp, Warren (TB)
(2058, 2001, 1,  6,  6),  -- DL Strahan, Michael (NYG)
(1668, 2001, 1,  5,  5),  -- LB Peterson, Mike (IND)
(1930, 2001, 1,  9,  9),  -- LB Simmons, Anthony (SEA)
(1080, 2001, 1,  0,  0),  -- DB Jenkins, Billy (DEN)
(1220, 2001, 1, 19, 19),  -- DB Knight, Sammy (NO)
-- ZEN (teamid 8) - off 45, def 32, final 17
(2436, 2001, 1,  5,  5),  -- HC Mora, Jim (IND)
(2457, 2001, 1, 22, 22),  -- QB Denver Team QB
( 152, 2001, 1,  0,  0),  -- RB Bennett, Michael (MIN)
( 160, 2001, 1,  0,  0),  -- RB Bettis, Jerome (PIT)
(  20, 2001, 1,  0,  0),  -- WR Alexander, Derrick (KC)
(1548, 2001, 1,  0,  0),  -- WR Moulds, Eric (BUF)
(1781, 2001, 1,  1,  1),  -- TE Riemersma, Jay (BUF)
(2111, 2001, 1,  6,  6),  -- K  Tennessee Kicker
( 556, 2001, 1, 11, 11),  -- OL Denver Offense
(1732, 2001, 1,  6,  6),  -- DL Pryce, Trevor (DEN)
(1957, 2001, 1,  6,  6),  -- DL Smith, Brady (ATL)
(1282, 2001, 1, 13, 13),  -- LB Lewis, Ray (BAL)
(1425, 2001, 1,  0,  0),  -- LB McKinnon, Ronald (ARI)
( 339, 2001, 1,  4,  4),  -- DB Carpenter, Keion (BUF)
(1812, 2001, 1,  3,  3),  -- DB Rolle, Samari (TEN)
-- Freezer Burn (teamid 5) - off 58, def 28, final 26
(2424, 2001, 1,  0,  0),  -- HC Fassel, Jim (NYG)
(1675, 2001, 1, 15, 15),  -- QB Philadelphia Team QB
( 612, 2001, 1,  0,  0),  -- RB Dunn, Warrick (TB)
(1346, 2001, 1, 10, 10),  -- RB Martin, Curtis (NYJ)
(1624, 2001, 1,  6,  6),  -- WR Owens, Terrell (SF)
(2167, 2001, 1, 14, 14),  -- WR Toomer, Amani (NYG)
( 222, 2001, 1,  1,  1),  -- TE Brady, Kyle (JAX)
(1028, 2001, 1,  9,  9),  -- K  Indianapolis Kicker
(1692, 2001, 1,  3,  3),  -- OL Pittsburgh Offense
( 301, 2001, 1,  1,  1),  -- DL Burnett, Rob (BAL)
(1420, 2001, 1,  6,  6),  -- DL McKenzie, Keith (GB)
( 781, 2001, 1,  7,  7),  -- LB Godfrey, Randall (TEN)
(2175, 2001, 1,  6,  6),  -- LB Trotter, Jeremiah (PHI)
(   4, 2001, 1,  5,  5),  -- DB Abraham, Donnie (TB)
( 268, 2001, 1,  3,  3),  -- DB Brown, Mike (CHI)
-- Barbarians (teamid 10) - off 45, def 41, final 12
(2426, 2001, 1,  0,  0),  -- HC Green, Dennis (MIN)
(2467, 2001, 1, 21, 21),  -- QB NY Giants Team QB
( 103, 2001, 1,  0,  0),  -- RB Barber, Tiki (NYG)
( 744, 2001, 1,  0,  0),  -- RB Garner, Charlie (OAK)
( 349, 2001, 1,  7,  7),  -- WR Carter, Cris (MIN)
( 999, 2001, 1,  2,  2),  -- WR Horn, Joe (NO)
(1909, 2001, 1,  2,  2),  -- TE Sharpe, Shannon (BAL)
( 336, 2001, 1,  6,  6),  -- K  Carolina Kicker
(1594, 2001, 1,  7,  7),  -- OL NY Jets Offense
( 958, 2001, 1,  1,  1),  -- DL Hicks, Eric (KC)
(1048, 2001, 1,  2,  2),  -- DL Jackson, Grady (OAK)
(1646, 2001, 1, 12, 12),  -- LB Patton, Marvcus (KC)
(2147, 2001, 1, 20, 20),  -- LB Thomas, Zach (MIA)
( 145, 2001, 1,  3,  3),  -- DB Bellamy, Jay (NO)
( 259, 2001, 1,  3,  3),  -- DB Brown, Eric (DEN)
-- MeggaMen (teamid 7) - off 46, def 33, final 5
(2417, 2001, 1,  4,  4),  -- HC Billick, Brian (BAL)
(1848, 2001, 1, 11, 11),  -- QB San Francisco Team QB
(  42, 2001, 1,  9,  9),  -- RB Anderson, Jamal (ATL)
( 936, 2001, 1,  1,  1),  -- RB Hearst, Garrison (SF)
( 721, 2001, 1,  0,  0),  -- WR Freeman, Antonio (GB)
(1374, 2001, 1, 11, 11),  -- WR McCaffrey, Ed (DEN)
(2393, 2001, 1,  1,  1),  -- TE Wycheck, Frank (TEN)
(1448, 2001, 1,  7,  7),  -- K  Miami Kicker
(2090, 2001, 1,  2,  2),  -- OL Tampa Bay Offense
( 347, 2001, 1,  2,  2),  -- DL Carter, Andre (SF)
( 353, 2001, 1,  5,  5),  -- DL Carter, Kevin (TEN)
( 240, 2001, 1,  2,  2),  -- LB Brooks, Derrick (TB)
( 625, 2001, 1, 14, 14),  -- LB Edwards, Donnie (KC)
(1469, 2001, 1,  2,  2),  -- DB Milloy, Lawyer (NE)
(2215, 2001, 1,  8,  8);  -- DB Walker, Brian (MIA)
