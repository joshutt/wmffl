$(document).ready(function () {
    setSorter();

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

function csv(frmt="csv") {
    var form = $("<form/>", {action: "leaders", method: "POST"});
    form.append($("<input/>", {name: "format", value: frmt}));
    $(document.body).append(form);
    form.submit();
}
