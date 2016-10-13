<script language="javascript">
var xmlHttp

function changeRoster(str, checkDate)
{ 
    xmlHttp=GetXmlHttpObject()
    if (xmlHttp==null)
     {
              alert ("Browser does not support HTTP Request")
               return
                } 
                var url="roster.php"
                url=url+"?teamid="+str
                url=url+"&checkDate="+checkDate
                xmlHttp.onreadystatechange=stateChanged 
                xmlHttp.open("GET",url,true)
                xmlHttp.send(null)
}

function stateChanged() 
{ 
     if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
          { 
               document.getElementById("inner").innerHTML=xmlHttp.responseText 
                } 
}

function GetXmlHttpObject()
{
    var xmlHttp=null;

    try
     {
          // Firefox, Opera 8.0+, Safari
           xmlHttp=new XMLHttpRequest();
            }
            catch (e)
             {
                  // Internet Explorer
                   try
                     {
                           xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
                             }
                              catch (e)
                                {
                                      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
                                        }
                                         }
                                         return xmlHttp;
}

</script>


<form>
    <select name="teamid" onChange="changeRoster(this.value, date.value)">
        <option value="1">Team 1</option>
        <option value="2">Team 2</option>
        <option value="3">Team 3</option>
        <option value="4">Team 4</option>
        <option value="5">Team 5</option>
        <option value="6">Team 6</option>
        <option value="7">Team 7</option>
        <option value="8">Team 8</option>
        <option value="9">Team 9</option>
        <option value="10">Team 10</option>
    </select>

    <input type="text" name="date"/>
    
</form>


<table>
    <tbody id="inner"/>
</table>
