// MEE Loader


function require(jspath) {
    document.write('<script type="text/javascript" src="' + jspath + '"><\/script>');
}

// load jquery and plugins needed
require("mee/jquery/jquery-1.6.1.js");
require("mee/jquery/jquery.caret.js");
require("mee/jquery/jquery.class.js");
require("mee/jquery/jquery.pxem.js");

require("mee/js/mee.main.js");
require("mee/js/mee.main.edit.js");
require("mee/js/mee.main.display.js");
require("mee/js/mee.tools.html.js");
require("mee/js/mee.parser.js");
require("mee/js/mee.tex.js");

require("mee/js/mee.elem.js");
require("mee/js/mee.elem.accent.js");
require("mee/js/mee.elem.space.js");

require("mee/js/mee.elemset.js");
require("mee/js/mee.elemset.normal.js");
require("mee/js/mee.elemset.basic.js");
require("mee/js/mee.elemset.array.js");

require("mee/js/mee.toolbar.js");
require("mee/js/mee.toolbar.test.js");
require("mee/js/mee.base.js");

// toolbar definitions

var debug_text = "";

// on page load call MEE init
$().ready(function () {
    // search page for items to display and create an instnce of MEE per item
    var mee = new MEE.Base();

    $('.debug').html(debug_text);
    //alert(debug_text);
    //MEE.extend("TESTING");
});
