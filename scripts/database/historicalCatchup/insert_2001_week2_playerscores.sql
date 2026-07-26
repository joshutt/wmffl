-- 2001 Week 2 player scores, transcribed from the newsletter at
-- football/history/2001Season/2001wk2.php ("WMFFL Scoring Breakdown").
--
-- activations already holds this week (150 rows: 15 slots x 10 teams,
-- with playerid=0 for the Norsemen's six empty slots from their illegal
-- lineup), so only playerscores is inserted here. Player ids were taken
-- from the existing activations rows joined to players, and every
-- team's offensive/defensive subtotal was verified against the
-- newsletter totals before writing this file.
--
-- Column semantics (per scripts/logscores/transferscores.php):
--   pts    = the player's computed score for the week
--   active = same value when the player was activated, NULL otherwise.
-- The newsletter only records activated players, so pts = active here.

INSERT INTO playerscores (playerid, season, week, pts, active) VALUES
-- Green Wave (teamid 9) - off 37, def 20, final 0
(2425, 2001, 2,  0,  0),  -- HC Fisher, Jeff (TEN)
(2464, 2001, 2,  4,  4),  -- QB Minnesota Team QB
( 667, 2001, 2, 16, 16),  -- RB Faulk, Marshall (STL)
(2260, 2001, 2,  2,  2),  -- RB Watters, Ricky (SEA)
( 275, 2001, 2,  3,  3),  -- WR Brown, Troy (NE)
(1036, 2001, 2,  0,  0),  -- WR Ismail, Qadry (BAL)
(1949, 2001, 2,  0,  0),  -- TE Sloan, David (DET)
(  91, 2001, 2,  4,  4),  -- K  Baltimore Kicker
(1029, 2001, 2,  8,  8),  -- OL Indianapolis Offense
(1640, 2001, 2,  7,  7),  -- DL Parrella, John (SD)
(1742, 2001, 2,  1,  1),  -- DL Randle, John (SEA)
(1493, 2001, 2,  0,  0),  -- LB Mitchell, Keith (NO)
(2146, 2001, 2,  3,  3),  -- LB Thomas, William (OAK)
(  28, 2001, 2,  5,  5),  -- DB Allen, Eric (OAK)
( 304, 2001, 2,  4,  4),  -- DB Burris, Jeff (IND)
-- Norsemen (teamid 3) - off 64, def 39, final 44 (only 9 players; illegal lineup)
(2422, 2001, 2,  0,  0),  -- HC Dungy, Tony (TB)
(2460, 2001, 2, 36, 36),  -- QB Indianapolis Team QB
( 809, 2001, 2, 10, 10),  -- RB Green, Ahman (GB)
(1066, 2001, 2, 13, 13),  -- RB James, Edgerrin (IND)
(1545, 2001, 2,  3,  3),  -- WR Moss, Randy (MIN)
( 784, 2001, 2,  2,  2),  -- TE Gonzalez, Tony (KC)
(1180, 2001, 2, 11, 11),  -- DL Kearse, Jevon (TEN)
(2012, 2001, 2, 22, 22),  -- LB Spikes, Takeo (CIN)
( 821, 2001, 2,  6,  6),  -- DB Green, Victor (NYJ)
-- Werewolves (teamid 2) - off 52, def 31, final 28
(2433, 2001, 2,  3,  3),  -- HC Martz, Mike (STL)
(2462, 2001, 2, -1, -1),  -- QB Kansas City Team QB
(1976, 2001, 2,  5,  5),  -- RB Smith, Lamar (MIA)
(2165, 2001, 2,  1,  1),  -- RB Tomlinson, LaDainian (SD)
( 996, 2001, 2,  0,  0),  -- WR Holt, Torry (STL)
(1989, 2001, 2, 32, 32),  -- WR Smith, Rod (DEN)
(2228, 2001, 2,  0,  0),  -- TE Walls, Wesley (CAR)
(1673, 2001, 2, 10, 10),  -- K  Philadelphia Kicker
(1603, 2001, 2,  2,  2),  -- OL Oakland Offense
(  60, 2001, 2,  2,  2),  -- DL Armstrong, Trace (OAK)
(2058, 2001, 2,  2,  2),  -- DL Strahan, Michael (NYG)
(1668, 2001, 2, 13, 13),  -- LB Peterson, Mike (IND)
(1930, 2001, 2,  6,  6),  -- LB Simmons, Anthony (SEA)
(1080, 2001, 2,  0,  0),  -- DB Jenkins, Billy (DEN)
(2159, 2001, 2,  8,  8),  -- DB Tillman, Pat (ARI)
-- Hempaholics (teamid 4) - off 69, def 24, final 38
(2428, 2001, 2,  0,  0),  -- HC Haslett, Jim (NO)
(2459, 2001, 2, 18, 18),  -- QB Green Bay Team QB
( 754, 2001, 2,  3,  3),  -- RB George, Eddie (TEN)
(1750, 2001, 2,  0,  0),  -- RB Redmond, J.R. (NE)
( 278, 2001, 2, 16, 16),  -- WR Bruce, Isaac (STL)
(1973, 2001, 2,  5,  5),  -- WR Smith, Jimmy (JAX)
(1142, 2001, 2,  8,  8),  -- TE Jones, Freddie (SD)
(1843, 2001, 2, 15, 15),  -- K  San Diego Kicker
(2020, 2001, 2,  4,  4),  -- OL St. Louis Offense
( 432, 2001, 2,  4,  4),  -- DL Coleman, Marco (WAS)
(1275, 2001, 2,  1,  1),  -- DL Lewis, Damione (STL)
(1711, 2001, 2,  0,  0),  -- LB Porter, Joey (PIT)
(2194, 2001, 2,  7,  7),  -- LB Urlacher, Brian (CHI)
(  84, 2001, 2,  4,  4),  -- DB Bailey, Champ (WAS)
( 285, 2001, 2,  8,  8),  -- DB Buchanan, Ray (ATL)
-- Barbarians (teamid 10) - off 21, def 23, final 0
(2426, 2001, 2,  0,  0),  -- HC Green, Dennis (MIN)
(2467, 2001, 2, -5, -5),  -- QB NY Giants Team QB
( 103, 2001, 2,  0,  0),  -- RB Barber, Tiki (NYG)
( 744, 2001, 2,  6,  6),  -- RB Garner, Charlie (OAK)
( 274, 2001, 2,  0,  0),  -- WR Brown, Tim (OAK)
( 349, 2001, 2,  0,  0),  -- WR Carter, Cris (MIN)
(1909, 2001, 2,  6,  6),  -- TE Sharpe, Shannon (BAL)
( 336, 2001, 2, 10, 10),  -- K  Carolina Kicker
(1594, 2001, 2,  4,  4),  -- OL NY Jets Offense
( 958, 2001, 2,  3,  3),  -- DL Hicks, Eric (KC)
(1048, 2001, 2,  2,  2),  -- DL Jackson, Grady (OAK)
(1646, 2001, 2,  8,  8),  -- LB Patton, Marvcus (KC)
(2147, 2001, 2,  9,  9),  -- LB Thomas, Zach (MIA)
( 147, 2001, 2,  0,  0),  -- DB Belser, Jason (KC)
( 259, 2001, 2,  1,  1),  -- DB Brown, Eric (DEN)
-- War Eagles (teamid 1) - off 48, def 30, final 25
(2438, 2001, 2,  5,  5),  -- HC Reid, Andy (PHI)
(2455, 2001, 2, 14, 14),  -- QB Cleveland Team QB
( 950, 2001, 2,  7,  7),  -- RB Henry, Travis (BUF)
(2103, 2001, 2, -2, -2),  -- RB Taylor, Fred (JAX)
( 199, 2001, 2, 12, 12),  -- WR Boston, David (ARI)
( 435, 2001, 2,  0,  0),  -- WR Coles, Laveranues (NYJ)
(1274, 2001, 2,  1,  1),  -- TE Lewis, Chad (PHI)
( 560, 2001, 2,  2,  2),  -- K  Detroit Kicker
( 807, 2001, 2,  9,  9),  -- OL Green Bay Offense
( 992, 2001, 2,  4,  4),  -- DL Holmes, Kenny (NYG)
(2105, 2001, 2,  0,  0),  -- DL Taylor, Jason (MIA)
(1931, 2001, 2, 11, 11),  -- LB Simmons, Brian (CIN)
(2358, 2001, 2,  6,  6),  -- LB Wilson, Al (DEN)
(1910, 2001, 2,  4,  4),  -- DB Sharper, Darren (GB)
(2203, 2001, 2,  5,  5),  -- DB Vincent, Troy (PHI)
-- Crusaders (teamid 6) - off 64, def 28, final 26
(2443, 2001, 2,  5,  5),  -- HC Shanahan, Mike (DEN)
(2021, 2001, 2, 22, 22),  -- QB St. Louis Team QB
( 531, 2001, 2,  1,  1),  -- RB Davis, Stephen (WAS)
(2281, 2001, 2, -2, -2),  -- RB Wheatley, Tyrone (OAK)
( 907, 2001, 2, 29, 29),  -- WR Harrison, Marvin (IND)
(1113, 2001, 2,  0,  0),  -- WR Johnson, Keyshawn (TB)
( 566, 2001, 2, -1, -1),  -- TE Dilger, Ken (IND)
(1060, 2001, 2,  7,  7),  -- K  Jacksonville Kicker
(1591, 2001, 2,  3,  3),  -- OL NY Giants Offense
( 227, 2001, 2,  5,  5),  -- DL Bratzke, Chad (IND)
( 584, 2001, 2,  0,  0),  -- DL Douglas, Hugh (PHI)
( 162, 2001, 2,  3,  3),  -- LB Biekert, Greg (OAK)
( 695, 2001, 2, 12, 12),  -- LB Fletcher, London (STL)
( 834, 2001, 2,  0,  0),  -- DB Griffith, Robert (MIN)
( 909, 2001, 2,  8,  8),  -- DB Harrison, Rodney (SD)
-- Freezer Burn (teamid 5) - off 46, def 38, final 18
(2424, 2001, 2,  4,  4),  -- HC Fassel, Jim (NYG)
(1675, 2001, 2, 22, 22),  -- QB Philadelphia Team QB
(  47, 2001, 2,  0,  0),  -- RB Anderson, Richie (NYJ)
(1346, 2001, 2, 11, 11),  -- RB Martin, Curtis (NYJ)
(1624, 2001, 2,  1,  1),  -- WR Owens, Terrell (SF)
(2167, 2001, 2,  1,  1),  -- WR Toomer, Amani (NYG)
( 365, 2001, 2,  0,  0),  -- TE Chamberlain, Byron (MIN)
(1028, 2001, 2,  6,  6),  -- K  Indianapolis Kicker
(1476, 2001, 2,  1,  1),  -- OL Minnesota Offense
(1420, 2001, 2,  4,  4),  -- DL McKenzie, Keith (GB)
(1708, 2001, 2, 11, 11),  -- DL Porcher, Robert (DET)
( 781, 2001, 2,  6,  6),  -- LB Godfrey, Randall (TEN)
(2175, 2001, 2,  9,  9),  -- LB Trotter, Jeremiah (PHI)
( 268, 2001, 2,  3,  3),  -- DB Brown, Mike (CHI)
(1998, 2001, 2,  5,  5),  -- DB Smoot, Fred (WAS)
-- ZEN (teamid 8) - off 46, def 34, final 24
(2436, 2001, 2,  4,  4),  -- HC Mora, Jim (IND)
(2457, 2001, 2, 22, 22),  -- QB Denver Team QB
( 152, 2001, 2,  3,  3),  -- RB Bennett, Michael (MIN)
(2027, 2001, 2,  0,  0),  -- RB Staley, Duce (PHI)
(1548, 2001, 2,  0,  0),  -- WR Moulds, Eric (BUF)
(2052, 2001, 2,  0,  0),  -- WR Stokes, J.J. (SF)
(1781, 2001, 2,  2,  2),  -- TE Riemersma, Jay (BUF)
(2111, 2001, 2,  7,  7),  -- K  Tennessee Kicker
( 556, 2001, 2,  8,  8),  -- OL Denver Offense
(1732, 2001, 2,  4,  4),  -- DL Pryce, Trevor (DEN)
(1957, 2001, 2,  0,  0),  -- DL Smith, Brady (ATL)
(1282, 2001, 2,  5,  5),  -- LB Lewis, Ray (BAL)
(1425, 2001, 2,  6,  6),  -- LB McKinnon, Ronald (ARI)
( 339, 2001, 2,  5,  5),  -- DB Carpenter, Keion (BUF)
(1812, 2001, 2, 14, 14),  -- DB Rolle, Samari (TEN)
-- MeggaMen (teamid 7) - off 46, def 22, final 12
(2417, 2001, 2,  0,  0),  -- HC Billick, Brian (BAL)
(1848, 2001, 2, 10, 10),  -- QB San Francisco Team QB
(  42, 2001, 2, 17, 17),  -- RB Anderson, Jamal (ATL)
(  45, 2001, 2,  0,  0),  -- RB Anderson, Mike (DEN)
( 721, 2001, 2,  6,  6),  -- WR Freeman, Antonio (GB)
(1772, 2001, 2,  0,  0),  -- WR Rice, Jerry (OAK)
(2393, 2001, 2,  1,  1),  -- TE Wycheck, Frank (TEN)
(1602, 2001, 2, 11, 11),  -- K  Oakland Kicker
(  92, 2001, 2,  1,  1),  -- OL Baltimore Offense
( 347, 2001, 2,  4,  4),  -- DL Carter, Andre (SF)
( 353, 2001, 2,  2,  2),  -- DL Carter, Kevin (TEN)
(  63, 2001, 2,  2,  2),  -- LB Arrington, Lavar (WAS)
( 625, 2001, 2, 10, 10),  -- LB Edwards, Donnie (KC)
(1469, 2001, 2,  2,  2),  -- DB Milloy, Lawyer (NE)
(2215, 2001, 2,  2,  2);  -- DB Walker, Brian (MIA)
