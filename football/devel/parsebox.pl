#!/usr/bin/perl

#$LOCATION = "C:\\Program Files\\FLM2000\\Reports\\";
$LOCATION = "/export/playground/wmffl/reports/";

sub readLine(HISTORY) {
    $line = <HISTORY>;
    chomp();
    @breaklist = split(/,/, $line);
    return @breaklist;
}

sub readTeam(HISTORY) {
    $teamcounter = 0;
    while (true) {
        @breaklist = &readLine(HISTORY);
        if (!scalar(@breaklist)) {return;}

        if ($breaklist[1] == "") {
            $teamname = $breaklist[0];
            $games[int($teamcounter/2)][$teamcounter%2] = $teamname;
            $teamcounter++;
        } else {
            $player = {
                    'Position' => $breaklist[0],
                    'ID' => $breaklist[1],
                    'Team' => $teamname,
                    'Pts' => $breaklist[3]
            };
            $position = $breaklist[0];
            $id = $breaklist[1];
            $pts = $breaklist[3];

            $pospts{$teamname}{$position} += $pts;
            $players{$id} = $player;
        }

    } 
}



%finallist;

for ($week=1; $week<=16; $week++) {
    # The file to open
    if ($week < 10) {
        $filename = "020".$week."_BoxScore.csv";
    } else {
        $filename = "02".$week."_BoxScore.csv";
    }
    open(HISTORY,$LOCATION.$filename);


    # variables
    @games = undef;
    %pospts = undef;
    %players = undef;
    %versus = undef;

    # Define the endline of files to be DOS format
    $/ = "\015\012";

    # Parse the file
    <HISTORY>;
    &readTeam(HISTORY);
    close(HISTORY);

    # Print the output
    foreach(keys (%pospts)) {
        $teamname = $_;
        %poshash = $pospts{$teamname};
        #print "\n\n".$teamname . "\n";
        @pos = ("HC:", "QB:", "RB:", "WR:", "TE:", "Off:", "K :","DL:","LB:", "DB:");
        for ($i=0; $i<scalar(@pos); $i++) {
           #print $pos[$i]." ".$pospts{$teamname}{$pos[$i]} ."\n";
        }
    }

    # Store who each team plays
    for ($i=0; $i<scalar(@games); $i++) {
        $versus{$games[$i][0]} = $games[$i][1];
        $versus{$games[$i][1]} = $games[$i][0];
    }

    foreach $id (keys (%players)) {
        $team = $players{$id}{"Team"};
        $pts = $players{$id}{"Pts"};
        $pos = $players{$id}{"Position"};
        $teamagainst = $versus{$team};
        $ptsagainst = $pospts{$teamagainst}{$pos};
#        if ($pos=="RB:" || $pos=="WR:" || $pos=="DL:" || $pos=="LB:" || $pos=="DB:") {
        if ($pos =~ /(RB)|(WR)|(DL)|(LB)|(DB)/) {
            $adjptsagainst = $ptsagainst / 2;
        } else {$adjptsagainst = $ptsagainst;}

        if ($adjptsagainst < 0) {$adjptsagainst = 0;}
        $mvppts = $pts - $adjptsagainst;
        $mvppts = ($mvppts < 0) ? 0 : $mvppts;
    #    print $pos." ".$mvppts."\n";
        if ($mvppts > 0) {
            if (exists $finallist{$id}) {
                $finallist{$id}{"mvppts"} += $mvppts;
            } else {
                $players{$id}{"mvppts"} = $mvppts;
                $finallist{$id} = $players{$id};
            }
        }
    }
}




sub bymvppts {
    $finallist{$b}{"mvppts"} <=> $finallist{$a}{"mvppts"};
}

@sortedlist = sort bymvppts keys %finallist;


$thelength = scalar(@sortedlist);
$thelength = 10;

for ($i=0; $i<$thelength; $i++) {
#    print $sortedlist[$i];
    $k = $i+1;
    print $k.". ".$finallist{$sortedlist[$i]}->{"Position"}." ".$finallist{$sortedlist[$i]}->{"ID"}." ".$finallist{$sortedlist[$i]}->{"mvppts"}."\n";
}

print "\n\nDefense\n";
$k = 0;
for ($i=0; $k<$thelength && $i<scalar(@sortedlist); $i++) {
    $pos = $finallist{$sortedlist[$i]}->{"Position"};
#    if (($pos=="DL:") || ($pos=="LB:") || ($pos=="DB:")) {
    if ($pos =~ /(DB)|(DL)|(LB)/) { 
        $k++;
        print $k.". ".$finallist{$sortedlist[$i]}->{"Position"}." ".$finallist{$sortedlist[$i]}->{"ID"}." ".$finallist{$sortedlist[$i]}->{"mvppts"}."\n";
    }
}


