$( document ).ready(function() {
    //
    //
    //$.tablesorter.defaults.widget = ["zebra"];
    setSorter();
/*
    $("#statTable").tablesorter({
	headers: {
	    1: { sorter: 'pos' }
	},
	cssHeader: "header",
	cssAsc: "headerSortUp",
	cssDesc: "headerSortDown",
	widgets: ["zebra"],
	//debug: true
    });
*/

    // Show the correct selected options
    let displaySelector = $('#display option:selected');
    $( '#' + displaySelector.val() ).show();
    let current = '#' + displaySelector.val();

    $('#display').change(function() {
        let changeVal = '#' + $('#display option:selected').val();
        $( changeVal ).show();
        $( current ).hide();
        current = changeVal;
        refresh();
    });

    $('#team').change(function () {
        refresh();
    });

    $('#pos').change(function () {
        refresh();
    });
});


$.tablesorter.addParser({
    id: 'pos',
    is: function(s) {
        return false;
    },
    format: function(s) {
        if (s.toUpperCase() === "HC") {
            return 0;
	} else if (s.toUpperCase() === "QB") {
	    return 1;
	} else if (s.toUpperCase() === "RB") {
	    return 2;
	} else if (s.toUpperCase() === "WR") {
	    return 3;
	} else if (s.toUpperCase() === "TE") {
	    return 4;
	} else if (s.toUpperCase() === "K") {
	    return 5;
	} else if (s.toUpperCase() === "OL") {
	    return 6;
	} else if (s.toUpperCase() === "DL") {
	    return 7;
	} else if (s.toUpperCase() === "LB") {
	    return 8;
	} else if (s.toUpperCase() === "DB") {
	    return 9;
	}
    },
    type: 'numeric'
});


function setSorter() {
    $("#statTable").tablesorter({
	headers: {
	    1: { sorter: 'pos' }
	},
	cssHeader: "header",
	cssAsc: "headerSortUp",
	cssDesc: "headerSortDown",
	widgets: ["zebra"],
	//debug: true
    });
}


function reloadTable(data) {
    $("#mainTable").html(data);
    setSorter();
}


function refresh() {
    var display = $("#display").val();
    var frmt = "ajax";
    if (display === "team") {
        var teamLoad = $("#team").val();
        $.post("weekbyweek", {team: teamLoad, format: frmt}, reloadTable);
    } else {
        var pos = $("#pos").val();
        $.post("weekbyweek", {pos: pos, format: frmt}, reloadTable);
    }  
}


function csv(frmt="csv") {
    var form = $("<form/>", { action: "weekbyweek", method: "POST" });
    form.append($("<input/>", {name: "format", value: frmt}));

    var display = $("#display").val();
    if (display === "team") {
        form.append($("<input/>", {name: "team", value: $("#team").val()}));
    } else {
        form.append($("<input/>", {name: "pos", value: $("#pos").val()}));
    }
    $(document.body).append(form);
    form.submit();
}
