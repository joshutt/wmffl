// Injured Reserve add/remove submission for /transactions/ir.
// Posts each toggled player to the JSON endpoint, then reloads.
function sub() {
    const page = document.getElementById("irPage");
    const url = page.dataset.updateUrl;
    const token = page.dataset.token;

    document.getElementsByName("irAdd").forEach(function (item) {
        if (item.checked) {
            $.post(url, {method: "Add", playerid: item.value, _token: token});
        }
    });

    document.getElementsByName("irRemove").forEach(function (item) {
        if (!item.checked) {
            $.post(url, {method: "Remove", playerid: item.value, _token: token});
        }
    });
    setTimeout(function () { location.reload(); }, 200);
}
