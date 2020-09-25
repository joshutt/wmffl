#!/usr/local/devel/python

matchDict = {1: 'BUF', 2: 'IND', 3: 'MIA', 4: 'NE', 5: 'NYJ', 6: 'CIN', 7: 'CLE',
             8: 'TEN', 9: 'JAC', 10: 'PIT', 11: 'DEN', 12: 'KC', 13: 'LV', 14: 'LAC',
             15: 'SEA', 16: 'DAL', 17: 'NYG', 18: 'PHI', 19: 'ARI', 20: 'WAS',
             21: 'CHI', 22: 'DET', 23: 'GB', 24: 'MIN', 25: 'TB', 26: 'ATL', 27: 'CAR',
             28: 'LAR', 29: 'NO', 30: 'SF', 31: 'BAL', 32: 'HOU'}


def double_byte(byte_as_string):
    return_value = ord(byte_as_string[1]) << 8
    return_value = return_value + ord(byte_as_string[0])
    if ord(byte_as_string[1]) & 128:
        return_value = (return_value ^ 65535) + 1
        return_value = return_value * -1
    return return_value


def add_off_tds(scores):
    pts = 0
    for score in scores:
        if score.is_td():
            pts = pts + 6
        if score.is_special_teams():
            pts = pts + 6
        if score.is2pt():
            pts = pts + 2
    return pts


class Score:
    def __init__(self, score_type, yards=0):
        self.scoreType = score_type
        self.yards = yards

    def is_td(self):
        if self.scoreType <= 11 and self.yards > 0:
            return 1
        return 0

    def is_special_teams(self):
        # I think scoreType 7 is fumble recovery
        # I think scoreType 8 is definitely fumble recovery
        # if self.scoreType == 4 \
        #         or self.scoreType == 5 \
        #         or self.scoreType == 9 \
        #         or self.scoreType == 10 \
        #         or self.scoreType == 11:
        #     return 1
        if self.scoreType in (4, 5, 9, 10, 11):
            return 1
        return 0

    def is2pt(self):
        if self.scoreType >= 63:
            return 1
        return 0

    def is_fg(self):
        if self.scoreType == 13:
            return 1
        return 0

    def is_saftey(self):
        if self.scoreType == 12:
            return 1
        return 0

    def is_xpt(self):
        if self.scoreType == 61:
            return 1
        return 0

    def get_yards(self):
        return self.yards


class Player:
    def __init__(self, player_id, week):
        self.hcid = None
        self.teamagt = None
        self.secRemain = None
        self.id = player_id
        self.week = week
        self.scores = []
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

    def process_record(self, the_record):
        if ord(the_record[7]) == 1:
            num_scored = ord(the_record[8])
            for i in range(0, num_scored):
                type_score = ord(the_record[9 + i * 5])
                yards = double_byte(the_record[10 + i * 5:12 + i * 5])
                self.scores.append(Score(type_score, yards))
        else:
            self.intThrow = ord(the_record[10])
            self.passYards = double_byte(the_record[12:14])
            self.rushYards = double_byte(the_record[16:18])
            self.receptions = double_byte(the_record[18:20])
            self.recYards = double_byte(the_record[20:22])
            self.tackles = ord(the_record[22])
            self.sacks = ord(the_record[24]) / 2.0
            self.intCatch = ord(the_record[27])
            self.passDefend = ord(the_record[28])
            self.intReturn = ord(the_record[29])
            self.fumbles = ord(the_record[32])
            self.fumbRec = ord(the_record[34])
            self.forceFumb = ord(the_record[35])
            self.fumbleReturn = double_byte(the_record[36:38])

    def num_td(self):
        count = 0
        for score in self.scores:
            if score.is_td() and not score.is_special_teams():
                count = count + 1
        return count

    def num_special_teams(self):
        count = 0
        for score in self.scores:
            if score.is_special_teams():
                count = count + 1
        return count

    def num_2pts(self):
        count = 0
        for score in self.scores:
            if score.is2pt():
                count = count + 1
        return count

    def num_saftey(self):
        count = 0
        for score in self.scores:
            if score.is_saftey():
                count = count + 1
        return count

    def num_xpts(self):
        counts = [0, 0]
        for score in self.scores:
            if score.is_xpt():
                if score.get_yards() > 0:
                    counts[0] = counts[0] + 1
                else:
                    counts[1] = counts[1] + 1
        return counts

    def num_fg(self):
        counts = [0, 0, 0, 0, 0]
        for score in self.scores:
            if score.is_fg():
                yards = score.get_yards()
                if yards >= 60:
                    counts[3] = counts[3] + 1
                elif yards >= 50:
                    counts[2] = counts[2] + 1
                elif yards >= 40:
                    counts[1] = counts[1] + 1
                elif yards >= 0:
                    counts[0] = counts[0] + 1
                elif yards >= -30:
                    counts[4] = counts[4] + 1
        return counts

    def score_defense(self):
        pts = self.tackles
        pts = pts + int(self.sacks * 2)
        if self.sacks >= 3:
            pts = pts + int(self.sacks - 2)
        pts = pts + self.intCatch * 4
        pts = pts + self.passDefend
        pts = pts + self.fumbRec * 2
        pts = pts + self.forceFumb * 3
        pts = pts + int((self.fumbleReturn + self.intReturn) / 20)
        pts = pts + self.num_saftey() * 6
        for score in self.scores:
            if score.is_td():
                pts = pts + 9
            if score.is_special_teams():
                pts = pts + 3
            if score.is2pt():
                pts = pts + 2
            if score.scoreType == 13:
                pts = pts + 6
        return int(pts)

    def score_offense(self):
        pts = 0
        pts = pts - self.fumbles * 2
        total_yards = self.passYards + self.rushYards + self.recYards
        if total_yards >= 70:
            pts = pts + int((total_yards - 60) / 10)
        if self.receptions >= 5:
            pts = pts + self.receptions - 4
        pts += add_off_tds(self.scores)
        return int(pts)

    def score_qb(self):
        pts = 0
        pts = pts - (self.fumbles + self.intThrow) * 2
        total_yards = self.passYards + self.rushYards + self.recYards
        if total_yards >= 200:
            pts = pts + int((total_yards - 175) / 25)
        pts += add_off_tds(self.scores)
        return int(pts)

    def score_te(self):
        pts = self.score_offense()
        if 2 <= self.receptions <= 6:
            pts = pts + 1
        if self.receptions == 4:
            pts = pts + 1
        if self.receptions > 12:
            pts = pts + self.receptions - 12
        return int(pts)

    def score_k(self):
        pts = self.num_2pts() * 2
        xps = self.num_xpts()
        fgs = self.num_fg()
        pts = pts + xps[0] - xps[1]
        pts = pts + fgs[0] * 3 + fgs[1] * 4 + fgs[2] * 5 + fgs[3] * 10 - fgs[4]
        return pts


class Team:
    def __init__(self, num, week):
        self.num = num
        self.week = week
        self.scores = []
        self.teamPlayed = 0
        self.pts = 0
        self.againstPts = 0
        self.yards = 0
        self.sacks = 0
        self.rushTD = 0

    def get_team_abb(self):
        return matchDict[self.num]

    def num_rush_td(self):
        count = 0
        for aScore in self.scores:
            if aScore.scoreType == 2:
                count = count + 1
        return count
