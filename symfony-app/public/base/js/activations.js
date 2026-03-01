/**
 * Created by Josh on 9/2/2017.
 */

function swapActivations(theForm) {
    var week = theForm.value;
    makeHttpRequest('weekSubAct.php?week='+week, 'changeTable', 0);

}

function changeTable(responseText) {
    document.getElementById("subAct").innerHTML = responseText;
}

function makeHttpRequest(url, callback_function, returnXML, post) {

    var httpRequest = false;

    if (window.XMLHttpRequest) {
        httpRequest = new XMLHttpRequest();
        if (httpRequest.overrideMimeType) {
            httpRequest.overrideMimeType('text/xml');
        }
    } else if (window.ActiveXObject) {
        try {
            httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }

    if (!httpRequest) {
        alert ('Unfortunately your browser doesn\'t support this feature.');
        return false;
    }
    if (callback_function) {
        httpRequest.onreadystatechange = function() {
            if (httpRequest.readyState == 4) {
                if (httpRequest.status == 200) {
                    if (returnXML) {
                        eval(callback_function + '(httpRequest.responseXML)');
                    } else {
                        eval(callback_function + '(httpRequest.responseText)');
                    }
                }
            }
        }
    }

    if (post) {
        httpRequest.open('POST', url, true);
        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpRequest.send(post);

    } else {
        httpRequest.open('GET', url, true);
        httpRequest.send(null);
    }
}
