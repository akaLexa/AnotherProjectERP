
function logFilter() {
    genIn({
        element:'logContent',
        address:'|site|page/Logs/Center',
        type:'POST',
        data: $('#filterTr input[type=date],#filterTr select').serialize(),
        loadicon: '<tr><td colspan="5" style="text-align: center;">Загрузка...</td></tr>'
    })
}

$(document).ready(function () {
    logFilter();
});