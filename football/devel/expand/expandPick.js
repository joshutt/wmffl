protect = new Array();
var pullback = new Array();
alternate = new Array();

function rotate(playerRow) {
//    alert(playerRow.childNodes[0].innerHTML);
//    document.getElementById("p1").innerHTML = playerRow.childNodes[0].innerHTML;
}


function saveClick(player) {
    if (player.checked) {
        //alert(player.value +"-"+player.name);
        //alert(player)

        if (player.name == 'pro[]') {
            if (!contains(protect, player.value)) {
                if (protect.length >= 5) {
                    alert ("You May only protect 5 players");
                    player.checked = false;
                } else {
                    protect.push(player.value);

                    pbElement = document.getElementById("pb"+player.value);
                    altElement = document.getElementById("alt"+player.value);

                    if (pbElement.checked || altElement.checked) {
                        pbElement.checked = false;
                        pullback = remove(pullback, player.value);
                        altElement.checked = false;
                        alternate = remove(alternate, player.value);
                    }
                }
            }


        } else if (player.name == 'pb[]') {
            if (!contains(pullback, player.value)) {
                if (pullback.length >= 2) {
                    alert ("You May only pull-back 2 players");
                    player.checked = false;
                } else {
                    pullback.push(player.value);

                    proElement = document.getElementById("pro"+player.value);
                    altElement = document.getElementById("alt"+player.value);

                    if (proElement.checked || altElement.checked) {
                        proElement.checked = false;
                        protect = remove(protect, player.value);
                        altElement.checked = false;
                        alternate = remove(alternate, player.value);
                    }
                }
            }
        } else if (player.name == 'alt[]') {
            if (!contains(alternate, player.value)) {
                if (alternate.length >= 1) {
                    alert ("You May only choose 1 alternate");
                    player.checked = false;
                } else {
                    alternate.push(player.value);

                    pbElement = document.getElementById("pb"+player.value);
                    proElement = document.getElementById("pro"+player.value);

                    if (pbElement.checked || proElement.checked) {
                        pbElement.checked = false;
                        pullback = remove(pullback, player.value);
                        proElement.checked = false;
                        protect = remove(protect, player.value);
                    }
                }
            }
        }

    } else {
        if (player.name == "pro[]") {
            protect = remove(protect, player.value);
        } else if (player.name == "pb[]") {
            pullback = remove(pullback, player.value);
        } else if (player.name == "alt[]") {
            alternate = remove(alternate, player.value);
        }
    }

    listFill();
}

var sortF = 'pos';

function sort() {
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("tableList").innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET", "teamSort.php?sort="+sortF, true);
    xmlhttp.send();

    if (sortF == 'pos') {
        sortF = 'teamid';
        document.getElementById("posSort").innerHTML="Sort By Team";
    } else {
        sortF = 'pos';
        document.getElementById("posSort").innerHTML="Sort By Position";
    }
}


function checkform() {
    if (protect.length == 5 && pullback.length == 2 && alternate.length == 1) {
        return true;
    }
    return confirm("You haven't protected your full allocation.  Are you sure you want to submit these protections?");
}

function load() {
    sortF = 'teamid';
    sort();
}

function listFill() {
    id = -1;
    for (id in protect) {
        //alert(protect[id]);
        name = document.getElementById(protect[id]).cells[3].innerHTML;
        i = new Number(id)+1;
        key = "p"+i;
        document.getElementById(key).innerHTML = i + "."+name;
    }
    id++;
    while (id < 5) {
        i = new Number(id)+1;
        key = "p"+i;
        document.getElementById("p"+i).innerHTML = i + ".";
        id++;
    }

    id = -1;
    for (id in pullback) {
        //alert(protect[id]);
        name = document.getElementById(pullback[id]).cells[3].innerHTML;
        i = new Number(id)+1;
        key = "pb"+i;
        document.getElementById(key).innerHTML = i + "."+name;
    }
    id++;
    while (id < 2) {
        i = new Number(id)+1;
        key = "pb"+i;
        document.getElementById("pb"+i).innerHTML = i + ".";
        id++;
    }


    id = -1;
    for (id in alternate) {
        //alert(protect[id]);
        name = document.getElementById(alternate[id]).cells[3].innerHTML;
        i = new Number(id)+1;
        key = "alt";
        document.getElementById(key).innerHTML = i + "."+name;
    }
    id++;
    while (id < 1) {
        i = new Number(id)+1;
        key = "alt";
        document.getElementById("alt").innerHTML = i + ".";
        id++;
    }
}

var visState = 'visible';
var altState = 'collapse';

function hide() {
    var cssRules;
    var theClass = "tr.protect";
    var element = 'visibility';
    var value=altState;

    var added = false;
    for (var S = 0; S < document.styleSheets.length; S++){

        if (document.styleSheets[S]['rules']) {
            cssRules = 'rules';
        } else if (document.styleSheets[S]['cssRules']) {
            cssRules = 'cssRules';
        } else {
        }

        for (var R = 0; R < document.styleSheets[S][cssRules].length; R++) {
            if (document.styleSheets[S][cssRules][R].selectorText == theClass) {
                if(document.styleSheets[S][cssRules][R].style[element]){
                    document.styleSheets[S][cssRules][R].style[element] = value;
                    added=true;
                    break;
                }
            }
        }
        if(!added){
            if(document.styleSheets[S].insertRule){
                document.styleSheets[S].insertRule(theClass+' { '+element+': '+value+'; }',document.styleSheets[S][cssRules].length);
            } else if (document.styleSheets[S].addRule) {
                document.styleSheets[S].addRule(theClass,element+': '+value+';');
            }
        }
    }

    if (altState == 'collapse') {
        document.getElementById('protectShow').innerHTML = "Show Protected";
    } else {
        document.getElementById('protectShow').innerHTML = "Hide Protected";
    }

    var tmp = altState;
    altState = visState;
    visState = tmp;
}


function contains(anArray, aValue) {
    x = 0;
    while (x < anArray.length) {
        if (anArray[x] == aValue) {
            return true;
        }
        x++;
    }
    return false;
}

function remove(anArray, aValue) {
    newArray = new Array();
    for (x in anArray) {
        if (anArray[x] == aValue) {
            //alert("Match: "+anArray[x]+" - "+aValue+" = "+x);
            if (x > 0) {
                y = new Number(x)-1;
                newArray = anArray.slice(0, x);
                //alert(anArray.slice(0, x));
            }
            y =  new Number(x) + 1;
            newArray = newArray.concat(anArray.slice(y));
                //alert(anArray.slice(y));
                //alert(newArray);
            return newArray;
        }
    }
    return anArray;
}

