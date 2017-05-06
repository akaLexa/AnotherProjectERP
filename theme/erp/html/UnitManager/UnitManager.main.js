
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
        loadicon:'<div style="width: 100%; text-align: center;color:green; margin-top:100px;">Загружаю...</div>',
        callback:function () {
            if(currentTab == 'Menu'){
                knowMenuSec();
            }
        }
    });
}


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
function addSpecialProject(id) {
    mwce_confirm({
        title:'Требуется решение',
        text:'Данная функция создаст проект для выбранной группы пользователей. Проект не может быть удален, но может быть завершен. Вы уверены?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/SetSpecProject?id='+id,
                    callback:function () {
                        mwce_confirm.close();
                        genTabContent('Group');
                    }
                })
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    })
}
function setGroupFounder(group,founder) {
    mwce_confirm({
        title:'Требуется решение',
        text:'Вы уверены?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/SetFounder?id='+group,
                    type:'POST',
                    data:'id='+founder,
                    callback:function () {
                        mwce_confirm.close();
                        genTabContent('Group');
                    }
                })
            },
            'Нет':function () {
                mwce_confirm.close();
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

function moduleFiltr() {
    genIn({
        element:'ModulesLists',
        address:'|site|page/|currentPage|/GetModuleList',
        type:'POST',
         data:$('#filterModule select').serialize(),
        loadicon:'<tr><td colspan="5" style="color:green;text-align: center;">Загружаю..</td></tr>',
        callback:function () {

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
function clearModuleCache() {
    mwce_confirm({
        title: 'Очистка кеша модулей',
        text: 'Вы уверены что хотите очистить кеш модулей?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ClearModuleCache',
                    before:function () {
                        document.querySelector('#for_mwce_confirm').innerHTML = 'Думаю, подождите, пожалуйста...';
                    },
                    callback:function () {
                        mwce_confirm.close();
                    }
                })
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

function addPlugin() {
    var cPlugin = document.querySelector('#unregPl').value;
    genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/PluginAdd',
        type:'POST',
        data:'pluginName='+cPlugin,
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive.error != undefined){
                    mwce_alert(receive.error,'Ошибка..');
                }
                else{
                    PluginsFilter();
                    var select = document.querySelector('#unregPl');
                    select.options[select.selectedIndex]=null;
                    select.selectedIndex = 0;
                }
            }
            catch (e){
                console.error(e.message);
            }

        }
    })
}
function PluginsFilter() {
    genIn({
        element:'pluginsBodyTable',
        address:'|site|page/|currentPage|/GetPluginList',
        type:'POST',
        loadicon:'<tr><td colspan="3" style="text-align: center;color:green;">Загружаю...</td></tr>'
    })
}
function editPlugin(id) {
    $('#forDialogs').dialog({
        title:'Редактировать плагин',
        width:600,
        modal:true,
        resizable:false,
        buttons:{
            'Сохранить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/EditPlugin?id='+id,
                    type:'POST',
                    data:$('#editPluginForm').serialize(),
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r);
                            if(receive.error != undefined){
                                mwce_alert(receive.error,'Ошибка..');
                            }
                            else{
                                PluginsFilter();
                                $('#forDialogs').dialog('close');
                            }
                        }
                        catch (e){
                            console.error(e.message);
                        }


                    }
                });
            },
            'Удалить':function () {
                mwce_confirm({
                    title:'Требуется подтверждение',
                    text:'Вы действительно хотите удалить плагин?',
                    buttons:{
                        'Да':function () {
                            genIn({
                                noresponse:true,
                                address:'|site|page/|currentPage|/DelPlugin?id='+id,
                                callback:function (r) {
                                    try{
                                        var receive = JSON.parse(r);
                                        if(receive.error != undefined){
                                            mwce_alert(receive.error,'Ошибка..');
                                        }
                                        else{
                                            mwce_confirm.close();
                                            PluginsFilter();
                                            $('#forDialogs').dialog('close');
                                        }
                                    }
                                    catch (e){
                                        console.error(e.message);
                                    }

                                }
                            });
                        },
                        'Нет':function () {
                            mwce_confirm.close();
                        }
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        close:function () {
            $(this).dialog('destroy');
        },
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/EditPlugin?id='+id,
                loadicon:'Загружаю...'
            });
        }
    });
}
function clearPluginCache() {
    mwce_confirm({
        title: 'Очистка кеша плагинов',
        text: 'Вы уверены что хотите очистить кеш плагинов?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ClearPluginCache',
                    before:function () {
                        document.querySelector('#for_mwce_confirm').innerHTML = 'Думаю, подождите, пожалуйста...';
                    },
                    callback:function () {
                        mwce_confirm.close();
                    }
                })
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}

