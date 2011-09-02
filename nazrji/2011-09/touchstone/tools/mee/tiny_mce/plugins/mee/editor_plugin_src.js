/**
* editor_plugin_src.js
*
* Copyright 2009, Moxiecode Systems AB
* Released under LGPL License.
*
* License: http://tinymce.moxiecode.com/license
* Contributing: http://tinymce.moxiecode.com/contributing
*/
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
                    //$(main).css('background-color', '');
                    //$(mee_currentElement).css('border', 'none');
                }
                if (n && n != mee_currentElement) {

                    // iterate up the parent elements to try and find a div or span with class of mee

                    function findMee(element) {
                        if ($(element).hasClass('mee'))
                            return element;
                        if (!element.parentNode)
                            return null;
                        return findMee(element.parentNode);
                    }
                    function findMeeCont(element) {
                        if ($(element).hasClass('mee_tinymce_cont'))
                            return element;
                        if (!element.parentNode)
                            return null;
                        return findMeeCont(element.parentNode);
                    }
                    var mee = findMee(n);

                    var active = false;
                    if (mee) {
                        active = true;
                        mee_currentElement = mee;

                        // highlight the current element in some way

                        //var main = $(mee).data('main');
                        //$(main).css('background-color', '#c0c0ff');
                        //$(mee).css('border', '1px solid blue');
                        //ed.selection.select(mee);
                        //ed.selection.setNode(mee);
                    } else {
                        mee_currentElement = null;
                    }

                    var meecont = findMeeCont(n);
                    if (meecont) {
                        // we are in the overlay bit at the end of the document, this is not allowed. MOVE!
                        var plugin = ed.plugins["mee"];
                        ed.selection.setRng(plugin.rng);
                    }
                    cm.setActive('mee', active);
                    cm.setActive('meeinline', active);
                } else {
                    mee_currentElement = null;
                }
            });

            // add stylesheets when document is created
            ed.onPreInit.add(function (ed) {
                var addhtml = '<link rel="stylesheet" type="text/css" href="mee/css/main.css"><\/link>'
                addhtml += '<link rel="stylesheet" type="text/css" href="mee/css/fonts.css"><\/link>'
                ed.dom.doc.getElementsByTagName("head")[0].insertAdjacentHTML("beforeEnd", addhtml);
            });

            // when content is loaded, add the render js
            ed.onSetContent.add(function (ed, o) {
                // Replaces all a characters with b characters
                var js = "<script>$().ready(function () { setTimeout(\"MEE.Base.Render($('#" + ed.id + "_ifr')[0].contentDocument.body, $('#" + ed.id + "_ifr')[0].contentDocument);\",300) });</script>";
                //setTimeout("MEE.Base.Render($('#" + ed.id + "_ifr')[0].contentDocument.body, $('#" + ed.id + "_ifr')[0].contentDocument);",500)
                $(ed.getBody()).append(js);

                // store the start selection for later use
                var plugin = ed.plugins["mee"];
                plugin.rng = ed.selection.getRng();
            });


            ed.onChange.add(function (ed, o) {
                MEE.Base.Render(ed.getBody(), ed.getDoc());
            });
            ed.onKeyUp.add(function (ed, o) {
                MEE.Base.Render(ed.getBody(), ed.getDoc());
            });

            /*ed.onPostRender.add(function (ed, o) {
            // Replaces all a characters with b characters
            //alert("onPostRender");
            });
            */
            ed.onSaveContent.add(function (ed, content) {
                var doc = $('<div>');
                doc.html(content.content);
                $(doc).find('.mee_tinymce_cont').remove();
                $(doc).find('.mee').each(function () {
                    var latex = $(this).attr('title');
                    $(this).html(latex);
                    $(this).removeAttr('id');
                    $(this).removeAttr('style');
                    $(this).removeAttr('title');
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
            MEE.Base.Render(this.editor.getBody(), this.editor.getDoc());
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