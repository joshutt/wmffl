<?php
  function  authenticate()  {
    Header( "WWW-authenticate:  basic  realm='Test  Authentication  System'");
    Header( "HTTP/1.0  401  Unauthorized");
    echo  "You  must  enter  a  valid  login  ID  and  password  to  access  this  resource\n";
    exit;
  }

  if(!isset($PHP_AUTH_USER)  ||  ($SeenBefore ==  1  &&  !strcmp($OldAuth,  $PHP_AUTH_USER))  )  {
    authenticate();
  }  
  else  {
    echo  "Welcome:  $PHP_AUTH_USER<BR>";
    echo  "Old:  $OldAuth";
    echo  "<FORM  ACTION=\"$PHP_SELF\"  METHOD=POST>\n";
    echo  "<INPUT  TYPE=HIDDEN  NAME=\"SeenBefore\"  VALUE=\"1\">\n";
    echo  "<INPUT  TYPE=HIDDEN  NAME=\"OldAuth\"  VALUE=\"$PHP_AUTH_USER\">\n";
    echo  "<INPUT  TYPE=Submit  VALUE=\"Re  Authenticate\">\n";
    echo  "</FORM>\n";

}
?>
