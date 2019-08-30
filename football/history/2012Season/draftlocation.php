<?
require_once "utils/start.php";

$title = "2012 WMFFL Draft Information";
?>

<head>
    <style>
.subj {font-weight: bold; float:left; clear:left;}
.answ {float: left; clear:right;}
.areaBox {float: left; padding:10px; page-break-after:always;}
.leftBox {float: left; padding:10px;}
    </style>
</head>

<? include "base/menu.php"; ?>

<H1 Align=Center>Draft Information</H1>
<H5 ALIGN=Center><I>August 19, 2012</I></H5>
<HR size = "1">

<?
if ($isin) {
?>

<span class="leftBox">
<iframe width="640" height="480" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=906+holborn+ct,+sterling,+va+20164&amp;aq=0&amp;oq=906+holborn&amp;sll=38.003385,-79.420925&amp;sspn=5.989424,9.239502&amp;ie=UTF8&amp;hq=&amp;hnear=906+Holborn+Ct,+Sterling,+Virginia+20164&amp;t=m&amp;ll=39.004578,-77.419538&amp;spn=0.023345,0.036478&amp;z=14&amp;iwloc=A&amp;output=embed"></iframe><br /><small><a href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=906+holborn+ct,+sterling,+va+20164&amp;aq=0&amp;oq=906+holborn&amp;sll=38.003385,-79.420925&amp;sspn=5.989424,9.239502&amp;ie=UTF8&amp;hq=&amp;hnear=906+Holborn+Ct,+Sterling,+Virginia+20164&amp;t=m&amp;ll=39.004578,-77.419538&amp;spn=0.023345,0.036478&amp;z=14&amp;iwloc=A" style="color:#0000FF;text-align:left">View Larger Map</a></small>
</span>


<span class="areaBox">
<table border="0" width="100%">
<tr><td><span class="subj">Date:</span></td>
<td><span class="answ">August 26th, 2012</span></td></tr>

<tr><td><span class="subj">Time:</span></td>
<td><span class="answ">1:00 pm EDT</span></td></tr>

<tr><td><span class="subj">Location:</span></td>
<td><span class="answ">906 Holborn Ct.<br/>Sterling, VA</span></td></tr>
</table>
</span>

<?
} else {
?>
    Please log in to get get address information.
<?
}

include "base/footer.html"; 
?>
