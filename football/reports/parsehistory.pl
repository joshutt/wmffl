#!/usr/bin/perl

# The file to open
open(HISTORY,"C:\\Program Files\\FLM2000\\Reports\\02_History2.csv");

# variables
$activescore = 0;
$potentialscore = 0;
$totalscore =0;
%gamesplayed;
%teampoints;
%potpoints;
%positions;

# Define the endline of files to be DOS format
$/ = "\015\012";

# readthe first list
<HISTORY>;

# for every line in the file do this
while (<HISTORY>) {
    chomp();    # cut off the endline info
    
    # If this is the Player/Week line, set the teamanme to the previous line
    if ($_ =~ /Player\/Week/) {
        $line =~ s/,//g;
        $teamname = $line;
        $activescore = 0;
        $potentialscore = 0;
        %teamarray;
        next;
    } 

    # set line to previous read in and break it up
    $line = $_;
    @breaklist = split(/,/, $line);

    if (@breaklist[0] =~ /Potential/) {
        for ($i=3; $i<scalar(@breaklist); $i++) {
            $potentialscore += @breaklist[$i];
        }
        $potpoints{$teamname} = $potentialscore;
        $gamesplayed{$teamname} = $i-3;
    }

    # for each week in the history list
    for ($i=3; $i<scalar(@breaklist)-1; $i++) {
        # if the player was on the team at the time
        if (@breaklist[$i] !~ /X/) {
            $totalscore += @breaklist[$i];

            # if the player was activated 
            if (@breaklist[$i] =~ /\*/) {
                $activescore += @breaklist[$i];
                $teampoints{$teamname} = $activescore;
                $positions{$teamname." ".@breaklist[0]} += @breaklist[$i];
           }
        }
    }
}

# print out total points for each team
print "TOTAL POINTS\n";
foreach(sort keys(%teampoints)) {
    $ptsscored = $teampoints{$_};
    $ptspotential = $potpoints{$_};
    $games = $gamesplayed{$_};
    $power = ($ptsscored*2 + $ptspotential) / (3 * $games);
    print "$_ : $teampoints{$_} - $potpoints{$_} - ";
    printf "%.2f\n", $power;
}

print "\n\nPOSITION POINTS\n";
foreach(sort keys(%positions)) {
    $position = $_;
    print "$position $positions{$position} \n";
}


close (HISTORY);
