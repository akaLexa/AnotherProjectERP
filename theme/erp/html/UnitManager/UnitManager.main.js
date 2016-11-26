
var currentTab;

$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {

    var curID = e.target.id.split('_')[1];

    genTabContent(curID);
});

function genTabContent(tab) {

    currentTab = tab;
    genIn({
        element:'tab_content',//+currentTab.toLowerCase(),
        address:'|site|page/|currentPage|/Get'+currentTab,
        loadicon:'<div style="width: 100%; text-align: center;color:green; margin-top:100px;">Загружаю...</div>'
    });
}

function moduleFiltr() {
    genIn({
        element:'ModulesLists',
        address:'|site|page/|currentPage|/GetModuleList',
        type:'POST',
       /* data:$('#filterMenus input[type=text],#filterMenus select').serialize(),*/
        loadicon:'<tr><td colspan="5" style="color:green;text-align: center;">Загружаю..</td></tr>',
        callback:function () {
          /*  knowMenuSec();*/
        }
    })
}



$(document).ready(function(){
    genTabContent('Group');
});

function groupEdit(id) {
    $('#forDialogs').dialog({
        title:'Редактировать группу',
        modal:true,
        width:600,
        resizable:false,
        buttons:{
            add:{
                text:'Сохранить',
                click:function () {
                    if(document.querySelector('#GroupNameText').value.trim().length>0){
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/EditGroup?id='+id,
                            type:'POST',
                            data:$('#groupEditForm').serialize(),
                            callback:function (r) {
                                var receive = JSON.parse(r);
                                if(receive.error != undefined){
                                    mwce_alert(receive.error,'Ошибка..');
                                }
                                else{
                                    genTabContent('Group');
                                    $('#forDialogs').dialog('close');
                                }
                            }
                        });
                    }

                }
            },
            cancel:{
                text:'Отмена',
                click:function () {
                    $(this).dialog('close');
                }
            }
        },
        create:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/EditGroup?id='+id,
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    })
}

function add() {
    $('#forDialogs').dialog({
        title:'Добавить группу',
        modal:true,
        width:600,
        resizable:false,
        buttons:{
            add:{
                text:'Добавить',
                click:function () {
                    if(document.querySelector('#GroupNameText').value.trim().length>0){
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/GetGroup',
                            type:'POST',
                            data:$('#groupAddForm').serialize(),
                            callback:function (r) {
                                var receive = JSON.parse(r);
                                if(receive.error != undefined){
                                    mwce_alert(receive.error,'Ошибка..');
                                }
                                else{
                                    genTabContent('Group');
                                    $('#forDialogs').dialog('close');
                                }
                            }
                        });
                    }

                }
            },
            cancel:{
                text:'Отмена',
                click:function () {
                    $(this).dialog('close');
                }
            }
        },
        create:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/AddGroup',
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    })
}

function groupDel(id) {
    mwce_confirm({
        title:'Подтверждение действия',
        text:'Внимание, чтобы удалить гуппу, сначала убедитесь, что к ней не привязаны роли и пользователи. Вы точно уверены, что хотите удалить группу?',
        buttons:{
            'Да':{
                text:'Да',
                click:function () {
                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/DelGroup',
                        type:'POST',
                        data:'id='+id,
                        callback:function (r) {
                            var receive = JSON.parse(r);
                            if(receive.error != undefined)
                                mwce_alert(receive.error,'Внимание!');
                            else{
                                genTabContent(currentTab);
                            }
                        }
                    });
                    mwce_confirm.close();
                }
            },
            'Нет':{
                text:'Нет',
                click:function () {
                    mwce_confirm.close();
                }
            }
        }
    })
}


function addRole() {
    $('#forDialogs').dialog({
        title:'Добавить роль',
        modal:true,
        resizable:false,
        width:300,
        buttons:{
            add:{
                text:'Добавить',
                click:function () {
                    if(document.querySelector('#roleNameText').value.trim().length>0){
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/AddRole',
                            type:'POST',
                            data:$('#addRoleForm').serialize(),
                            callback:function (r) {
                                var receive = JSON.parse(r);
                                if(receive.error != undefined){
                                    mwce_alert(receive.error,'Ошибка..');
                                }
                                else{
                                    genTabContent(currentTab);
                                    $('#forDialogs').dialog('close');
                                }
                            }
                        });
                    }

                }
            },
            cancel:{
                text:'Отмена',
                click:function () {
                    $(this).dialog('close');
                }
            }
        },
        create:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/AddRole',
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    })
}

