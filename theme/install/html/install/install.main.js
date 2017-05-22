
var inProcess = false;

function chosenBuild() {
    mwceAPI.genIn({
        element:'buildParams',
        address:'|site|page/|currentPage|/GetBuildInfo',
        type:'POST',
        data:$('#buildList select').serialize(),
        loadicon:'|lng_load|'
    })
}
function beginSetup() {
    if(!inProcess){
        mwceAPI.genIn({
            noresponse:true,
            address:'|site|page/|currentPage|/Setup',
            type:'POST',
            data:$('#buildList select').serialize()+'&'+$('#setupForm').serialize(),
            loadicon:'|lng_load|',
            before:function () {
                document.querySelector('#resultSetup').innerHTML = '<img src="|site|theme/imgs/preLoad.gif" border="0" style="width: 32px;">';
                inProcess = true;
            },
            callback:function (r) {
                document.querySelector('#resultSetup').innerHTML = '';
                try{
                    var receive = JSON.parse(r);
                    if(receive['error']!=undefined)
                        mwceAPI.alert(receive['error'],'|lng_errTitle|');
                    else if(receive['success'] != undefined){
                        document.querySelector('#resultSetup').innerHTML = '<b class="text-success">'+receive['success']+'</b>';
                        document.querySelector('#startInstallButton').disabled = true;
                    }
                }
                catch (e){
                    mwceAPI.alert('|lng_err5|','|lng_errTitle|');
                    console.error(e.message);
                }
                finally {
                    inProcess = false;
                }
            }
        })
    }
    else
        mwceAPI.alert('|lng_toSlow|','|lng_errTitle|');

}