function UserFilter() {
    genIn({
        element:'UserFormContent',
        address:'|site|page/|currentPage|/GetUserList',
        type:'POST',
        data:$('#userFilter select,#userFilter input[type=text]').serialize(),
        loadicon:'<tr><td colspan="4" style="text-align: center;color:green;">Загружаю...</td></tr>'
    })
}
function AddUserForm() {
    $('#forDialogs').dialog({
        title:'Добавить пользователя',
        modal:true,
        resizable:false,
        width:600,
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/AddUser',
                loadicon:'Загружаю...'
            })
        },
        close:function () {
            $(this).dialog('destroy');
        },
        buttons:{
            'Добавить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/AddUser',
                    type:'POST',
                    data:$('#AddUserForm').serialize(),
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r.trim());
                            if(receive['error']!= undefined){
                                mwce_alert(receive['error'],'Ошибка');
                            }
                            else{
                                UserFilter();
                                $('#forDialogs').dialog('close');
                            }
                        }
                        catch (e)
                        {
                            console.error(e.message)
                        }
                    }
                })
            },
            'Закрыть':function () {
                $('#forDialogs').dialog('close');
            }
        }
    });
}
function EditUser(uid) {
    $('#forDialogs').dialog({
        title:'Редактировать пользователя',
        modal:true,
        resizable:false,
        width:600,
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/EditUser?id='+uid,
                loadicon:'Загружаю...'
            })
        },
        close:function () {
            $(this).dialog('destroy');
        },
        buttons:{
            'Сохранить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/EditUser?id='+uid,
                    type:'POST',
                    data:$('#editUserForm').serialize(),
                    callback:function (r) {
                        try{
                            var receive = JSON.parse(r.trim());
                            if(receive['error']!= undefined){
                                mwce_alert(receive['error'],'Ошибка');
                            }
                            else{
                                UserFilter();
                                $('#forDialogs').dialog('close');
                            }
                        }
                        catch (e)
                        {
                            console.error(e.message)
                        }
                    }
                })
            },
            'Закрыть':function () {
                $('#forDialogs').dialog('close');
            }
        }
    });
}

