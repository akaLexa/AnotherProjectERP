var currentTab;

$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
    var curID = e.target.id;
    genTabContent(curID);
});

$(document).ready(function () {
    if(window.location.hash.trim().length==0){
        window.location.hash = '|defaultTab|';
    }

    $(window.location.hash).tab('show');
});

function genTabContent(tab) {
    if(tab.length >0){
        console.log(tab);
        window.location.hash = tab;
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
                    default:
                        currentTab = tab;
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

function genUserFromGroup(targetID,groupID) {
    genIn({
        element:targetID,
        address:'|site|page/|currentPage|/UserFromGroup?id=' + groupID,
        loadicon:'Загружаю...'
    });
}



var curAddStageSetings;
function rebuildProjectPlan(project,startDate) {
    genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/RebuildPlan?id=' + project +'&dateStart='+startDate,
        before:function () {
            document.querySelector('#projectPlanBody').style.opacity = 0.3;
        },
        callback:function () {
            document.querySelector('#projectPlanBody').style.opacity = 1;
            tabProjectPlanGetPlan();
        }
    });
}
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
                if(document.querySelector('#tbUserList') != undefined){
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
                }
                else
                    mwce_alert('Не выбран ответственный','Внимание');

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
function tabProjectPlanDeleteStage(stageID){
    mwce_confirm({
        title:'Подтверждение',
        text:'Вы действительно хотите удалить данную стадию?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+stageID+'&act=deleteStage',
                    type:'POST',
                    callback:function(r) {
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
                            mwce_confirm.close();
                        }
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
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
                if (document.querySelector('#tbUserList') != undefined) {
                    genIn({
                        element: 'forDialogs',
                        address: '|site|page/|currentPage|/ExecAction?tab=' + currentTab + '&id=' + id + '&act=edit',
                        type: 'POST',
                        data: $('#editStageForm').serialize(),
                        loadicon: 'Загружаюсь..',
                        callback: function (r) {
                            try {
                                var receive = JSON.parse(r);
                                if (receive['error'] != undefined) {
                                    mwce_alert(receive['error'], 'Внимание!');
                                }
                            }
                            catch (e) {
                                //console.error(e.message);
                            }
                            finally {
                                tabProjectPlanGetPlan();
                                $('#forDialogs').dialog('close');
                            }
                        }
                    });
                }
                else
                    mwce_alert('Не выбран ответственный', 'Внимание');
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        title:'Отредактировать',
        resizable:false,
        width:500,
        modal:true
    });
}
function tabProjectPlanAddTask(stageID) {
    $('#forDialogs').dialog({
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+stageID+'&act=addStageTask',
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
                if(document.querySelector('#tbUserList') != undefined){
                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+stageID+'&act=addStageTask',
                        type:'POST',
                        data:$('#addTaskForm').serialize(),
                        callback:function(r) {
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
                }
                else
                    mwce_alert('Не выбран ответственный пользователь','Внимание');


            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        title:'Добавить задачу',
        resizable:false,
        width:600,
        modal:true
    });
}
function tabProjectPlanEditTask(taskID) {
    $('#forDialogs').dialog({
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+taskID+'&act=editStageTask',
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
                if(document.querySelector('#tbUserList') != undefined){
                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+taskID+'&act=editStageTask',
                        type:'POST',
                        data:$('#editTaskForm').serialize(),
                        callback:function(r) {
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
                }
                else
                    mwce_alert('Не выбран ответственный пользователь','Внимание');


            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        title:'Изменить задачу',
        resizable:false,
        width:600,
        modal:true
    });
}
function DeleteTask(taskID){
    mwce_confirm({
        title:'Подтверждение',
        text:'Вы действительно хотите удалить данную задачу?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id='+taskID+'&act=deleteTask',
                    type:'POST',
                    callback:function(r) {
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
                            mwce_confirm.close();
                        }
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

function SendProjectMessage(){
    if(document.querySelector('#_messageText').value.trim().length<1)
        mwce_alert('Не введен текст сообщения','Внимание');
    else{
        //console.log($('#messageSends').serialize(),document.querySelector('#_messageText').value);

        genIn({
            noresponse:true,
            address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=addComment',
            type:'POST',
            data:$('#messageSends').serialize(),
            callback:function(r) {
                try{
                    var receive = JSON.parse(r);
                    if(receive['error'] != undefined){
                        mwce_alert(receive['error'],'Внимание!');
                    }
                    else{
                        $('#listenersSpanList input[type=checkbox]').each(function (id, elem) {
                            elem.checked = false;
                        });
                        document.querySelector('#_messageText').value = '';
                    }
                }
                catch(e) {
                    //console.error(e.message);
                }
                finally {
                    genIn({
                        element:'messageTabContent',
                        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=getList',
                        callback:function(r) {
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

                            }
                        },
                        loadicon:'Загружаюсь...'
                    });
                }
            }
        });
    }

}
