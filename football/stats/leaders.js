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

function setSorter() {
    $("#statTable").tablesorter({
        sortList: [[13, 1]],
        cssHeader: "header",
        cssAsc: "headerSortUp",
        cssDesc: "headerSortDown",
        widgets: ["zebra"],
        //debug: true
    });
}
