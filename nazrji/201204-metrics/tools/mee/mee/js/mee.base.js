// create MEE class
$.Class.extend("MEE.Base",
{
    Render: function (source, mcedoc) {
        this.fontwaitlimit = 10;
        // build all recursive definitions
        MEE.Base.displays = new Array();
        MEE.Base.edits = new Array();

        //MEE.Base.buildDefs();
        MEE.Base.to_process = new Array();

        if (!source)
            source = document.body;
        source = $(source)
        source.find("div.mee").each(this.callback('processDiv'));
        source.find("span.mee").each(this.callback('processSpan'));
        source.find("input.mee").each(this.callback('processInput'));

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
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        MEE.Base.process(proc);
        MEE.Base.updateProgress();
        setTimeout("MEE.Base.ProcessNext_Align()", 5);
    },

    ProcessNext_Fonts: function () {
        this.fontwaitlimit--;
        i = MEE.Base.current;
        if (i >= MEE.Base.to_process.length) {
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        if (proc.eqn && this.fontwaitlimit > 0) {
            if (!proc.eqn.FontsLoaded()) {
                this.setProgressMessage("Waiting on Fonts");
                setTimeout("MEE.Base.ProcessNext_Fonts()", 5);
                return;
            }
        }
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
          var h = this.replacePX(proc.elem.style.height) 
                                              + this.replacePX(proc.elem.style.paddingTop)
                                               + this.replacePX(proc.elem.style.paddingBottom);
          
          var elem = proc.elem.parentNode;
          if(elem.tagName == 'SPAN') {
            elem = elem.parentNode; // if we are in a table set the height on the tr not the td
          } 
          if(elem.tagName == 'TD') {
            elem = elem.parentNode; // if we are in a table set the height on the tr not the td
          } 
          
          if(h == 0 && this.replacePX(elem.style.height) == 0) {
            elem.style.height = 'auto';
          } else if(elem.style.height == '' || h > this.replacePX(elem.style.height)) {
            elem.style.minHeight = h + 'px';
            elem.style.paddingTop = proc.elem.style.paddingTop;
          }
        } else {
           var w = this.calcWidth(proc.elem,0);
           proc.elem.parentNode.style.width = w + 'px';
           //var h = this.calcHeight(proc.elem,0);
           //proc.elem.parentNode.style.height = h + 'px';
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
    },
    //#endregion
    replacePX: function (val) {
      val = parseInt(val);
      if(!val || val == 'NaN') {
        return 0;
      } else {
        return val;
      }
    },
    calcWidth : function (e,w) {
        //recursivly caculate width of an equasion
       if(e.childNodes) {
          for(var i = 0; i < e.childNodes.length; i++) {
            if(e.childNodes[i].style) {
              je=$(e.childNodes[i]);
              if (je.width() > w)
                  w += je.width();
            }
            w = this.calcWidth(e.childNodes[i],w);
          }
          return w;
       }
       return 0;
    },
    calcHeight : function (e,h) {
        //recursivly caculate height of an equasion
       if(e.childNodes) {
          for(var i = 0; i < e.childNodes.length; i++) {
            if(e.childNodes[i].style) {
              je=$(e.childNodes[i]);
              //if (je.height() > h)
                  h += je.height();
            }
            h = this.calcWidth(e.childNodes[i],h);
          }
          return h;
       }
       return 0;
    }
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