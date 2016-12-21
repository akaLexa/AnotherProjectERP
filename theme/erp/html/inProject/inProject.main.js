var currentTab;

$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {

    var curID = e.target.id;

    genTabContent(curID);
});

$(document).ready(function () {
    genTabContent('|defaultTab|')
});

function genTabContent(tab) {
    if(tab.length >0){

        genIn({
            element:'tab_content',
            address:'|site|page/|currentPage|/TabContent?tab='+tab+'&id=|col_projectID|',
            loadicon:'Загружаюсь...',
            before:function () {
                if(currentTab!= undefined && currentTab == 'tabMain'){
                    tinymce.remove();
                }
            },
            callback:function () {

                switch (tab){
                    case 'tabMain':
                            tinymce.init({
                                selector: '#_projectDesc',
                                height: 300,
                                menubar: false,
                                plugins: [
                                    'advlist autolink lists link image charmap print preview anchor',
                                    'searchreplace visualblocks code fullscreen',
                                    'insertdatetime media table contextmenu paste code'
                                ],
                                toolbar: 'undo redo |  styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                                language: 'ru_RU',
                                browser_spellcheck: true
                            });
                        currentTab = tab;
                        break;
                    case 'tabProjectPlan':
                        currentTab = tab;
                        tabProjectPlanGetPlan();
                        break;
                }
            }
        });

    }
}

function tabMainSave() {
    tinymce.triggerSave();
    genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=save',
        type:'POST',
        data:$('#tabMainForm').serialize(),
        before:function () {
            document.querySelector('#saveMainTabNoticer').innerHTML='Сохраняю...';
        },
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive['error'] != undefined){
                    mwce_alert(receive['error'],'Внимание!');
                }
            }
            catch(e) {

            }
            finally {
                document.querySelector('#saveMainTabNoticer').innerHTML = '';
            }
        }
    });
}

var curAddStageSetings;
function tabProjectPlanGetPlan() {
    genIn({
        element:'projectPlanBody',
        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=getList',
        loadicon:'<tr><td colspan="2" style="color:green;text-align: center">Загружаюсь..</td></tr>',
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive['error'] != undefined){
                    mwce_alert(receive['error'],'Внимание!');
                }
            }
            catch(e) {

            }
        }
    });
}
function tabProjectPlanAdd(){
    $('#forDialogs').dialog({
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=add',
                loadicon:'Загружаюсь..',
                callback:function (r) {
                    try{
                        var receive = JSON.parse(r);
                        if(receive['error'] != undefined){
                            mwce_alert(receive['error'],'Внимание!');
                            $('#forDialogs').dialog('close');
                        }
                    }
                    catch(e) {

                    }
                }
            });
        },
        close:function () {
            $(this).dialog('destroy');
        },
        buttons:{
            'Добавить':function () {
                genIn({
                    element:'forDialogs',
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=add',
                    type:'POST',
                    data:$('#addStageForm').serialize(),
                    loadicon:'Загружаюсь..',
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r);
                            if(receive['error'] != undefined){
                                mwce_alert(receive['error'],'Внимание!');

                            }
                        }
                        catch(e) {
                            //console.error(e.message);
                        }
                        finally {
                            tabProjectPlanGetPlan();
                            $('#forDialogs').dialog('close');
                        }
                    }
                });

            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        title:'Добавить стадию в проект',
        resizable:false,
        width:500,
        modal:true
    });
}

function tabProjectPlanEdit(id){
    $('#forDialogs').dialog({
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+id+'&act=edit',
                loadicon:'Загружаюсь..',
                callback:function (r) {
                    try{
                        var receive = JSON.parse(r);
                        if(receive['error'] != undefined){
                            mwce_alert(receive['error'],'Внимание!');
                            $('#forDialogs').dialog('close');
                        }
                    }
                    catch(e) {

                    }
                }
            });
        },
        close:function () {
            $(this).dialog('destroy');
        },
        buttons:{
            'Сохранить':function () {
                genIn({
                    element:'forDialogs',
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+id+'&act=edit',
                    type:'POST',
                    data:$('#editStageForm').serialize(),
                    loadicon:'Загружаюсь..',
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r);
                            if(receive['error'] != undefined){
                                mwce_alert(receive['error'],'Внимание!');

                            }
                        }
                        catch(e) {
                            //console.error(e.message);
                        }
                        finally {
                            tabProjectPlanGetPlan();
                            $('#forDialogs').dialog('close');
                        }
                    }
                });

            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        title:'Добавить стадию в проект',
        resizable:false,
        width:500,
        modal:true
    });
}