<?
function scoreHC($scoreArray) {
	$pts = 0;
	if ($scoreArray['played'] > 0) {
		if ($scoreArray['ptdiff'] == 0) 
			$pts = 1;
		else if ($scoreArray['ptdiff'] > 0) {
			$pts = 3;
			$pts += floor($scoreArray['ptdiff']/10);
		}

        if ($scoreArray['penalties'] <= 3) {
            $pts += 3;
        } else if ($scoreArray['penalties'] <= 6) {
            $pts += 2;
        } else if ($scoreArray['penalties'] <= 8) {
            $pts += 1;
        } else if ($scoreArray['penalties'] <= 10) {
            $pts += 0;
        } else if ($scoreArray['penalties'] <= 12) {
            $pts -= 1;
        } else if ($scoreArray['penalties'] <= 14) {
            $pts -= 2;
        } else {
            $pts -= 3;
        }
	}
	return $pts;
}

function scoreQB($scoreArray) {
    $pts = 0;
    $pts -= ($scoreArray['fum'] + $scoreArray['intthrow']) * 2;
    if ($scoreArray['yards'] >= 200) {
        $pts += floor(($scoreArray['yards'] - 175)/25);
    }
    $pts += $scoreArray['tds'] * 6;
    $pts += $scoreArray['2pt'] * 2;
    return $pts;
}


function scoreOffense($scoreArray) {
    $pts = 0;
    $pts -= $scoreArray['fum'] * 2;
    if ($scoreArray['yards'] >= 70) {
        $pts += floor(($scoreArray['yards'] - 60)/10);
    }
    if ($scoreArray['rec'] >= 5) {
        $pts += $scoreArray['rec'] - 4;
    }
    $pts += $scoreArray['tds'] * 6;
    $pts += $scoreArray['2pt'] * 2;
    $pts += $scoreArray['specTD'] * 12;
    return $pts;
}

function scoreRB($scoreArray) {return scoreOffense($scoreArray);}
function scoreWR($scoreArray) {return scoreOffense($scoreArray);}

function scoreTE($scoreArray) {
    $pts = scoreOffense($scoreArray);
    if ($scoreArray['rec'] >= 2 && $scoreArray['rec'] <= 6) {
        $pts += 1;
    } 
    if ($scoreArray['rec'] == 4) {
        $pts += 1;
    }
    if ($scoreArray['rec'] > 12) {
        $pts += $scoreArray['rec'] - 12;
    }
    return $pts;
}


function scoreOL($scoreArray) {
	$pts = $scoreArray['tds'];
	if ($scoreArray['yards'] >= 100) {
		$pts += floor($scoreArray['yards']/10 - 9);
	}
	if ($scoreArray['played']) {
		switch ($scoreArray['sacks']) {
			case 0 :
				$pts += 5; break;
			case 1 :
				$pts += 2; break;
			case 2 :
				$pts += 1; break;
			case 5 :
				$pts -= 1; break;
			case 6 :
				$pts -= 2; break;
			case 7 :
				$pts -= 5; break;
			default:
				if ($scoreArray['sacks'] >= 8)
					$pts -= ($scoreArray['sacks'] - 6) * 5;
				break;
		}
	}
	return $pts;
}

function scoreK($scoreArray) {
	$pts = $scoreArray['XP'];
	$pts -= $scoreArray['MissXP'];
	$pts += $scoreArray['2pt']*2;
	$pts += $scoreArray['FG30']*3;
	$pts += $scoreArray['FG40']*4;
	$pts += $scoreArray['FG50']*5;
	$pts += $scoreArray['FG60']*10;
	$pts -= $scoreArray['MissFG30'];
    $pts += $scoreArray['specTD'] * 12;
	return $pts;
}
	

function scoreDefense($scoreArray) {
    $pts = $scoreArray['tackles'];
    $pts += floor($scoreArray['sacks'] * 2);
    if ($scoreArray['sacks'] >= 3) {
        $pts += floor($scoreArray['sacks']-2);
    }
    $pts += $scoreArray['intcatch'] * 4;
    $pts += $scoreArray['passdefend'];
    $pts += $scoreArray['fumrec'] * 2;
    $pts += $scoreArray['forcefum'] * 3;
    if ($scoreArray['returnyards'] > 0) {
        $pts += floor($scoreArray['returnyards']/20);
    }
    $pts += $scoreArray['tds'] * 9;
    $pts += $scoreArray['2pt'] * 2;
    $pts += $scoreArray['specTD'] * 12;
	$pts += $scoreArray['Safety'] * 6;
    $pts += $scoreArray['blockpunt'] * 3;
    $pts += $scoreArray['blockxp'] * 3;
    $pts += $scoreArray['blockfg'] * 3;
    return $pts;
}

