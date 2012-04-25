  tinyMCE.init({ 
    mode : "specific_textareas", 
    editor_selector : "mceEditor",
    theme : "advanced", 
    skin : "default",
    plugins : "table,visualchars,nonbreaking", 
    // Theme options
    theme_advanced_buttons1 : "cut,copy,paste,|,undo,|,bold,italic,underline,|,sub,sup,|,justifyleft,justifycenter,justifyright,|,numlist,bullist,|,tablecontrols", 
    theme_advanced_buttons2 : "", 
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top", 
    theme_advanced_toolbar_align : "left",
    // Example content CSS (should be your site CSS) 
    content_css : cfgRootPath + "/css/editor_pink.css"
}); 
