-- 2006 WMFFL draft picks, transcribed from football/history/2006Season/draftsummary.txt.
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
--
-- The source page's round headers ("Rd# Franchise ...") don't print a round
-- number this year, unlike every other season; round numbers below were
-- recovered by counting the header occurrences in order down the page.

DELETE FROM draftpicks WHERE Season = 2006;

INSERT INTO draftpicks (Season, Round, Pick, teamid, playerid) VALUES
-- Round 1
(2006, 1, 1, 8, 6154),  -- Go Balls Deep: RB Bush,Reggie (NO)
(2006, 1, 2, 9, 2617),  -- Gallic Warriors: QB Delhomme,Jake (CAR)
(2006, 1, 3, 4, 6179),  -- Lindbergh Baby Casserole: RB Addai,Joseph (IND)
(2006, 1, 4, 4, 5854),  -- Lindbergh Baby Casserole: RB Gore,Frank (SF)
(2006, 1, 5, 3, 5889),  -- Norsemen: RB Barber,Marion (DAL)
(2006, 1, 6, 6, 6177),  -- Crusaders: RB Williams,DeAngelo (CAR)
(2006, 1, 7, 4, 1002),  -- Lindbergh Baby Casserole: WR Houshmandzadeh,T.J. (CIN)
(2006, 1, 8, 10, 2938),  -- Whiskey Tango: WR Walker,Javon (DEN)
(2006, 1, 9, 5, 2512),  -- Sacks on the Beach: LB Pierce,Antonio (NYG)
(2006, 1, 10, 9, 2802),  -- Gallic Warriors: RB Foster,De'shaun (CAR)
-- Round 2
(2006, 2, 1, 3, 5661),  -- Norsemen: RB Parker,Willie (PIT)
(2006, 2, 2, 9, 492),  -- Gallic Warriors: TE Crumpler,Alge (ATL)
(2006, 2, 3, 4, 5837),  -- Lindbergh Baby Casserole: LB Tatupu,Lofa (SEA)
(2006, 2, 4, 8, 7904),  -- Go Balls Deep: RB Bell,Mike (DEN)
(2006, 2, 5, 3, 5135),  -- Norsemen: DL Umenyiora,Osi (NYG)
(2006, 2, 6, 6, 5449),  -- Crusaders: QB Manning,Eli (NYG)
(2006, 2, 7, 2, 2684),  -- Werewolves: QB McNabb,Donovan (PHI)
(2006, 2, 8, 10, 6172),  -- Whiskey Tango: RB Maroney,Laurence (NE)
(2006, 2, 9, 8, 5458),  -- Go Balls Deep: QB Roethlisberger,Ben (PIT)
(2006, 2, 10, 7, 695),  -- MeggaMen: LB Fletcher,London (BUF)
-- Round 3
(2006, 3, 1, 1, 2614),  -- Bitch Better Have My Money: QB Culpepper,Daunte (MIA)
(2006, 3, 2, 9, 1356),  -- Gallic Warriors: WR Mason,Derrick (BAL)
(2006, 3, 3, 4, 5544),  -- Lindbergh Baby Casserole: DB Vasher,Nathan (CHI)
(2006, 3, 4, 8, 2783),  -- Go Balls Deep: LB Davis,Andra (CLE)
(2006, 3, 5, 3, 6192),  -- Norsemen: RB White,LenDale (TEN)
(2006, 3, 6, 6, 5112),  -- Crusaders: LB Barnett,Nick (GB)
(2006, 3, 7, 2, 5485),  -- Werewolves: RB Bell,Tatum (DEN)
(2006, 3, 8, 10, 2175),  -- Whiskey Tango: LB Trotter,Jeremiah (PHI)
(2006, 3, 9, 5, 5464),  -- Sacks on the Beach: DL Smith,Will (NO)
(2006, 3, 10, 7, 1046),  -- MeggaMen: WR Jackson,Darrell (SEA)
-- Round 4
(2006, 4, 1, 4, 5452),  -- Lindbergh Baby Casserole: DB Taylor,Sean (WAS)
(2006, 4, 2, 9, 237),  -- Gallic Warriors: LB Brooking,Keith (ATL)
(2006, 4, 3, 4, 2125),  -- Lindbergh Baby Casserole: LB Thomas,Adalius (BAL)
(2006, 4, 4, 8, 1736),  -- Go Balls Deep: LB Quarles,Shelton (TB)
(2006, 4, 5, 3, 5571),  -- Norsemen: DB Coleman,Erik (NYJ)
(2006, 4, 6, 6, 5566),  -- Crusaders: DB Wilson,Gibril (NYG)
(2006, 4, 7, 2, 101),  -- Werewolves: DB Barber,Ronde (TB)
(2006, 4, 8, 10, 6156),  -- Whiskey Tango: LB Hawk,A.J. (GB)
(2006, 4, 9, 5, 1159),  -- Sacks on the Beach: RB Jones,Thomas (CHI)
(2006, 4, 10, 7, 5497),  -- MeggaMen: DB Williams,Madieu (CIN)
-- Round 5
(2006, 5, 1, 1, 5090),  -- Bitch Better Have My Money: WR Johnson,Andre (HOU)
(2006, 5, 2, 9, 737),  -- Gallic Warriors: WR Galloway,Joey (TB)
(2006, 5, 3, 4, 6157),  -- Lindbergh Baby Casserole: TE Davis,Vernon (SF)
(2006, 5, 4, 8, 1989),  -- Go Balls Deep: WR Smith,Rod (DEN)
(2006, 5, 5, 3, 5453),  -- Norsemen: TE Winslow,Kellen (CLE)
(2006, 5, 6, 6, 295),  -- Crusaders: DL Burgess,Derrick (OAK)
(2006, 5, 7, 8, 2872),  -- Go Balls Deep: TE McMichael,Randy (MIA)
(2006, 5, 8, 4, 105),  -- Lindbergh Baby Casserole: RB Barlow,Kevan (NYJ)
(2006, 5, 9, 5, 5457),  -- Sacks on the Beach: DB Robinson,Dunta (HOU)
(2006, 5, 10, 7, 5862),  -- MeggaMen: RB Morency,Vernand (HOU)
-- Round 6
(2006, 6, 1, 1, 540),  -- Bitch Better Have My Money: DB Dawkins,Brian (PHI)
(2006, 6, 2, 9, 2859),  -- Gallic Warriors: DB Lewis,Michael (PHI)
(2006, 6, 3, 4, 5456),  -- Lindbergh Baby Casserole: WR Williams,Reggie (JAX)
(2006, 6, 4, 8, 1549),  -- Go Balls Deep: WR Muhammad,Muhsin (CHI)
(2006, 6, 5, 3, 5867),  -- Norsemen: LB Morrison,Kirk (OAK)
(2006, 6, 6, 6, 2922),  -- Crusaders: WR Stallworth,Donte' (NO)
(2006, 6, 7, 2, 2817),  -- Werewolves: DL Grant,Charles (NO)
(2006, 6, 8, 10, 2371),  -- Whiskey Tango: DB Winfield,Antoine (MIN)  [manually resolved - see note below]
(2006, 6, 9, 8, 2252),  -- Go Balls Deep: LB Washington,Marcus (WAS)
(2006, 6, 10, 7, 1220),  -- MeggaMen: DB Knight,Sammy (KC)
-- Round 7
(2006, 7, 1, 1, 6160),  -- Bitch Better Have My Money: LB Sims,Ernie (DET)
(2006, 7, 2, 9, 1874),  -- Gallic Warriors: DL Schobel,Aaron (BUF)
(2006, 7, 3, 4, 1282),  -- Lindbergh Baby Casserole: LB Lewis,Ray (BAL)
(2006, 7, 4, 8, 2727),  -- Go Balls Deep: QB Vick,Michael (ATL)
(2006, 7, 5, 3, 5251),  -- Norsemen: LB June,Cato (IND)
(2006, 7, 6, 6, 2925),  -- Crusaders: TE Stevens,Jerramy (SEA)
(2006, 7, 7, 2, 5477),  -- Werewolves: TE Watson,Ben (NE)
(2006, 7, 8, 10, 2197),  -- Whiskey Tango: DL Vanden Bosch,Kyle (TEN)
(2006, 7, 9, 5, 2954),  -- Sacks on the Beach: LB Witherspoon,Will (STL)
(2006, 7, 10, 7, 2588),  -- MeggaMen: QB Bledsoe,Drew (DAL)
-- Round 8
(2006, 8, 1, 1, 2838),  -- Bitch Better Have My Money: DB Hope,Chris (TEN)
(2006, 8, 2, 9, 2731),  -- Gallic Warriors: QB Warner,Kurt (ARI)
(2006, 8, 3, 4, 2594),  -- Lindbergh Baby Casserole: QB Brees,Drew (NO)
(2006, 8, 4, 8, 5558),  -- Go Balls Deep: DL Allen,Jared (KC)
(2006, 8, 5, 1, 6153),  -- Bitch Better Have My Money: DL Williams,Mario (HOU)
(2006, 8, 6, 6, 1823),  -- Crusaders: DL Roye,Orpheus (CLE)
(2006, 8, 7, 2, 5205),  -- Werewolves: DL Mathis,Robert (IND)
(2006, 8, 8, 10, 5147),  -- Whiskey Tango: LB Briggs,Lance (CHI)
(2006, 8, 9, 5, 2699),  -- Sacks on the Beach: QB Plummer,Jake (DEN)
(2006, 8, 10, 7, 5478),  -- MeggaMen: LB Dansby,Karlos (ARI)
-- Round 9
(2006, 9, 1, 1, 5840),  -- Bitch Better Have My Money: LB Thurman,Odell (CIN)
(2006, 9, 2, 9, 398),  -- Gallic Warriors: LB Clark,Danny (OAK)
(2006, 9, 4, 8, 5816),  -- Go Balls Deep: DL Spears,Marcus (DAL)
(2006, 9, 5, 3, 5828),  -- Norsemen: WR Brown,Reggie (PHI)  [manually resolved - see note below]
(2006, 9, 6, 6, 809),  -- Crusaders: RB Green,Ahman (GB)
(2006, 9, 8, 10, 5460),  -- Whiskey Tango: WR Evans,Lee (BUF)
(2006, 9, 9, 5, 999),  -- Sacks on the Beach: WR Horn,Joe (NO)
-- Round 10
(2006, 10, 1, 1, 3003),  -- Bitch Better Have My Money: PK Feely,Jay (NYG)
(2006, 10, 2, 9, 5817),  -- Gallic Warriors: WR Jones,Matt (JAX)
(2006, 10, 3, 4, 1825),  -- Lindbergh Baby Casserole: DL Rucker,Mike (CAR)
(2006, 10, 4, 8, 1251),  -- Go Balls Deep: DB Law,Ty (KC)
(2006, 10, 5, 3, 5899),  -- Norsemen: DB Rhodes,Kerry (NYJ)
(2006, 10, 6, 6, 5462),  -- Crusaders: WR Clayton,Michael (TB)
(2006, 10, 7, 2, 1197),  -- Werewolves: DL Kerney,Patrick (ATL)
(2006, 10, 8, 10, 5165),  -- Whiskey Tango: RB Brown,Chris (TEN)
(2006, 10, 9, 5, 409),  -- Sacks on the Beach: DB Clements,Nate (BUF)
(2006, 10, 10, 7, 3007),  -- MeggaMen: PK Graham,Shayne (CIN)
-- Round 11
(2006, 11, 1, 1, 2685),  -- Bitch Better Have My Money: QB McNair,Steve (BAL)
(2006, 11, 2, 9, 2853),  -- Gallic Warriors: DL Kampman,Aaron (GB)
(2006, 11, 3, 4, 5818),  -- Lindbergh Baby Casserole: WR Clayton,Mark (BAL)  [manually resolved - see note below]
(2006, 11, 5, 3, 6175),  -- Norsemen: WR Holmes,Santonio (PIT)
(2006, 11, 6, 6, 751),  -- Crusaders: DL Gbaja-Biamila,Kabeer (GB)
(2006, 11, 7, 2, 1194),  -- Werewolves: WR Kennison,Eddie (KC)
(2006, 11, 8, 10, 1193),  -- Whiskey Tango: DB Kennedy,Kenoy (DET)
(2006, 11, 9, 5, 45),  -- Sacks on the Beach: RB Anderson,Mike (BAL)
(2006, 11, 10, 7, 5801),  -- MeggaMen: WR Edwards,Braylon (CLE)
-- Round 12
(2006, 12, 1, 1, 777),  -- Bitch Better Have My Money: WR Glenn,Terry (DAL)
(2006, 12, 3, 4, 5268),  -- Lindbergh Baby Casserole: PK Brown,Josh (SEA)
(2006, 12, 4, 8, 5455),  -- Go Balls Deep: DB Hall,Deangelo (ATL)
(2006, 12, 5, 3, 5823),  -- Norsemen: DL Castillo,Luis (SD)
(2006, 12, 6, 6, 1690),  -- Crusaders: RB Pittman,Michael (TB)
(2006, 12, 7, 2, 5127),  -- Werewolves: LB Mitchell,Kawika (KC)
(2006, 12, 8, 10, 2760),  -- Whiskey Tango: WR Branch,Deion (NE)
(2006, 12, 9, 5, 1180),  -- Sacks on the Beach: DL Kearse,Jevon (PHI)
(2006, 12, 10, 7, 2600);  -- MeggaMen: QB Brunell,Mark (WAS)

-- Manually resolved ambiguous names (multiple players table matches for the
-- same last/first name; resolved using the NFL team on the pick line, the
-- player's real draft era, and/or retirement year):
--   Winfield,Antoine (DB, MIN) -> playerid 2371, Antoine Winfield Sr, drafted 1999 rd1 by Buffalo, with the Vikings by 2006 (not playerid 13551, his son, drafted 2020)
--   Brown,Reggie (WR, PHI) -> playerid 5828, Reggie Brown, Philadelphia Eagles, drafted 2005 rd2 (not playerid 272 [RB], 3223 [1993], or 3915 [LB])
--   Clayton,Mark (WR, BAL) -> playerid 5818, Mark Clayton, Baltimore Ravens, drafted 2005 rd1 (not playerid 3249, the Dolphins-era Mark Clayton, retired long before 2006)
