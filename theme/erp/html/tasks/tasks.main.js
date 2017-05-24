
var curPage;

function filterTask() {
    mwce.genIn({
        element:'TaskBody',
        address:'|site|page/|currentPage|/GetList',
        loadicon:'<tr><td colspan="8" style="text-align: center; color:green">Загружаюсь...</td></tr>',
        type:'POST',
        data:$('#taskF1 select,#taskF1 input[type=date],#taskF1 input[type=text],#taskF2 input[type=text],#taskF2 input[type=checkbox],#taskF2 select').serialize()+'&curPage='+curPage
    })
}

function paginate(id) {
    curPage = id;
    filterTask();
}

function taskRedir(id) {
    document.querySelector('#tGoForm').action='|site|page/|currentPage|/In.html?id='+id;
    document.querySelector('#tGoForm').submit();
}

function addTask() {
    $('#dialogBx').dialog({
        title:'Добавить задачу',
        modal:true,
        width:600,
        resizable:false,
        open:function () {
            mwce.genIn({
                element:'dialogBx',
                address:'|site|page/|currentPage|/add'
            });
        },
        close:function () {
            $(this).dialog('destroy');
            filterTask();
        },
        buttons:{
            'Добавить':function () {

                var dn = new Date();
                var dw = new Date(document.querySelector('#_endDate').value + ' ' + document.querySelector('#_endTime').value);

                if(document.querySelector('#tbUserList') != undefined
                    && document.querySelector('#_taskName').value.trim().length > 0
                    && document.querySelector('#_endDate').value.trim().length > 0
                    && document.querySelector('#_endTime').value.trim().length > 0
                    && dn.getTime() < dw.getTime()
                ){
                    mwce.genIn({
                        noresponse:true,
                        address:'|site|page/|currentPage|/Add',
                        type:'POST',
                        data:$('#addTask').serialize(),
                        callback:function(r) {
                            try{
                                var receive = JSON.parse(r);
                                if(receive['error'] != undefined){
                                    mwce.alert(receive['error'],'Внимание!');
                                }
                                else {
                                    $('#dialogBx').dialog('close');
                                }
                            }
                            catch(e) {
                                //console.error(e.message);
                            }
                        }
                    });
                }
                else
                    mwce.alert('Не заполнены важные параметры или выставлена неадекватная дата','Внимание');
            },
            'Отмена':function () {

            }
        }
    });
}

function getCurator(unit) {
    mwce.genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/GetCurators?id='+unit,
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive['error'] != undefined){
                    mwce.alert(receive['error'],'Внимание');
                    $('#tdResp1').empty();
                }
            }
            catch (e){
                $('#tdResp1').empty();
                $('#tdResp1').append(r);
            }
        }
    });
}

function getResp(unit) {
    mwce.genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/GetResps?id='+unit,
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive['error'] != undefined){
                    mwce.alert(receive['error'],'Внимание');
                    $('#tdResp').empty();
                }
            }
            catch (e){
                $('#tdResp').empty();
                $('#tdResp').append(r);
            }
        }
    });
}

$(document).ready(function () {
    curPage = 1;
    filterTask();
});