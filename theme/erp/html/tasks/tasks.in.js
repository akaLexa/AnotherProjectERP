
$(document).ready(function () {
    tinymce.init({
        selector: '#_taskComment',
        height: 180,
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
});

function accept(type) {
    if(type == 2){
        if(document.querySelector('#acceptReason').value.trim().length  == 0){
            document.querySelector('#acceptReason').style.display = 'block';
        }
        else{
            document.querySelector('#acceptTaskFrom').submit();
        }
    }
}

function inWactions(type) {
    if(type == 3){
        var curDate = new Date();
        var TaskDate = new Date(document.querySelector('#endPlanTask').value);
        if(TaskDate.getTime() < curDate.getTime() && document.querySelector('#acceptReason').value.trim().length  == 0){
            mwce_alert('Для завершения задачи необходимо ввести причину просрочки','Внимание!');
        }
        else{
            document.querySelector('#actionTaskForm').submit();
        }
    }
    else if(type == 2)
    {
        if(document.querySelector('#acceptReason').value.trim().length  == 0){
            mwce_alert('Чтобы отклонить задачу, требуется указать причину','Внимание!');
        }
        else{
            document.querySelector('#actionTaskForm').submit();
        }
    }
    else if(type == 99){
        $('#taskDiag').dialog({
            open:function () {
                genIn({
                    element:'taskDiag',
                    address:'|site|page/|currentPage|/ReStart?id=|col_taskID|',
                    loadicon:'Загружаю...'
                });
            },
            close:function () {
                $(this).dialog('destroy');
            },
            buttons:{
                'Перезапустить':function () {
                    var now = new Date();
                    var inF = new Date(document.querySelector('#_finishTo').value + ' ' + document.querySelector('#_finishToTime').value);

                    if(now.getTime() >= inF.getTime()){
                        mwce_alert('Дата звершения должна быть больше, чем сегодня','Внимание');
                    }
                    else{
                        $('#taskDiag').dialog('close');
                        genIn({
                            noresponse:true,
                            address:'|site|page/|currentPage|/ReStart?id=|col_taskID|',
                            type:'POST',
                            data:$('#reStartFrom').serialize(),
                            callback:function () {
                                window.location.reload();
                            }
                        });
                    }
                },
                'Закрыть':function () {
                    $(this).dialog('close');
                }
            },
            title:'Перезапустить задачу',
            resizable:false,
            width:400,
            modal:true
        });
    }
    else{
        document.querySelector('#actionTaskForm').submit();
    }
}

function choseAction(type) {
    if(type == 3){
        var curDate = new Date();
        var TaskDate = new Date(document.querySelector('#endPlanTask').value);
        if(TaskDate.getTime() < curDate.getTime()){
            document.querySelector('#acceptReason').style.display = 'block';
        }
        else{
            document.querySelector('#acceptReason').style.display = 'none';
        }
    }
    else if(type == 2){
        document.querySelector('#acceptReason').style.display = 'block';
    }
}

function sendMessage() {
    tinymce.triggerSave('#_taskComment');
    if(document.querySelector("#_taskComment").value.trim().length >0){
        genIn({
            noresponse:true,
            address:'|site|page/|currentPage|/AddComment',
            type:'POST',
            data:$('#commentArea').serialize()+'&task=|col_taskID|',
            callback:function () {
                document.querySelector("#_taskComment").value='';
                tinyMCE.get('_taskComment').setContent('');
                genIn({
                    element:'commentsList',
                    address:'|site|page/|currentPage|/ShowComment?id=|col_taskID|',
                    loadicon:'Загружаю...'
                });
            }
        });
    }
}