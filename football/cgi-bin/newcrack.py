#import zipfile
#from players import doubleByte
from players import Player
from players import Team
from players import Score

#from filereader import HeaderRecord


#dirPath = "C:\\Program Files\\FLM2000\\Resource"
#statFile = "%s\\f0104b.fs0"%dirPath
#statFile = "%s\\f0201a.fs0"%dirPath
#statFile = "%s\\f0203a.fs0"%dirPath
#dnFile = zipfile.ZipFile(statFile)
#indyStats = dnFile.read('indstats.nfl')
#teamStats = dnFile.read('teamstat.nfl')

#indFile = open("unzip/indstats.nfl")
indFile = open("indstats.nfl")
indyStats = indFile.read()
indFile.close()

# Get Headers
#header = HeaderRecord(indyStats)

#playerDict = {}
#numRecords = len(indyStats) / 84
#numRecords -= 2
#for i in range(2,numRecords) :
#	theRecord = indyStats[i*84:(i+1)*84-1]
#	week = ord(theRecord[0])
#	id = doubleByte(theRecord[1:3])

#	if (playerDict.has_key(id)) :
#		thePlayer = playerDict[id]
#	else :
#		thePlayer = Player(id, week)
#	thePlayer.processRecord(theRecord)
#	playerDict[id] = thePlayer

#teamDict = {}
#numRecords = len(teamStats) / 169;
#for j in range(2, numRecords) :
#	theRecord = teamStats[i*169:(i+1)*169-1]
#	week = ord(theRecord[0])
	

#for thePlayer in playerDict.values() :
#	score = thePlayer.scoreOffense()
#	if (score) :
#		print "%d = %d"%(thePlayer.id, score)

import StringIO

def valuate (bArr) :
	value = 0
	cArr = bArr
	while (len(cArr) > 0) :
		value = value << 8
		value = value + ord(cArr[-1])
		cArr = cArr[0:-1]
	return value

theFile = StringIO.StringIO(indyStats)

theFile.seek(74)

count = 0
size = 0
thePlayers = {}
singleFlag = 0
while (size != ord('\x63')) :
	size = ord(theFile.read(1))
	#print count
	#count += 1
	#if count==10 :
	#	break
	playerid = 0
	week = 0
	numScores = 0
	player = None
	while (size != ord('\x62') and size != ord('\x63')) :
		if (size == 1) :
			singleFlag = 1
		elif (size == 2) :
			singleFlag = 0
		
		id = valuate(theFile.read(4))
		if (id == 1) :
			playerid = valuate(theFile.read(4))
		elif (id == 2) :
			week = valuate(theFile.read(4))
			if (thePlayers.has_key(playerid)) :
				player = thePlayers[playerid]
			else :	
				player = Player(playerid, week)
		elif (id == 5) :
			player.stillplay = valuate(theFile.read(4))
		elif (id == 10) :
			numScores = valuate(theFile.read(4))
			#print "Times %d"%numScores
			newCount = 0
			while (newCount < numScores) :
				theFile.seek(1, 1)
				newId = valuate(theFile.read(4))
				if (newId == 12) :
					scoreType = valuate(theFile.read(4))
				elif (newId == 13) :
					scoreYards = valuate(theFile.read(4))
					newCount = newCount + 1
					player.scores.append(Score(scoreType, scoreYards))
				else :
					theFile.seek(4, 1)
				
			#for i in range(0, numScores) :
				#print "%d this time %d"%(playerid, i) 
				#theFile.seek(14, 1)
				#scoreType = valuate(theFile.read(4))
				#theFile.seek(5, 1)
				#scoreYards = valuate(theFile.read(4))
				#if (scoreYards > 0) :
					#theFile.seek(18, 1)
				#player.scores.append(Score(scoreType, scoreYards))
			# for each numscore read the score and store
		elif (id == 22) :
			player.intThrow = valuate(theFile.read(4))
		elif (id == 24) :
			player.passYards = valuate(theFile.read(4))
		elif (id == 29) :
			player.rushYards = valuate(theFile.read(4))
		elif (id == 30) :
			player.receptions = valuate(theFile.read(4))
		elif (id == 32) :
			player.recYards = valuate(theFile.read(4))
		elif (id == 33) :
			player.tackles = valuate(theFile.read(4))
		elif (id == 35) :
			player.sacks = valuate(theFile.read(4)) / 2.0
		elif (id == 37) :
			player.intCatch = valuate(theFile.read(4))
		elif (id == 38) :
			player.passDefend = valuate(theFile.read(4))
		elif (id == 39) :
			player.intReturn = valuate(theFile.read(4))
		elif (id == 41) :
			player.fumbles = valuate(theFile.read(4))
		elif (id == 43) :
			player.fumbRec = valuate(theFile.read(4))
		elif (id == 44) :
			player.forceFumb = valuate(theFile.read(4))
		elif (id == 45) :
			player.fumbleReturn = valuate(theFile.read(4))
		elif (id == 101) :
			# This happens on the first team record
			size = ord('\x63')
			break
		elif (singleFlag) :
			theFile.seek(1, 1)
		else :
			theFile.seek(4, 1)
		size = ord(theFile.read(1))
	#print "%d - %d - %d"%(playerid, week, numScores)
	#print player
	if (player) :
		thePlayers[playerid] = player

