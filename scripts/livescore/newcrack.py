import sys
import StringIO
from players import Player
from players import Team
from players import Score
from players import matchDict

def valuate (bArr) :
	value = 0
	cArr = bArr
	#cArr[-1] = int(cArr[-1]) & 0x7f
	if (len(bArr) == 0) :
		return 0;
	while (len(cArr) > 0) :
		value = value << 8
		addVal = ord(cArr[-1])
		value = value + addVal
		cArr = cArr[0:-1]
	negMax = 1 << (len(bArr) * 8 - 1)
	if (value > negMax) :
		exor = (negMax << 1) - 1
		value = value ^ exor
		value = value + 1
		value = value * -1
	return value

# Determine input and output files
inputFile = "indstats.nfl"
outputFile = "out.sql"
if (len(sys.argv) >= 2) :
    inputFile = sys.argv[1]
    if (len(sys.argv) >= 3) :
        outputFile = sys.argv[2]

# Open file and read data
indFile = open(inputFile, "r")
indyStats = indFile.read()
indFile.close()

# if file isn't longer than the header, no point
if (len(indyStats) <= 40) :
    sys.exit(1)
theFile = StringIO.StringIO(indyStats)

# Read Header information
year = valuate(theFile.read(4))
revision = valuate(theFile.read(4))
wholeWeek = valuate(theFile.read(4))
#wholeWeek = 2
theFile.read(4)
date = valuate(theFile.read(4))
theFile.read(20)

thePlayers = {}
id = valuate(theFile.read(4))
typeCd = ord(theFile.read(1))
while (id != ord('\x15') and id != ord('\x0a')) :
    # Get the value of this field
    if (typeCd == 1) :
        value = ord(theFile.read(1))
    elif (typeCd == 2) :
        value = valuate(theFile.read(4))
    elif (typeCd == 3) :
        length = valuate(theFile.read(4))
        value = theFile.read(length)
    elif (typeCd == 4) :
        value = valuate(theFile.read(8))

    if (id == 1) :
        week = value
    elif (id == 2) :
        playerid = value
        #print playerid
        if (thePlayers.has_key(playerid)) :
            player = thePlayers[playerid]
        else :
            player = Player(playerid, week)
            #print "created player: %d"%playerid
            thePlayers[playerid] = player
    elif (id == 3) :
        # team id
        pass
    elif (id == 4) :
        player.teamagt = value
    elif (id == 5) :
        # Head coach id, need to handle this somehow
        player.hcid = value
    elif (id == 6) :
        player.stillPlay = value
    elif (id == ord('\x65')) :
        player.complete = value
    elif (id == ord('\x66')) :
        player.pts = value
    elif (id == ord('\x69')) :
        player.intThrow = value
    elif (id == ord('\x6a')) :
        player.passYards = value
        #if (player.id == 7666) :
        #    print "Pass Yards %d"%value
    elif (id == ord('\x6c')) :
        player.sackAgt = value
    elif (id == ord('\x6f')) :
        player.rushYards = value
        #if (player.id == 7666) :
        #    print "Pass Yards %d"%value
    elif (id == ord('\x71')) :
        player.receptions = value
    elif (id == ord('\x72')) :
        player.recYards = value
    elif (id == ord('\x75')) :
        player.tackles = value
    elif (id == ord('\x7b')) :
        player.sacks = value / 2.0
    elif (id == ord('\x7d')) :
        player.intCatch = value
    elif (id == ord('\x7e')) :
        player.intReturn = value
    elif (id == ord('\x80')) :
        player.passDefend = value
    elif (id == ord('\x82')) :
        player.fumbles = value
    elif (id == ord('\x83')) :
        player.fumbRec = value
    elif (id == ord('\x89')) :
        player.forceFumb = value
    elif (id == ord('\x84')) :
        player.fumbleReturn = value
    elif (id == ord('\x9a')) :
        player.blockPunt = value
    elif (id == ord('\x9b')) :
        player.blockFG = value
    elif (id == ord('\x9c')) :
        player.blockXP = value
    elif (id == ord('\x9e')) :
        player.penalties = value


    id = valuate(theFile.read(4))
    #print id
    if (id == 0) : break
    typeCd = ord(theFile.read(1))

