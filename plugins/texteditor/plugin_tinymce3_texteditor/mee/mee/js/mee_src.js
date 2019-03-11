// MEE Loader

function loadjscssfile(filename, filetype) {
    if (!filetype) {
        // no filetype specified, get this from the filename
        var lastdotpos = filename.lastIndexOf(".");
        if (lastdotpos)
            filetype = filename.substr(lastdotpos + 1);
    }
    if (filetype == "js") { //if filename is a external JavaScript file
        document.write('<script type="text/javascript" src="' + mee_baseurl + filename + '"><\/script>');
    }
    else if (filetype == "css") { //if filename is an external CSS file
        document.write('<link rel="stylesheet" type="text/css" href="' + mee_baseurl + filename + '"><\/link>');
    }
}

var filesadded = "" //list of files already added
if (typeof mee_baseurl == "undefined")
    var mee_baseurl = null;

function checkloadjscssfile(filename, filetype) {
    if (!mee_baseurl)
        findBaseUrl();
    if (filesadded.indexOf("[" + filename + "]") == -1) {
        loadjscssfile(filename, filetype)
        filesadded += "[" + filename + "]" //List of files added in the form "[filename1],[filename2],etc"
    }
    else
        alert("file already added!")
}

function findBaseUrl() {
    $('head, body').children('script').each(function () {
        var script = this.src;
        var tofind = "/mee_src.js";
        if (script.substr(script.length - tofind.length) == tofind) {
            mee_baseurl = script.substr(0, script.length - tofind.length - 2);
            return;
        }
    });

    if (!mee_baseurl)
        mee_baseurl = "mee/";
}

findBaseUrl();
 
//uncompressed debug
//loadjscssfile("js/mee_src_src.js",'js');
//compressed live
if($.browser.msie &&  $.browser.version < 9) {
  loadjscssfile("js/mee_src_src.js",'js');
} else {
  loadjscssfile("js/mee.js",'js');
}