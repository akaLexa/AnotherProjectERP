
function hbTaskFilter() {
    mwce.genIn({
        element:'hbTaskTypeBody',
        address:'|site|page/|currentPage|/GetList',
        loadicon:'<tr><td colspan="2" style="text-align: center; color:green;">Загружаюсь...</td></tr>'
    });
}

function hbTypeDel(id) {
    mwce.confirm({
        title:'Требуется подтверждение',
        text:'Вы действительно хотите удалить позицию?',
        buttons:{
            'Да':function () {
                mwce.genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/Del?id='+id,
                    before:function () {
                        document.querySelector('#tbTaskpos_'+id).style.opacity = 0.2;
                    },
                    callback:function () {
                        document.querySelector('#tbTaskpos_'+id).remove();
                        mwce.confirm.close();
                    }
                });
            },
            'Нет':function () {
                mwce.confirm.close();
            }
        }
    })
}

function hbTypeAdd() {
    $('#dialogs').dialog({
        open:function () {
            mwce.genIn({
                element:'dialogs',
                address:'|site|page/|currentPage|/Add',
                loadicon:'Загружаю'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        },
        buttons: {
            'Добавить':function () {
                mwce.genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/Add',
                    type:'POST',
                    data:$('#AddHbTypeForm').serialize(),
                    before:function () {
                        document.querySelector('#AddHbTypeForm').style.opacity = 0.5;
                    },
                    callback:function () {
                        $('#dialogs').dialog('close');
                        hbTaskFilter();
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        width:500,
        modal:true,
        resizable:false,
        title:'Добавить'
    });

}

function hbTypeEdit(id) {
    $('#dialogs').dialog({
        open:function () {
            mwce.genIn({
                element:'dialogs',
                address:'|site|page/|currentPage|/Edit?id='+id,
                loadicon:'Загружаю'
            });
        },
        close:function () {
            $(this).dialog('destroy');
        },
        buttons: {
            'Сохранить':function () {
                mwce.genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/Edit?id='+id,
                    type:'POST',
                    data:$('#EditHbTypeForm').serialize(),
                    before:function () {
                        document.querySelector('#EditHbTypeForm').style.opacity = 0.5;
                    },
                    callback:function () {
                        $('#dialogs').dialog('close');
                        hbTaskFilter();
                    }
                });
            },
            'Закрыть':function () {
                $(this).dialog('close');
            }
        },
        width:500,
        modal:true,
        resizable:false,
        title:'Редактировать'
    });

}

$(document).ready(function () {
    hbTaskFilter();
});