# Games in progress
while (id != ord('\x15')) :
    # Get the value of this field
    if (typeCd == 1) :
        value = ord(theFile.read(1))
    elif (typeCd == 2) :
        value = valuate(theFile.read(4))
    elif (typeCd == 3) :
        length = valuate(theFile.read(4))
        value = theFile.read(length)
    elif (typeCd == 4) :
        value = valuate(theFile.read(8))
    #print "Val: %s" % value

    if (id == ord('\x0a')) :
        teamid = value
        #print "teamid %s"%teamid
    elif (id == ord('\x0b')) :
        thePlayers[teamid].stillPlay = value
        thePlayers[teamid].secRemain = 3600
        #print "still play %s"%value
    elif (id == ord('\x0c')) :
        qRemain = 4-value;
        thePlayers[teamid].secRemain = qRemain*15*60
        if (qRemain > 0) :
            thePlayers[teamid].secRemain = qRemain*15*60
            thePlayers[teamid].stillPlay = 1
        elif (value > 8) :
            thePlayers[teamid].secRemain = 0
        #print "Q Remain %s"%value
    elif (id == ord('\x14')) :
        thePlayers[teamid].secRemain += value
        thePlayers[teamid].stillPlay = 1
        #print "Extra Secs: %s"%value

    id = valuate(theFile.read(4))
    #print "ID: %d"%id
    if (id == 0) : break
    typeCd = ord(theFile.read(1))
    #print 

#sys.exit()
teams = {}
scorelength = 0
while (id != ord('\x0a')) :
    # Get the value of this field
    #print id
    if (typeCd == 1) :
        value = ord(theFile.read(1))
    elif (typeCd == 2) :
        value = valuate(theFile.read(4))
    elif (typeCd == 3) :
        length = valuate(theFile.read(4))
        value = theFile.read(length)
    elif (typeCd == 4) :
        value = valuate(theFile.read(8))

    if (id == ord('\x15')) :
        teamid = value
        if (teams.has_key(teamid)) :
            team = teams[teamid]
        else :
            team = Team(teamid, wholeWeek)
            teams[teamid] = team
    elif (id == ord('\x17')) :
        scoretype = value
        if (scoretype == 2) :
            teams[teamid].rushTD += 1
    elif (id == ord('\x18')) :
        #print '0i18'
        scorelength = value
    elif (id == ord('\x1a')) :
        #print '0i1a'
        playerid = value
        if (not thePlayers.has_key(playerid)) :
            thePlayers[playerid] = Player(playerid, wholeWeek)
        thePlayers[playerid].scores.append(Score(scoretype, scorelength))
    elif (id == ord('\x1c')) :
        if (scoretype == 1) :
            playerid = value
            if (not thePlayers.has_key(playerid)) :
                thePlayers[playerid] = Player(playerid, wholeWeek)
            thePlayers[playerid].scores.append(Score(scoretype, scorelength))
    elif (id == ord('\x1e')) :
        ptaftertype = value
    elif (id == ord('\x1f') or id == ord('\x20')) :
        playerid = value
        if (not thePlayers.has_key(playerid)) :
            thePlayers[playerid] = Player(playerid, wholeWeek)
        if (ptaftertype == 1) :
            thePlayers[playerid].scores.append(Score(61, 2))
        elif (ptaftertype == 2) :
            thePlayers[playerid].scores.append(Score(61, -2))
        elif (ptaftertype == 3 or ptaftertype == 5) :
            thePlayers[playerid].scores.append(Score(63, 2))

    try :
        id = valuate(theFile.read(4))
        typeCd = ord(theFile.read(1))
    except: 
        break



