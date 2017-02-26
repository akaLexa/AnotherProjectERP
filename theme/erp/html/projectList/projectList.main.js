var stages='';
var curPage = 1;

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



function projectFilter(cur_Page) {
    if(cur_Page != undefined)
        curPage = cur_Page;
    else
        curPage = 1;

    genIn({
        element:'projectListContent',
        address:'|site|page/|currentPage|/GetProjects',
        type:'POST',
        data:$('#filterForProjects input[type=text], #filterForProjects input[type=date], #filterForProjects select').serialize()+'&curPage='+curPage+'&stages='+stages + '&'+$('#additionalFilter input[type=checkbox]').serialize(),
        loadicon:'<tr><td colspan="8" style="text-align: center; color: gray;">Загружаю..</td></tr>'
    });
}
function paginate(id) {
    projectFilter(id);
}

function openChoseStages() {
    $('#curStagesList').dialog({
        title:'Стадии проекта',
        width:400,
        height:300,
        resizable:false,
        buttons:{
            'Применить':function () {
                $('#curStagesList input[type=checkbox]').each(function (i,obj) {
                    if(obj.checked){
                        if(stages.length>0)
                            stages+=',';
                        stages+=obj.value;
                    }
                    $('#curStagesList').dialog('close');

                });
                paginate(1);
            },
            'Очистить':function () {
                stages = '';
                $('#curStagesList input[type=checkbox]').each(function (i,obj) {
                    obj.checked = false;
                })
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        }
    });
}