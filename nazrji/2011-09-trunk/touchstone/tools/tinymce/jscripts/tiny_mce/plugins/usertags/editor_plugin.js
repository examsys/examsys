var arrUserTags = new Array('{{Name}}','{{Address}}','{{Company}}');

(function() {

tinymce.create('tinymce.plugins.usertags', {
    init : function(ed, url) {

        },
    createControl: function(n, cm) {
        switch (n) {
            case 'usertags':
                var mlb = cm.createListBox('usertags', {
                     title : 'Insert details    ',
                     onselect : function(v) {
                          tinyMCE.activeEditor.selection.setContent(v);
                     }
                });

                // Add some values to the list box
                //The array arrUserTags, must be defined before the editor is added!!!
                
                for(i=0;i<arrUserTags.length;i++)
                {
                    mlb.add(arrUserTags[i], arrUserTags[i]);
                }


                // Return the new listbox instance
                return mlb;
        }

        return null;
    }
});
