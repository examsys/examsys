  tinyMCE.init({ 
    mode : "specific_textareas", 
    editor_selector : "mceEditor",
    theme : "advanced", 
    skin : "default",
    plugins : "table,visualchars,nonbreaking,xhtmlxtras,insertimagestaff,insertcaptivatestaff", 
    theme_advanced_blockformats : "p,div,h1,h2,h3,h4,h5",
    // Theme options
    theme_advanced_buttons1 : "formatselect,|,cut,copy,paste,|,undo,|,bold,italic,|,sub,sup,|,justifyleft,justifycenter,justifyright,|,numlist,bullist,|,tablecontrols,|,insertimagestaff,insertcaptivatestaff,code", 
    theme_advanced_buttons2 : "", 
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top", 
    theme_advanced_toolbar_align : "left",
    // Example content CSS (should be your site CSS) 
    content_css : "/touchstone/css/editor.css", 
}); 
