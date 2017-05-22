
var curPage;

function evFilter() {
    mwceAPI.genIn({
        element:'evBody',
        address:'|site|page/|currentPage|/GetList',
        type:'POST',
        data:$('#filterEvTr input[type=date],#filterEvTr input[type=checkbox],#filterEvTr select').serialize() + '&curPage='+curPage,
        loadicon:'<tr><td colspan="7" style="text-align: center; color:green">Загружаю</td></tr>'
    })
}

function paginate(id) {
    curPage = id;
    evFilter();
}

function pushEvent(id) {

    mwceAPI.genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/PushEvent?id='+id,
        before:function () {
            document.querySelector('#evPosTd_'+id).style.color = 'red';
            document.querySelector('#evPosTd_'+id).style.opacity = 0.8;
        },
        callback:function (r) {
            try{
                var receive = JSON.parse(r);
                if(receive['status'] == 1){
                    document.querySelector('#evPosTd_'+id).style.color = 'black';
                    document.querySelector('#evPosTd_'+id).style.opacity = 1;
                }
                else{

                    document.querySelector('#evPosTd_'+id).style.opacity = 0.3;
                }

            }
            catch (e){
                mwceAPI.alert(e.message,'Ошибка!');
            }

        }
    });
}

function goToLink(id,link) {
    mwceAPI.genIn({
        noresponse:true,
        address:'|site|page/|currentPage|/ReadEvent?id='+id,
        callback:function () {
            document.querySelector('#evPos_'+id).remove();
        }
    });

    document.querySelector('#redirectForm').action='|site|'+link;
    document.querySelector('#redirectForm').submit();
}

function isReadEvent(id) {
    mwceAPI.confirm({
        title:'Подтвердить',
        text:'Вы уверены, что хотите отметить выбранное событие как прочитанное?',
        buttons:{
            'Да':function () {
                mwceAPI.confirm.close();
                mwceAPI.genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/ReadEvent?id='+id,
                    before:function () {
                        document.querySelector('#evPos_'+id).style.opacity = 0.2;
                    },
                    callback:function () {
                        document.querySelector('#evPos_'+id).remove();
                    }
                });
            },
            'Нет':function () {
                mwceAPI.confirm.close();
            }
        }
    });
}
function isReadAllEvent() {
    mwceAPI.confirm({
        title:'Подтвердить',
        text:'Вы уверены, что хотите отметить все события как прочитанное?',
        buttons:{
            'Да':function () {
                mwceAPI.confirm.close();
                mwceAPI.genIn({
                    address:'|site|page/|currentPage|/SetAllRead',
                    callback:function () {
                        evFilter();
                    }
                });
            },
            'Нет':function () {
                mwceAPI.confirm.close();
            }
        }
    });
}

$(document).ready(function () {
    curPage = 1;
    evFilter();
});