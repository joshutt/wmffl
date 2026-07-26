-- 2003 WMFFL draft picks, transcribed from football/history/2003Season/draftsummary.txt.
--
-- Only season/round/pick/teamid/playerid are populated (id is auto-increment,
-- orgTeam and pickTime are left for Josh to fill in as needed).
-- Player ids were matched by lastname/firstname against the players table;
-- picks that were streaming placeholders ("team QB", "team kicker", "team
-- offense") or explicitly "(NONE)" in the source have no real player and are
-- listed instead in insert_1999-2004_draftpicks_unresolved.sql, not here.

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(2003, 1, 1, 6, 1066),  -- Crusaders: RB James,Edgerrin (IND)
(2003, 1, 2, 8, 1277),  -- Rednecks: RB Lewis,Jamal (BAL)
(2003, 1, 3, 9, 625),  -- Green Wave: LB Edwards,Donnie (SD)
(2003, 1, 4, 1, 237),  -- War Eagles: LB Brooking,Keith (ATL)
(2003, 1, 5, 10, 862),  -- Whiskey Tango: RB Hambrick,Troy (DAL)
(2003, 1, 6, 5, 934),  -- Freezer Burn: TE Heap,Todd (BAL)
(2003, 1, 7, 3, 2413),  -- Norsemen: RB Zereoue,Amos (PIT)
(2003, 1, 8, 4, 2626),  -- Illuminati: QB Favre,Brett (GB)
(2003, 1, 9, 7, 303),  -- MeggaMen: WR Burress,Plaxico (PIT)
(2003, 1, 10, 2, 1355),  -- Werewolves: LB Maslowski,Mike (KC)
-- Round 2
(2003, 2, 1, 6, 2922),  -- Crusaders: WR Stallworth,Donte' (NO)
(2003, 2, 2, 8, 5089),  -- Rednecks: WR Rogers,Charles (DET)
(2003, 2, 3, 5, 695),  -- Freezer Burn: LB Fletcher,London (BUF)
(2003, 2, 4, 1, 754),  -- War Eagles: RB George,Eddie (TEN)
(2003, 2, 5, 10, 936),  -- Whiskey Tango: RB Hearst,Garrison (SF)
(2003, 2, 6, 5, 2676),  -- Freezer Burn: QB Maddox,Tommy (PIT)
(2003, 2, 7, 3, 2027),  -- Norsemen: RB Staley,Duce (PHI)
(2003, 2, 8, 4, 5090),  -- Illuminati: WR Johnson,Andre (HOU)
(2003, 2, 9, 7, 330),  -- MeggaMen: RB Canidate,Trung (WAS)
(2003, 2, 10, 1, 2126),  -- War Eagles: RB Thomas,Anthony (CHI)
-- Round 3
(2003, 3, 1, 6, 2685),  -- Crusaders: QB McNair,Steve (TEN)
(2003, 3, 2, 8, 5176),  -- Rednecks: RB Smith,Onterrio (MIN)
(2003, 3, 3, 5, 1356),  -- Freezer Burn: WR Mason,Derrick (TEN)
(2003, 3, 4, 1, 5),  -- War Eagles: DL Abraham,John (NYJ)
(2003, 3, 5, 10, 782),  -- Whiskey Tango: LB Gold,Ian (DEN)
(2003, 3, 6, 5, 1825),  -- Freezer Burn: DL Rucker,Mike (CAR)
(2003, 3, 7, 3, 445),  -- Norsemen: LB Colvin,Rosevelt (NE)
(2003, 3, 8, 4, 768),  -- Illuminati: LB Gildon,Jason (PIT)
(2003, 3, 9, 7, 2645),  -- MeggaMen: QB Green,Trent (KC)
(2003, 3, 10, 7, 1220),  -- MeggaMen: DB Knight,Sammy (MIA)
-- Round 4
(2003, 4, 1, 6, 474),  -- Crusaders: LB Cowart,Sam (NYJ)
(2003, 4, 2, 8, 1598),  -- Rednecks: DB O'Neal,Deltha (DEN)
(2003, 4, 3, 9, 1863),  -- Green Wave: DL Sapp,Warren (TB)
(2003, 4, 4, 1, 909),  -- War Eagles: DB Harrison,Rodney (NE)
(2003, 4, 5, 10, 516),  -- Whiskey Tango: DB Darius,Donovin (JAX)
(2003, 4, 6, 5, 186),  -- Freezer Burn: DB Bly,Dre' (DET)
(2003, 4, 7, 3, 1974),  -- Norsemen: DL Smith,Justin (CIN)
(2003, 4, 8, 4, 2304),  -- Illuminati: DL Wiley,Marcellus (SD)
(2003, 4, 9, 7, 2358),  -- MeggaMen: LB Wilson,Al (DEN)
(2003, 4, 10, 2, 1097),  -- Werewolves: WR Johnson,Chad (CIN)
-- Round 5
(2003, 5, 1, 6, 1954),  -- Crusaders: DL Smith,Aaron (PIT)
(2003, 5, 2, 8, 5096),  -- Rednecks: LB Suggs,Terrell (BAL)
(2003, 5, 3, 9, 612),  -- Green Wave: RB Dunn,Warrick (ATL)
(2003, 5, 4, 1, 594),  -- War Eagles: WR Driver,Donald (GB)
(2003, 5, 5, 10, 1533),  -- Whiskey Tango: WR Morgan,Quincy (CLE)
(2003, 5, 6, 5, 1318),  -- Freezer Burn: RB Mack,Stacey (HOU)
(2003, 5, 7, 6, 53),  -- Crusaders: DB Archuleta,Adam (STL)
(2003, 5, 8, 4, 3027),  -- Illuminati: K Mare,Olindo (MIA)
(2003, 5, 9, 7, 751),  -- MeggaMen: DL Gbaja-Biamila,Kabeer (GB)
(2003, 5, 10, 2, 2357),  -- Werewolves: DB Wilson,Adrian (ARI)
-- Round 6
(2003, 6, 1, 6, 716),  -- Crusaders: TE Franks,Bubba (GB)
(2003, 6, 2, 7, 2383),  -- MeggaMen: DB Woodson,Rod (OAK)
(2003, 6, 3, 9, 1989),  -- Green Wave: WR Smith,Rod (DEN)
(2003, 6, 4, 1, 1877),  -- War Eagles: DB Schulters,Lance (TEN)
(2003, 6, 5, 10, 275),  -- Whiskey Tango: WR Brown,Troy (NE)
(2003, 6, 6, 5, 913),  -- Freezer Burn: LB Hartwell,Edgerton (BAL)
(2003, 6, 7, 3, 268),  -- Norsemen: DB Brown,Mike (CHI)  [manually resolved - see note below]
(2003, 6, 8, 4, 5092),  -- Illuminati: DB Newman,Terence (DAL)
(2003, 6, 9, 7, 1701),  -- MeggaMen: TE Pollard,Marcus (IND)
(2003, 6, 10, 2, 1973),  -- Werewolves: WR Smith,Jimmy (JAX)
-- Round 7
(2003, 7, 1, 6, 1641),  -- Crusaders: DB Parrish,Tony (SF)
(2003, 7, 2, 8, 2593),  -- Rednecks: QB Brady,Tom (NE)
(2003, 7, 3, 9, 201),  -- Green Wave: LB Boulware,Peter (BAL)
(2003, 7, 4, 1, 286),  -- War Eagles: RB Buckhalter,Correll (PHI)
(2003, 7, 5, 10, 2661),  -- Whiskey Tango: QB Johnson,Brad (TB)
(2003, 7, 6, 5, 2833),  -- Freezer Burn: DL Henderson,John (JAX)
(2003, 7, 7, 3, 1667),  -- Norsemen: LB Peterson,Julian (SF)
(2003, 7, 8, 4, 5095),  -- Illuminati: DL Williams,Kevin (MIN)
(2003, 7, 9, 7, 2899),  -- MeggaMen: WR Reed,Josh (BUF)
(2003, 7, 10, 2, 565),  -- Werewolves: LB Diggs,Na'il (GB)
-- Round 8
(2003, 8, 1, 6, 2372),  -- Crusaders: DL Wistrom,Grant (STL)
(2003, 8, 2, 8, 162),  -- Rednecks: LB Biekert,Greg (MIN)
(2003, 8, 3, 9, 774),  -- Green Wave: DB Glenn,Aaron (HOU)
(2003, 8, 4, 1, 2857),  -- War Eagles: WR Lelie,Ashley (DEN)
(2003, 8, 5, 10, 958),  -- Whiskey Tango: DL Hicks,Eric (KC)
(2003, 8, 6, 7, 1772),  -- MeggaMen: WR Rice,Jerry (OAK)
(2003, 8, 7, 3, 2594),  -- Norsemen: QB Brees,Drew (SD)
(2003, 8, 8, 4, 35),  -- Illuminati: RB Alstott,Mike (TB)
(2003, 8, 9, 7, 138),  -- MeggaMen: LB Bell,Kendrell (PIT)
(2003, 8, 10, 2, 105),  -- Werewolves: RB Barlow,Kevan (SF)
-- Round 9
(2003, 9, 1, 6, 2175),  -- Crusaders: LB Trotter,Jeremiah (WAS)
(2003, 9, 2, 8, 2100),  -- Rednecks: DB Taylor,Bobby (PHI)
(2003, 9, 3, 9, 222),  -- Green Wave: TE Brady,Kyle (JAX)
(2003, 9, 4, 1, 2609),  -- War Eagles: QB Collins,Kerry (NYG)
(2003, 9, 5, 10, 737),  -- Whiskey Tango: WR Galloway,Joey (DAL)
(2003, 9, 6, 5, 2550),  -- Freezer Burn: RB Shipp,Marcel (ARI)
(2003, 9, 7, 3, 1084),  -- Norsemen: DL Jenkins,Kris (CAR)  [manually resolved - see note below]
(2003, 9, 8, 4, 2967),  -- Illuminati: QB Harrington,Joey (DET)
(2003, 9, 9, 7, 2975),  -- MeggaMen: QB Ramsey,Patrick (WAS)
(2003, 9, 10, 2, 2668),  -- Werewolves: QB Kitna,Jon (CIN)
-- Round 10
(2003, 10, 1, 6, 666),  -- Crusaders: RB Faulk,Kevin (NE)
(2003, 10, 2, 8, 2989),  -- Rednecks: K Carney,John (NO)
(2003, 10, 3, 9, 1046),  -- Green Wave: WR Jackson,Darrell (SEA)
(2003, 10, 5, 10, 1482),  -- Whiskey Tango: DB Minter,Mike (CAR)
(2003, 10, 7, 3, 2272),  -- Norsemen: DB Wesley,Greg (KC)
(2003, 10, 8, 4, 5120),  -- Illuminati: LB Henderson,E.J. (MIN)
(2003, 10, 9, 7, 2040),  -- MeggaMen: RB Stewart,James (DET)  [manually resolved - see note below]
(2003, 10, 10, 2, 983),  -- Werewolves: DL Holliday,Vonnie (KC)
-- Round 11
(2003, 11, 1, 6, 673),  -- Crusaders: WR Ferguson,Robert (GB)
(2003, 11, 2, 8, 2850),  -- Rednecks: TE Jolley,Doug (OAK)
(2003, 11, 3, 9, 452),  -- Green Wave: WR Conway,Curtis (NYJ)
(2003, 11, 4, 1, 2370),  -- War Eagles: LB Winborn,Jamie (SF)
(2003, 11, 5, 10, 1249),  -- Whiskey Tango: DB Lassiter,Kwamie (SD)
(2003, 11, 6, 7, 2925),  -- MeggaMen: TE Stevens,Jerramy (SEA)
(2003, 11, 7, 3, 2949),  -- Norsemen: DB Williams,Tank (TEN)
(2003, 11, 8, 4, 1998),  -- Illuminati: DB Smoot,Fred (WAS)
(2003, 11, 9, 5, 160),  -- Freezer Burn: RB Bettis,Jerome (PIT)
(2003, 11, 10, 2, 1884),  -- Werewolves: DB Scott,Chad (PIT)
-- Round 12
(2003, 12, 1, 6, 3001),  -- Crusaders: K Elam,Jason (DEN)
(2003, 12, 2, 8, 779),  -- Rednecks: DL Glover,La' roi (DAL)
(2003, 12, 3, 9, 434),  -- Green Wave: DL Coleman,Rod (OAK)
(2003, 12, 4, 1, 2131),  -- War Eagles: DB Thomas,Fred (NO)
(2003, 12, 5, 10, 1732),  -- Whiskey Tango: DL Pryce,Trevor (DEN)
(2003, 12, 6, 5, 84),  -- Freezer Burn: DB Bailey,Champ (WAS)
(2003, 12, 7, 3, 2787),  -- Norsemen: RB Duckett,T.J. (ATL)
(2003, 12, 8, 4, 1967),  -- Illuminati: RB Smith,Emmitt (ARI)
(2003, 12, 9, 5, 1584),  -- Freezer Burn: WR Northcutt,Dennis (CLE)
(2003, 12, 10, 2, 1955);  -- Werewolves: RB Smith,Antowain (NE)

-- Manually resolved ambiguous names (multiple players table matches for the
-- same last/first name; resolved using the NFL team on the pick line, the
-- player's real draft era, and/or retirement year):
--   Brown,Mike (DB, CHI) -> playerid 268, Mike Brown, Chicago Bears S drafted 2000 rd2 (not playerid 10210 [WR] or 14620 [DB, no draft info, team TEN])
--   Jenkins,Kris (DL, CAR) -> playerid 1084, Kris Jenkins, Carolina Panthers NT drafted 2001 rd2 (not playerid 15105, drafted 2024)
--   Stewart,James (RB, DET) -> playerid 2040, retired 2005, matches the Jaguars/Lions-era James Stewart (not playerid 3625, a different James Stewart who retired 1996)
