var selelem = null;
$().ready(function () {
    // fetch any selected item from the parent, and build an editor page for it
    var elem = tinyMCEPopup.editor.plugins["mee"].getCurrentElement();
    var latex = "";
    var inline = false;
    if (elem) {
        selelem = elem;
        var url = elem.src;
        url = url;
        
        var data = url.substr(url.indexOf('?'));
        var data = unescape(data.substr(1));
        var data = $.parseJSON(data);

        latex = unencodeQuotes(data.latex);
        inline = data.inline;
    } else {
        var url = document.URL;
        var tail = url.substr(url.indexOf('?') + 1);
        if (tail == "inline=1")
            inline = true;
    }

    var newinput = $('<input>');

    newinput.attr('name', 'eleminput');
    newinput.addClass('mee');
    newinput.addClass('activate');
    newinput.addClass('tabopen:symbols');
    $('#editor_cont').append(newinput);
    $(newinput)[0].value = latex;

    // if we are coming from a span element
    if (inline)
        newinput.addClass('inline');

    var url = document.URL;
    var tail = url.substr(url.indexOf('?') + 1);
    if (!elem && tail == "inline=1")
        newinput.addClass('inline');

    newinput.attr('latex', latex);

    if (latex) {
        document.getElementById("insert").style.display = "none";
    } else {
        document.getElementById("update").style.display = "none";
    }

    setTimeout("MEE.Base.Render();", 1);
});

function updateMME() {
    insertMME();
}


function insertMME() {
    var edit = MEE.Base.edits[0];
    var html = "";

    var node = tinyMCEPopup.editor.selection.getNode();
    var fontsize = $(node).css('font-size');

    var data = {};
    data.inline = edit.inline;
    data.latex = encodeQuotes(edit.latex);
    data.fontsize = fontsize;

    var datatxt = JSON.stringify(data);

    var url = document.URL.substring(0, document.URL.lastIndexOf("/"));
    var src = url+ "/frame.html?" + datatxt;

    var style = 'display:block';
    if(data.inline) {
      style = 'display:inline';
    }
    if (selelem) {
        selelem.src = src;
        $(selelem).attr('src', src);
    } else {
        html = "<iframe class='mee_iframe' style='" + style + "' src='" + src + "' frameborder='0'></iframe>";
        tinyMCEPopup.editor.execCommand('mceInsertContent', true, html);
    }

    $(node).children('iframe').removeAttr('data-mce-style').css('display','inline');
    
    tinyMCEPopup.editor.execCommand('mceRepaint');
    tinyMCEPopup.close();
    

    /*tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
    //tinyMCEPopup.editor.selection.setContent(html);
    //tinyMCEPopup.editor.plugins["mee"].update();
    tinyMCEPopup.close();*/
}

function encodeQuotes(str) {
    str = str.replace(/'/g,'~quot~');
    str = str.replace(/"/g,'~dblquot~');
    return str;
}

function unencodeQuotes(str) {
    str = str.replace(/~quot~/g,"'");
    str = str.replace(/~dblquot~/g,'"');
    return str;
}