<?
/***********************************************************************
 *  This script is fully commented to allow for easy analysis of what is
 *  occuring.  No I don't normally code this way, it is quite tedious to
 *  comment every damn line, but it is the best way to make sure that
 *  the code is fully understandable by non-programmers.
 *
 *  I use the randomizer provided by http://www.random.org, rather than
 *  the built in randomizers for a couple of reasons.  First off the
 *  whole point was to use a randomizer that was outside verifiable,
 *  that could not be manipulated to fit my own devious purposes.  The
 *  main reason however was that someone had previously expressed concern
 *  of the pseduo-randomness of the built in picker.  While given the
 *  small range of numbers we are working from here (21) and the fact that
 *  there is no compelling need to ensure that the generated numbers are
 *  safe enough for cryptography the built-in randomizer would probably
 *  be sufficent.  However, given millions of iterations it is possible
 *  that there could be a small biasis toward, or against, one set of numbers.
 *  Thus I chose to use the truly random number generator at random.org.
 *  It uses radio frequency noise to generate it's bits and is complient
 *  with RFC1750 (Randomness Recommendations for Security).  For more indepth
 *  information visit to http://www.random.org.
 *
 *  Copyright 2003  Josh Utterback (josh at wmffl NOSPAM dot com)
 *  Version 1.0
 *  Created 8/1/2003
 *  Last Modified: 8/1/2003
 *  Licence: GPL
 ***********************************************************************/

//////////////////////////////////////////////////////////////////////
// This method accepts a low and high integer and returns an array
// of all the integers in that range (inclusive) in random order.  The
// default minimum is 1 and the maximum is 21.
function getRandomSeq($min=1, $max=21) {
    unset($returnValue);   // Makes sure that $returnValue is null

    // If min is larger than max, then swap them (min should always be lower)
    if ($min > $max) {
        $temp = $min;       // Put min in a temporary variable
        $min = $max;        // assign value of max to min
        $max = $temp;       // assign value of temporary varaible to max
    }

    // Get the results of calling the random sequencer.  This is where
    // the randomization actually occurs.  You can find out details of
    // how the randomization works at http://www.random.org.
    $returnValue = file_get_contents("http://www.random.org/cgi-bin/randseq?min=$min&max=$max");

    // The previous call comes back as one long string, this breaks each line
    // of the string up and puts the results into an array.
    $returnArray = split("\n", $returnValue);
    return $returnArray;    // Returns the array
}
?>



<?
$outputList = getRandomSeq(1, 21);  // Generate a random sequence of numbers

print "****<BR>";                   // Print some pretty stars
foreach ($outputList as $num) {     // Loop through each item in the arrray in order
    print "$num<BR>";               // Print the number at this location in the array
}
print "****";                       // Print some more pretty starts

// Print link to source code
print "<P><A HREF=\"randseq-src.php\">View Source</A></P>";
?>

