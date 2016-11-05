
function genIn(params)
{
    if(params["element"] != undefined || params["noresponse"] == true )
    {
        if(params["address"] != undefined)
        {
            if(params["type"] != undefined)
            {
                params["type"] = params["type"].toUpperCase();
            }
            else
                params["type"] = 'GET';

            if(params["dataType"] != undefined)
            {
                params["dataType"]='html';
            }


            if(params["loadicon"] != undefined)
            {
                $("#"+params["element"]).empty();
                $("#"+params["element"]).append(params["loadicon"]);
            }
            if( params["data"] != undefined)
                indata = params["data"];
            else
                var indata ="";

            if(params["before"] != undefined)
            {
                params["before"]();
            }

            $.ajax({
                url: params["address"],
                cache: false,
                type: params["type"],
                data: indata,
                dataType: params["dataType"],
                async: true,
                success: function(response)
                {
                    if(params["noresponse"] == undefined || params["noresponse"] == false)
                    {

                        $("#"+params["element"]).empty();

                        if(params["fade"] != undefined)
                            $("#"+params["element"]).append(response).fadeIn(params["fade"]);
                        else
                            $("#"+params["element"]).append(response);
                    }

                    if(params["callback"] != undefined)
                    {
                        params["callback"](response);
                    }
                },
                error:  function(){

                    if(params["errcallback"] != undefined)
                    {
                        params["errcallback"]();
                    }
                    else
                    {
                        if(params["noresponse"] == true)
                        {
                            alert("Error 404?");
                        }
                        else
                        {
                            $("#"+params["element"]).empty();
                            $("#"+params["element"]).append("ERROR 404.");
                        }
                    }
                }
            });

        }
        else
            console.error("-> function genIn, parameter 'address' is undefined, action aborted");
    }
    else
        console.error("-> function genIn, parameter 'element' is undefined, action aborted");

}

function replacepoint(obj)
{
    obj.value = obj.value.replace(/\,/, ".");
}

function withNDS_(idfrom,idto)
{

    document.getElementById(idto).value =  (document.getElementById(idfrom).value * 1.18).toFixed(2);

}
function withoutNDS_(idfrom,idto)
{
    document.getElementById(idto).value =  (document.getElementById(idfrom).value*100/118).toFixed(2);
}

function check_s(obj)
{
    if(obj.value != undefined)
    {
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
}

function _implode( glue, pieces ) {

    return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}


function mwce_alert(msg,title) {
    if(title== undefined){
        title = 'Warning!';
    }
    var d = document.createElement('DIV');
    d.id='for_mwce_Alert';
    d.style.display = 'none';
    d.title = title;
    d.innerHTML = msg;
    document.body.appendChild(d);

    $('#for_mwce_Alert').dialog({
        modal: true,
        buttons: {
            Ok: function() {
                $( this ).dialog( 'close' );
            }
        },
        close:function () {
            $( this ).dialog( 'destroy' );
            $('#for_mwce_Alert').remove();
        }
    });
}

function mwce_confirm(params) {

    if(params instanceof Object){

        if(params['title'] == undefined)
            params['title'] ='Attention!';

        if(params['text'] == undefined){
            console.error('[mwce_confirm]: params[text] is empty!');
            return;
        }

        if(params['buttons'] == undefined || !(params['buttons'] instanceof Object))
        {
            console.error('[mwce_confirm]: params[buttons] is empty or wrong!');
            return;
        }

        if(params['width'] == undefined)
            params['width'] = 400;

        if(params['height'] == undefined)
            params['height'] = "auto";

        var d = document.createElement('DIV');
        d.id='for_mwce_confirm';
        d.style.display = 'none';
        d.title = params['title'];
        d.innerHTML = params['text'];
        document.body.appendChild(d);

        $('#for_mwce_confirm').dialog({
            resizable: false,
            height: params['height'],
            width: params['width'],
            modal: true,
            buttons:params['buttons'],
            close:function () {
                $(this).dialog('destroy');
                $('#for_mwce_confirm').remove();
            }
        });
    }
    else{
        console.error('[mwce_confirm]: params must be a JSON');
    }
}

mwce_confirm.close = function () {
    $('#for_mwce_confirm').dialog('close');
};

function htmlspecialchars_decode(string, quoteStyle) {
    // eslint-disable-line camelcase
    //       discuss at: http://locutus.io/php/htmlspecialchars_decode/
    //      original by: Mirek Slugen
    //      improved by: Kevin van Zonneveld (http://kvz.io)
    //      bugfixed by: Mateusz "loonquawl" Zalega
    //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //         input by: ReverseSyntax
    //         input by: Slawomir Kaniecki
    //         input by: Scott Cariss
    //         input by: Francois
    //         input by: Ratheous
    //         input by: Mailfaker (http://www.weedem.fr/)
    //       revised by: Kevin van Zonneveld (http://kvz.io)
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    //        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
    //        returns 1: '<p>this -> &quot;</p>'
    //        example 2: htmlspecialchars_decode("&amp;quot;")
    //        returns 2: '&quot;'

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
}