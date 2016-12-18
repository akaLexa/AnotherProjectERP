var currentTab;

$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {

    var curID = e.target.id;

    genTabContent(curID);
});

$(document).ready(function () {
    genTabContent('|defaultTab|')
});

function genTabContent(tab) {
    if(tab.length >0){

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


                        break;
                }
                currentTab = tab;
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