function changenews() {
    var uid = document.getElementById("news").value;
    location.href = 'index.php?uid=' + uid;
}


function changeyear() {
    var uid = document.getElementById("artSeason").value;
    location.href = 'index.php?artSeason=' + uid;
}
