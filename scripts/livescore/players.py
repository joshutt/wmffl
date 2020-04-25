#!/usr/local/devel/python

matchDict = {1: 'BUF', 2: 'IND', 3: 'MIA', 4: 'NE', 5: 'NYJ', 6: 'CIN', 7: 'CLE', \
			 8: 'TEN', 9: 'JAC', 10: 'PIT', 11: 'DEN', 12: 'KC', 13: 'LVR', 14: 'LAC', \
			 15: 'SEA', 16: 'DAL', 17: 'NYG', 18: 'PHI', 19: 'ARI', 20: 'WAS', \
			 21: 'CHI', 22: 'DET', 23: 'GB', 24: 'MIN', 25: 'TB', 26: 'ATL', 27: 'CAR', \
			 28: 'LAR', 29: 'NO', 30: 'SF', 31: 'BAL', 32: 'HOU'}
			
def doubleByte(byteAsString) :
	returnValue = 0
	returnValue = ord(byteAsString[1]) << 8
	returnValue = returnValue + ord(byteAsString[0])
	if (ord(byteAsString[1]) & 128) :
		returnValue = (returnValue ^ 65535)+1
		returnValue = returnValue * -1
	return returnValue


class Score :
	def __init__ (self, scoreType, yards=0) :
		self.scoreType = scoreType
		self.yards = yards

	def isTD(self) :
		if (self.scoreType <= 11 and self.yards > 0) :
			return 1
		return 0

	def isSpecialTeams(self) :
        # I think scoreType 7 is fumble recovery
        # I think scoreType 8 is definitely fumble recovery
		if (self.scoreType==4 or self.scoreType==5 or self.scoreType==9 or self.scoreType==10 or self.scoreType==11) :
			return 1
		return 0

	def is2pt(self) :
		if (self.scoreType >= 63) :
			return 1
		return 0

	def isFG(self) :
		if (self.scoreType == 13) :
			return 1
		return 0

	def isSaftey(self) :
		if (self.scoreType == 12) :
			return 1
		return 0

	def isXPt(self) :
		if (self.scoreType == 61) :
			return 1
		return 0

	def getYards(self) :
		return self.yards



