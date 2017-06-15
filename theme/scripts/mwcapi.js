var mwce = {};

mwce.waitAsync = true;
mwce.debugMode = false;

mwce._isOpenAjax = false;
mwce._openNotify = 0;
mwce.errors = [];
mwce.lang = {};

mwce.ajax = function (params) {

    if(!params["dataType"]){
        params["dataType"] = 'html';
        if(mwce.debugMode)
            console.warn('-> dataType is empty. Default set html');
    }

    if(!params["type"]){
        params["type"] = 'GET';
        if(mwce.debugMode)
            console.warn('-> type is empty. Default set GET');
    }
    else{
        params["type"] = params["type"].toUpperCase();
    }

    if(!params["data"]){
        params["data"] = "";

        if(mwce.debugMode && params["type"] !== 'GET')
            console.warn('-> Maybe send data is empty!');
    }

    if(!params["address"]){
        throw 1;
    }

    if (mwce.waitAsync && mwce._isOpenAjax){
        throw 2;
    }

    $.ajax({
        url: params["address"],
        cache: false,
        type: params["type"],
        data: params["data"],
        dataType: params["dataType"],//xml, json, jsonp, script, html, text
        async: true,
        beforeSend: function(){
            mwce._isOpenAjax = true;

            if(params["loadicon"] && params["element"])
            {
                $('#' + params['element']).empty();
                $('#' + params['element']).append(params['loadicon']);
            }

            if(params["before"]){
                params["before"]();
            }
        },
        success: function(response)
        {
            mwce._isOpenAjax = false;
            if(params["element"])
            {
                $('#' + params["element"]).empty();

                if(params["fade"] !== undefined)
                    $('#' + params["element"]).append(response).fadeIn(params["fade"]);
                else
                    $('#' + params["element"]).append(response);
            }

            if(params["callback"])
            {
                params["callback"](response);
            }

        },
        error:  function(jqXHR, textStatus, errorThrown){

            mwce._isOpenAjax = false;

            if(params["error"])
            {
                params["error"](jqXHR, textStatus, errorThrown);
            }
            else
            {
                if(params["element"]){

                    $("#" + params["element"]).empty();

                    if(textStatus)
                        $("#"+params["element"]).append(textStatus);
                    else
                        $("#"+params["element"]).append("Resource load error. Maybe wrong web address");
                }
                else{

                    if(textStatus)
                        console.error('-> ' + textStatus);
                    else
                        console.error('-> Resource load error. Maybe wrong web address;');
                }
            }
        }
    });
};

mwce.genIn = function (params) {
    if (params["type"]) {
        params["type"] = params["type"].toUpperCase();
    }

    if(params['alertErrors'] === undefined){
        params['alertErrors'] = true;
    }

    try {
        mwce.ajax(params);
    }
    catch (e) {
        if (typeof e == 'number') {
            var msg = mwce.errors[e] ? mwce.errors[e] : 'error ' + e;
            console.warn(' -> ',msg);
            if(params['alertErrors']){
                mwce.alert(msg);
            }
        }
        else {
            console.error(e);
        }
    }
};

mwce.alert = function (msg,title) {

    if(!msg){
        msg = 'em.. message text is empty 0_o';
    }
    if(!title){
        title = mwce.lang['alertTitle'] ? mwce.lang['alertTitle'] : 'Warning!';
    }

    $('<div/>').dialog({
        title: title,
        modal: true,
        buttons: {
            Ok: function() {
                $( this ).dialog( 'close' );
            }
        },
        open:function () {
            this.innerHTML = msg;
        },
        close:function () {
            $( this ).dialog( 'destroy' );
        }
    });
};

mwce.confirm = function (params){
    mwce.confirm.close();

    if(params instanceof Object){

        if(!params['title']){
            params['title'] = mwce.lang['alertTitle'] ? mwce.lang['alertTitle'] : 'Attention!';
        }

        if(!params['text']){
            console.warn('[mwce.confirm]: params[text] is empty!');
            return;
        }

        if(params['buttons'] === undefined || !(params['buttons'] instanceof Object))
        {
            console.warn('[mwce.confirm]: params[buttons] is empty or wrong!');
            return;
        }

        if(!params['width'])
            params['width'] = 400;

        if(!params['height'])
            params['height'] = 'auto';

        $('<div/>').dialog({
            resizable: false,
            height: params['height'],
            width: params['width'],
            title:params['title'],
            modal: true,
            buttons:params['buttons'],
            open:function () {
                mwce.confirm._body = this;
                this.innerHTML = params['text'];
            },
            close:function () {
                mwce.confirm._body = undefined;
                $(this).dialog('destroy');
            }
        });
    }
    else{
        console.warn('[mwce_confirm]: params must be a JSON');
    }
};

mwce.notify = function (msg,title,type,callback) {

    if(!document.querySelector('#_mwce_notifiesDiv')){
        var MainNotice = document.createElement('div');
        MainNotice.style.position = 'fixed';
        MainNotice.style.width = '350px';
        MainNotice.style.height = 'auto';
        MainNotice.style.bottom = '5px';
        MainNotice.style.left = '5px';
        MainNotice.id = '_mwce_notifiesDiv';

        document.body.appendChild(MainNotice);

        console.log('-> [mwce]: append notice div');
    }

    mwce._openNotify ++;
    var types = ['info','warning','danger','success'];

    if(!type){
        type = 0;
    }

    if(!title){
        switch(type) {
            case 0:
                title = 'Информация';
                break;
            case 1:
                title = 'Внимание';
                break;
            case 2:
                title = 'Ошибка';
                break;
            case 3:
            case 4:
                title = 'Уведомление';
                break;
        }

    }

    msg = '<strong style="margin-right: 15px;">' + title + '</strong><p>' + msg + '</p>';

    var notice = document.createElement('div');
    notice.classList.add('alert','alert-'+types[type]);
    notice.style.float = 'left';
    notice.style.minWidth = '250px';
    notice.innerHTML = "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+msg;

    $(notice).on('closed.bs.alert', function () {
        mwce._openNotify--;
    });

    setTimeout(function () {
        $(notice).fadeOut(3000,function () {
            $(notice).alert('close');
            if(callback)
                callback();
        });

    }, 2000);

    document.querySelector('#_mwce_notifiesDiv').append(notice);
};

