var selelem = null;
$().ready(function () {
    // fetch any selected item from the parent, and build an editor page for it
    var elem = tinyMCEPopup.editor.plugins["mee"].getCurrentElement();
    var latex = "";
    var inline = false;
    if (elem) {
        selelem = elem;
        var url = elem.src;
        url = unescape(url);
        
        var data = url.substr(url.indexOf('?'));
        var data = data.substr(1);
        var data = $.parseJSON(data);

        latex = data.latex;
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

    var data = new Object();
    data.inline = edit.inline;
    data.latex = edit.latex;
    data.fontsize = fontsize;

    var datatxt = JSON.stringify(data);

    var src = "/tools/tinymce/jscripts/tiny_mce/plugins/mee/frame.html?" + datatxt;
    if (selelem) {
        selelem.src = src;
        $(selelem).attr('src', src);
        //$selelem).refresh();
    } else {

        if (edit.inline) {
            html = "<iframe class='mee_iframe' src='" + src + "' frameborder='0'></iframe>";
        } else {
            html = "<p><iframe class='mee_iframe' src='" + src + "' frameborder='0'></iframe></p>";
        }

        tinyMCEPopup.editor.execCommand('mceInsertContent', true, html);
    }

    tinyMCEPopup.editor.execCommand('mceRepaint');
    tinyMCEPopup.close();
    

    /*tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
    //tinyMCEPopup.editor.selection.setContent(html);
    //tinyMCEPopup.editor.plugins["mee"].update();
    tinyMCEPopup.close();*/
}