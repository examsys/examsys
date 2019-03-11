/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('emailtags');

	tinymce.create('tinymce.plugins.ExamplePlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			switch (n) {
				case 'emailtags':
					var arrEmailTags = [["Paper Title","{paper-title}"],["Total Paper Mark","{total-paper-mark}"],["Random mark for Paper","{random-mark}"],["Student Title","{student-title}"],["Student Last Name","{student-last-name}"],["Student Mark","{student-mark}"],["Student %","{student-percent}"],["Student Time","{student-time}"],["Class mean Mark","{class-mean-mark}"],["Class mean percent","{class-mean-percent}"],["Class StDev","{class-stdev}"],["Class max mark","{class-max-mark}"],["Class min mark","{class-min-mark}"],["Class mean time","{class-mean-time}"]];
					var mlb = cm.createListBox('emailtags', {
						title : 'Insert tag:    ',
					    onselect : function(v) {
					          tinyMCE.activeEditor.selection.setContent(v);
					          tinyMCE.activeEditor.focus();
					     }
					});
					
					// Add some values to the list box
					//The array arrUserTags, must be defined before the editor is added!!!
					   
					for(i=0;i<arrEmailTags.length;i++)
					{
						mlb.add(arrEmailTags[i][0], arrEmailTags[i][1]);
					}
					
					// Return the new listbox instance
					return mlb;
			}
			
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Email tags plugin',
				author : 'Rob Ingram',
				authorurl : 'https://rogo-eassessment-docs.atlassian.net',
				infourl : 'https://rogo-eassessment-docs.atlassian.net',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('emailtags', tinymce.plugins.ExamplePlugin);
})();