-- 2005 WMFFL draft picks, transcribed from football/history/2005Season/draftsummary.txt.
--
-- The draftpicks table already has rows for this season (teamid/orgTeam only,
-- playerid always NULL) that are incomplete/inaccurate. This file deletes them
-- before inserting the full, verified set below, so it can be run standalone.
--
-- Only season/round/pick/teamid/playerid are populated (id is auto-increment;
-- orgTeam and pickTime are left for Josh to fill in as needed).
-- Player ids were matched by lastname/firstname against the players table;
-- picks that were streaming placeholders ("team offense") have no real player
-- and are listed instead in insert_2005-2006_draftpicks_unresolved.sql, not here.

DELETE FROM draftpicks WHERE Season = 2005;

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(2005, 1, 1, 4, 2945),  -- Lindbergh Baby Casserole: RB Westbrook,Brian (PHI)
(2005, 1, 2, 1, 5150),  -- Bug Stompers: WR Burleson,Nate (MIN)
(2005, 1, 3, 6, 5803),  -- Crusaders: RB Williams,Carnell (TB)
(2005, 1, 4, 5, 5836),  -- Sacks on the Beach: RB Arrington,J.J. (ARI)
(2005, 1, 5, 9, 2645),  -- Gallic Warriors: QB Green,Trent (KC)
(2005, 1, 6, 4, 5800),  -- Lindbergh Baby Casserole: RB Brown,Ronnie (MIA)
(2005, 1, 7, 5, 1282),  -- Sacks on the Beach: LB Lewis,Ray (BAL)
(2005, 1, 8, 8, 5450),  -- Rednecks: WR Fitzgerald,Larry (ARI)
(2005, 1, 9, 3, 5802),  -- Norsemen: RB Benson,Cedric (CHI)
(2005, 1, 10, 7, 2802),  -- MeggaMen: RB Foster,De'shaun (CAR)
-- Round 2
(2005, 2, 1, 4, 2617),  -- Lindbergh Baby Casserole: QB Delhomme,Jake (CAR)
(2005, 2, 2, 1, 1710),  -- Bug Stompers: WR Porter,Jerry (OAK)
(2005, 2, 3, 6, 1991),  -- Crusaders: WR Smith,Steve (CAR)  [manually resolved - see note below]
(2005, 2, 4, 5, 1046),  -- Sacks on the Beach: WR Jackson,Darrell (SEA)
(2005, 2, 5, 9, 2235),  -- Gallic Warriors: WR Ward,Hines (PIT)
(2005, 2, 6, 10, 594),  -- Whiskey Tango: WR Driver,Donald (GB)
(2005, 2, 7, 2, 5165),  -- Werewolves: RB Brown,Chris (TEN)
(2005, 2, 8, 8, 612),  -- Rednecks: RB Dunn,Warrick (ATL)
(2005, 2, 9, 3, 105),  -- Norsemen: RB Barlow,Kevan (SF)
(2005, 2, 10, 7, 2593),  -- MeggaMen: QB Brady,Tom (NE)
-- Round 3
(2005, 3, 1, 4, 663),  -- Lindbergh Baby Casserole: LB Farrior,James (PIT)
(2005, 3, 2, 1, 5110),  -- Bug Stompers: RB Johnson,Larry (KC)
(2005, 3, 3, 6, 2609),  -- Crusaders: QB Collins,Kerry (OAK)
(2005, 3, 4, 5, 5088),  -- Sacks on the Beach: QB Palmer,Carson (CIN)
(2005, 3, 5, 9, 909),  -- Gallic Warriors: DB Harrison,Rodney (NE)
(2005, 3, 6, 10, 2857),  -- Whiskey Tango: WR Lelie,Ashley (DEN)
(2005, 3, 7, 2, 152),  -- Werewolves: RB Bennett,Michael (MIN)
(2005, 3, 8, 8, 2103),  -- Rednecks: RB Taylor,Fred (JAX)
(2005, 3, 9, 3, 2477),  -- Norsemen: WR Bennett,Drew (TEN)
(2005, 3, 10, 7, 435),  -- MeggaMen: WR Coles,Laveranues (NYJ)
-- Round 4
(2005, 4, 1, 4, 2651),  -- Lindbergh Baby Casserole: QB Hasselbeck,Matt (SEA)
(2005, 4, 2, 1, 1549),  -- Bug Stompers: WR Muhammad,Muhsin (CHI)
(2005, 4, 3, 6, 5102),  -- Crusaders: DB Polamalu,Troy (PIT)
(2005, 4, 4, 5, 1973),  -- Sacks on the Beach: WR Smith,Jimmy (JAX)
(2005, 4, 5, 9, 278),  -- Gallic Warriors: WR Bruce,Isaac (STL)
(2005, 4, 6, 10, 2817),  -- Whiskey Tango: DL Grant,Charles (NO)
(2005, 4, 7, 4, 5801),  -- Lindbergh Baby Casserole: WR Edwards,Braylon (CLE)
(2005, 4, 8, 8, 5139),  -- Rednecks: WR Calico,Tyrone (TEN)
(2005, 4, 9, 3, 5808),  -- Norsemen: WR Williams,Mike (DET)  [manually resolved - see note below]
(2005, 4, 10, 7, 2344),  -- MeggaMen: RB Williams,Ricky (MIA)
-- Round 5
(2005, 5, 1, 4, 597),  -- Lindbergh Baby Casserole: RB Droughns,Reuben (CLE)
(2005, 5, 2, 1, 2252),  -- Bug Stompers: LB Washington,Marcus (WAS)
(2005, 5, 3, 6, 303),  -- Crusaders: WR Burress,Plaxico (NYG)
(2005, 5, 4, 5, 2058),  -- Sacks on the Beach: DL Strahan,Michael (NYG)
(2005, 5, 5, 9, 2147),  -- Gallic Warriors: LB Thomas,Zach (MIA)
(2005, 5, 6, 10, 2512),  -- Whiskey Tango: LB Pierce,Antonio (NYG)
(2005, 5, 7, 2, 1104),  -- Werewolves: TE Johnson,Eric (SF)
(2005, 5, 8, 8, 63),  -- Rednecks: LB Arrington,Lavar (WAS)
(2005, 5, 9, 1, 5844),  -- Bug Stompers: RB Shelton,Eric (CAR)
(2005, 5, 10, 7, 2105),  -- MeggaMen: DL Taylor,Jason (MIA)  [manually resolved - see note below]
-- Round 6
(2005, 6, 1, 4, 5520),  -- Lindbergh Baby Casserole: TE Cooley,Chris (WAS)
(2005, 6, 2, 1, 2951),  -- Bug Stompers: DB Williams,Roy (DAL)
(2005, 6, 3, 6, 2300),  -- Crusaders: TE Wiggins,Jermaine (MIN)
(2005, 6, 4, 5, 2698),  -- Sacks on the Beach: QB Pennington,Chad (NYJ)
(2005, 6, 5, 9, 240),  -- Gallic Warriors: LB Brooks,Derrick (TB)
(2005, 6, 6, 10, 5571),  -- Whiskey Tango: DB Coleman,Erik (NYJ)
(2005, 6, 7, 2, 5497),  -- Werewolves: DB Williams,Madieu (CIN)
(2005, 6, 8, 8, 1531),  -- Rednecks: LB Morgan,Dan (CAR)
(2005, 6, 9, 3, 825),  -- Norsemen: LB Greenwood,Morlon (HOU)
(2005, 6, 10, 7, 101),  -- MeggaMen: DB Barber,Ronde (TB)
-- Round 7
(2005, 7, 1, 4, 2833),  -- Lindbergh Baby Casserole: DL Henderson,John (JAX)
(2005, 7, 2, 1, 5122),  -- Bug Stompers: DB Hamlin,Ken (SEA)
(2005, 7, 3, 6, 637),  -- Crusaders: DL Ellis,Shaun (NYJ)
(2005, 7, 4, 5, 1220),  -- Sacks on the Beach: DB Knight,Sammy (KC)
(2005, 7, 5, 9, 1356),  -- Gallic Warriors: WR Mason,Derrick (BAL)
(2005, 7, 6, 10, 2357),  -- Whiskey Tango: DB Wilson,Adrian (ARI)
(2005, 7, 7, 2, 5112),  -- Werewolves: LB Barnett,Nick (GB)
(2005, 7, 8, 8, 5811),  -- Rednecks: DB Davis,Thomas (CAR)
(2005, 7, 9, 3, 347),  -- Norsemen: LB Carter,Andre (SF)  [manually resolved - see note below]
(2005, 7, 10, 7, 2954),  -- MeggaMen: LB Witherspoon,Will (CAR)
-- Round 8
(2005, 8, 1, 4, 5810),  -- Lindbergh Baby Casserole: LB Merriman,Shawne (SD)
(2005, 8, 2, 1, 5140),  -- Bug Stompers: TE Smith,L.J. (PHI)
(2005, 8, 3, 6, 5134),  -- Crusaders: DB Scott,Bryan (ATL)
(2005, 8, 4, 5, 160),  -- Sacks on the Beach: RB Bettis,Jerome (PIT)
(2005, 8, 5, 9, 2597),  -- Gallic Warriors: QB Brooks,Aaron (NO)
(2005, 8, 6, 10, 2012),  -- Whiskey Tango: LB Spikes,Takeo (BUF)
(2005, 8, 7, 5, 5185),  -- Sacks on the Beach: RB Suggs,Lee (CLE)
(2005, 8, 8, 10, 5828),  -- Whiskey Tango: WR Brown,Reggie (PHI)  [manually resolved - see note below]
(2005, 8, 9, 3, 5463),  -- Norsemen: LB Williams,D.J. (DEN)
(2005, 8, 10, 7, 5812),  -- MeggaMen: LB Johnson,Derrick o. (KC)  [manually resolved - see note below]
-- Round 9
(2005, 9, 1, 4, 5806),  -- Lindbergh Baby Casserole: DB Rolle,Antrel (ARI)
(2005, 9, 2, 1, 2967),  -- Bug Stompers: QB Harrington,Joey (DET)
(2005, 9, 3, 6, 1002),  -- Crusaders: WR Houshmandzadeh,T.J. (CIN)
(2005, 9, 4, 5, 3001),  -- Sacks on the Beach: PK Elam,Jason (DEN)
(2005, 9, 5, 9, 398),  -- Gallic Warriors: LB Clark,Danny (OAK)
(2005, 9, 6, 10, 5094),  -- Whiskey Tango: QB Leftwich,Byron (JAX)
(2005, 9, 7, 2, 1989),  -- Werewolves: WR Smith,Rod (DEN)
(2005, 9, 8, 8, 1641),  -- Rednecks: DB Parrish,Tony (SF)
(2005, 9, 9, 1, 5494),  -- Bug Stompers: DB Boulware,Michael (SEA)
(2005, 9, 10, 7, 2647),  -- MeggaMen: QB Griese,Brian (TB)
-- Round 10
(2005, 10, 1, 4, 5825),  -- Lindbergh Baby Casserole: TE Miller,Heath (PIT)
(2005, 10, 2, 1, 5135),  -- Bug Stompers: DL Umenyiora,Osi (NYG)
(2005, 10, 5, 9, 5460),  -- Gallic Warriors: WR Evans,Lee (BUF)
(2005, 10, 6, 10, 5854),  -- Whiskey Tango: RB Gore,Frank (SF)
(2005, 10, 7, 2, 1159),  -- Werewolves: RB Jones,Thomas (CHI)
(2005, 10, 8, 8, 2699),  -- Rednecks: QB Plummer,Jake (DEN)
(2005, 10, 9, 3, 5204),  -- Norsemen: DB Holt,Terrence (DET)
(2005, 10, 10, 7, 636),  -- MeggaMen: DL Ellis,Greg (DAL)
-- Round 11
(2005, 11, 1, 4, 2976),  -- Lindbergh Baby Casserole: PK Akers,David (PHI)
(2005, 11, 2, 1, 703),  -- Bug Stompers: LB Foley,Steve (SD)
(2005, 11, 3, 6, 1974),  -- Crusaders: DL Smith,Justin (CIN)
(2005, 11, 4, 5, 1931),  -- Sacks on the Beach: LB Simmons,Brian (CIN)
(2005, 11, 5, 9, 158),  -- Gallic Warriors: DL Berry,Bert (ARI)
(2005, 11, 6, 10, 3160),  -- Whiskey Tango: PK Reed,Jeff (PIT)
(2005, 11, 7, 2, 2961),  -- Werewolves: QB Carr,David (HOU)
(2005, 11, 8, 8, 1180),  -- Rednecks: DL Kearse,Jevon (PHI)
(2005, 11, 9, 3, 5325),  -- Norsemen: LB Brackett,Gary (IND)
(2005, 11, 10, 7, 5457),  -- MeggaMen: DB Robinson,Dunta (HOU)
-- Round 12
(2005, 12, 1, 4, 830),  -- Lindbergh Baby Casserole: DL Griffin,Cornelius (WAS)
(2005, 12, 3, 6, 5840),  -- Crusaders: LB Thurman,Odell (CIN)
(2005, 12, 4, 5, 2053),  -- Sacks on the Beach: WR Stokley,Brandon (IND)
(2005, 12, 5, 9, 5883),  -- Gallic Warriors: RB Clarett,Maurice (DEN)
(2005, 12, 6, 10, 5368),  -- Whiskey Tango: WR Lewis,Greg (PHI)
(2005, 12, 7, 2, 5097),  -- Werewolves: DB Trufant,Marcus (SEA)
(2005, 12, 8, 8, 1910),  -- Rednecks: DB Sharper,Darren (MIN)
(2005, 12, 9, 3, 5539),  -- Norsemen: WR Parker,Samie (KC)
(2005, 12, 10, 7, 5805);  -- MeggaMen: WR Williamson,Troy (MIN)

