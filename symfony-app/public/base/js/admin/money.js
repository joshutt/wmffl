$(document).ready(function() {
    $('.editable-span').click(function() {
        var spanContent = $(this).text();
        var id = $(this)[0].id;
        $(this).replaceWith('<input type="text" id="' + id + '" value="' + spanContent + '" onchange="sendChange(event);" size="4" />');

        $('input').blur(function() {
            var newContent = $(this).val();
            $(this).replaceWith('<span id="'+id+'" class="editable-span">' + newContent + '</span>');
        });
    });
});


function sub() {
    console.log("In sub");


    pds = $('[id^="paid-"]');
    pds.each(function() {
        if ($(this).prop("checked") === $(this).prop("defaultChecked")) {
            console.log("No Change");
        } else {
            console.log("Change");
        }
    })
}


function toggleChange(event) {
    const inputElement = event.target;
    const newValue = inputElement.checked;
    console.log("Send "+inputElement.id+ " a new value of "+newValue);
    $.post("recordChange", {
        field: inputElement.id,
        val: newValue
    }, function(response) {
        console.log(response);
    }, "json");
}

function sendChange(event) {
    const inputElement = event.target;
    const newValue = inputElement.value;
    console.log(inputElement);
    console.log("Send "+inputElement.id+ " a new value of "+newValue);
    $.post("recordChange", {
        field: inputElement.id,
        val: newValue
    }, function(response) {
        console.log(response);
    }, "json");
}