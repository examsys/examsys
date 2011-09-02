// create MEE class
$.Class.extend("MEE.Base",
{
    Render: function (source, mcedoc) {
        if (mcedoc) {
            this.ProcessForTinyMCE(source, mcedoc);
            return;
        }

        this.fontwaitlimit = 10;
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
        }
        return false;
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
        //debug.log('ProcessNext', i, proc);
        MEE.Base.process(proc);
        MEE.Base.updateProgress();

        setTimeout("MEE.Base.ProcessNext_Fonts()", 1);
    },

    ProcessNext_Fonts: function () {
        this.fontwaitlimit--;
        i = MEE.Base.current;
        if (i >= MEE.Base.to_process.length) {
            // dont creating HTML, 

            //MEE.Base.current = 0;
            //setTimeout("MEE.Base.ProcessAlignNext()", 1);
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        //debug.log('ProcessNext_Fonts', i, proc);
        if (proc.eqn && this.fontwaitlimit > 0) {
            if (!proc.eqn.FontsLoaded()) {
                this.setProgressMessage("Waiting on Fonts");
                setTimeout("MEE.Base.ProcessNext_Fonts()", 5);
                return;
            }
        }
        //debug.log("Fonts OK");
        setTimeout("MEE.Base.ProcessNext_Align()", 1);
    },

    ProcessNext_Align: function () {
        i = MEE.Base.current++;
        if (i >= MEE.Base.to_process.length) {
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        //debug.log('ProcessNext_Align', i, proc);
        if (proc.eqn)
            proc.eqn.Align();

        MEE.Base.updateProgress();

        setTimeout("MEE.Base.ProcessNext()", 1);
    },

    processDiv: function (elem) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'display';
        proc.inline = false;

        MEE.Base.to_process.push(proc);
    },

    processSpan: function (elem) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'display';
        proc.inline = true;
        MEE.Base.to_process.push(proc);
    },

    processInput: function (elem) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'edit';
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
    },
    //#endregion

    //#region Tiny MCE rendering, requires immediate render instead of timed render
    ProcessForTinyMCE: function (source, mcedoc) {

        // need to find out the tinymce edit window in use
        //this.tinymce = tinymce;

        // build all recursive definitions
        MEE.Base.displays = new Array();
        MEE.Base.edits = new Array();

        //MEE.Base.buildDefs();
        MEE.Base.to_process = new Array();

        if (!source)
            source = document.body;

        if (!this.source) this.source = new Array();
        if (!this.mcedocs) this.mcedocs = new Array();
        this.source.push(source);
        this.mcedocs.push(mcedoc);

        var elems = $(source).find("div.mee");
        for (var i = 0; i < elems.length; i++)
            this.processDivMCE(elems[i], mcedoc);

        var elems = $(source).find("span.mee");
        for (var i = 0; i < elems.length; i++)
            this.processSpanMCE(elems[i], mcedoc);

        setTimeout("MEE.Base.LayoutOverlays();", 10);
    },

    LayoutOverlays: function () {
        for (var i = 0; i < this.source.length; i++) {
            var res = $(this.source[i]).find('.mee_tinymce_cont');
            for (var k = 0; k < res.length; k++) {
                var elem = res[k];
                this.LayoutOverlay(elem, this.mcedocs[i]);
            }
            //$(this.source[i]).find('.mee_tinymce_cont').each(this.callback('LayoutOverlay'));
        }
    },

    LayoutOverlay: function (elem, mcedoc) {
        // find main instance based on id, if not there, then remove 
        var id = $(elem).attr('id');
        id = id.replace('cont', 'elem');
        var el = mcedoc.getElementById(id);

        if (!el) {
            $(elem).remove();
        } else {
            var element = $(el);
            var cont = $(elem);

            var pos = $(element).offset();
            var bottom = this.getElOffset(element, "bottom");
            var top = this.getElOffset(element, "top");

            if ($(element).is('span')) {
                cont.css('left', pos.left + 'px');
                $(element).css('padding-right', $(cont).outerWidth() - MEE.Data.blankspacesize(element) + 'px');

                var contheight = $(cont).outerHeight() + top + bottom;
                var elemheight = $(element).outerHeight();
                if (elemheight > contheight)
                    top += Math.abs(elemheight - contheight);

                cont.css('top', pos.top + top - 1 + 'px');
            } else {
                cont.css('left', Math.floor(($(element).outerWidth() - $(cont).outerWidth()) / 2) + 'px')
                cont.css('top', pos.top + top + 'px');
            }
        }
    },

    getElOffset: function (elem, which) {
        var bottom = parseInt($(elem).css('padding-' + which).replace('px', ''));
        if (!bottom)
            bottom = parseInt($(elem).css('margin-' + which).replace('px', ''));
        if (!bottom)
            bottom = 0;

        return bottom;
    },

    processDivMCE: function (elem, doc) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'display';
        proc.inline = false;

        if ($(proc.elem).attr('latex'))
            return;

        var meeeqn = new MEE.Display(elem, false, doc);
        MEE.Base.displays.push(meeeqn);
    },

    processSpanMCE: function (elem, doc) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'display';
        proc.inline = true;

        if ($(proc.elem).attr('latex'))
            return;

        var meeeqn = new MEE.Display(elem, true, doc);
        MEE.Base.displays.push(meeeqn);
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
        this.width = 0;
        this.height = 0;
        this.top = 0;
        this.bottom = 0;
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
        var res = "";
        res += "w " + this.width;
        res += " h " + this.height;
        res += " t " + this.top;
        res += " b " + this.bottom;
        return res;
    }
});
//#endregion