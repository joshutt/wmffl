<?php

include_once 'config.inc';
require_once 'HTML/Form.php';

main();


function displayCharges() {
    
    $teams = getTeamList();
    $teams["0"] = "Entire League";
    
    $form = new HTML_Form('', "post");
    
    $form->addSelect("team", "Team", $teams);
    $form->addText("desc" , "Description");
    $form->addText("amount", "Amount");
    $form->addRadio("credit", "Credit", "yes", true);
    $form->addRadio("credit", "Debit", "no", false);
    $form->addHidden("sent", "1");
    $form->addSubmit("submit", "Submit");
    
    $form->display();
    
}

function getTeamList() {
    $query = "select teamid, name from team where active=1 order by name";
    $result = mysql_query($query) or die("Unable to get team names: ".mysql_error());
    
    $teams = array();
    while(list($teamid, $team) = mysql_fetch_array($result)) {
        $teams[$teamid] = $team;
    }
    return $teams;
}


function processCharge() {
    
    $errors = array();
    
    $teamId = $_POST["team"];
    $desc = trim($_POST["desc"]);
    $amt = $_POST["amount"];
    $credit = $_POST["credit"];
    
    // Make sure amount entered is a number
    if ((float) $amt) {
        // No Op
    } else {
        $errors[] = "Amount should be a number";
    }
    
    // Make sure a description was entered
    if (strlen($desc) == 0) {
        $errors[] = "Please enter a description";
    }
    
    if (sizeof($errors)) {
        foreach ($errors as $error) {
            print "<p><font color='red'>$error</font></p>";
        }
        displayCharges();
        return;
    }
    
    
    // Do the insert query
    $query = "INSERT INTO money (teamid, description, amount) VALUES ";
    $desc = mysql_real_escape_string($desc);
    
    if ($credit == "no") {
        $amt = -$amt;
    }
    
    if ($teamId == "0") {
        $teamList = getTeamList();
        
        $first = true;
        foreach ($teamList as $teamId => $name) {
	        if (!$first) {
	            $query .= ", "; 
	        } else {
	            $first = false;
	        }
	        $query .= "($teamId, '$desc', $amt) ";
        }
    } else {
        $query .= "($teamId, '$desc', $amt) ";
    }
    mysql_query($query) or die("Unable to insert charge into database ".mysql_error());
    
    print "Charge successfully entered.  <a href=\"\"/>Another?</a>";
}


function main() {
    //var_dump($_POST);
    
    if (isset($_POST) && $_POST["sent"] == "1") {
        // process charge here    
        processCharge();
    } else {
	    displayCharges(); 
    }
}