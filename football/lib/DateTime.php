<?
  /* lib_DateTime.php
   *
   * A small library used to manipulate date and time structure, without
   * the 1/1/1970 to 2038 limit.
   * A date and time is stored into a float value (called the DateTime structure).
   * The decimal part store a representation of a day (0.0 is 0h0m0s, 0.5 is 12h00, 0.999 is 23h59m59s.
   * The round part store the number of day till 31/12/1899.
   *  To get the date only of a DateTime variable : intval($dt).
   *  To get the hour only of a DateTime variable : frac($dt);
   * So it is easy to compare dates, to add date or time value.
   * The DateTime structure and those associated functions are
   * similar to the TDateTime object of Borland Delphi.
   *
   * Check for updates here :
   *   http://membres.lycos.fr/wincocktail/other
   */

  define ('DT_NB_MS_DAY', 24 * 60 * 60 * 1000);   // Do not change here
  define ('DATE_DELTA', 693594);                  // Days between 1/1/0001 and 12/31/1899
  define ('DT_FMT_US', 'Y/m/d h:i:s\'x"');        // Format of US date and time
  define ('DT_FMT_FR', 'd/m/Y h:i:s\'x"');        // Format of french date and time
  define ('DT_DEF_FMT', DT_FMT_US);               // ** Change here if you want
  define ('DT_DEF_FMT_DATE_US', 'Y/m/d');         // Format of US date
  define ('DT_DEF_FMT_DATE_FR', 'd/m/Y');         // Format of french date
  define ('DT_DEF_FMT_DATE', DT_DEF_FMT_DATE_US); // ** Change here if you want
  define ('DT_DEF_FMT_HOUR', 'h:i:s\'x"');        // Format of time

  function Frac($n)
  // Return the decimal value of a number/ 12.3456 -> 0.3456
  {
    return ($n - intval($n));
  }

  function EncodeTime($h = 0,$m = 0,$s = 0,$ms = 0)
  // Convert hour,minute,second,millisecond to DateTime
  {
    $nbms = $ms +
            ($s * 1000) +
            ($m * 60 * 1000) +
            ($h * 60 * 60 * 1000);
    return ($nbms / DT_NB_MS_DAY);
  }

  function DecodeTime($dt, &$h, &$m, &$s, &$ms)
  // Convert DateTime to hour, minure, second, millisecond
  // If $dt is > 1, then the number of hour is > 24
  {
    $a = DecodeTimeToArray($dt);
    $h = $a['hour'];
    $m = $a['min'];
    $s = $a['sec'];
    $ms = $a['msec'];
    return "$h:$m:$s'$ms\"";
  }

  function DecodeTimeToArray($dt)
  // Convert DateTime to hour, minure, second, millisecond, into an associative array
  // array keys are 'hour', 'min', 'sec' and 'msec'
  // If $dt is > 1, then the number of hour is > 24
  {
    $nbms = round($dt * DT_NB_MS_DAY); // 'round()' is very important. If not, we have precision error in milliseconds

    $h = intval($nbms / (60*60*1000));
    $nbms = $nbms - ($h * 60 * 60 * 1000);

    $m = intval($nbms / (60 * 1000));
    $nbms = $nbms - ($m * 60 * 1000);

    $s = intval($nbms / 1000);
    $ms = ($nbms - ($s * 1000));
    return (array('hour'=>$h, 'min'=>$m, 'sec'=>$s, 'msec'=>$ms));
  }

  function IsLeapYear($year)
  // Return true if $year is a leap year
  {
    return (($year % 4 == 0) && (($year % 100 != 0) || ($year % 400 == 0)));
  }

  // This array contains the number of days by months by type of year (noleap = 0, leap=1)
  $MonthDays = array( 0=>array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31),
                      1=>array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31));

  function EncodeDate_old($year,$month,$day)
  // Convert year, month, day to DateTime
  // 0 values are not allowed but are not traped
  {
    global $MonthDays;
    $daytable = $MonthDays[IsLeapYear($year) * 1];
    $i = $year-1;
    // get all days plus all leap years, minus non-leap years
    $days = $i * 365 + intval($i / 4) - intval($i / 100) + intval($i / 400);
    // the years before 1582 were all leap if divisible by 4
    if ($year > 1582)
      $days += 12;
    else
    {
      $days += intval($year / 100);
      $days -= intval($year / 400);
    }
    // get the days for the month up to the current one
    for($i = 0;$i < $month-1;$i++)
    {
      $days += $daytable[$i];
     // days += DaysInMonth((TMonth)i, DateEntry->fYear);
    }
    // now add the current days of the month
    $days += $day;
    // now adjust for the 10 missing days (Oct 4 - Oct 15, 1582)
    if ($days > 577737)
    {
      $days -= 10;
    }
    return $days;
  }

  function EncodeDate($year, $month, $day)
  {
    global $MonthDays;
    $daytable = $MonthDays[IsLeapYear($year) * 1];
    if (($year >= 1) && ($year <= 9999) && ($month >= 1) && ($month <= 12) &&
        ($day >= 1) && ($day <= $daytable[$month - 1]))
    {
      for ($i = 1; $i <= $month - 1; $i++)
      {
        $day += $daytable[$i - 1];
      }
      $i = $year - 1;
      return $i * 365 + intval($i / 4) - intval($i / 100) + intval($i / 400) + $day - DATE_DELTA;
    }
  }

  function DateTimeToTimeStamp($dt)
  {
  //result.Time := Trunc(Frac(DateTime) * MSecsPerDay);
  //result.Date := 1 + DateDelta + Trunc(System.Int(DateTime));
  /*
    $t = round(frac($dt) * DT_NB_MS_DAY);
    $d = 1 + DATE_DELTA + intval($dt);
  */
    return $dt + DATE_DELTA;
  }

  function DecodeDate($daynumber,&$y,&$m,&$d)
  // Convert a DateTime value to year, month and day
  // DateTime value ($daynumber) is the number of elapsed day
  // till 1/1/0
  {
    $a = DecodeDateToArray($daynumber);
    $y = $a['year'];
    $m = $a['month'];
    $d = $a['day'];
  }

  function DecodeDateToArray_old($daynumber)
  // Convert a DateTime value to an associative array.
  // Array keys are 'year', 'month' and 'day'
  // DateTime value ($daynumber) is the number of elapsed day
  // till 1/1/0
  {
    global $MonthDays;

    if ($daynumber > 577737)
    {
      $daynumber += 10;
    }
    $y = intval($daynumber / 365);
    $d = $daynumber % 365;
    if ($y < 1700)
    {
      $d -= intval($y / 4);
    }
    else
    {
      $d -= intval($y / 4);
      $d += intval($y / 100);
      $d -= intval($y / 400);
      $d -= 12;
    }
    while ($d <= 0)
    {
      $d += 365 + IsLeapYear($y);
      $y--;
    }

    // y is the number of elapsed years, add 1 to get current
    $y++;
    // figure out the month and current day too
    $daytable = $MonthDays[IsLeapYear($y) * 1];
    for ($m = 1; $m <= 12 ; $m++)
    {
      $days = $daytable[$m -1];
      if ($d <= $days)
      {
        break;
      }
      else
      {
        $d -= $days;
      }
    }
    return(array('year'=>$y, 'month'=>$m, 'day'=>$d));
  }

  function DivMod($dividend, $divisor, &$result, &$remainder)
  {
    $result = intval($dividend / $divisor);
    $remainder = $dividend % $divisor;
  }

  function DecodeDateToArray($dt)
  {

    global $MonthDays;
    $D1 = 365;
    $D4 = $D1 * 4 + 1;
    $D100 = $D4 * 25 - 1;
    $D400 = $D100 * 4 + 1;
    $t = intval(DateTimeToTimeStamp($dt));
    if ($t<=0)
    {
      $year = 0;
      $month = 0;
      $day = 0;
    }
    else
    {
      $t--;
      $y = 1;
      while ($t >= $D400)
      {
        $t -= $D400;
        $y += 400;
      }
      DivMod($t, $D100, $i, $d);
      if ($i == 4)
      {
        $i--;
        $d += $D100;
      }
      $y += ($i * 100);
      DivMod($d, $D4, $i, $d);
      $y += ($i * 4);
      DivMod($d, $D1, $i, $d);
      if ($i == 4)
      {
        $i--;
        $d += $D1;
      }
      $y += $i;
      $daytable = $MonthDays[IsLeapYear($y) * 1];
      $m = 1;
      while (true)
      {
        $i = $daytable[$m - 1];
        if ($d < $i)
          break;
        $d -= $i;
        $m++;
      }
      $d++;
    } // Else
    return array('year'=>$y, 'month'=>$m, 'day'=>$d);
  }

  function EncodeDateTime($ye, $mo, $da, $ho=0, $mi=0, $se=0, $ms=0)
  // Convert year, month, day, hour, minute, second, millisecond into a DateTime value
  {
    return (EncodeDate($ye, $mo, $da) + EncodeTime($ho, $mi, $se, $ms));
  }

  function DecodeDateTime($dt, &$ye, &$mo, &$da, &$ho, &$mi, &$se, &$ms)
  // Convert a DateTime value to year, month, day, hour, minure, seconde, millisecond
  {
    $nbdays = intval($dt);
    $nbhours = Frac($dt);

    DecodeDate($nbdays, $ye, $mo, $da);
    DecodeTime($nbhours, $ho, $mi, $se, $ms);
  }

  function DecodeDateTimeToArray($dt)
  // Convert a DateTime value to an associative array.
  // Array keys are 'year', 'month', 'day', 'hour', 'sec', 'msec'
  {
    DecodeDateTime($dt, $ye, $mo, $da, $ho, $mi, $se, $ms);
    return array('year'=>$ye,
                 'month'=>$mo,
                 'day'=>$da,
                 'hour'=>$ho,
                 'min'=>$mi,
                 'sec'=>$se,
                 'msec'=>$ms);
  }

  function IncMonth($date, $nbmonth)
  // Increase a DateTime value ($date) to a number of months ($nbmonth)
  // Note : to add a number of days, just do $date++ (where $date is the DateTime value)
  {
    global $MonthDays;
    if ($nbmonth >= 0) $sign = 1; else $sign = -1;
    DecodeDate($date, $year, $month, $day);
    $year += intval($nbmonth / 12);
    $month += $nbmonth;
    if ($month-1 > 11)
    {
      $year += $sign;
      $month += -12 * $sign;
    }
    $daytable = $MonthDays[IsLeapYear($year) * 1];
    if ($day > $daytable[$month-1]) $day = $daytable[$month-1];
    return EncodeDate($year, $month, $day) + Frac($date);
  }

  function DateTimeToUnix($dt)
  // Convert a DateTime value ($dt) to a Unix epoch timestamp
  // Warning, ther is no check of invalid DateTime date (<1970 or >2038)
  {
    $ar = DecodeDateTimeToArray($dt);
    return mktime($ar['hour'], $ar['min'], $ar['sec'], $ar['month'], $ar['day'], $ar['year']);
  }

  Function UnixToDateTime($ts)
  // Convert a unix epoch timestamp to a DateTime value
  {
    $ar = getdate($ts);
    return EncodeDateTime($ar['year'], $ar['mon'], $ar['mday'], $ar['hours'], $ar['minutes'], $ar['seconds']);
  }

  Function NowToDateTime()
  // Return the current date and time in a DateTime value
  {
    list($usec, $sec) = explode(' ',microtime());
    $usec = ($usec*1000);
    $dt = UnixToDateTime($sec);
    DecodeDateTime($dt, &$ye, &$mo, &$da, &$ho, &$mi, &$se, &$ms);
    return EncodeDateTime($ye, $mo, $da, $ho, $mi, $se, $usec);
  }

  /* Format :
     d : 01..31 day
     m : 01..12 month
     Y : 0000..1999 year 4digit
     y : 00..99 year 2digit
     h : 01..24 hours
     i : 00..59 minutes
     s : 00..59 seconds
     x : 000.999 milliseconds
   */
  Function DateTimeToStr($dt, $format=DT_DEF_FMT)
  // Return a formated DateTime value
  {
    $ar = DecodeDateTimeToArray($dt);
    $f = $format;
    $f = str_replace('d', sprintf('%02d', $ar['day']), $f);
    $f = str_replace('m', sprintf('%02d', $ar['month']), $f);
    $f = str_replace('Y', sprintf('%04d', $ar['year']), $f);
    $f = str_replace('y', sprintf('%02d', ($ar['year'] / 100)), $f);
    $f = str_replace('h', sprintf('%02d', $ar['hour']), $f);
    $f = str_replace('i', sprintf('%02d', $ar['min']), $f);
    $f = str_replace('s', sprintf('%02d', $ar['sec']), $f);
    $f = str_replace('x', sprintf('%03d', $ar['msec']), $f);
    return $f;
  }

  Function TimeToStr($dt, $format=DT_DEF_FMT_HOUR)
  // Return a formated time value
  {
    $ar = DecodeTimeToArray($dt);
    $f = $format;
    $f = str_replace('h', sprintf('%02d', $ar['hour']), $f);
    $f = str_replace('i', sprintf('%02d', $ar['min']), $f);
    $f = str_replace('s', sprintf('%02d', $ar['sec']), $f);
    $f = str_replace('x', sprintf('%03d', $ar['msec']), $f);
    return $f;
  }

  Function DateToStr($dt, $format=DT_DEF_FMT_DATE)
  // Return a formated date value
  {
    $ar = DecodeDateTimeToArray($dt);
    $f = $format;
    $f = str_replace('d', sprintf('%02d', $ar['day']), $f);
    $f = str_replace('m', sprintf('%02d', $ar['month']), $f);
    $f = str_replace('Y', sprintf('%04d', $ar['year']), $f);
    $f = str_replace('y', sprintf('%02d', ($ar['year'] / 100)), $f);
    return $f;
  }

  Function DateTimeToDbDateTime($dt)
  // Convert a DateTime value to a Mysql DATETIME value
  // Mysql datetime format is YYYY-MM-DD HH:MM:SS
  {
    $ar = DecodeDateTimeToArray($dt);
    //return $ar['year'].'-'.$ar['month'].'-'.$ar['day'].' '.$ar['hour'].':'.$ar['min'].':'.$ar['sec'];
    return DateTimeToStr($dt, 'Y-m-d h:i:s');
  }

  Function DbDateTimeToDateTime($str)
  // Convert a MySql DATETIME value to a DateTime value,
  // or false if the $str string is not valid
  {
    $r = preg_match('/^(.*)-(.*)-(.*) (.*):(.*):(.*)/', $str, $ar);
    if ($r >= 1)
    {
      return EncodeDateTime($ar[1], $ar[2], $ar[3], $ar[4], $ar[5], $ar[6], 00 /* msec is not set */);
    }
    else
      return false;
  }

  Function DateTimeToDurationToArray($dt)
  // Return a DateTime as a duration
  {
    $dt = abs($dt);
    $tim_arr = DecodeTimeToArray(frac($dt));
    $nbdays = intval($dt);
    $y = intval($nbdays / 365);
    $rest = $nbdays % 365;
    $m = intval($rest / 30);
    $d = $rest % 30;
    return array('year'=>$y, 'month'=>$m, 'day'=>$d) + $tim_arr;
    /*
    $ar = DecodeDateTimeToArray($dt);
    if (intval($dt) == 0)
    {
      $ar['year'] = 0;
      $ar['month'] = 0;
      $ar['day'] = 0;
    }
    else
    {
      $ar['year'] = $ar['year'] - 1;
      $ar['month'] = $ar['month'] - 1;
    }
    return $ar;
    */
  }

  Function DateTimeToDurationStr($dt, $format = DT_DEF_FMT )
  // Return DateTime as a duration, according to $format specifier
  {
    $ar = DateTimeToDurationToArray($dt);
    $f = $format;
    $f = str_replace('d', sprintf('%02d', $ar['day']), $f);
    $f = str_replace('m', sprintf('%02d', $ar['month']), $f);
    $f = str_replace('Y', sprintf('%04d', $ar['year']), $f);
    $f = str_replace('y', sprintf('%02d', ($ar['year'] / 100)), $f);
    $f = str_replace('h', sprintf('%02d', $ar['hour']), $f);
    $f = str_replace('i', sprintf('%02d', $ar['min']), $f);
    $f = str_replace('s', sprintf('%02d', $ar['sec']), $f);
    $f = str_replace('x', sprintf('%03d', $ar['msec']), $f);
    return $f;
  }
  
  function DayOfWeek($dt)
  // Return the number of the week day, from 1(Mon) to 7(sun)
  // !! Oups, is there a bug here ?
  {
    return (intval(DateTimeToTimeStamp($dt)) % 7) + 1;
  }
  
?>

 