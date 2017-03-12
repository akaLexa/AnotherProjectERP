var currentTab;
var curState = |col_ProjectPlanState|;

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
function getStagesByGroup(group){
    document.querySelector('#stageFormDate').style.visibility = 'hidden';
    document.querySelector('#stageFormPeople').innerHTML='';
    genIn({
        element:'stageFormStages',
        address:'|site|page/|currentPage|/ExecAction?tab=tabMain&act=getStageList&id=|col_projectID|&group='+group,
        loadicon:'Загружаю'
    });
}
function getRespByStage(stage){
    document.querySelector('#stageFormDate').style.visibility = 'hidden';
    genIn({
        element:'stageFormPeople',
        address:'|site|page/|currentPage|/ExecAction?tab=tabMain&act=getRespList&id=|col_projectID|&stage='+stage,
        loadicon:'Загружаю',
        callback:function (r) {
            if(r.trim().length>10){
                document.querySelector('#stageFormDate').style.visibility = 'visible';
            }
        }
    });
}
function changeProjectStage() {

    $('#forDialogs').dialog({
        title:'Следующая стадия',
        width:500,
        modal:true,
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/ExecAction?tab=tabMain&id=|col_projectID|&act=stageMove',
                callback:function (r) {
                    try{
                        var receive = JSON.parse(r);
                        if(receive['error'] != undefined){
                            $('#forDialogs').dialog('close');
                            mwce_alert(receive['error'],'Внимание!');
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
            'Продолжить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ExecAction?tab=tabMain&id=|col_projectID|&act=stageMove',
                    type:'POST',
                    data:$('#nextStageForm').serialize(),
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r);
                            if(receive['error'] != undefined){
                                mwce_alert(receive['error'],'Внимание!');
                            }
                            else{
                                $('#forDialogs').dialog('close');
                                window.location.reload();
                            }
                        }
                        catch(e) {
                            mwce_alert('Произошла обшибка: скорее всего, Вы не заполнили какие-то обязательные поля','Внимание');
                        }
                    }
                });
            },
            'Закрыть':function () {
                $('#forDialogs').dialog('close');
            }
        }
    });
    
}
function agreeStage(state) {

    var desc = document.querySelector('#disagreeDesc').value.trim();

    if(state == 2 && desc.length<1){
        mwce_alert('Чтобы отказаться от стадии, необходимо указать причину отказа.','Внимание!');
        return;
    }

    genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=stageAction&type='+state+'&desc='+desc,
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive['error'] != undefined)
                    mwce_alert(receive['error'],'Внимание!');
                else{
                    window.location.reload();
                }
            }
            catch (e){
                console.error(e.message);
            }
        }
    });
}

