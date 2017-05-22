function filter() {
    genIn({
        element:'uhbContent',
        address:'|site|page/|currentPage|/GetList',
        type:'POST',
        data:$('#curFilterFields select, #curFilterFields input[type=text]').serialize(),
        loadicon:'<tr><td colspan="7" style="text-align: center; color: gray;">Загрузка...</td></tr>'
    })
}

$(document).ready(function () {
    filter();
});