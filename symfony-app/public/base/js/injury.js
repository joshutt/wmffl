$(document).ready(function () {
    $(".tablesorter").tablesorter();
});

function sub() {
    let irAdds = document.getElementsByName("irAdd");
    irAdds.forEach(function(item) {
        if (item.checked) {
            $.post("updateIR.php", {
                method: "Add",
                playerid: item.value
            });
        }
    });

    let irRemove = document.getElementsByName("irRemove");
    irRemove.forEach(function (item) {
        if (!item.checked) {
            $.post("updateIR.php", {
                method: "Remove",
                playerid: item.value
            });
        }
    });
    setTimeout(rl, 200);
}

function rl() {
    location.reload();
}

function toggleIRCurrent() {
    $('#fullList').hide();
    $('#currentLists').show();
}

function toggleIRFull() {
    $('#fullList').show();
    $('#currentLists').hide();
}
