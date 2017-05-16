
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