for playerid in thePlayers.keys():
    if (playerid <= 32) :
        continue;
    player = thePlayers[playerid]

    print "INSERT INTO stats (statid, season, week, played, yards, intthrow, rec, fum, tackles, sacks, intcatch, passdefend, returnyards, fumrec, forcefum, \
	tds, 2pt, specTD, Safety, XP, MissXP, FG30, FG40, FG50, FG60, MissFG30, blockpunt, blockfg, blockxp) VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %d, %3.1f, \
	%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE played=%d, yards=%d, intthrow=%d, rec=%d, fum=%d, tackles=%d, \
	sacks=%3.1f, intcatch=%d, passdefend=%d, returnyards=%d, fumrec=%d, forcefum=%d, tds=%d, 2pt=%d, specTD=%d, Safety=%d, XP=%d, MissXP=%d, FG30=%d, FG40=%d, \
	FG50=%d, FG60=%d, MissFG30=%d, blockpunt=%d, blockfg=%d, blockxp=%d ;" % (player.id, year, player.week, player.stillPlay+player.complete, \
	(player.passYards+player.rushYards+player.recYards), player.intThrow, player.receptions, player.fumbles, player.tackles, player.sacks, player.intCatch, \
	player.passDefend, (player.intReturn+player.fumbleReturn), player.fumbRec, player.forceFumb, player.numTD(), player.num2Pts(), player.numSpecialTeams(), \
	player.numSaftey(), player.numXPts()[0], player.numXPts()[1], player.numFG()[0], player.numFG()[1], player.numFG()[2], player.numFG()[3], player.numFG()[4], \
	player.blockPunt, player.blockFG, player.blockXP, player.stillPlay+player.complete, (player.passYards+player.rushYards+player.recYards), player.intThrow, \
	player.receptions, player.fumbles, player.tackles, player.sacks, player.intCatch, player.passDefend, (player.intReturn+player.fumbleReturn), player.fumbRec, \
	player.forceFumb, player.numTD(), player.num2Pts(), player.numSpecialTeams(), player.numSaftey(), player.numXPts()[0], player.numXPts()[1], player.numFG()[0], \
	player.numFG()[1], player.numFG()[2], player.numFG()[3], player.numFG()[4], player.blockPunt, player.blockFG, player.blockXP)


for x in range(1, 33) :
    teamplay = thePlayers[x].teamagt
    if (teamplay != 0) :
        margin = thePlayers[x].pts - thePlayers[teamplay].pts

    	print "INSERT INTO stats (statid, season, week, played, ptdiff, penalties) VALUES (%d, %d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE played=%d, ptdiff=%d, penalties=%d; " \
	    % (thePlayers[x].hcid, year, thePlayers[x].week, thePlayers[x].stillPlay+thePlayers[x].complete, margin, thePlayers[x].penalties, \
	    thePlayers[x].stillPlay+thePlayers[x].complete, margin, thePlayers[x].penalties)


inProgArray = ([], [], [])
for x in range(1, 33) :
    player = thePlayers[x]
    playerid = player.id + 600
    rushTD = 0
    if not hasattr(player, 'secRemain') :
        if player.stillPlay > 8 :
        #if player.stillPlay == 10 :
            player.secRemain = 0
            player.complete = 1
        else :
            player.secRemain = 3600
            player.complete = 0

    #print "id [%s]  Complete  %s   Still  %s  Remain %s" %(playerid, player.complete, player.stillPlay, player.secRemain)
    #print dir(thePlayers[x])
    if (player.secRemain > 0) :
    #if (thePlayers[x].stillPlay == 1 or player.secRemain > 0) :
        inProgArray[1].append(x)
    elif (thePlayers[x].complete == 1) :
        inProgArray[2].append(x)
    else :
        inProgArray[0].append(x)
    if (teams.has_key(x)) :
        rushTD = teams[x].rushTD

    # INSERT OL players
    print "INSERT INTO stats (statid, season, week, played, yards, sacks, tds) VALUES (%d, %d, %d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE played=%d, yards=%d, \
	sacks=%d, tds=%d;"%(playerid, year, player.week, player.stillPlay+player.complete, player.rushYards, player.sackAgt, rushTD, player.stillPlay+player.complete, \
	player.rushYards, player.sackAgt, rushTD)

#print inProgArray
if (len(inProgArray[1]) > 0) :
    #print "UPDATE nflstatus SET status='P' WHERE season=%d AND week=%d and status<>'B' and nflteam in ("%(year, wholeWeek)
    for x in inProgArray[1] :
        #print matchDict[x]
        print "UPDATE nflgames SET complete=0, secRemain=%d WHERE season=%d AND week=%d and homeTeam='%s';"%(thePlayers[x].secRemain , year, wholeWeek, matchDict[x])

if (len(inProgArray[2]) > 0) :
    #print "UPDATE nflstatus SET status='F' WHERE season=%d AND week=%d and status<>'B' and nflteam in ("%(year, wholeWeek)
    print "UPDATE nflgames SET complete=1 , secRemain=0 WHERE season=%d AND week=%d and homeTeam in ("%(year, wholeWeek)
    for x in inProgArray[2] :
        #if hasattr(thePlayers[x], 'secRemain') :
        #    print "Team [%s]  Time Remain [%s]" % (matchDict[x], thePlayers[x].secRemain)
        #else :
        #    print "Team [%s]  Time Remain [%s]" % (matchDict[x], 0)

        #print matchDict[x]
        print "'%s', "%matchDict[x]
    print "'XXX');"

