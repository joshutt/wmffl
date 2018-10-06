$(document).ready(function () {
    setSorter();

    // Show the correct selected options
    $('#' + $('#display option:selected').val()).show();
    var current = '#' + $('#display option:selected').val();

    $('#display').change(function () {
        $('#' + $('#display option:selected').val()).show();
        $(current).hide();
        current = '#' + $('#display option:selected').val();
    });
});


$.tablesorter.addParser({
    id: 'pos',
    is: function (s) {
        return false;
    },
    format: function (s) {
        if (s.toUpperCase() == "HC") {
            return 0;
        } else if (s.toUpperCase() == "QB") {
            return 1;
        } else if (s.toUpperCase() == "RB") {
            return 2;
        } else if (s.toUpperCase() == "WR") {
            return 3;
        } else if (s.toUpperCase() == "TE") {
            return 4;
        } else if (s.toUpperCase() == "K") {
            return 5;
        } else if (s.toUpperCase() == "OL") {
            return 6;
        } else if (s.toUpperCase() == "DL") {
            return 7;
        } else if (s.toUpperCase() == "LB") {
            return 8;
        } else if (s.toUpperCase() == "DB") {
            return 9;
        }
    },
    type: 'numeric'
});


function setSorter() {
    $("#statTable").tablesorter({
        headers: {
            0: {sorter: 'pos'}
        },
        cssHeader: "header",
        cssAsc: "headerSortUp",
        cssDesc: "headerSortDown",
        widgets: ["zebra"],
        //debug: true
    });
}