function mfilter() {
    genIn({
        element:'menu_Body',
        address:'|site|page/|currentPage|/GetMenuList',
        type:'POST',
        data:$('#filterMenus input[type=text],#filterMenus select').serialize(),
        loadicon:'<tr><td colspan="3" style="color:green;text-align: center;">Загружаю..</td></tr>',
        callback:function () {
            knowMenuSec();
        }
    });
}
function addNewMenu(){
    $('#forDialogs').dialog({
        title:'Добавить меню',
        width:500,
        modal:true,
        resizable:false,
        buttons:{
            'Добавить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/AddNewMenu',
                    type:'POST',
                    data:$('#newMenu').serialize(),
                    before:function () {
                        document.querySelector('#newMenu').style.opacity = '0.2';
                    },
                    callback:function (){
                        $('#forDialogs').dialog('close');
                        genTabContent('Menu');
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            document.querySelector('#forDialogs').innerHTML='<form id="newMenu">' +
                '<input type="text" class="form-control" style="width:400px;display: inline-block;" name="newName" placeholder="Название нового меню">'+
                '</form>';

        },
        close:function () {
            $(this).dialog('destroy');
            document.querySelector('#forDialogs').innerHTML='';
        }
    });
}
function AddInMenu() {
    var curMenu = document.querySelector('#menuList').value;
    $('#forDialogs').dialog({
        title:'Добавление позиции',
        width:580,
        modal:true,
        resizable:false,
        buttons:{
            'Сохранить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/AddInMenu?id='+curMenu,
                    type:'POST',
                    data:$('#addInMenu').serialize(),
                    before:function () {
                        document.querySelector('#addInMenu').style.opacity = '0.2';
                    },
                    callback:function (){
                        $('#forDialogs').dialog('close');
                        mfilter();
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/AddInMenu?id='+curMenu,
                loadicon:'Загружаю'
            })
        },
        close:function () {
            $(this).dialog('destroy');
        }
    });
}
function EditInMenu(id) {

    $('#forDialogs').dialog({
        title:'Изменение позиции',
        width:580,
        modal:true,
        resizable:false,
        buttons:{
            'Сохранить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/EditInMenu?id='+id,
                    type:'POST',
                    data:$('#EditInMenu').serialize(),
                    before:function () {
                        document.querySelector('#EditInMenu').style.opacity = '0.2';
                    },
                    callback:function (){
                        $('#forDialogs').dialog('close');
                        mfilter();
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/EditInMenu?id='+id,
                loadicon:'Загружаю'
            })
        },
        close:function () {
            $(this).dialog('destroy');
        }
    });
}
function DelPosMenu(id) {
    mwce_confirm({
        title:'Требуется подтверждение',
        text:'Вы уверены, что хотите удалить текущее меню?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/DelPosMenu?id='+id,
                    callback:function (r){
                        mwce_confirm.close();
                        mfilter();
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    })
}
function DelMenu() {
    var curMenu = document.querySelector('#menuList').value;
    mwce_confirm({
        title:'Требуется подтверждение',
        text:'Вы уверены, что хотите удалить позицию из меню?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/DelMenu?id='+curMenu,
                    callback:function (r){
                        mwce_confirm.close();
                        genTabContent('Menu');
                    }
                });
            },
            'Нет':function () {
                mwce_confirm.close();
            }
        }
    });
}
function knowMenuSec() {
    var curMenu = document.querySelector('#menuList').value;
    genIn({
        noresponse:true,
        address:"|site|page/|currentPage|/MenuSeq?id="+curMenu,
        callback:function (r) {
            var answ = JSON.parse(r);
            document.querySelector('#mSeq').value = answ['seq'];
        }
    });

}
function setMenuSec() {
    var curMenu = document.querySelector('#menuList').value;
    genIn({
        noresponse:true,
        address:"|site|page/|currentPage|/MenuSeq?id="+curMenu,
        type:'POST',
        data:$('#seq_Menu').serialize()
    });

}
function getAccess(){
    var obj = document.querySelector('#menuList');
    $('#forDialogs').dialog({
        title:'Просмотр Прав',
        width:380,
        modal:true,
        resizable:false,
        buttons:{
            'Сохранить':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/GetMenuAccess?upd=1',
                    type:'POST',
                    data:'menuType='+obj.options[obj.selectedIndex].text+'&'+$('#accessFrom').serialize(),
                    before:function () {
                        document.querySelector('#accessFrom').style.opacity = '0.2';
                    },
                    callback:function (r){
                        $('#forDialogs').dialog('close');
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/GetMenuAccess',
                type:'POST',
                data:'menuType='+obj.options[obj.selectedIndex].text,
                loadicon:'Загружаю'
            })
        },
        close:function () {
            $(this).dialog('destroy');
        }
    });
}
function getlink(){
    check = document.getElementById("cptype").checked;
    textbx = document.getElementById("linkadr");

    if(textbx.value.indexOf("http",0)==-1)
    {
        if($("#pagesList").val()!='-1')
        {
            if (check)
                textbx.value =  'page/'+ $("#pagesList").val() + '.html';
            else
                textbx.value =  '' + $("#pagesList").val();
        }

    }
}
function menuRefresh() {
    mwce_confirm({
        title:'Требуется принять решение',
        text:'Вы действительно хотите очистеть кеш меню?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ClearMenuCache',
                    type:'POST',
                    before:function () {
                       document.querySelector('#for_mwce_confirm').style.opacity = 0.3;
                    },
                    callback:function (){
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

function saveMainCfg() {
    genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/GetMainCfg',
        type:'POST',
        data:$('#MainCfgForm').serialize(),
        callback:function () {
            mwce_alert('Сохранено','Сообщение');
        }
    });
}

function addNewCfg() {
    $('#forDialogs').dialog({
        title:'Добавить новую конфигурацию',
        modal:true,
        width:500,
        resizable:false,
        buttons:{
            'Добавить':function () {
                if(document.querySelector('#cfgName_').value.trim().length<1){
                    mwce_alert('Не заполнено служебное название конфига','Внимание!');
                }
                else{
                    genIn({
                        noresponse: true,
                        address:'|site|page/|currentPage|/GetFormCfg',
                        type:'POST',
                        data:$('#cfgAdder').serialize(),
                        callback:function () {
                            $('#forDialogs').dialog('close');
                            genTabContent('Configurator');
                        }
                    });
                }
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        open:function () {
            genIn({
                element:'forDialogs',
                address:'|site|page/|currentPage|/GetFormCfg',
                loadicon:'Загрузка...'
            });
        },
        close: function () {
            $(this).dialog('destroy');
        }
    });
}

$(document).ready(function(){
    genTabContent('Group');
});