-- Manually resolved ambiguous names (multiple players table matches for the
-- same last/first name; resolved using the NFL team on the pick line, the
-- player's real draft era, and/or retirement year):
--   Smith,Steve (WR, CAR) -> playerid 1991, Steve Smith Sr, Carolina Panthers, drafted 2001 (not playerid 8108, a different Steve Smith who didn't enter the NFL until 2007)
--   Williams,Mike (WR, DET) -> playerid 5808, USC's Mike Williams, drafted 2005 rd1 pick10 by the Lions (not playerid 7555, Syracuse's Mike Williams, drafted 2002 rd1 by Buffalo)
--   Taylor,Jason (DL, MIA) -> playerid 2105, Jason Taylor, Miami Dolphins DE/LB drafted 1997 rd3 (not playerid 14847, drafted 2023)
--   Carter,Andre (DL/LB, SF) -> playerid 347, Andre Carter, San Francisco 49ers DE, drafted 2001 rd1
--   Brown,Reggie (WR, PHI) -> playerid 5828, Reggie Brown, Philadelphia Eagles, drafted 2005 rd2 (not playerid 272 [RB], 3223 [1993], or 3915 [LB])
--   Johnson,Derrick o. (LB, KC) -> playerid 5812; source has a stray "o."; Derrick Johnson, Kansas City Chiefs, drafted 2005 rd1 pick15
