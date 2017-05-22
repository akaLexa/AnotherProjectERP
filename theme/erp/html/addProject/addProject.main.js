
var isSerialProject = false;


function checkSerialProject() {

    var prj = parseInt(document.querySelector('#projectNum').value);
    if (prj !== undefined) {
        mwceAPI.mwceAPI.genIn({
            address:'|site|page/|currentPage|/CheckProjectNum',
            type:'POST',
            data:'projectNum='+prj,
            before:function () {
                document.querySelector('#messageCheck').style.color = 'gray';
                document.querySelector('#messageCheck').innerHTML = 'Загрузка..';
            },
            callback:function (r) {
                document.querySelector('#messageCheck').innerHTML ='';

                try{
                    var receive = JSON.parse(r);
                    if(receive['name'] !== undefined){
                        document.querySelector('#messageCheck').style.color = 'gray';

                        if(document.querySelector('#projectName').value.trim().length >0){
                            document.querySelector('#messageCheck').innerHTML = 'Название серийного проекта: '+receive['name'];
                        }
                        else{
                            document.querySelector('#projectName').value = receive['name'];
                            document.querySelector('#messageCheck').innerHTML = "Проект найден";
                        }
                    }
                    else{
                        document.querySelector('#messageCheck').style.color = 'red';
                        if(receive['error'] !== undefined){
                            document.querySelector('#messageCheck').innerHTML = receive['error'];
                        }
                        else{
                            document.querySelector('#messageCheck').innerHTML = 'Произошла ошибка при проверке';
                        }
                    }
                }
                catch (e)
                {
                    console.error(e.message);
                }
            }
        });
    }
    else
        mwceAPI.alert('Не указан номер проекта');

}

function beforeAdd() {

    if(document.querySelector('#projectName').value.trim().length >0){
        document.querySelector('#addingForm').submit();
    }
    else
    {
        mwceAPI.alert('Не заполнено название проекта');
    }
}