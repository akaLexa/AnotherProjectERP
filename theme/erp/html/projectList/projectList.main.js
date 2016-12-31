
$(document).ready(function () {
    projectFilter();

    $('#filterForProjects').each(function(){
        $(this).keydown(
            function (e){
                if(e.keyCode == 13)
                {
                    projectPage=1;
                    projectFilter();
                    e.preventDefault();
                }
            }
        );
    });
});

var curPage = 1;

function projectFilter() {
    genIn({
        element:'projectListContent',
        address:'|site|page/|currentPage|/GetProjects',
        type:'POST',
        data:$('#filterForProjects input[type=text], #filterForProjects input[type=date], #filterForProjects select').serialize()+'&curPage='+curPage,
        loadicon:'<tr><td colspan="8" style="text-align: center; color: gray;">Загружаю..</td></tr>'
    });
}
function paginate(id) {
    curPage = id;
    projectFilter();
}