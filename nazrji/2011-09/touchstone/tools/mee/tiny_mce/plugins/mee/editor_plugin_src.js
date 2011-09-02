/**
* editor_plugin_src.js
*
* Copyright 2009, Moxiecode Systems AB
* Released under LGPL License.
*
* License: http://tinymce.moxiecode.com/license
* Contributing: http://tinymce.moxiecode.com/contributing
*/

function getFrameForDocument(document) {
    var w = document.defaultView || document.parentWindow;
    var frames = w.parent.document.getElementsByTagName('iframe');
    for (var i = frames.length; i-- > 0; ) {
        var frame = frames[i];
        try {
            var d = frame.contentDocument || frame.contentWindow.document;
            if (d === document)
                return frame;
        } catch (e) { }
    }
}


function updateMEE(frame, align, inline) {
    var iframe = getFrameForDocument(frame.document);
    var eqn = $(frame.document).find('#eqn_inner');

    if (inline) {
        $(iframe).css('width', align.width + 'px');
    } else {
        $(iframe).css('width', '100%');
    }
    $(iframe).css('height', align.height + 'px');
    $(iframe).css('vertical-align', 'middle');
}

function clickMEEiFrame(frame) {
    if (frame.document)
        frame = getFrameForDocument(frame.document);
    else
        frame = $(frame).children()[0];

    // we have the iframe element that we have clicked on 
    // need to set the selection to it
    var e = tinymce.activeEditor;
    e.selection.select(frame);
}

(function () {
    tinymce.PluginManager.requireLangPack('mee');

    var mee_currentElement = null;

    tinymce.create('tinymce.plugins.MEE', {
        init: function (ed, url) {
            var t = this;
            t.editor = ed;

            // Register commands
            ed.addCommand('mceMEE', function () {
                ed.windowManager.open({
                    file: url + '/dialog.html',
                    width: 870,
                    height: 550,
                    inline: 1
                }, {
                    plugin_url: url // Plugin absolute URL
                    //some_custom_arg : 'custom arg' // Custom argument
                });
            });

            ed.addCommand('mceMEEInline', function () {
                ed.windowManager.open({
                    file: url + '/dialog.html?inline=1',
                    width: 870,
                    height: 550,
                    inline: 1
                }, {
                    plugin_url: url // Plugin absolute URL
                    //some_custom_arg : 'custom arg' // Custom argument
                });
            });

            // Register buttons
            ed.addButton('mee', { title: 'mee.desc', cmd: 'mceMEE', image: url + '/img/mee.png' });
            ed.addButton('meeinline', { title: 'mee.descinline', cmd: 'mceMEEInline', image: url + '/img/meeinline.png' });

            // Add a node change handler, selects the button in the UI when a image is selected
            ed.onNodeChange.add(function (ed, cm, n) {
                if (n == mee_currentElement)
                    return;

                if (mee_currentElement) {
                    // unhighlight the current element in some way

                    //var main = $(mee_currentElement).data('main');
                    //$(mee_currentElement).css('background-color', '');
                    $(mee_currentElement).css('border', '1px solid transparent');
                }
                if (n && n != mee_currentElement) {

                    // iterate up the parent elements to try and find a div or span with class of mee

                    function findMee(element) {
                        if ($(element).hasClass('mee_iframe'))
                            return element;
                        if (!element.parentNode)
                            return null;
                        return findMee(element.parentNode);
                    }
                    var mee = findMee(n);

                    /*if (!mee)
                    {
                        var ps = n.previousSibling;
                        if ($(ps).hasClass('mee_iframe'))
                            mee = ps;
                    }*/
                    var active = false;
                    if (mee) {
                        active = true;
                        mee_currentElement = mee;

                        $(mee).css('border', '1px solid blue');
                    } else {
                        mee_currentElement = null;
                    }

                    cm.setActive('mee', active);
                    cm.setActive('meeinline', active);
                } else {
                    mee_currentElement = null;
                }
            });

            // when content is loaded, add the render js
            ed.onSetContent.add(function (ed, o) {
                var addhtml = '<link rel="stylesheet" type="text/css" href="mee/css/main.css"><\/link>';
                addhtml += '<link rel="stylesheet" type="text/css" href="mee/css/fonts.css"><\/link>';
                $(ed.getBody()).prepend(addhtml);

                var body = ed.getBody();
                var elems = $(body).find('.mee');
                for (var i = 0 ; i < elems.length; i++){
                    var elem = elems[i];
                    var data = new Object();
                    var eltype = elem.tagName;
                    if (eltype == "DIV"){
                        data.inline = false;
                    } else {
                        data.inline = true;
                    }
                    data.latex = $(elem).html();
                    data.fontsize = $(elem).css('font-size');

                    var datatxt = JSON.stringify(data);

                    var html = "<iframe src='tiny_mce/plugins/mee/frame.html#" + datatxt + "' width='1000' height='5' frameborder='0'></iframe>";
                    var newelem = $(html);

                    $(newelem).addClass('mee_iframe');
                    $(newelem).insertBefore(elem);
                    if (!data.inline)
                        newelem.attr('align','middle');

                    $(elem).remove();
                    

                }
            });

            ed.onVisualAid.add(function (ed, o) {
                //alert(o);
            });
 
            ed.onSaveContent.add(function (ed, content) {
                var doc = $('<div>');
                doc.html(content.content);
                //$(doc).find('.mee_iframe').remove();
                $(doc).find('.mee_iframe').each(function () {
                    var src = $(this).attr('src');
                    var data = src.substr(src.indexOf('#'));
                    var data = data.substr(1);
                    var data = JSON.parse(data);

                    $(this).removeClass('mee_iframe');
                   
                    if (data.inline) {
                        var newelem = $('<span>');
                    } else {
                        var newelem = $('<div>');
                    }
                    $(newelem).html(data.latex);
                    $(newelem).addClass('mee');
                    $(newelem).insertBefore(this);
                    $(this).remove();
                });
                content.content = doc.html();

                //alert("onSaveContent");
            });

            ed.onSubmit.add(function (ed) {
                //alert("onSubmit");
            });
        },

        // updates the editor
        update: function () {
            // find all
            //MEE.Base.Render(this.editor.getBody(), this.editor.getDoc());
        },

        getInfo: function () {
            return {
                longname: 'MEE',
                author: 'Adam Clarke',
                version: tinymce.majorVersion + "." + tinymce.minorVersion
            };
        },

        getCurrentElement: function () {
            return mee_currentElement;
        }
        // Private methods
    });

    // Register plugin
    tinymce.PluginManager.add('mee', tinymce.plugins.MEE);
})();