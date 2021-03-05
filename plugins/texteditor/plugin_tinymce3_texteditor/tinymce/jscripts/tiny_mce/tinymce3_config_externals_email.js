requirejs(['tinyMCE', 'rogoconfig', 'jquery'], function (tinyMCE, config, $) {
    tinyMCE.init({
        mode: "specific_textareas",
        relative_urls: false,
        remove_script_host: false,
        forced_root_block: 'div',
        force_br_newlines: false,
        force_p_newlines: false,
        editor_selector: "editorStandard",
        theme: "advanced",
        language : 'en',
        language_load : true,
        skin: "default",
        plugins: "table,visualchars,nonbreaking,xhtmlxtras,paste",
        // Theme options
        theme_advanced_buttons1: "cut,copy,paste,|,undo,|,bold,italic,underline,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,numlist,bullist,|,tablecontrols,|,code",
        theme_advanced_buttons2: "",
        theme_advanced_buttons3: "",
        theme_advanced_toolbar_location: "top",
        theme_advanced_toolbar_align: "left",
        theme_advanced_path: false,
        theme_advanced_statusbar_location: "none",

        // Example content CSS (should be your site CSS)
        content_css: config.cfgrootpath + "/css/editor_externals.css",
        entity_encoding: "named",

        setup: function (ed) {
            ed.onInit.add(function (ed, evt) {

                var dom = ed.dom;
                tinymce.dom.Event.add(dom.getRoot(), 'blur', function (e) {
                    // Do something when the editor window is blured.
                    tinyMCE.triggerSave();

                    if (typeof $("#" + ed.id).valid != 'undefined') {
                        if ($("#" + ed.id).valid() == 1) {
                            ed.getBody().style.backgroundColor = "#ffffff";
                        } else {
                            ed.getBody().style.backgroundColor = "#ffd6d6";
                        }
                    }
                });
            });
        }
    });
});