class Player :
	def __init__ (self, id, week) :
		self.id = id
		self.week = week
		self.scores = []
		self.__setToZero()
		
	def processRecord (self, theRecord) :
		if (ord(theRecord[7]) == 1) :
			numScored = ord(theRecord[8])
			for i in range(0, numScored) :
				typeScore = ord(theRecord[9+i*5])
				yards = doubleByte(theRecord[10+i*5:12+i*5])
				self.scores.append(Score(typeScore, yards))
		else :
			self.intThrow = ord(theRecord[10])
			self.passYards = doubleByte(theRecord[12:14])
			self.rushYards = doubleByte(theRecord[16:18])
			self.receptions = doubleByte(theRecord[18:20])
			self.recYards = doubleByte(theRecord[20:22])
			self.tackles = ord(theRecord[22])
			self.sacks = ord(theRecord[24]) / 2.0
			self.intCatch = ord(theRecord[27])
			self.passDefend = ord(theRecord[28])
			self.intReturn = ord(theRecord[29])
			self.fumbles = ord(theRecord[32])
			self.fumbRec = ord(theRecord[34])
			self.forceFumb = ord(theRecord[35])
			self.fumbleReturn = doubleByte(theRecord[36:38])
	
	def numTD (self) :
		count = 0
		for score in self.scores :
			if (score.isTD() and not score.isSpecialTeams()) :
				count = count + 1
		return count

	def numSpecialTeams (self) :
		count = 0
		for score in self.scores :
			if (score.isSpecialTeams()) :
				count = count + 1
		return count

	def num2Pts (self) :
		count = 0
		for score in self.scores :
			if (score.is2pt()) :
				count = count + 1
		return count

	def numSaftey (self) :
		count = 0
		for score in self.scores :
			if (score.isSaftey()) :
				count = count + 1
		return count

	def numXPts (self) :
		counts = [0, 0]
		for score in self.scores :
			if (score.isXPt()) :
				if (score.getYards() > 0) :
					counts[0] = counts[0] + 1
				else :
					counts[1] = counts[1] + 1
		return counts

	def numFG (self) :
		counts = [0, 0, 0, 0, 0]
		for score in self.scores :
			if (score.isFG()) :
				yards = score.getYards()
				if (yards >= 60) :
					counts[3] = counts[3] + 1
				elif (yards >= 50) :
					counts[2] = counts[2] + 1
				elif (yards >= 40) :
					counts[1] = counts[1] + 1
				elif (yards >= 0):
					counts[0] = counts[0] + 1
				elif (yards >= -30) :
					counts[4] = counts[4] + 1
		return counts		
			
	def scoreDefense(self) :
		pts = self.tackles
		pts = pts + int(self.sacks * 2)
		if (self.sacks >= 3) :
			pts = pts + int(scks-2)
		pts = pts + self.intCatch * 4
		pts = pts + self.passDefend
		pts = pts + self.fumbRec * 2
		pts = pts + self.forceFumb * 3
		pts = pts + int((self.fumbleReturn + self.intReturn)/20)
		pts = pts + self.numSaftey() * 6
		for score in self.scores :
			if (score.isTD()) :
				pts = pts + 9
			if (score.isSpecialTeams()) :
				pts = pts + 3
			if (score.is2pt()) :
				pts = pts + 2
			if (score.scoreType == 13) :
				pts = pts + 6
		return int(pts)

	def scoreOffense(self) :
		pts = 0
		pts = pts - self.fumbles*2
		totalYards = self.passYards + self.rushYards + self.recYards
		if (totalYards >= 70) :
			pts = pts + int((totalYards - 60)/10)
		if (self.receptions >= 5) :
			pts = pts + self.receptions - 4
		for score in self.scores :
			if (score.isTD()) :
				pts = pts + 6
			if (score.isSpecialTeams()) :
				pts = pts + 6
			if (score.is2pt()) :
				pts = pts + 2
		return int(pts)
		
	def scoreQB(self) :
		pts = 0
		pts = pts - (self.fumbles + self.intThrow) * 2
		totalYards = self.passYards + self.rushYards + self.recYards
		if (totalYards >= 200) :
			pts = pts + int((totalYards - 175)/25)
		for score in self.scores :
			if (score.isTD()) :
				pts = pts + 6
			if (score.isSpecialTeams()) :
				pts = pts + 6
			if (score.is2pt()) :
				pts = pts + 2
		return int(pts)
		
	def scoreTE(self) :
		pts = self.scoreOffense()
		if (self.receptions >= 2 and self.receptions <= 6) :
			pts = pts + 1
		if (self.receptions == 4) :
			pts = pts + 1
		if (self.receptions > 12) :
			pts = pts + self.receptions - 12
		return int(pts)

	def scoreK(self) :
		pts = num2Pts() * 2
		xps = numXPts()
		fgs = numFG()
		pts = pts + xps[0] - xps[1]
		pts = pts + fgs[0]*3 + fgs[1]*4 + fgs[2]*5 + fgs[3]*10 - fgs[4]
		return pts
		

	def __setToZero (self) :
		self.stillPlay = 0
		self.complete = 0
		self.intThrow = 0
		self.passYards = 0
		self.rushYards = 0
		self.receptions = 0
		self.recYards = 0
		self.tackles = 0
		self.sacks = 0
		self.intCatch = 0
		self.passDefend = 0
		self.intReturn = 0
		self.fumbles = 0
		self.fumbRec = 0
		self.forceFumb = 0
		self.fumbleReturn = 0
		self.xpt = 0
		self.missxpt = 0
		self.fg30 = 0
		self.fg40 = 0
		self.fg50 = 0
		self.fg60 = 0
		self.missfg30 = 0
		self.offTDs = 0
		self.defTDs = 0
		self.stTDs = 0
		self.safety = 0
		self.twopts = 0
		self.pts = 0
		self.sackAgt = 0
		self.blockPunt = 0
		self.blockFG = 0
		self.blockXP = 0
		self.penalties = 0


class Team :
	def __init__ (self, num, week) :
		self.num = num
		self.week = week
		self.scores = []
		self.teamPlayed = 0
		self.pts = 0
		self.againstPts = 0
		self.yards = 0
		self.sacks = 0
		self.rushTD = 0

	def getTeamAbb (self) :
		return matchDict[self.num];

	def numRushTD (self) :
		count = 0
		for aScore in self.scores :
			if (aScore.scoreType == 2) :
				count = count + 1
		return count
