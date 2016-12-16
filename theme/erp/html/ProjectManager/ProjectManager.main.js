var currentTab;

$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {

    var curID = e.target.id;

    genTabContent(curID);
});

function genTabContent(tab) {

    currentTab = tab;
    genIn({
        element:'tab_content',
        address:'|site|page/|currentPage|/'+currentTab,
        loadicon:'<div style="width: 100%; text-align: center;color:green; margin-top:100px;">Загружаю...</div>',
        callback:function () {
            if(currentTab == 'TabsManagement'){
                showTabsCfg(document.querySelector('#TabChosen').value);
            }

        }
    });
}

function getStagesList() {
    genIn({
        element:'prStageBody',
        address:'|site|page/|currentPage|/GetStageList',
        loadicon:'<tr><td colspan="3" style="text-align: center;color:green;">Загружаю...</td></tr>'
    });
}
function editStage(id) {
    $('#forDialogs').dialog({
        title:'Редактировать стадию',
        modal:true,
        resizable:false,
        width:490,
        buttons:{
            'Сохранить':function () {
                if(document.querySelector('#stageName_').value.trim().length >0){
                    genIn({
                        noresponse:true,
                        before:function () {
                            document.querySelector('#forDialogs').style.opacity = 0.2;
                        },
                        address:'|site|page/|currentPage|/StageEdit?id='+id,
                        type:'POST',
                        data:$('#stageEform').serialize(),
                        callback:function () {
                            getStagesList();
                            $('#forDialogs').dialog('close');
                        }
                    });
                }
                else{
                    mwce_alert('Не введено название','Сообщение');
                }
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            document.querySelector('#forDialogs').style.opacity = 1;
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/StageEdit?id='+id,
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    });
}
function addStage() {
    $('#forDialogs').dialog({
        title:'Добавить стадию',
        modal:true,
        resizable:false,
        width:490,
        buttons:{
            'Добавить':function () {
                if(document.querySelector('#stageName_').value.trim().length >0){
                    genIn({
                        noresponse:true,
                        before:function () {
                            document.querySelector('#forDialogs').style.opacity = 0.2;
                        },
                        address:'|site|page/|currentPage|/StageAdd',
                        type:'POST',
                        data:$('#stageEform').serialize(),
                        callback:function () {
                            getStagesList();
                            $('#forDialogs').dialog('close');
                        }
                    });
                }
                else{
                    mwce_alert('Не введено название','Сообщение');
                }
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            document.querySelector('#forDialogs').style.opacity = 1;
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/StageAdd',
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    });
}
function delStage(id) {
    mwce_confirm({
        title:'Требуется решение',
        text:'Вы действительно хотите удалить стадию?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/DeleteSage?id='+id,
                    before:function () {
                        document.querySelector('#for_mwce_confirm').style.opacity = 0.2;
                    },
                    callback:function () {
                        getStagesList();
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


function saveCfg() {

    mwce_confirm({
        title:'Требуется решение',
        text:'Вы уверены, что хотите сохранить конфигурацию?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/SaveProjecrCfg',
                    type:'POST',
                    data:$('#projectCfgForm').serialize(),
                    before:function () {
                        document.querySelector('#projectCfgForm').style.opacity = 0.5;
                        document.querySelector('#statusIds').innerHTML='Сохраняю...';
                    },
                    callback:function () {
                        document.querySelector('#projectCfgForm').style.opacity = 1;
                        document.querySelector('#statusIds').innerHTML='';
                    }
                });
                mwce_confirm.close();
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

/**
 * настройка вкладок
 */
function showTabsCfg(tabName) {
    genIn({
        element:'tabsCfgList',
        address:'|site|page/|currentPage|/TabCfg',
        type:"POST",
        data:'TabChosen='+tabName,
        loadicon:'<tr><td colspan="2" style="text-align: center; color:green">Загружаю...</td></tr>'
    })
}
function saveTabCfg() {

    mwce_confirm({
        title:'Требуется решение',
        text:'Вы уверены, что хотите сохранить конфигурацию?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/SaveTabCfg?tab='+document.querySelector('#TabChosen').value,
                    type:'POST',
                    data:$('#configCustomForm').serialize(),
                    before:function () {
                        document.querySelector('#configCustomForm').style.opacity = 0.5;
                    },
                    callback:function () {
                        document.querySelector('#configCustomForm').style.opacity = 1;
                    }
                });
                mwce_confirm.close();
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

$(document).ready(function(){
    genTabContent('GetStageForm');
});