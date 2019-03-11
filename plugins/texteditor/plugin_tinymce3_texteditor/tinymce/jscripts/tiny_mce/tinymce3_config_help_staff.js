tinyMCE.init({ 
	mode : "specific_textareas", 
	editor_selector : "editorStandard",
	theme : "advanced", 
	skin : "default",
	plugins : "table,visualchars,nonbreaking,xhtmlxtras,insertimagestaff,insertcaptivatestaff,paste",
	theme_advanced_blockformats : "p,div,h1,h2,h3,h4,h5",
	// Theme options
	theme_advanced_buttons1 : "formatselect,|,cut,copy,paste,|,undo,|,bold,italic,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,numlist,bullist,|,insertimagestaff,insertcaptivatestaff,tablecontrols,|,code", 
	theme_advanced_buttons2 : "", 
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top", 
	theme_advanced_toolbar_align : "left",
  theme_advanced_path : false,
  theme_advanced_statusbar_location : "none",
  // Example content CSS (should be your site CSS) 
	content_css : cfgRootPath + "/css/help.css",


	setup : function(ed) {
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