theTeams = {}
theFile.seek(-5, 1)
size = ord(theFile.read(1))
teamid = 0
theTeam = None
while (size != ord('\x0a')) :
	
	if (size == 1) :
		singleFlag = 1
	elif (size == 2) :
		singleFlag = 0
	elif (size == 10) :
		break
	elif (size == ord('\x63')) :
		print 'Here %d'%teamid
		if (theTeam) :
			theTeams[teamid] = theTeam
		teamid = 0
		theTeam = None
		try :
			size = ord(theFile.read(1))
		except TypeError :
			break
		continue
	id = valuate(theFile.read(4))
	
	if (id == 101) :
		teamid = valuate(theFile.read(4))
	elif (id == 102) :
		week = valuate(theFile.read(4))
		theTeam = Team(teamid, week)
	elif (id == 103) :
		theTeam.teamPlayed = valuate(theFile.read(4))
        elif (id == 105) :
            theTeam.finalflag = valuate(theFile.read(1))
	elif (id == 110) :
		# Pts scored
		theTeam.pts = valuate(theFile.read(4))
	elif (id == 117) :
		# rush yards
		theTeam.yards = valuate(theFile.read(4))
	elif (id == 122) :
		# sacks against
		theTeam.sacks = valuate(theFile.read(4))
	elif (id == 200) :
		# num oif scores
		numScores = valuate(theFile.read(4))
		newCount = 0
		while (newCount < numScores) :
			theFile.seek(1, 1)
			newId = valuate(theFile.read(4))
			if (newId == 202) :
				scoreType = valuate(theFile.read(4))
				theTeam.scores.append(Score(scoreType))
				newCount = newCount + 1
			else :
				theFile.read(4)
	elif (singleFlag) :
		theFile.seek(1, 1)
	else :
		theFile.seek(4, 1)
	size = ord(theFile.read(1))

print theTeams.keys()
for theKey in theTeams.keys() :
	theTeams[theKey].againstPts = theTeams[theTeams[theKey].teamPlayed].pts
	

theFile.close()

keyList = thePlayers.keys()
keyList.sort()
#for key in thePlayers.keys() :
outpu = open("out.sql", "w")
#print 'DELETE * FROM stats WHERE week=%d; \n\r '%(thePlayers[keyList[0]].week)
#print "INSERT INTO stats (statid, week, yards, intthrow, rec, fum, tackles, sacks"
#print ", intcatch, passdefend, returnyards, fumrec, forcefum, tds, 2pt, specTD) VALUES "
outpu.write( 'DELETE FROM stats WHERE week=%d; \n '%(thePlayers[keyList[0]].week))
outpu.write( "INSERT INTO stats (statid, week, yards, intthrow, rec, fum, tackles, sacks")
outpu.write( ", intcatch, passdefend, returnyards, fumrec, forcefum, tds, 2pt, specTD, Safety, XP, MissXP, FG30, FG40, FG50, FG60, MissFG30) VALUES ")
first = 1
for key in keyList :
	player = thePlayers[key]
	#print "Playerid is %d with %d rushing yards"%(player.id, player.rushYards)
	if (not first) :
		#print ", "
		outpu.write( ", ")
		
	first = 0
	#print "(%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d) " % \
	#	(player.id, player.week, player.passYards+player.rushYards+player.recYards, \
	#	player.intThrow, player.receptions, player.fumbles, player.tackles, \
	#	player.sacks, player.intCatch, player.passDefend, player.intReturn+player.fumbleReturn, \
	#	player.fumbRec, player.forceFumb, player.numTD(), player.num2Pts(), player.numSpecialTeams())
	fgs = player.numFG()
	xps = player.numXPts()
	outpu.write( "(%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d) " % \
		(player.id, player.week, player.passYards+player.rushYards+player.recYards, \
		player.intThrow, player.receptions, player.fumbles, player.tackles, \
		player.sacks, player.intCatch, player.passDefend, player.intReturn+player.fumbleReturn, \
		player.fumbRec, player.forceFumb, player.numTD(), player.num2Pts(), player.numSpecialTeams(), \
		player.numSaftey(), xps[0], xps[1], fgs[0], fgs[1], fgs[2], fgs[3], fgs[4]))
#print "; "
outpu.write( ";\n")

for theKey in theTeams.keys() :
	team = theTeams[theKey]
	outpu.write("INSERT INTO stats (statid, week, ptdiff) ");
	outpu.write("SELECT statid, %d, %d FROM players "%(team.week, team.pts-team.againstPts))
	outpu.write("WHERE position='HC' AND NFLTeam='%s';\n"%team.getTeamAbb())
	outpu.write("INSERT INTO stats (statid, week, yards, sacks, tds) ")
	outpu.write("SELECT statid, %d, %d, %d, %d FROM players "%(team.week, team.yards, team.sacks, team.numRushTD()))
	outpu.write("WHERE position='OL' and NFLTeam='%s';\n"%team.getTeamAbb())
        outpu.write("UPDATE nflstatus SET status='%s' "%team.getStatus())
        outpu.write("WHERE nflteam='%s' AND week=%d;\n"%(team.getTeamAbb(), team.week))
	
#print valuate('\xae\xff\xff\xff')	
outpu.close()