function roleEdit(id) {
    $('#forDialogs').dialog({
        title:'Редактировать роль',
        modal:true,
        resizable:false,
        buttons:{
            add:{
                text:'Сохранить',
                click:function () {
                    if(document.querySelector('#roleNameText').value.trim().length>0){
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/EditRole?id='+id,
                            type:'POST',
                            data:$('#editRoleForm').serialize(),
                            callback:function (r) {
                                var receive = JSON.parse(r);
                                if(receive.error != undefined){
                                    mwce_alert(receive.error,'Ошибка..');
                                }
                                else{
                                    genTabContent(currentTab);
                                    $('#forDialogs').dialog('close');
                                }
                            }
                        });
                    }

                }
            },
            cancel:{
                text:'Отмена',
                click:function () {
                    $(this).dialog('close');
                }
            }
        },
        create:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/EditRole?id='+id,
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    })
}

function roleDel(id) {
    mwce_confirm({
        title:'Подтверждение действия',
        text:'Внимание, чтобы удалить роль, сначала убедитесь, что к ней не привязаны гуппы и пользователи. Вы точно уверены, что хотите удалить группу?',
        buttons:{
            'Да':{
                text:'Да',
                click:function () {
                    genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/DelRole',
                        type:'POST',
                        data:'id='+id,
                        callback:function (r) {
                            var receive = JSON.parse(r);
                            if(receive.error != undefined)
                                mwce_alert(receive.error,'Внимание!');
                            else{
                                genTabContent(currentTab);
                            }
                        }
                    });
                    mwce_confirm.close();
                }
            },
            'Нет':{
                text:'Нет',
                click:function () {
                    mwce_confirm.close();
                }
            }
        }
    })
}

function addModule() {
    $('#forDialogs').dialog({
        title:'Добавить модуль',
        modal:true,
        resizable:false,
        width:800,
        buttons:{
            add:{
                text:'Добавить',
                click:function () {
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/AddModule',
                            type:'POST',
                            data:$('#addModuleForm').serialize(),
                            callback:function (r) {
                                try{
                                    var receive = JSON.parse(r);
                                    if(receive.error != undefined){
                                        mwce_alert(receive.error,'Ошибка..');
                                    }
                                    else{
                                        $('#forDialogs').dialog('close');
                                    }
                                }
                                catch (e){
                                    console.error(e.message);
                                }
                                finally {
                                    moduleFiltr();
                                }
                            }
                        });
                }
            },
            cancel:{
                text:'Отмена',
                click:function () {
                    $(this).dialog('close');
                }
            }
        },
        create:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/AddModule',
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    })
}

function editModule(id) {
    $('#forDialogs').dialog({
        title:'Редактировать модуль',
        modal:true,
        resizable:false,
        width:800,
        buttons:{
            add:{
                text:'Сохранить',
                click:function () {
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/EditModule?id='+id,
                            type:'POST',
                            data:$('#editMForm').serialize(),
                            callback:function (r) {
                                try{
                                    var receive = JSON.parse(r);
                                    if(receive.error != undefined){
                                        mwce_alert(receive.error,'Ошибка..');
                                    }
                                    else{
                                        $('#forDialogs').dialog('close');
                                    }
                                }
                                catch (e){
                                    console.error(e.message);
                                }
                                finally {
                                    moduleFiltr();
                                }
                            }
                        });
                }
            },
            cancel:{
                text:'Отмена',
                click:function () {
                    $(this).dialog('close');
                }
            }

        },
        create:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/EditModule?id='+id,
                loadicon:'Загружаю...'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        }
    })
}

function delModule(id) {
    mwce_confirm({
        title:'Требуется решение',
        text:'Вы действительно ходите удалить текущий модуль?',
        buttons:{
            'Да':function () {
                genIn({
                    element:'forDialogs',
                    address:'|site|page/|currentPage|/DelModule?id='+id,
                    callback:function () {
                        mwce_confirm.close();
                        moduleFiltr();
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

function mfilter() {
    genIn({
        element:'menu_Body',
        address:'|site|page/|currentPage|/GetMenu',
        type:'POST',
        data:$('#filterMenus input[type=text],#filterMenus select').serialize(),
        loadicon:'<tr><td colspan="3" style="color:green;text-align: center;">Загружаю..</td></tr>',
        callback:function () {
            knowMenuSec();
        }
    })
}