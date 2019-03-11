/**
* editor_plugin_src.js
*
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


function updateMEE(frame, w, h , inline) {
    var iframe = $(getFrameForDocument(frame.document));
    var eqn = $(frame.document).find('#eqn_inner');

    iframe.css('width', w + 5 + 'px');
    iframe.css('height', h + 'px');
    iframe.css('vertical-align', 'middle');
}

function clickMEEiFrame(frame) {

    edit = false;
    
    if (frame.document)
        frame = getFrameForDocument(frame.document);
    else
        frame = $(frame).children()[0];

    // we have the iframe element that we have clicked on 
    // need to set the selection to it
    var e = tinymce.activeEditor;
    
    e.selection.select(frame);
    
    if ($.browser.mozilla || $.browser.msie) {
        var plugin = e.plugins["mee"];
        if( plugin.getCurrentElement() == frame) {
          edit = true;
        }
        plugin.nodeChange(e, e.controlManager, frame);
    }
    
    if(edit) {
      e.execCommand('mceMEE');
    } else if(!$.browser.mozilla) {
      e.execCommand('mceMEE');
    }
}

function unencodeQuotes(str) {
    str = str.replace(/~quot~/g,"'");
    str = str.replace(/~dblquot~/g,'"');
    return str;
}

function encodeQuotes(str) {
    str = str.replace(/'/g,'~quot~');
    str = str.replace(/"/g,'~dblquot~');
    return str;
}

(function () {
    tinymce.PluginManager.requireLangPack('mee');

    tinymce.create('tinymce.plugins.MEE', {
        mee_currentElement: null,

        init: function (ed, url) {
            var t = this;
            t.editor = ed;

            // Register commands
            ed.addCommand('mceMEE', function () {
                ed.selection.select(ed.plugins["mee"].getCurrentElement());
                ed.windowManager.open({
                    file: url + '/dialog.html',
                    width: 870,
                    height: 460,
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
                ed.plugins["mee"].nodeChange(ed, cm, n);
            });

            // when content is loaded, add the render js
            ed.onSetContent.add(function (ed, o) {
                //removed 05/03/2013 causing ie to download this files recousivly !
                //var addhtml = '<link rel="stylesheet" type="text/css" href="/tools/mee/mee/css/main.css"><\/link>';
                //addhtml += '<link rel="stylesheet" type="text/css" href="/tools/mee/mee/css/fonts.css"><\/link>';
                //$(ed.getBody()).prepend(addhtml);

                var body = ed.getBody();
                var elems = $(body).find('.mee');
                for (var i = 0 ; i < elems.length; i++){
                    var elem = elems[i];
                    var data = {};
                    var eltype = elem.tagName;
                    if (eltype == "DIV"){
                        data.inline = false;
                    } else {
                        data.inline = true;
                    }
                    data.latex = encodeQuotes($(elem).html());
                    data.fontsize = $(elem).css('font-size');

                    var datatxt = JSON.stringify(data);

                    var html = "<iframe class='mee_iframe' src='" + url + "/frame.html?" + datatxt + "' frameborder='0'></iframe>";
                    var newelem = $(html);

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
                $(doc).find('.mee_iframe').each(function () {
                    var src = $(this).attr('src');
                    var data = src.substr(src.indexOf('?'));
                    var data = data.substr(1);
                    var data = data;
                    var data = $.parseJSON(data);
                    data.latex = unencodeQuotes(data.latex);

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

            });

            ed.onSubmit.add(function (ed) {
            });
        },

        nodeChange: function (ed, cm, n) {
            if (n == this.mee_currentElement)
                return;

            this.mee_lastElement = this.mee_currentElement;
                
            if (this.mee_currentElement) {
                // unhighlight the current element in some way

                //var main = $(mee_currentElement).data('main');
                //$(mee_currentElement).css('background-color', '');
                $(this.mee_currentElement).css('border', '0px solid transparent');
            }
            if (n && n != this.mee_currentElement) {

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
                    this.mee_currentElement = mee;

                    $(mee).css('border', '1px solid blue');
                } else {
                    this.mee_currentElement = null;
                }

                cm.setActive('mee', active);
                cm.setActive('meeinline', active);
            } else {
                this.mee_currentElement = null;
            }
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
            if (this.mee_currentElement)
                return this.mee_currentElement;
            if (this.mee_lastElement)
                return this.mee_lastElement;
            return null;
        }
        // Private methods 
    });

    // Register plugin
    tinymce.PluginManager.add('mee', tinymce.plugins.MEE);
})();