var filesadded = "" //list of files already added
var mee_baseurl = null;

function findBaseUrl() {
    $('head, body').children('script').each(function () {
        var script = this.src;
        var tofind = "/mee.js";
        if (script.substr(script.length - tofind.length) == tofind) {
            mee_baseurl = script.substr(0, script.length - tofind.length - 2);
            return;
        }
        var tofind = "/mee.php";
        if (script.substr(script.length - tofind.length) == tofind) {
            mee_baseurl = script.substr(0, script.length - tofind.length - 2);
            return;
        }
    });

    if (!mee_baseurl)
        mee_baseurl = "mee/";
}
findBaseUrl();

document.write('<link rel="stylesheet" type="text/css" href="' + mee_baseurl + 'css/combined.css"><\/link>');

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
