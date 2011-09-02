
$().ready(function () {
    // fetch any selected item from the parent, and build an editor page for it
    var elem = tinyMCEPopup.editor.plugins["mee"].getCurrentElement();
    var latex = $(elem).attr('title');
    var newinput = $('<input>');

    newinput.attr('name', 'eleminput');
    newinput.addClass('mee');
    newinput.addClass('activate');
    $('#editor_cont').append(newinput);
    $(newinput)[0].value = latex;

    // if we are coming from a span element
    if ($(elem).is('span'))
        newinput.addClass('inline');

    var url = document.documentURI
    var tail = url.substr(url.indexOf('?')+1);
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
    var edit = MEE.Base.edits[0];
    var html = "";
    var elem = tinyMCEPopup.editor.plugins["mee"].getCurrentElement();
    //$().remove();
    if (edit.inline)
    {
        html = "<span class='mee'>" + edit.latex + "</span>";
    } else {
        html = "<div class='mee nocomp'>" + edit.latex + "</div>";
    }
    //html = "<iframe src='http://www.google.co.uk' width='300' height='200' />";
    var newelem = $(html);
    $(elem).after(newelem);
    $(elem).remove();
    //tinyMCEPopup.editor.selection.setContent(html);
    tinyMCEPopup.editor.plugins["mee"].update();
    tinyMCEPopup.close();
}


function insertMME() {
    var edit = MEE.Base.edits[0];
    var html = "";
    if (edit.inline) {
        html = "<span class='mee'>" + edit.latex + "</span>&nbsp;";
    } else {
        html = "<div class='mee nocomp'>" + edit.latex + "</div>&nbsp;";
    }
    //html = "<iframe src='http://www.google.co.uk' width='300' height='200' />";
    tinyMCEPopup.editor.selection.setContent(html);
    tinyMCEPopup.editor.plugins["mee"].update();
    tinyMCEPopup.close();
}