function changeStageTask() {
    var planDate = new Date('|col_dateEndPlan|');
    var nowDate = new Date();
    var text = '';
    //отключение
    if(curState == 1){
        text = 'Вы уверены, что хотите отключить выполнение плана?' +
            ' <textarea id="descPlanArea" class="form-control" style=" width:100%; height: 100px;" placeholder="Пожалуйста, укажите причину отключения автоплана"></textarea>';

        mwce_confirm({
            title:'Внимание',
            text:text,
            buttons:{
                'Да':function () {
                    if(document.querySelector('#descPlanArea').value.trim().length <3){
                        document.querySelector('#descPlanArea').placeholder = 'Пожалуйста, укажите причину отключения автоплана. Параметр обязателен к заполнению!';
                    }
                    else{

                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/ExecAction?tab=tabMain&id=|col_projectID|&act=switchPlan',
                            type:'POST',
                            data:'planState=0&descState='+document.querySelector('#descPlanArea').value.trim(),
                            callback:function (r) {
                                try{
                                    var receive = JSON.parse(r);
                                    if(receive['error'] != undefined){
                                        mwce_alert(receive['error'],'Внимание!');
                                    }
                                    else if(receive['stageIsLate'] != undefined){
                                        mwce_alert('Не казана причина просрочки стадии','Внимание!');
                                    }
                                    else{
                                        $('#glyphPlanIconStart').removeClass('planStarted');
                                        $('#glyphPlanIconStop').addClass('planStopped');
                                        curState = 0;
                                        window.location.reload();
                                    }
                                }
                                catch(e) {

                                }
                                finally {
                                    mwce_confirm.close();

                                }
                            }
                        });

                    }
                },
                'Нет':function () {
                    mwce_confirm.close();
                }
            }
        });
    }
    else{

        text = 'Вы уверены, что хотите включить автовыполнение плана? После включения данной функции проект <u>автоматически перейдет на следующую стадию</u> и Вы <u>не сможете редактировать план</u>, пока он запущен!';
        if(nowDate.getTime() > planDate.getTime())
            text+= '<textarea id="descStageArea" class="form-control" style=" width:100%; height: 100px;" placeholder="Пожалуйста, укажите причину просрочки стадии. Этот параметр обязателен!"></textarea>';

        mwce_confirm({
            title:'Внимание',
            text:text,
            buttons:{
                'Да':function () {
                    var dataString = '';

                    if(document.querySelector('#descStageArea')!=undefined){
                        if(document.querySelector('#descStageArea').value.trim().length <3)
                            return;
                        else
                            dataString = '&descStage='+document.querySelector('#descStageArea').value.trim();
                    }

                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/ExecAction?tab=tabMain&id=|col_projectID|&act=switchPlan',
                        type:'POST',
                        data:'planState=1' + dataString,
                        callback:function (r) {
                            try{
                                var receive = JSON.parse(r);
                                if(receive['error'] != undefined){
                                    mwce_alert(receive['error'],'Внимание!');
                                }
                                else if(receive['stageIsLate'] != undefined){
                                    mwce_alert('Не указана причина просрочки стадии','Внимание!');
                                }
                                else{
                                    $('#glyphPlanIconStart').addClass('planStarted');
                                    $('#glyphPlanIconStop').removeClass('planStopped');
                                    curState = 1;
                                    window.location.reload();
                                }
                            }
                            catch(e) {

                            }
                            finally {
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
}

function genTabContent(tab) {
    if(tab.length >0){
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
                currentTab = tab;
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
                        break;
                    case 'tabProjectPlan':
                        tabProjectPlanGetPlan();
                        break;
                    case 'tabTasks':
                        filterTask();
                        break;
                    case 'tabDocs':
                        filterDocs();
                        break;
                    default:
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
            $('#saveMainTabNoticer').empty();
            $('#saveMainTabNoticer').show();
            $('#saveMainTabNoticer').append('Сохраняю...');
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
                $('#saveMainTabNoticer').empty();
                $('#saveMainTabNoticer').append('Сохранено').fadeOut(800);
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
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&stageID='+stageID+'&act=deleteStage',
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
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&stageID='+id+'&act=edit',
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
                        address: '|site|page/|currentPage|/ExecAction?tab=' + currentTab + '&id=|col_projectID|&stageID=' + id + '&act=edit',
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
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&stageID='+stageID+'&act=addStageTask',
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
                var dn = new Date();
                var dw = new Date(document.querySelector('#_endDate').value + ' ' + document.querySelector('#_endTime').value);

                if(document.querySelector('#tbUserList') != undefined && dn.getTime() < dw.getTime()){
                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&stageID='+stageID+'&act=addStageTask',
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
                                if(currentTab == 'tabTasks')
                                    filterTask();
                                $('#forDialogs').dialog('close');
                            }
                        }
                    });
                }
                else
                    mwce_alert('Не выбран ответственный пользователь или дата неадекватна','Внимание');


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
                address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&taskID='+taskID+'&act=editStageTask',
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
                        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&taskID='+taskID+'&act=editStageTask',
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
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&taskID='+taskID+'&act=deleteTask',
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

function addTask() {
    $('#forDialogs').dialog({
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/ExecAction?tab=tabTasks&id=|col_projectID|&act=add',
                loadicon:'Загружаюсь..',
                callback:function (r) {
                    tinymce.init({
                        selector: '#taskDesc',
                        height: 200,
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

                    try{
                        var receive = JSON.parse(r);
                        if(receive['error'] != undefined){
                            mwce_alert(receive['error'],'Внимание!');
                            $('#forDialogs').dialog('close');
                        }
                        else{
                            $('#tabTasks').tab('show');
                        }
                    }
                    catch(e) {

                    }
                }
            });
        },
        close:function () {
            tinymce.remove('#taskDesc');
            $(this).dialog('destroy');
        },
        buttons:{
            'Добавить':function () {

                if(document.querySelector('#_taskName').value.trim().length<1){
                    mwce_alert('Не заполнено название задачи','Внимание');
                }
                else if(document.querySelector('#_endDate').value){

                    var now = new Date();
                    var inF = new Date(document.querySelector('#_endDate').value + ' ' + document.querySelector('#_endTime').value);

                    if(now.getTime() >= inF.getTime()){
                        mwce_alert('Дата звершения должна быть больше, чем сегодня','Внимание');
                    }
                    else if(document.querySelector('#tbUserList1')!= undefined){
                        tinymce.triggerSave('#taskDesc');
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/ExecAction?tab=tabTasks&id=|col_projectID|&act=add',
                            type:'POST',
                            data:$('#addTask').serialize(),
                            callback:function (r) {
                                try{
                                    var receive = JSON.parse(r);
                                    if(receive['error'] != undefined)
                                        mwce_alert(receive['error'],'Ошибка');
                                    else{

                                    }
                                }
                                catch (e){
                                    mwce_alert(e.message,'Ошибка');
                                }
                                $('#forDialogs').dialog('close');
                            }
                        });
                    }
                    else{
                        mwce_alert('Не указан ответственный','Внимание');
                    }

                }
                else{
                    mwce_alert('Не указана дата звершения','Внимание');
                }
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        title:'Добавить задачу',
        resizable:false,
        width:600,
        modal:true,
        position:{
            at:'top'
        }
    });
}
function filterTask(){
    genIn({
        element:'tbTaskBody',
        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=getList',
        loadicon:'<tr><td colspan="7" style="text-align: center; color:green">Загружаюсь</td></tr>',
        type:'POST',
        data:$('#taskFilters select,#taskFilters input[type=date],#taskFilters input[type=text]').serialize()
    });
}
function taskRedir(id) {
    document.querySelector('#tGoForm').action='|site|page/tasks/In.html?id='+id;
    document.querySelector('#tGoForm').submit();
}

var checkedFiles = [];
var currentTDU;
function filterDocs(){
    var docG = document.querySelector('#curChosenDg').value;
    if(docG>0){
        document.querySelector('#toUploadFile').disabled = false;
        document.querySelector('#addFolder').disabled = false;
    }
    else{
        document.querySelector('#toUploadFile').disabled = true;
        document.querySelector('#addFolder').disabled = true;
    }
    genIn({
        element:'tabDocsContent',
        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=getFiles',
        loadicon:'<tr><td colspan="5" style="text-align: center; color:green">Загружаюсь</td></tr>',
        type:'POST',
        data:$('#filterDocsz select,#filterDocsz input[type=text],#filterDocsz input[type=hidden]').serialize()
    });
}
function downloadDocs() {
    console.log('-> open upload window');
    var newWin = window.open('|site|page/Docs/ProjectUpload?p=|col_projectID|&gr=' + document.querySelector('#curChosenDg').value + '&f='+document.querySelector('#_chosenFolder').value, 'fUpload', 'width=650,height=300,left=400,top=100,menubar=no,toolbar=no,resizable=no,location=no,status=no,personalbar=no');
    currentTDU = setInterval(function(){
        if(newWin.closed)
        {
            console.log('-> close upload window');
            filterDocs();
            clearInterval(currentTDU);
        }},700);
}
function addNewDocFolder() {
    $('#forDialogs').dialog({
        open:function () {
            document.querySelector('#forDialogs').innerHTML = '<form id="addNewFolderForm">' +
                '<div class="form-group"><input type="text" name="newFname" class="form-control inlineBlock" maxlength="254" style="width: 450px;" placeholder="Название папки"></div>' +
                '</form>';
        },
        close:function () {
            $(this).dialog('destroy');
        },
        title:'Добавить папку в документы',
        resizable:false,
        width:500,
        modal:true,
        buttons:{
            'Добавить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=addFolder',
                    type:'POST',
                    data:$('#filterDocsz select,#filterDocsz input[type=text],#filterDocsz input[type=hidden]').serialize()+'&'+$('#addNewFolderForm').serialize(),
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r);
                            if(receive['folder'] != undefined)
                                SetInFolder(receive['folder'],$('#curChosenDg').val());
                            else
                                filterDocs();
                        }
                        catch(e) {
                            filterDocs();
                        }
                        finally {

                            $('#forDialogs').dialog('close');
                        }
                    }
                });
            }
            ,'Закрыть':function () {
                $(this).dialog('close');
            }
        }
    });
}
function SetInFolder(id,group) {
    $('#curChosenDg').val(group);
    document.querySelector('#_chosenFolder').value = id;
    filterDocs();
}
function delFolder(id) {
    mwce_confirm({
        title:'Требуется решение',
        text:'Вы дейстивтельно хотите удалить <mark>папку вместе с файлами</mark>?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=delFolder&folder='+id,
                    callback:function () {
                        filterDocs();
                        mwce_confirm.close();
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}
function delFile(id) {
    mwce_confirm({
        title:'Требуется решение',
        text:'Вы дейстивтельно хотите удалить файл?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=delFile&file='+id,
                    callback:function () {
                        filterDocs();
                        mwce_confirm.close();
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}
function docCheckeds(obj) {
    var viz;
    var state = document.querySelector('#_mainCheckerDocs').checked;

    $('#tabDocsContent input[type=checkbox]').each(function () {

        if(state == true) {
            viz = 1;
            this.checked = true;
            checkedFiles[this.value] = 1;
        }
        else {
            this.checked = false;
            delete checkedFiles[this.value];
            viz = 0;
        }
    });

    if(viz>0)
        document.querySelector('#docFoot').style.display = 'table-cell';
    else
        document.querySelector('#docFoot').style.display = 'none';
}
function fileChecked(obj) {
    if(obj.checked)
        checkedFiles[obj.value] = 1;
    else
        delete checkedFiles[obj.value];

    var i =0;

    for (var j in checkedFiles)
        i++;

    if(i>0)
        document.querySelector('#docFoot').style.display = 'table-cell';
    else
    {
        document.querySelector('#_mainCheckerDocs').checked = false;
        document.querySelector('#docFoot').style.display = 'none';
    }
}
function folderDownload(id) {
    document.querySelector('#docForm').action = '|site|page/Docs/ProjectFolderDownload?f='+id;
    document.querySelector('#docForm').submit();
}
function fileAccept() {
    var choosen = $('#_actionFileType').val();
    if(choosen == 1){
        FilesDownload();
    }
    else{
        FilesDelete();
    }
}
function FilesDownload() {
    var queue = '';

    $('#tabDocsContent input[type=checkbox]').each(function () {
        if(this.checked){
            if(queue.length>0)
                queue+=',';
            queue+=this.value;
            this.checked = false;
        }
    });

    document.querySelector('#docForm').action = '|site|page/Docs/ProjectFilesDownload?queue='+queue;
    document.querySelector('#docForm').submit();
}
function FilesDelete() {

    mwce_confirm({
        title:'Требуется решение',
        text:'Вы дейстивтельно хотите удалить выбранные файлы?',
        buttons:{
            'Да':function () {
                var queue = '';

                $('#tabDocsContent input[type=checkbox]').each(function () {
                    if(this.checked){
                        if(queue.length>0)
                            queue+=',';
                        queue+=this.value;
                        this.checked = false;
                    }
                });

                if(queue.length>0){
                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/ExecAction?tab='+currentTab+'&id=|col_projectID|&act=delFiles',
                        type:'POST',
                        data:'queue='+queue,
                        callback:function () {
                            mwce_confirm.close();
                            filterDocs();
                        }
                    });
                }
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

function addInPlan(project) {

    $('#forDialogs').dialog({
        title:'Добавить Новый шаблон',
        resizable:true,
        modal:true,
        width:400,
        buttons:{
            'Добавить':function () {
                if(document.querySelector('#themName').value.trim().length > 0){
                    genIn({
                        noresponse:true,
                        address:'|site|page/inProject/ExecAction?tab=tabProjectPlan&id=|col_projectID|&act=AddTheme',
                        type:'POST',
                        data:'tName='+document.querySelector('#themName').value,
                        callback:function (e) {
                            try{
                                var receive = JSON.parse(e);
                                if(receive['error'] != undefined){
                                    mwce_alert(receive['error'],'Ошибка');
                                }
                                else{
                                    genTabContent('tabProjectPlan');
                                }
                            }
                            catch (error)
                            {
                                console.log(error.message);
                            }
                            finally {
                                $('#forDialogs').dialog('close');
                            }
                        }
                    });
                }
                else
                    console.error('пустое имя');

            },
            'Закрыть':function () {
                $('#forDialogs').dialog('close');
            }
        },
        open:function () {
            $('#forDialogs').empty();
            $('#forDialogs').append('<div class="alert alert-warning" role="alert"><b>Внимание!</b> В шаблон плана проекта будут сохранены <u>только запланированные</u> стадии и задачи!</div><input type="text" class="form-control" name="themName" id="themName" placeholder="Название нового шаблона">');
        },
        close:function () {
            $(this).dialog('destroy');
        }
    });
}
function removePlanStage(){
    mwce_confirm({
        title:'Требуется решение',
        text:'Вы дейсвительно хотите удалить позицию из шаблонов плана проекта?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/inProject/ExecAction?tab=tabProjectPlan&id=|col_projectID|&act=DelTheme&pos='+document.querySelector('#planStageTheme').value,
                    callback:function (e) {
                        genTabContent('tabProjectPlan');
                        mwce_confirm.close();
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}
function PlanImport() {
    var curPlanPiece = parseInt(document.querySelector('#planStageTheme').value);
    if(curPlanPiece >0)
    {
        genIn({
            noresponse:true,
            address:'|site|page/inProject/ExecAction?tab=tabProjectPlan&id=|col_projectID|&act=addPlan&pos='+document.querySelector('#planStageTheme').value,
            callback:function (e) {
                tabProjectPlanGetPlan();
            }
        });
    }
    else{
        mwce_alert('Прежде следует выбрать шаблон');
    }
}
