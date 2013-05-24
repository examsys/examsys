tinyMCE.init({
    mode : "specific_textareas",
    forced_root_block : 'div',
    force_br_newlines : false,
    force_p_newlines : false,
    //language : window.navigator.language,
    editor_selector : "mceEditor",
    theme : "advanced",
    skin : "default",
    plugins : "mee,table,visualchars,nonbreaking,xhtmlxtras,paste,insertimagequestioneditor",
    // Theme options
    theme_advanced_buttons1 : "cut,copy,paste,|,undo,|,bold,italic,underline,|,sub,sup,|,mee,|,justifyleft,justifycenter,justifyright,|,numlist,bullist,|,insertimagequestioneditor,tablecontrols,|,code",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",

    // Example content CSS (should be your site CSS)
    content_css : cfgRootPath + "/css/editor.css",
    entity_encoding : "named",

    setup : function(ed) {
      ed.onInit.add(function(ed, evt) {

        var dom = ed.dom;
        tinymce.dom.Event.add(dom.getRoot(), 'blur', function(e) {
          // Do something when the editor window is blured.
          tinyMCE.triggerSave();

          if (typeof jQuery != 'undefined') {
            if (typeof $("#" + ed.id).valid != 'undefined') {
              if ($("#" + ed.id).valid() == 1) {
                ed.getBody().style.backgroundColor = "#ffffff";
              } else {
                ed.getBody().style.backgroundColor = "#ffd6d6";
              }
            }
          }
        });
      });

      // If there is no text content, return nothing.
      // After http://alastairc.ac/2010/03/removing-emtpy-html-tags-from-tinymce/
      ed.onPostProcess.add(function(ed, o) {
        var text = o.content;

        if (text != '') {
          text = text.replace(/^(<div>&nbsp;<\/div>\s*)+/, '');
          text = text.replace(/^(<p>&nbsp;<\/p>\s*)+/, '');
          text = text.replace(/^(<div><\/div>\s*)+/, '');
          text = text.replace(/^(<p><\/p>\s*)+/, '');
          text = text.replace(/^(<br \/>\s*)+/, '');
          text = text.replace(/^\s*/, '');
        }
        o.content = text;
      });
    }
});

