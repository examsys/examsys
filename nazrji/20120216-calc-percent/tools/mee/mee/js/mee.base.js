// create MEE class
$.Class.extend("MEE.Base",
{
    Render: function (source, mcedoc) {
        /*if (mcedoc) {
            this.ProcessForTinyMCE(source, mcedoc);
            return;
        }*/

        this.fontwaitlimit = 0;
        // build all recursive definitions
        MEE.Base.displays = new Array();
        MEE.Base.edits = new Array();

        //MEE.Base.buildDefs();
        MEE.Base.to_process = new Array();

        if (!source)
            source = document.body;

        $(source).find("div.mee").each(this.callback('processDiv'));
        $(source).find("span.mee").each(this.callback('processSpan'));
        $(source).find("input.mee").each(this.callback('processInput'));

        if (MEE.Base.to_process.length > 0)
            MEE.Base.createProgress(MEE.Base.to_process.length);

        MEE.Base.current = 0;

        setTimeout("MEE.Base.ProcessNext()", 1);
        $(document.body).click(this.callback('pageClick'));
    },

    pageClick: function () {
        if (MEE.Edit.toolbar && MEE.Edit.toolbar.currentEdit) {
            var edit = MEE.Edit.toolbar.currentEdit;
            if (!$(edit.inputelement).hasClass('activate')) {
                edit.deactivate();
            }
            return false;
        }
        return true;
    },

    //#region Process elements
    ProcessNext: function () {
        i = MEE.Base.current;
        if (i >= MEE.Base.to_process.length) {
            // dont creating HTML, 

            //MEE.Base.current = 0;
            //setTimeout("MEE.Base.ProcessAlignNext()", 1);
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        MEE.Base.process(proc);
        MEE.Base.updateProgress();

        setTimeout("MEE.Base.ProcessNext_Fonts()", 1);
    },

    ProcessNext_Fonts: function () {
        this.fontwaitlimit--;
        i = MEE.Base.current;
        if (i >= MEE.Base.to_process.length) {
            MEE.Base.removeProgress();
            return;
        }

        //var proc = this.to_process[i];
        //if (proc.eqn && this.fontwaitlimit > 0) {
        //    if (!proc.eqn.FontsLoaded()) {
        //        this.setProgressMessage("Waiting on Fonts");
        //        setTimeout("MEE.Base.ProcessNext_Fonts()", 5);
        //        return;
        //    }
        //}
        //debug.log("Fonts OK");
        setTimeout("MEE.Base.ProcessNext_Align()", 5);
    },

    ProcessNext_Align: function () {
        i = MEE.Base.current++;
        if (i >= MEE.Base.to_process.length) {
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        if (proc.eqn)
            proc.eqn.Align();

        MEE.Base.updateProgress();
        $(proc.elem).css('color','');
        
        //add some hight and padding to the parent elments to help with layout.
        if(!$(proc.elem).hasClass('meeInMCE')) {
          var h = parseInt(proc.elem.style.height) 
                                              + parseInt(proc.elem.style.paddingTop)
                                               + parseInt(proc.elem.style.paddingBottom);
          if(proc.elem.parentNode.style.height == '' || h > parseInt(proc.elem.parentNode.style.height)) {
            proc.elem.parentNode.style.height = h + 'px';
            proc.elem.parentNode.style.paddingTop = proc.elem.style.paddingTop;
          }
        }
          
        setTimeout("MEE.Base.ProcessNext()", 1);
    },

    processDiv: function (elem) {
        var proc = {
          elem : elem,
          type : 'display',
          inline : false
        };
        $(proc.elem).css('color','white');
        MEE.Base.to_process.push(proc);
    },

    processSpan: function (elem) {
        var proc = {
          elem : elem,
          type :'display',
          inline : true
        }
        $(proc.elem).css('color','white');
        MEE.Base.to_process.push(proc);
    },

    processInput: function (elem) {
        var proc = {
          elem : elem,
          type : 'edit'
        }
        MEE.Base.to_process.push(proc);
    },

    process: function (proc) {

        if (proc.type == "display") {
            if ($(proc.elem).attr('latex'))
                return;
            var meeeqn = new MEE.Display(proc.elem, proc.inline, this.tinymce);
            MEE.Base.displays.push(meeeqn);
            proc.eqn = meeeqn;
        } else if (proc.type == "edit") {
            var meeeqn = new MEE.Edit(proc.elem);
            MEE.Base.edits.push(meeeqn);
        }


    },
    //#endregion 

    //#region Progress bar
    createProgress: function () {
        MEE.Base.html_progress = $('<div>');
        MEE.Base.html_progress.addClass('mee_progress');
        MEE.Base.html_progress.html("Processing equations: 0%");
        $(document.body).append(MEE.Base.html_progress);
    },

    setProgressMessage: function (message) {
        MEE.Base.html_progress.html(message);
    },
    updateProgress: function () {
        var pct = Math.ceil(MEE.Base.current / MEE.Base.to_process.length * 100);
        MEE.Base.html_progress.html("Processing equations: " + pct + "%");
    },

    removeProgress: function () {
        $('.mee_progress').remove();
    }
    //#endregion
},
{
});

//#region class to handle element alignment
$.Class.extend("MEE.Align",
{
    width: 0,
    height: 0,
    top: 0,
    bottom: 0,
    init: function () {
    },
    Merge: function (align) {
        this.width += align.width;
        if (this.height == 0) {
            this.height = align.height;
        } else {
            if (align.top > this.top)
                this.height += align.top - this.top;
            if (align.bottom > this.bottom)
                this.height += align.bottom - this.bottom;
        }
        this.top = Math.max(this.top, align.top);
        this.bottom = Math.max(this.bottom, align.bottom);
    },
    toString: function () {
        return "w " + this.width + " h " + this.height + " t " + this.top + " b " + this.bottom;
    }
});
//#endregion