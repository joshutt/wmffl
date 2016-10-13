<?
    echo  "Welcome:  $REMOTE_USER<BR>";
    echo  "Old:  $OldAuth";
    echo  "<FORM  ACTION=\"authenticate.php\"  METHOD=POST>\n";
    echo  "<INPUT  TYPE=HIDDEN  NAME=\"SeenBefore\"  VALUE=\"1\">\n";
    echo  "<INPUT  TYPE=HIDDEN  NAME=\"OldAuth\"  VALUE=\"$REMOTE_USER\">\n";
    echo  "<INPUT  TYPE=Submit  VALUE=\"Re  Authenticate\">\n";
    echo  "</FORM>\n";
?>