function scoreDL($scoreArray) {return scoreDefense($scoreArray);}
function scoreLB($scoreArray) {return scoreDefense($scoreArray);}
function scoreDB($scoreArray) {return scoreDefense($scoreArray);}


function scoreString($score, $pos) {
    $returnString = "";
    switch ($pos) {
        case 'HC':
            if ($score['played'] > 0) {
                $ptDiff = $score['ptdiff'];
                if ($ptDiff == 0) {
                    $pts = 1;
                } else if ($ptDiff > 0) {
                    $pts = floor($ptDiff/10) + 3;
                } else {
                    $pts = 0;
                }
                $returnString .= "^$ptDiff point difference^$pts";
                
                $ptDiff = $score['penalties'];
                if ($ptDiff <= 3) {
                    $pts = 3;
                } else if ($ptDiff <= 6) {
                    $pts = 2;
                } else if ($ptDiff <= 8) {
                    $pts = 1;
                } else if ($ptDiff <= 10) {
                    $pts = 0;
                } else if ($ptDiff <= 12) {
                    $pts = -1;
                } else if ($ptDiff <= 14) {
                    $pts = -2;
                } else {
                    $pts = -3;
                }
                $returnString .= "^$ptDiff penalties^$pts";
            }
            break;
        case 'QB':
            $yards = $score['yards'];
            if ($yards < 200) {
                $pts = 0;
            } else {
                $pts = floor(($yards - 175)/25);
            }
            if ($yards != 0) { $returnString .= "^$yards combined yards^$pts"; }

            $tds = $score['tds'];
            $pts = $tds * 6;
            if ($tds > 0) {$returnString .= "^$tds touchdowns^$pts";}

            $tds = $score['2pt'];
            $pts = $tds * 2;
            if ($tds > 0) {$returnString .= "^$tds 2-pt conversions^$pts";}

            $tds = $score['fum'];
            $pts = $tds * -2;
            if ($tds > 0) {$returnString .= "^$tds fumbles^$pts";}

            $tds = $score['intthrow'];
            $pts = $tds * -2;
            if ($tds > 0) {$returnString .= "^$tds interceptions^$pts";}
            break;
        case 'RB': case 'WR': 
            $yards = $score['yards'];
            if ($yards < 70) {
                $pts = 0;
            } else {
                $pts = floor(($yards - 60)/10);
            }
            if ($yards != 0) { $returnString .= "^$yards combined yards^$pts"; }

            $rec = $score['rec'];
            if ($rec < 5) {
                $pts = 0;
            } else {
                $pts = $rec - 4;
            }
            if ($rec > 0) {$returnString .= "^$rec receptions^$pts";}

            $tds = $score['tds'];
            $pts = $tds * 6;
            if ($tds > 0) {$returnString .= "^$tds touchdowns^$pts";}

            $spec = $score['specTD'];
            $pts = $spec * 12;
            if ($spec > 0) {$returnString .= "^$spec special team touchdowns^$pts";}

            $tds = $score['2pt'];
            $pts = $tds * 2;
            if ($tds > 0) {$returnString .= "^$tds 2-pt conversions^$pts";}

            $tds = $score['fum'];
            $pts = $tds * -2;
            if ($tds > 0) {$returnString .= "^$tds fumbles^$pts";}
            break;
        case 'TE':
            $yards = $score['yards'];
            if ($yards < 70) {
                $pts = 0;
            } else {
                $pts = floor(($yards - 60)/10);
            }
            if ($yards != 0) { $returnString .= "^$yards combined yards^$pts"; }

            $rec = $score['rec'];
            if ($rec < 2) {
                $pts = 0;
            } else if ($rec < 8) {
                $pts = floor($rec/2);
            } else if ($rec < 12) {
                $pts = $rec - 4;
            } else {
                $pts = 2 * ($rec - 8);
            }
            if ($rec > 0) {$returnString .= "^$rec receptions^$pts";}

            $tds = $score['tds'];
            $pts = $tds * 6;
            if ($tds > 0) {$returnString .= "^$tds touchdowns^$pts";}

            $spec = $score['specTD'];
            $pts = $spec * 12;
            if ($spec > 0) {$returnString .= "^$spec special team touchdowns^$pts";}

            $tds = $score['2pt'];
            $pts = $tds * 2;
            if ($tds > 0) {$returnString .= "^$tds 2-pt conversions^$pts";}

            $tds = $score['fum'];
            $pts = $tds * -2;
            if ($tds > 0) {$returnString .= "^$tds fumbles^$pts";}
            break;
        case 'K' :
            $tds = $score['XP'];
            $pts = $tds * 1;
            if ($tds > 0) {$returnString .= "^$tds extra points^$pts";}

            $tds = $score['MissXP'];
            $pts = $tds * -1;
            if ($tds > 0) {$returnString .= "^$tds missed extra points^$pts";}

            $tds = $score['2pt'];
            $pts = $tds * 2;
            if ($tds > 0) {$returnString .= "^$tds 2-pt conversions^$pts";}

            $tds = $score['FG30'];
            $pts = $tds * 3;
            if ($tds > 0) {$returnString .= "^$tds field goals (0-39 yards)^$pts";}

            $tds = $score['FG40'];
            $pts = $tds * 4;
            if ($tds > 0) {$returnString .= "^$tds field goals (40-49 yards)^$pts";}

            $tds = $score['FG50'];
            $pts = $tds * 5;
            if ($tds > 0) {$returnString .= "^$tds field goals (50-59 yards)^$pts";}

            $tds = $score['FG60'];
            $pts = $tds * 10;
            if ($tds > 0) {$returnString .= "^$tds field goals (60+ yards)^$pts";}

            $tds = $score['MissFG30'];
            $pts = $tds * -1;
            if ($tds > 0) {$returnString .= "^$tds missed field goals (under 30 yards)^$pts";}

            $spec = $score['specTD'];
            $pts = $spec * 12;
            if ($spec > 0) {$returnString .= "^$spec special team touchdowns^$pts";}
            break;
        case 'OL' :
            $tds = $score['tds'];
            $pts = $tds * 1;
            if ($tds > 0) {$returnString .= "^$tds rushing touchdowns^$pts";}

            $tds = $score['yards'];
            $pts = $tds * 1;
            if ($tds < 100) {
                $pts = 0;
            } else {
                $pts = floor(($pts - 90)/10);
            }
            if ($tds > 0) {$returnString .= "^$tds rushing yards^$pts";}

            if ($score['played']) {
                $sacks = $score['sacks'];
                switch ($sacks) {
                    case 0 :
                        $pts = 5; break;
                    case 1 :
                        $pts = 2; break;
                    case 2 :
                        $pts = 1; break;
                    case 5 :
                        $pts = -1; break;
                    case 6 :
                        $pts = -2; break;
                    case 7 :
                        $pts = -5; break;
                    default:
                        if ($sacks >= 8) {
                            $pts = ($sacks - 6) * -5;
                        } else {
                            $pts = 0;
                        }
                        break;
                }
                $returnString .= "^$sacks sacks allowed^$pts";
            }

            break;
        case 'DL' :  case 'LB' :  case 'DB' :
            $tds = $score['tackles'];
            $pts = $tds * 1;
            if ($tds > 0) {$returnString .= "^$tds tackles^$pts";}

            $tds = $score['passdefend'];
            $pts = $tds * 1;
            if ($tds > 0) {$returnString .= "^$tds pass defense^$pts";}

            $sacks = $score['sacks'];
            $pts = $sacks * 2;
            if ($sacks >= 3) {
                $pts += floor($sacks - 2);
            }
            if ($sacks > 0) {$returnString .= "^$sacks sacks^$pts";}

            $tds = $score['intcatch'];
            $pts = $tds * 4;
            if ($tds > 0) {$returnString .= "^$tds interceptions^$pts";}

            $tds = $score['fumrec'];
            $pts = $tds * 2;
            if ($tds > 0) {$returnString .= "^$tds fumbles recovered^$pts";}

            $tds = $score['forcefum'];
            $pts = $tds * 3;
            if ($tds > 0) {$returnString .= "^$tds forced fumbles^$pts";}

            $tds = $score['returnyards'];
            $pts = floor($tds/20);
            if ($tds > 0) {$returnString .= "^$tds return yards^$pts";}

            $tds = $score['tds'];
            $pts = $tds * 9;
            if ($tds > 0) {$returnString .= "^$tds touchdowns^$pts";}

            $spec = $score['specTD'];
            $pts = $spec * 12;
            if ($spec > 0) {$returnString .= "^$spec special team touchdowns^$pts";}

            $tds = $score['2pt'];
            $pts = $tds * 2;
            if ($tds > 0) {$returnString .= "^$tds 2-pt conversions^$pts";}

            $tds = $score['Safety'];
            $pts = $tds * 6;
            if ($tds > 0) {$returnString .= "^$tds safeties^$pts";}

            $tds = $score['blockpunt'] + $score['blockxp'] + $score['blockfg'];
            $pts = $tds * 3;
            if ($tds > 0) {$returnString .= "^$tds blocked kicks^$pts";}

            break;
    }
    while($returnString[0] == '^') {
        $returnString = substr($returnString, 1);
    }
    return $returnString;
}

