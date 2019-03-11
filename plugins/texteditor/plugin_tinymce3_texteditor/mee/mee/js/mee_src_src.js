// MEE Loader

function require(jspath) {
    document.write('<script type="text/javascript" src="' + jspath + '"><\/script>');
}

// load jquery and plugins needed
checkloadjscssfile("jquery/async.js");
checkloadjscssfile("jquery/jquery.caret.js");
checkloadjscssfile("jquery/jquery.class.js");
checkloadjscssfile("jquery/jquery.pxem.js");
checkloadjscssfile("jquery/jquery.scale9.js");
checkloadjscssfile("jquery/jquery.textarea.js");
checkloadjscssfile("jquery/jquery.xml2json.js");
checkloadjscssfile("jquery/jquery.cookie.js");
checkloadjscssfile("jquery/json2.js");

checkloadjscssfile("js/mee.main.js");
checkloadjscssfile("js/mee.main.edit.js");
checkloadjscssfile("js/mee.main.display.js");
checkloadjscssfile("js/mee.tools.html.js");
checkloadjscssfile("js/mee.parser.js");
checkloadjscssfile("js/mee.data.js");
checkloadjscssfile("js/mee.data.tex.js");
checkloadjscssfile("js/mee.data.chars.js");

checkloadjscssfile("js/mee.elem.js");
checkloadjscssfile("js/mee.elem.accent.js");
checkloadjscssfile("js/mee.elem.boxed.js");
checkloadjscssfile("js/mee.elem.space.js");
checkloadjscssfile("js/mee.elem.input.js");
checkloadjscssfile("js/mee.elem.answer.js");
checkloadjscssfile("js/mee.elem.bond.js");

checkloadjscssfile("js/mee.elemset.js");
checkloadjscssfile("js/mee.elemset.normal.js");
checkloadjscssfile("js/mee.elemset.basic.js");
checkloadjscssfile("js/mee.elemset.array.js");

checkloadjscssfile("js/mee.toolbar.js");
checkloadjscssfile("js/mee.base.js");
checkloadjscssfile("js/mee.images.js");

checkloadjscssfile("js/mee.undo.js");
checkloadjscssfile("js/mee.symhist.js");
checkloadjscssfile("js/mee.font.js");
checkloadjscssfile("js/mee.maxima.js");

checkloadjscssfile("css/toolbar.css");
checkloadjscssfile("css/main.css");
checkloadjscssfile("css/edit.css");
checkloadjscssfile("css/fonts.css");


// toolbar definitions
if (!Array.indexOf) {
    Array.prototype.indexOf = function (obj) {
        for (var i = 0; i < this.length; i++) {
            if (this[i] == obj) {
                return i;
            }
        }
        return -1;
    } 
}

var debug_text = "";

// on page load call MEE init
$().ready(function () {
    // search page for items to display and create an instnce of MEE per item
    if (typeof no_auto_mee == 'undefined' || !no_auto_mee) {
        //setTimeout("MEE.Base.Render(document.body, document);", 1);
        setTimeout("MEE.Base.Render();", 1);
    }
    $('.debug').html(debug_text);
});
