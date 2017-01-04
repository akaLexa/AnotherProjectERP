
var curPage;

function filterTask() {
    genIn({
        element:'TaskBody',
        address:'|site|page/|currentPage|/GetList',
        loadicon:'<tr><td colspan="8" style="text-align: center; color:green">Загружаюсь...</td></tr>',
        type:'POST',
        data:$('#taskF1 select,#taskF1 input[type=date],#taskF1 input[type=text],#taskF2 input[type=text],#taskF2 input[type=checkbox],#taskF2 select').serialize()+'&curPage='+curPage
    })
}

function paginate(id) {
    curPage = id;
    filterTask();
}

function taskRedir(id) {
    document.querySelector('#tGoForm').action='|site|page/|currentPage|/In.html?id='+id;
    document.querySelector('#tGoForm').submit();
}

$(document).ready(function () {
    curPage = 1;
    filterTask();
});