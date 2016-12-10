
var isSerialProject = false;
var checkedSerial = false;

function checkSerialProject() {
    checkedSerial = true;
}

function beforeAdd() {

    if(document.querySelector('#projectName').value.trim().length >0){
        if(document.querySelector('#isSerial').checked && !checkedSerial){
            return;
        }

        document.querySelector('#addingForm').submit();
    }
    else
    {
        mwce_alert('Не заполнено название проекта','Внимание!');
    }

}