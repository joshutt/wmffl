<?

function readInt($fp) {
    $result = fread($fp, 4);


    $total = 0;
    foreach (array(0,1,2,3) as $x) {
        $total +=  ord($result[$x]) * pow(256, $x);
    }
    return $total;
}

function readBoolean($fp) {
    $result = ord(fread($fp, 1));
    return $result;
}

function readString($fp) {
    $length = readInt($fp);
    $result = fread($fp, $length);
    return $result;
}


function readFloat($fp) {
    $readNum = fread($fp, 8);
    return bin2float($readNum);
}


function bin2float ($bin) {
    $float = (float) 0;

    // Read Exponent and Sign (+/-)
    $exponent = ord ($bin{7});
    if ($sign = $exponent & 128) $exponent -= 127;
    $exponent <<= 4;

    // Read the remaining bit for Exponent and loop through Mantissa, calculating the Fraction
    $fraction = (float) 1;
    $div = 1;
    for ($x=6; $x>=0; $x--) {
        $byte = ord ($bin{$x});
        for ($y=7; $y>=0; $y--) {
            if ($x==6 && $y>=4) {
                if ($byte & (1 << $y)) $exponent += (1 << ($y-4));
            } else {
                $div *= 0.5;
                if ($byte & (1 << $y)) $fraction += $div;
            }
        }
    }

    // 0 value check
    if (!$exponent && $fraction == 1) return 0;

    // Final calc, returning the converted float
    $exponent -= 1023;

    $float = pow (2, $exponent) * $fraction;
    if ($sign) $float = -($float);

    return $float;
}


include "dates.php";


#$fp = fopen('data/tran2005.nfl', 'rb') or die("ERROR: ");
$fp = fopen('data/play2005.nfl', 'rb') or die("ERROR: ");
#$fp = fopen('data/sch2005.nfl', 'rb') or die("ERROR: ");
#$fp = fopen('data/inj2005.nfl', 'rb') or die("ERROR: ");
fseek($fp, 40, SEEK_SET) ;

while (!feof($fp)) {

$field = readInt($fp);
$type = ord(fread($fp, 1));
switch($type) {
    case 1: $result = readBoolean($fp); break;
    case 2: $result = readInt($fp); break;
    case 3: $result = readString($fp); break;
    case 4: $result = readFloat($fp); break;
}

if ($type == 4) {
    print "$field - $type - $result - ".getffDate($result)."\n";
} else {
    print "$field - $type - $result\n";
}
}


fclose($fp);
?>
