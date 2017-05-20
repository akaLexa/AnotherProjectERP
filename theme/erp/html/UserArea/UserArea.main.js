
var currentTab;

$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {

    var curID = e.target.id;

    genTabContent(curID);
});
$(document).ready(function () {
    genTabContent('GetMain');
});

function genTabContent(tab) {

    currentTab = tab;
    genIn({
        element:'tab_content',//+currentTab.toLowerCase(),
        address:'|site|page/|currentPage|/'+currentTab,
        loadicon:'<div style="width: 100%; text-align: center;color:green; margin-top:100px;">Загружаю...</div>',
        callback:function () {

        }
    });
}

function deleteImg() {
    mwce_confirm({
        title:'Требуется подтверждение',
        text:'Вы действительно хотите удалить свою фотографию? Внимание, удалить фото по умолчанию не удастся!',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/DelPhoto',
                    callback:function () {
                        window.location.reload();
                    }
                })
            },
            'Нет':function () {
                mwce_confirm.close()
            }
        }
    });
}

function ChooseDep() {
    mwce_confirm({
        title:'Требуется подтверждение',
        text:'Вы уверены, что хотите изменить режим замещения?',
        buttons:{
            'Да':function () {
                genIn({
                    noresponse:true,
                    address:'|site|page/|currentPage|/GetMain',
                    type:'POST',
                    data:'depUser='+document.querySelector('#depUser').value,
                    callback:function () {
                        window.location.reload();
                    }
                })
            },
            'Нет':function () {
                mwce_confirm.close()
            }
        }
    });
}