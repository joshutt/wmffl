<?php

include_once 'config.inc';
require_once 'HTML/Form.php';

main();


function displayExpenseCharge() {
    
    $form = new HTML_Form('');
    
    $form->addText("desc" , "Description");
    $form->addText("amt" , "Amount");
    $form->addHidden("sent", "1");
    $form->addSubmit("submit", "Submit");
    
    $form->display();
    
}




function main() {
    displayExpenseCharge(); 
}