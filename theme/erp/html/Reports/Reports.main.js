function openReport(page){
    if(page !== '0'){
        mwceAPI.genIn({
            element:'reportContent',
            address:'|site|page/'+page,
            loadicon:'Загружаю...'
        });
    }
    else{
        document.querySelector('#reportContent').innerHTML='';
    }
}