mwce.confirm.close = function () {
    if(mwce.confirm._body){
        $(mwce.confirm._body).dialog('close');
    }
};

mwce.replacePoint = function (obj) {
    obj.value = obj.value.replace(/\,/, ".");
};

mwce.AddObjNDS = function (objID) {
    var _o = document.querySelector('#' + objID);
    _o.value = (_o.value * 1.18).toFixed(2);
};

mwce.DelObjNDS = function (objID) {
    var _o = document.querySelector('#' + objID);
    _o = (_o.value * 100 / 118).toFixed(2);
};

mwce.ObjNumbersOnly = function (obj) {
    if(obj.value){
        var value = obj.value;
        if(value.length>0)
        {
            var rep = /[,;":'a-zA-Zа-яА-Я\s]/;
            if (rep.test(value)) {
                value = value.replace(rep, '');
                obj.value = value;
            }
        }
    }
};

mwce.implode = function ( glue, pieces) {
    return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
};

mwce.htmlspecialchars_decode = function (string, quoteStyle) {
    /**
     *  eslint-disable-line camelcase
     *  discuss at: http://locutus.io/php/htmlspecialchars_decode/
     *  original by: Mirek Slugen
     *  improved by: Kevin van Zonneveld (http://kvz.io)
     *  bugfixed by: Mateusz "loonquawl" Zalega
     *  bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
     *  bugfixed by: Brett Zamir (http://brett-zamir.me)
     *  input by: ReverseSyntax
     *  input by: Slawomir Kaniecki
     *  input by: Scott Cariss
     *  input by: Francois
     *  input by: Ratheous
     *  input by: Mailfaker (http://www.weedem.fr/)
     *  revised by: Kevin van Zonneveld (http://kvz.io)
     *  reimplemented by: Brett Zamir (http://brett-zamir.me)
     *  example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
     *  returns 1: '<p>this -> &quot;</p>'
     *  example 2: htmlspecialchars_decode("&amp;quot;")
     *  returns 2: '&quot;'
     */

    var optTemp = 0;
    var i = 0;
    var noquotes = false;

    if (typeof quoteStyle === 'undefined') {
        quoteStyle = 2
    }
    string = string.toString()
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quoteStyle === 0) {
        noquotes = true
    }
    if (typeof quoteStyle !== 'number') {
        // Allow for a single string or an array of string flags
        quoteStyle = [].concat(quoteStyle);
        for (i = 0; i < quoteStyle.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quoteStyle[i]] === 0) {
                noquotes = true
            } else if (OPTS[quoteStyle[i]]) {
                optTemp = optTemp | OPTS[quoteStyle[i]]
            }
        }
        quoteStyle = optTemp
    }
    if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
        // PHP doesn't currently escape if more than one 0, but it should:
        string = string.replace(/&#0*39;/g, "'");
        // This would also be useful here, but not a part of PHP:
        // string = string.replace(/&apos;|&#x0*27;/g, "'");
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"')
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');

    return string
};

//todo: в релизе сжать код.

mwce.errors[1] = 'Не указан параметр \'address\' в ajax.';
mwce.errors[2] = 'Предыдущее действие еще не завершено. Пожалуйста, попробуйте еще раз позднее';


mwce.lang = {
    'alertTitle' : 'Внимание'
};

var _tmplCache_ = {};
templateFunct= function(str, data) {
/// <summary>
/// Client side template parser that uses &lt;#= #&gt; and &lt;# code #&gt; expressions.
/// and # # code blocks for template expansion.
/// NOTE: chokes on single quotes in the document in some situations
///       use &amp;rsquo; for literals in text and avoid any single quote
///       attribute delimiters.
/// </summary>
/// <param name="str" type="string">The text of the template to expand</param>
/// <param name="data" type="var">
/// Any data that is to be merged. Pass an object and
/// that object's properties are visible as variables.
/// </param>
/// <returns type="string" />
    var err = "";
    try {
        var func = _tmplCache_[str];
        if (!func) {
            var strFunc =
                "var p=[],print=function(){p.push.apply(p,arguments);};" +
                "with(obj){p.push('" +
                //                        str
                //                  .replace(/[\r\t\n]/g, " ")
                //                  .split("<#").join("\t")
                //                  .replace(/((^|#>)[^\t]*)'/g, "$1\r")
                //                  .replace(/\t=(.*?)#>/g, "',$1,'")
                //                  .split("\t").join("');")
                //                  .split("#>").join("p.push('")
                //                  .split("\r").join("\\'") + "');}return p.join('');";

                str.replace(/[\r\t\n]/g, " ")
                    .replace(/'(?=[^#]*#>)/g, "\t")
                    .split("'").join("\\'")
                    .split("\t").join("'")
                    .replace(/<#=(.+?)#>/g, "',$1,'")
                    .split("<#").join("');")
                    .split("#>").join("p.push('")
                + "');}return p.join('');";

            //alert(strFunc);
            func = new Function("obj", strFunc);
            _tmplCache_[str] = func;
        }
        return func(data);
    } catch (e) { err = e.message; }
    return "< # ERROR: " + err + " # >";
};