MEE.Main.extend("MEE.Edit",
{
    //#region Static definitiona
    toolbar: null,
    toolbarelem: null,
    toolbarcache: [],
    currentedit: null
    //#endregion
},
{
    // class extensions for using as an editor
    active: [],
    element: null,
    //#region Initialization
    init: function (element) {
        // make sure we are ok to run
        if (!this.checkProtocol())
            return;
          
        this.element = element.name;
        this.active[this.element] = false;
        
        // debug configuration
        this.debug = 0; // 0 = no debug, 1 = latex and maxima debug, 2 = full debug including element tree

        // turns maxima on or off
        this.maxima = 0;

        // initialize some variables
        this.mode = -1;
        this.inline = false;
        this.symhist = new MEE.SymHist();
        this.parser = new MEE.Parser();
        this.lasttext = "";
        this.prevlatex = "";

        // get latex to be editing
        this.latex = element.value;
        if ($(element).attr('latex'))
            this.latex = $(element).attr('latex');
        
        // set up for inline edit if needed
        if ($(element).hasClass('inline')) {
            this.inline = true;
        }

        // save and hide input element
        this.inputelement = element;
        this.inputname = element.id;
        if (this.debug) {
            $(element).css('width', '600px');
        } else {
            $(element).css("display", "none");
        }

        // build all needed html elements
        this.initHTML();

        // set no current selected element set
        this.curElemSet = null;

        // set current mode
        this.modeWYSIWYG();
        //this.modeRaw();

        this.createUndo();
        // dump element tree
    },

    // check if we are running a browser that cant load xml files locally
    checkProtocol: function () {
        if (document.location.protocol == 'file:' && $.browser.safari) {
            alert("The eeditor does not work properly on the local file system due to security settings in your browser. Please use a real webserver.");
            return false;
        }
        return true;
    },

    initHTML: function () {

        // build edit container
        this.editdiv = $('<div>');
        this.editdiv.addClass('mee_edit');
        
        //put any extra classes on the edit container for easy styling
        this.editdiv.addClass($(this.inputelement).attr('class'));
          
        
        $(this.inputelement).before(this.editdiv);

        // create maxima output if needed
        if (this.maxima) {
            this.maximaoutput = $('<input>');
            if (!this.debug) {
                this.maximaoutput.attr('type', 'hidden');
            } else {
                this.maximaoutput.css('width', '600px');
            }

            var name = $(this.inputelement).attr('name');

            $(this.maximaoutput).attr('name', name + '_maxima');
            $(this.maximaoutput).insertAfter(this.inputelement);
        }

        if (this.debug) {
            $('<span>Latex: </span>').insertBefore(this.inputelement);

            if (this.maxima) {
                $('<br />').insertBefore(this.maximaoutput);
                $('<span>Maxima: </span>').insertBefore(this.maximaoutput);
            }
        }

        // build equation container
        this.eqndiv = $('<div>');
        this.eqndiv.addClass('mee_edit_eqn');
        $(this.editdiv).append(this.eqndiv);

        // editor click event setup
        $(this.editdiv).click(this.callback('editorClick'));

        // init toolbar
        this.ToolbarType = 'default';
        if($(this.inputelement).hasClass('units')) {
          this.ToolbarType = 'units';
        }
        if($(this.inputelement).hasClass('sci')) {
          this.ToolbarType = 'sci';
        }
        
        this.initToolbar(this.ToolbarType);
        
        // create raw input box
        this.initRawInput();

        // create W input box
        this.initWInput();

        // created some needed elements
        this.initHightLightBox();
        this.initInputSizeBox();
        
    },

    initToolbar: function (tbtype) {
        // if we dont already have one, build a toolbar
        if (!MEE.Edit.toolbarcache[this.inputelement.id]) {
            MEE.Edit.toolbarcache[this.inputelement.id] = new MEE.Toolbar(this.inputelement);
            MEE.Edit.toolbarcache[this.inputelement.id].loadToolBar(tbtype);
            MEE.Edit.toolbar = MEE.Edit.toolbarcache[this.inputelement.id];
            MEE.Edit.toolbarelem = MEE.Edit.toolbarcache[this.inputelement.id].toolbarelem;
        } else {
          MEE.Edit.toolbar = MEE.Edit.toolbarcache[this.inputelement.id];
          MEE.Edit.toolbarelem = MEE.Edit.toolbarcache[this.inputelement.id].toolbarelem;
        }
        
        if ($(this.inputelement).hasClass('activate'))
          MEE.Edit.toolbar.activate = this;
        
    },

    initRawInput: function () {
        // create raw input box
        this.rawinput = $('<textarea>');
        this.rawinput.addClass('mee_edit_raw');
        $(this.editdiv).prepend(this.rawinput);

        // set up auto resize on the input box
        $(this.rawinput).autoResize();

        // set the latex to the raw input box
        this.rawinput.val(this.latex);
        this.rawinput.trigger('change.dynSiz');

        // set up events
        this.rawinput.keyup(this.callback('rawKeyUp'));
    },

    initWInput: function () {
        // create W input box
        this.inputelembox = $('<input>');
        this.inputelembox.addClass('mee_elem_input_box');
        $(this.editdiv).prepend(this.inputelembox);

        // events for input box
        $(this.inputelembox).keydown(this.callback('inputKeyDown'));
        $(this.inputelembox).keyup(this.callback('inputKeyUp'));
        $(this.inputelembox).keypress(this.callback('inputKeyPress'));

        // create input elem
        this.input = new MEE.ElemInput();
        this.inputelem = this.input.html_elem;
    },

    //create highlight object if it doesnt already exist. Only
    // need a single one across all instances of all editors
    initHightLightBox: function () {

        var hl = $('.mee_edit_highlight');
        if (hl.length == 0) {
            var hldiv = $('<div>');
            hldiv.addClass('mee_edit_highlight');
            hldiv.css('display', 'none');
            $(document.body).append(hldiv);

            var hldiv = $('<div>');
            hldiv.addClass('mee_edit_highlight_multi');
            hldiv.css('display', 'none');
            $(document.body).append(hldiv);

            var hldiv = $('<div>');
            hldiv.addClass('mee_edit_highlight_elem');
            hldiv.css('display', 'none');
            $(document.body).append(hldiv);
        }
    },

    // create single instance of input size box
    // used to find the width of the text that has been entered into 
    // the W mode movable edit box
    initInputSizeBox: function () {
        var boxsize = $('.mee_input_size');
        if (boxsize.length == 0) {
            var boxsize = $('<span>');
            boxsize.addClass('mee_input_size');
            $(document.body).append(boxsize);
            boxsize.html("");
        }
    },

    //#endregion

    //#region Sort out equation display stuff

    // clear out the equation display and rebuild it
    rebuildDisplay: function () {
        //console.log('rebuildDisplay');

        // create new element set
        this.elementset = new MEE.ElemSetNormal(this.latex, null, true);
        this.curElemSet = this.elementset;

        // clear out cache stuff
        MEE.Elem.elems = new Array();
        MEE.ElemSetBasic.basicelems = new Array();
        MEE.ElemSet.elemsets = new Array();

        // build equation that is in the input
        var depth = 1;
        if (this.inline)
            depth = 2;

        // clear out and replace contents of eqn container
        this.eqndiv.html("");

        // create html for equation and appent to eqndiv
        this.html_elem = this.elementset.toHTML(depth);
        this.eqndiv.append(this.html_elem);




        // highlight stuff
        if (this.mode == 1 && this.active[this.element]) {
            this.moveToSet(this.elementset, false, false);
            //this.changed();
            // this.curElemSet.Highlight();
        }

        // align all elements
        //this.formatElemSet();
        this.changed(null, 'rebuildDisplay');

    },

    changed: function (key, source) {
        if (typeof (source) == "undefined" || typeof (source) == "null") source = "";
        //console.log("changed - " + source);

        // get new latex and store any changes
        if (this.mode == 1)
            this.getNewWYSIWYGLatex();

        // make sure all elementsets and elements have isedit set. this does things like turn on
        // empty set highlighting
        this.setIsEditOnElems();

        // show/hide inputs
        this.showhideInputs();
        this.sortToolbarchecks();
        this.resizeRawInput();

        // set up the input box sizing ready for element alignment
        this.sizeInput(key)

        // Sort out any blank element padding etc
        this.sortBlanks();

        // rebuild the element alignment
        this.formatElemSet();

        // move input box to the correct location
        this.moveInput();

        // highlight the current element set
        this.Highlight();

        // focus back to the input
        this.focusInput();

        // rebind all click events
        this.bindElemClicks();

        // sort out context sensitive toolbar stuff
        this.showContextRegions();

        this.dump();
        
        // move the editor a bit !!! this breaks things :-)
        //var offset = $(this.editdiv).offset();
        //offset.top += ($(this.editdiv).height() / 2) - 5;
        //$(this.editdiv).offset(offset);
        
    },

    setIsEditOnElems: function () {
        for (var i = 0; i < MEE.Elem.elems.length; i++)
            MEE.Elem.elems[i].isedit = true;
        for (var i = 0; i < MEE.ElemSet.elemsets.length; i++)
            MEE.ElemSet.elemsets[i].isedit = true;
    },

    dump: function () {
        if (this.debug < 2) return;

        var res = "";
        res += "<div>";
        res += this.curElemSet.latex;
        res += "</div>";
        //if (this.undo)
        //    res += this.undo.dump();
        res += this.elementset.dump();

        $('.dump').html(res);
    },

    sizeInput: function (key) {
        // resize the I element to the size of the input text box
        if (this.inputelem) {
            var text = this.inputelembox.val();
            var newchar = String.fromCharCode(key);
            if (key > 48)
                text += newchar;
            if (key == 8) { // backspace
                // need to get text, and trim character before the caret (or select the caret contents)
                var caret = this.inputelembox.caret();
                if (caret.start == caret.end) {
                    // no selection, so remove the previous character
                    text = text.substr(0, caret.start - 1) + text.substr(caret.start);
                } else {
                    text = text.substr(0, caret.start) + text.substr(caret.end);
                }
            } else if (key == 46) { // delete
                // need to delete the character after the caret or the caret contents
                var caret = this.inputelembox.caret();
                if (caret.start == caret.end) {
                    // no selection, so remove the previous character
                    text = text.substr(0, caret.start) + text.substr(caret.start + 1);
                } else {
                    text = text.substr(0, caret.start) + text.substr(caret.end);
                }
            }

            /*if (this.lastkey && this.lastkey != "")
            text += this.lastkey;*/

            var size = this.textSize(text, this.inputelem)/* - MEE.Data.blankspacesize(this.inputelem)*/;
            if (size < 1)
                size = 1;
            //this.inputelem.css('padding-right', size + 'px');
            this.inputelembox.css('width', size + 15 + 'px');
            this.input.size = size;

            var fontsize = $(this.inputelem).css('font-size');
            this.inputelembox.css('font-size', fontsize);
        }

    },

    sortBlanks: function () {
        if (this.mode != 1)
            return;
        for (var i = 0; i < MEE.ElemSet.elemsets.length; i++) {
            if (MEE.ElemSet.elemsets[i]._name != "MEE.Row") {
                MEE.ElemSet.elemsets[i].sortBlanks();
            }
        }
    },

    moveInput: function () {
        if (this.mode != 1)
            return;

        // resize the I element to the size of the input text box
        if (this.inputelem) {
            MEE.Tools.HTML.AlignElementOver(this.inputelem, this.inputelembox, null, false);
            if (this.inputelembox.css('left') == "0px") {
                this.inputelembox.css('display', 'none');
            } else {
                this.inputelembox.css('display', 'block');
            }
        }
    },

    Highlight: function () {
        if (this.mode != 1 || !this.active[this.element]) {
            $('.mee_edit_highlight').css('display', 'none');
            $('.mee_edit_highlight_elem').css('display', 'none');
            return;
        }

        this.curElemSet.Highlight();
    },

    focusInput: function () {
        if (this.mode == 0) {
            this.rawinput.focus();
        } else {
            this.inputelembox.focus();
        }
    },

    bindElemClicks: function () {
        //$('.mee_elemsetbasic').unbind('click');
        $('.mee_elemset_empty_inner',this.editdiv).unbind('click');
        $('.mee_elem',this.editdiv).unbind('click');

        if (this.mode != 1)
            return;

        //$('.mee_elemsetbasic').click(this.callback('elementClick'));
        $('.mee_elemset_empty_inner',this.editdiv).click(this.callback('emptyClick'));
        $('.mee_elem',this.editdiv).click(this.callback('elemClick'));
        $('.mee_elem',this.editdiv).dblclick(this.callback('elemDblClick'));
    },

    clearAlign: function (elem) {
        // TODO: REMOVE THIS AND MAKE UNNECESSARY
        // this is a complete bodge, needs to be changed to only remove the align data in changed elements
        // will probably be not needed at all
        $(elem).css('padding', '');
        $(elem).css('margin', '');
        $(elem).children().each(this.callback('clearAlign'));
    },

    // format the top most element set with the padding required
    formatElemSet: function (key) {
        this.clearAlign(this.elementset.html_elem);
        this.elementset.sortAlign();

        $(this.eqndiv).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
        $(this.eqndiv).css('padding-top', this.elementset.align.top + 6 + 'px');
        $(this.eqndiv).css('padding-bottom', this.elementset.align.bottom + 6 + 'px');
    },
    //#endregion

    //#region stuff for dealing with the content

    // set the current latex
    setLatex: function (latex, source) {

        this.latex = latex;

        if (source != "undo")
            this.addUndo();
        if (source != "raw")
            this.rawinput.val(latex);
        if (source != "baseinput")
            $(this.inputelement).val(latex);

        if (this.maxima) {
            var maxima = MEE.Maxima.Convert(this.elementset);
            $(this.maximaoutput).val(maxima);
        }
        window.location.hash = latex;

        this.rebuildDisplay();
    },

    toolbarCommand: function (latex, item, wlatex, mlatex) {

        if (this.mode == 0) {
            // raw mode

            var newlatex = this.rawinput.val();
            var caret = this.rawinput.caret();
            var before = newlatex.substr(0, caret.start);
            var after = newlatex.substr(caret.end);

            if (latex.indexOf('$') == -1) {

                var padafter = false;
                if (latex.charAt(0) == "\\" && after.length > 0) {
                    if (after.charAt(0) != "[" & after.charAt(0) != "(" &
                    after.charAt(0) != "{" & after.charAt(0) != "\\") {
                        padafter = true; ;
                    }
                }

                // look up latex in commands and see if there are args needed
                var result = before;
                result += latex;

                var offset = result.length;
                if (padafter)
                    result += " ";
                result += after;

            } else {
                var inner = caret.text;

                var latex1 = latex.substr(0, latex.indexOf('$'));
                var latex2 = latex.substr(latex.indexOf('$') + 2);

                var padafter = false;
                if (latex2.charAt(latex2.length - 1) != "}" && after.length > 0) {
                    if (after.charAt(0) != "[" & after.charAt(0) != "(" &
                    after.charAt(0) != "{" & after.charAt(0) != "\\") {
                        padafter = true; ;
                    }
                }
                var padinner = false;
                if (latex1.charAt(latex1.length - 1) != "{" && inner.length > 0) {
                    if (inner.charAt(0) != "[" & inner.charAt(0) != "(" &
                    inner.charAt(0) != "{" & inner.charAt(0) != "\\") {
                        padinner = true; ;
                    }
                }

                var result = before + latex1;
                if (padinner)
                    result += ' ';
                result += inner;
                var offset = result.length;
                result += latex2;
                if (padafter)
                    result += ' ';
                result += after;
            }
            this.setLatex(result);
            this.rawinput.focus();
            this.rawinput.caret(offset, offset);

        } else {
            // WYSIWYG mode
            if (typeof this.curElemSet.selectStart == "number") {
                // store selection
                var sel = this.getSelStartEnd();
                var selelemset = this.curElemSet;

                if (mlatex)
                    latex = mlatex;

                var ip = this.curElemSet.getInputPos();

                // move cursor to end of selection
                var endelem = this.curElemSet.elements[sel.end];
                this.moveToElement(endelem);

                // if input was at start of selection, its now moved so shuffle selection down
                if (ip == sel.start - 1) {
                    sel.start--;
                    sel.end--;
                }

                // clear selection
                this.curElemSet.selectStart = null;

                // type in latex
                latex = latex.replace("$1", "\\XXREPLACEXX");

                this.inputAdd(latex + " ");

                // find position of $1
                var found = this.elementset.findElement('XXREPLACEXX');

                if (!found) {
                    // no replace found, so just remove the old selected elements
                    for (var i = sel.start; i <= sel.end; i++) {
                        var tomove = selelemset.elements[i];
                        tomove.html_elem.remove();
                    }

                    selelemset.elements.splice(sel.start, sel.end - sel.start + 1);

                    this.changed();
                    return;
                }

                var targetset = found.set;
                var targetoffset = found.elem.offset;

                // move old selection after $1
                for (var i = sel.start; i <= sel.end; i++) {
                    // need to move each element from current location to before the target offset
                    var tomove = selelemset.elements[i];

                    targetset.elements.splice(targetoffset, 0, tomove);
                    tomove.parent = targetset;
                    tomove.html_elem.insertBefore(found.elem.html_elem);
                    targetoffset++;
                }

                selelemset.elements.splice(sel.start, sel.end - sel.start + 1);

                // remove $1
                targetset.elements.splice(targetoffset, 1);
                found.elem.html_elem.remove();

                this.moveToElement(tomove);

                this.changed();

                return;
            }

            // check if W mode has different latex
            if (wlatex)
                latex = wlatex;

            // remove anything in the latex after a $1 (when using raw mode latex)
            if (latex.indexOf('$') > -1)
                latex = latex.substr(0, latex.indexOf('$'));

            if (latex.substr(latex.length - 1) == "{") {
                this.inputAdd(latex);
            } else {
                // add input to W mode
                this.inputAdd(latex + " ");
            }
        }

        // store any symbols into the history
        if ($(item).data('history') == 1)
            this.symhist.Add(item);

        this.changed();
    },

    // clear equation
    clear: function () {
        // TODO: Make sure this works in both modes
        if (confirm("Are you sure you want to clear the equation?")) {
            this.setLatex("");
        }
    },

    //#endregion 

    //#region Mode change functions //

    setInline: function () {
        this.inline = true;
        this.rebuildDisplay();
        this.sortToolbarchecks();
    },

    setDisplay: function () {
        this.inline = false;
        this.rebuildDisplay();
        this.sortToolbarchecks();
    },

    // set mode to wysiwyg
    modeWYSIWYG: function () {
        if (this.mode == 1)
            return;
        if(MEE.Edit.toolbar) {
          MEE.Edit.toolbar.enableTab("context");
        }
        this.mode = 1;
        this.rebuildDisplay();
    },

    // set mode to raw
    modeRaw: function () {
        if (this.mode == 0)
            return;

        MEE.Edit.toolbar.disableTab("context");
        this.inputAdd("");
        this.changed();

        this.rawinput.val(this.latex);

        this.mode = 0;
        this.rebuildDisplay();
    },

    sortToolbarchecks: function () {
        // inline mode checks
        if(MEE.Edit.toolbar) {
          if (this.inline) {
              MEE.Toolbar.ApplyImage('#mode_inline_check', 'toolbar/home_tick.png');
              MEE.Toolbar.ApplyImage('#mode_display_check', 'toolbar/home_tick_blank.png');
          } else {
              MEE.Toolbar.ApplyImage('#mode_display_check', 'toolbar/home_tick.png');
              MEE.Toolbar.ApplyImage('#mode_inline_check', 'toolbar/home_tick_blank.png');
          }

          // editor mode checks
          if (this.mode == 0) {
              MEE.Toolbar.ApplyImage('#mode_raw_check', 'toolbar/home_tick.png');
              MEE.Toolbar.ApplyImage('#mode_wysiwyg_check', 'toolbar/home_tick_blank.png');
          } else {
              MEE.Toolbar.ApplyImage('#mode_wysiwyg_check', 'toolbar/home_tick.png');
              MEE.Toolbar.ApplyImage('#mode_raw_check', 'toolbar/home_tick_blank.png');
          }
        }
    },
    //#endregion

    //#region Activation of edit area //
    // when an edit div is clicked
    editorClick: function () {
        //console.log("editorClick");

        if (MEE.Edit.activeEdit == this) {
            MEE.Edit.toolbar.hidePopups();

            // move selection to end if W mode
            if (this.mode == 1) {
                this.curElemSet.selectStart = null;
                this.moveToSet(this.elementset, false, false);
            }

            this.changed(null, 'editorClick');

            return false;
        }

        // activate current edit box
        this.activate();

        return false;
    },

    // activate this edit div
    activate: function () {
        //console.log("activate");

        if (this.active[this.element])
            return;
        
        this.active[this.element] = true;


        // check for an active edit box, if there is one deactivate it
        if (MEE.Edit.activeEdit) {
            MEE.Edit.activeEdit.deactivate();
        }
        MEE.Edit.activeEdit = this;
        
        //get the correct toolbar
        this.initToolbar(this.ToolbarType);
        
        // move toolbar to correct location
        $(this.editdiv).prepend(MEE.Edit.toolbarelem);
        MEE.Edit.toolbarelem.show();
        MEE.Edit.toolbar.currentEdit = this;
        MEE.Edit.activeEdit = this;
        MEE.Edit.toolbar.initEvents();

        // do some toolbar housekeeping
        this.sortUndoMeun();
        this.symhist.SortToolbar();

        this.openDefaultTab();

        if (this.mode == 1)
            this.moveToSet(this.elementset, false, false);

        this.changed(null, 'activate');
    },

    openDefaultTab: function () {
        //alert(this.inputelement.class);
        var classList = $(this.inputelement).attr('class').split(/\s+/);
        for (var i = 0; i < classList.length; i++) {
            var class_name = classList[i];
            if (class_name.indexOf(':') > -1) {
                var type = class_name.substr(0, class_name.indexOf(':'));
                var tab = class_name.substr(class_name.indexOf(':') + 1);
                if (type == "tabopen") {
                    $('#mee_tab_link_' + tab).children('a').click();
                }
            }
        }
    },

    showhideInputs: function () {
        if (this.active[this.element]) {
            if (this.mode == 0) {
                this.rawinput.css('display', 'block');
                this.inputelembox.css('display', 'none');
            } else {
                this.rawinput.css('display', 'none');
                this.inputelembox.css('display', 'block');
            }
        } else {
            this.rawinput.css('display', 'none');
            this.inputelembox.css('display', 'none');
        }
    },

    // deactivate this edit div
    deactivate: function () {
        if (!this.active[this.element])
            return;

        this.active[this.element] = false;

        MEE.Edit.toolbar.currentEdit = null;
        MEE.Edit.toolbar.closeTabs();
        MEE.Edit.toolbar.hide();
        //MEE.Edit.toolbarelem.hide();
        MEE.Edit.activeEdit = null

        this.rebuildDisplay();
    },
    //#endregion

    //#region RAW Stuff

    // resize the raw input box to fit the width
    resizeRawInput: function () {
        this.rawinput.css('width', $(this.editdiv).outerWidth() + 'px');
    },

    // handle changes to the raw input
    rawKeyUp: function (elem, ev) {
        if (ev.ctrlKey && !ev.shiftKey && ev.keyCode == 90)
            return this.doUndo();

        if (ev.ctrlKey && ev.shiftKey && ev.keyCode == 90)
            return this.doRedo();

        var latex = this.rawinput.val();
        if (latex == this.latex)
            return;

        this.setLatex(latex, "raw");
    },
    //#endregion

    //#region WYSIWYG Stuff


    inputKeyPress: function (elem, event) {
        //console.log("inputKeyPress");
        // text entered, so parse it and add the input to the current element set
        var key = event.which;
        var newchar = String.fromCharCode(event.which);


        var res = this.inputAdd(newchar);

        if (res)
            this.changed(key, 'inputKeyPress');
        else
            this.changed(null, 'inputKeyPress');
        return res;
    },

    inputAdd: function (newchar) {

        var curvalue = $(this.inputelembox).val() + newchar;

        var outputtext = {};

        // if there is more than 1 character change on the input, then parse it
        // one character at a time. This means that the parsing code for w mode only
        // needs to handle typeing, and not blocks of pasted text. Makes life TONS
        // easier
        if (Math.abs(this.lasttext.length - curvalue.length) > 1) {

            var text = "";
            var res = true;
            // reset current value then
            // need to add 1 character at a time to it
            // and parseInput
            for (var i = 0; i < curvalue.length; i++) {
                text += curvalue.charAt(i);
                outputtext.text = "";
                if (this.parseInput(text, outputtext)) {
                    text = outputtext.text;
                    res = false;
                }
            }
            if (text == " ")
                text = "";

            text = $.trim(text);

            this.lasttext = text;
            $(this.inputelembox).val(text);

            //this.changed(null, 'inputAdd');

            return res;

        } else {
            outputtext.text = "";
            if (this.parseInput(curvalue, outputtext)) {
                outputtext.text = $.trim(outputtext.text);

                $(this.inputelembox).val(outputtext.text);
                this.lasttext = outputtext.text;

                //this.changed(null, 'inputAdd');

                return false;
            } else {
                this.lasttext = curvalue;
            }
        }

        return true;
    },

    checkPrevElems: function (text) {
        if (text.substr(0, 1) == "\\")
            return "";
        
        // this should check the content of the elements instead of prevlatex, as prevlatex is WRONG


        for (var i = 0; i < MEE.Data.namedops.length; i++) {
            var nop = MEE.Data.namedops[i];
            var match = this.prevlatex.substr(this.prevlatex.length - nop.length);

            if (nop == match && this.prevlatex.substr(this.prevlatex.length - nop.length - 1, 1) != "\\") {
                if (this.prevlatex.substr(this.prevlatex.length - nop.length - 1, 1) == "t" && nop == "sin")
                    continue;
                // we found a typed named operator with a slash! (useless users!)
                var toremove = nop.length - 1;

                for (var i = 0; i < toremove; i++) {
                    var elem = this.curElemSet.getElemBeforeInput();
                    if (!elem)
                        continue;

                    // find the element before the one we are removing
                    var offset = this.curElemSet.getInputPos();
                    var prevelem = null;
                    var prevelem_offset = offset - 2;
                    if (prevelem_offset > -1)
                        prevelem = this.curElemSet.elements[prevelem_offset];

                    // remove elem from elemset
                    this.curElemSet.elements.splice(offset - 1, 1);

                    // remove the elem html
                    elem.html_elem.remove();
                }
                // need to remove the last nop.length-1 elements in the current set, and change text to \\nop

                return "\\" + nop;
            }
        }

        return "";
    },

    // parse some text and add it to elements
    parseInput: function (text, o) {
        // strip all but keyboard characters that we care about, as firefox passes all char codes here (chrome only passes visible so not needed, no idea about IE)

        text = text.replace(/[^a-zA-z0-9 .\,\/\<\>\?\;\:\"\'\`\!\@\#\$\%\^\&\*\(\)\[\]\{\}\_\+\=\-\|\\\~\#]+/g, '');
        //text = text.replace(/[^a-zA-z0-9\\\+\-\=\_\^\$\/]+/g,'');

        if (text == "")
            return false;

        // types something, and we have a selected content, need to replace the content
        if (typeof this.curElemSet.selectStart == "number") {
            this.deleteSelection();
        }

        if (text.length > 1)
            this.prevlatex = text;
        else
            this.prevlatex += text;

        var nop = this.checkPrevElems(text);
        if (nop) {
            text = nop + " ";
            this.prevlatex = "";
        }
        var allowspaces = false;
        if (this.curElemSet && this.curElemSet.parent && this.curElemSet.parent.eldata && this.curElemSet.parent.eldata.allowspaces)
            allowspaces = true;

        if (text == " " && this.curElemSet.leaveonsinglespace) {
            o.text = "";
            this.curElemSet.leaveonsinglespace = false;
            this.moveToParent();
            return true;
        }

        var tokens = this.parser.tokenize(text, true, allowspaces);

        var lastvalid = -1;
        for (var i = tokens.length; i > 0; i--) {
            if ('valid' in tokens[i - 1] && tokens[i - 1].valid) {
                lastvalid = i;
                break;
            }
        }

        if (lastvalid == -1) {
            this.lasttext = text;
            return false;
        }

        // check for sub and superscript tokens
        for (var i = 0; i < lastvalid; i++) {
            var token = tokens[i];
            var eldata = this.parser.getElementData(token);

            // if we have a sup or sub token, then do a script
            if (token.type == "subscript" || token.type == "superscript") {

                // get the element we are editing
                var elem = this.curElemSet.getElemBeforeInput();

                if (!elem) {
                    // need to create an empty elemnt for the sub and superscript to attach to
                    var elem = this.createNewElem(token, tokens, i);

                    // insert it into the current element set before the input element
                    this.insertIntoCurrentSet(elem);
                }

                // subscript processing
                if (elem[token.type]) {
                    var script = elem[token.type];
                } else {
                    var script = elem.SetScript(token, null, true);
                }
                // if there is no token for the subscript, then we have just types a _ or ^, so move to the subscript
                if (token.latex == "")
                    this.moveToSet(script);

            } else if (token.type == "arg") {
                if (token.incompletearg) {
                    this.curElemSet.single = false;

                    if (token.latex != "") {
                        // need to add the contents of the latex to the argument we are currently in, and 
                        // copy the rest of it into the input box as this will only occur if we have pasted in some stuff
                    }
                } else {

                    // need to add the token as the contents of the argument
                    // of the previous command
                    // this should only happen with pasted data, so will
                    // need to parse and add multiple elements


                }
            } else if (token.type == "extsingle" && token.latex == '[') {
                if (token.incompletearg) {
                    if (this.curElemSet.inarg) {
                        // we have done something like \sqrt[, the \sqrt has put us in the first argument, so need to find the parent and 
                        // move to its sarg for the [. Need to store the current argument to return to after the closing ].
                        var elem = this.curElemSet.parent;
                    } else {
                        var elem = this.curElemSet.getElemBeforeInput();
                    }

                    if (!elem) {
                        var elem = this.createNewElem(token, tokens, i);
                        this.insertIntoCurrentSet(elem);
                        continue;
                    }

                    if (elem.eldata.sarg_as_sup) {
                        if (!elem.superscript) {
                            // no superscript, so create one
                            var arg1 = {
                              latex : "",
                              type : "superscript"
                            };
                            elem.SetScript(arg1, null, true);
                        }
                        this.moveToSet(elem.superscript, true, false);
                        //this.curElemSet.single = false;
                        this.curElemSet.insarg = true;
                    } else if (elem.eldata.sarg_as_lower) {
                        // hunt down lower element set
                        this.moveToSet(elem.parent.row1.col0, true, false);
                        //this.curElemSet.single = false;
                        this.curElemSet.insarg = true;
                    } else {
                        var elem = this.createNewElem(token, tokens, i);
                        this.insertIntoCurrentSet(elem);
                    }

                    // move to the sarg of the element

                } else {
                    // complete sarg added, probably a paste!
                }
            } else if (token.type == "extsingle" && token.latex == ']' && this.curElemSet.insarg) {
                // we have a single closing ], and we are in a sarg
                this.moveToParent();

            } else if (token.type == "extsingle" && token.latex == '/') {
                // / typed, we need to do a over bracket thingy

                this.handleRSlash(token);

            } else if (eldata.all_as_arg01) {
                // / typed, we need to do a over bracket thingy

                this.handleAllAsArg10(token);

            } else if (token.type == "extsingle" && token.latex == "}") {
                // we have a single closing }

                // dont shuffle up in an array
                // find out if we are in an array
                var inarray = false;
                if (this.curElemSet.parent && this.curElemSet.parent.parent && this.curElemSet.parent.parent.parent) {
                    if (this.curElemSet.parent.parent.parent.type == "begin")
                        inarray = true;
                }

                if (this.curElemSet.parent && !inarray /*&& this.curElemSet.parent._name != "MEE.Row"*/) {
                    // shuffle up to the parent element set
                    var elem = this.moveToParent();

                    // if we have arguments left to add, then automatically goto the next one
                    this.moveToAnyArgs(elem);

                }
            } else if (token.type == "begin") {

                // create new element based on token
                var elem = this.createNewElem(token, tokens, i);

                if (tokens.length > i) {
                    // we have an argument token
                    elem.AddArray(tokens[i + 1]);
                }

                // insert it into the current element set before the input element
                this.insertIntoCurrentSet(elem);

                // move to the first row and column of the array
                var target = elem.main.row0.col0;

                this.moveToSet(target);
                this.curElemSet.inmatrix = true;

            } else if (token.type == "tab") {
                if (this.curElemSet.inmatrix) {
                    var matrix = this.getCurrentMatrix();
                    if (!matrix)
                        continue;

                    if (matrix && matrix.eldata.frac)
                        continue;

                    var pos = matrix.getPosition(this.curElemSet);

                    // are we in the last column? if not move along a column
                    if (this.curElemSet.col < matrix.cols - 1) {
                        var nextcol = this.curElemSet.col + 1;
                        pos.col++;

                        newset = matrix.getSetAt(pos);

                        matrix.fillInBlankCols();

                        this.moveToParent();
                        this.moveToSet(newset);

                        this.curElemSet.inmatrix = true;
                    } else {
                        // add a column to the matrix and move to it
                        this.arrayAppendCol();
                    }
                }
            } else if (token.type == "newline") {
                if (this.curElemSet.inmatrix) {
                    // insert a new row after the current one, and move to the start of it

                    var matrix = this.getCurrentMatrix();
                    if (!matrix)
                        continue;

                    if (matrix && matrix.eldata.frac)
                        continue;

                    var pos = matrix.getPosition(this.curElemSet);
                    if (pos.row == -1)
                        return;

                    var newset = null;
                    if (pos.row < matrix.rows - 1) {
                        pos.row++;
                        pos.col = 0;
                        matrix.InsertRow(pos.row);
                        newset = matrix.getSetAt(pos);
                    } else {
                        matrix.AppendRow();
                        pos.row = matrix.rows - 1;
                        pos.col = 0;
                        newset = matrix.getSetAt(pos);
                    }

                    if (newset) {
                        this.moveToSet(newset, false, false);
                    } else {
                        this.moveToSet(this.elementset, false, false);
                    }
                }
            } else if (token.type == "end") {

                var matrix = this.getCurrentMatrix();
                if (!matrix)
                    continue;

                if (matrix && matrix.eldata.frac)
                    continue;

                this.moveToParent(true);

            } else if (token.type == "size") {
                this.sizenext = token.latex;
            } else if (token.type == "extsingle" && token.isclosing) {
                //alert("Closing bracket");
                this.handleClosingBracket(token);
            } else {
                // anything else should be dealt with as a new element

                if (this.sizenext) {
                    token.size = this.sizenext;
                    this.sizenext = null;
                }

                // create new element based on token
                var elem = this.createNewElem(token, tokens, i);

                // insert it into the current element set before the input element
                this.insertIntoCurrentSet(elem);

                // if we are an arg or similar with no brackets, then need to shuffle up to the parnet set
                if (this.curElemSet.single && !this.curElemSet.inmatrix) {
                    this.curElemSet.single = false;
                    elem = this.moveToParent();
                }

                // if we have arguments left to add, then automatically goto the next one
                this.moveToAnyArgs(elem);
            }
        }

        for (var i = lastvalid; i < tokens.length; i++) {
            if (tokens[i].type == "command")
                o.text += "\\";
            o.text += tokens[i].latex;
        }
        o.text = $.trim(o.text);

        return true;
    },

    handleRSlash: function (token) {
        var offset = this.curElemSet.getInputPos();
        var backto = -1;
        for (var i = offset - 1; i >= 0; i--) {
            var elem = this.curElemSet.elements[i];
            if (elem.eldata.noautofrac) {
                backto = i;
                break;
            }
        }

        // create fraction
        var ftoken = {
          latex : "frac",
          type : "command"
        }
        var elem = this.createNewElem(ftoken);
        this.insertIntoCurrentSet(elem);

        // move all previous elements into the top of the fraction
        var upperset = elem.main.row0.col0;

        // remove all previous element from the set
        for (var k = backto + 1; k < offset; k++) {
            var tomove = this.curElemSet.elements[k];
            upperset.elements.push(tomove);
            upperset.html_elem.append(tomove.html_elem);
            tomove.parent = upperset;
            //console.log("Moving elem " + tomove.latex);
        }
        this.curElemSet.elements.splice(backto + 1, offset - backto - 1);

        // move to lower half of the fraction
        var lowerset = elem.main.row1.col0;

        this.moveToSet(lowerset, false, false);
        lowerset.leaveonsinglespace = true;
    },

    handleClosingBracket: function (token) {
        if (this.sizenext) {
            token.size = this.sizenext;
            this.sizenext = null;
        }

        // try to hunt down a previous opening bracket in the current element set
        var offset = this.curElemSet.getInputPos();
        var backto = -1;
        for (var k = offset - 2; k >= 0; k--) {
            // if this is an opening single bracket set result to true and break;
            var elem = this.curElemSet.elements[k];

            if (elem.type == "extsingle") {
                backto = k;
                break;
            }
        }

        // if found, turn this into a bracket pair
        if (backto > -1) {
            // get bracket type and size
            var lb_elem = this.curElemSet.elements[backto];
            var lbtype = lb_elem.latex;
            var lbsize = lb_elem.size;
            if (lbsize == 0)
                lbsize = -1;

            // create a new element set
            var ntoken = {};
            ntoken.latex = lbtype.replace("l", "p");
            if (ntoken.latex == "(")
                ntoken.latex = "pbrackets";
            if (ntoken.latex == "{")
                ntoken.latex = "pbrace";
            if (ntoken.latex == "[")
                ntoken.latex = "psqbrackets";
            ntoken.type = "extpair";
            var elem = this.createNewElem(ntoken);
            var itoken = {};
            itoken.latex = "";
            elem.SetMain(itoken);

            // add left and right brackets to the element set
            elem.size = lbsize;

            elem.sizer = token.size;
            if (elem.sizer == 0)
                elem.sizer = -1;

            var rbeldata = this.parser.getElementData(token);
            if (rbeldata.text)
                elem.eldata.rb = rbeldata.text; // lookup token.latex and get text from it
            else
                elem.eldata.rb = token.latex;

            // add element set to the doc
            this.insertIntoCurrentSet(elem);

            // move all items before us into the element set
            for (var k = backto + 1; k < offset; k++) {
                var tomove = this.curElemSet.elements[k];
                elem.main.elements.push(tomove);
                elem.main.html_elem.append(tomove.html_elem);
                tomove.parent = elem.main;
                //console.log("Moving elem " + tomove.latex);
            }

            elem.eldata.args = 0;


            // remove the bracket at backto
            lb_elem.html_elem.remove();

            // remove moved elements out of the old element set
            this.curElemSet.elements.splice(backto, offset - backto);

            // redo all offsets of curElemSet
            for (var k = 0; k < this.curElemSet.elements.length; k++) {
                this.curElemSet.elements[k].offset = k;
            }
            // redo all offsets of new elem set
            elem.main.sortBlanks();

        } else {
            // not found then just add a bracket and carry on

            var elem = this.createNewElem(token);
            this.insertIntoCurrentSet(elem);
        }
    },

    handleAllAsArg10: function (token) {
        // need to handle stuff like \over or \choose

        var offset = this.curElemSet.getInputPos();

        var elem = this.createNewElem(token);
        this.insertIntoCurrentSet(elem);

        // move all previous elements into the top of the fraction
        var upperset = elem.main.row0.col0;

        // remove all previous element from the set
        for (var k = 0; k < offset; k++) {
            var tomove = this.curElemSet.elements[k];
            upperset.elements.push(tomove);
            upperset.html_elem.append(tomove.html_elem);
            tomove.parent = upperset;
            //console.log("Moving elem " + tomove.latex);
        }
        this.curElemSet.elements.splice(0, offset);

        // move to lower half of the fraction
        var lowerset = elem.main.row1.col0;

        this.moveToSet(lowerset, false, false);
        lowerset.leaveonsinglespace = true;

    },

    inputKeyUp: function (elem, ev) {

    },

    doPaste: function () {
        this.inputAdd("");
        this.curElemSet.single = false;
        this.changed(null, 'doPaste');
    },

    inputKeyDown: function (elem, event) {
        if (event.ctrlKey && !event.shiftKey && event.keyCode == 90)
            return this.doUndo();

        if (event.ctrlKey && event.shiftKey && event.keyCode == 90)
            return this.doRedo();

        if (event.ctrlKey && !event.shiftKey && event.keyCode == 86) {
            // paste done, do a timeout to paste in the code
            setTimeout('MEE.Edit.activeEdit.doPaste()', 100);
        }

        var key = event.which;
        if (key == 8 || key == 32 || key == 46 || key == 35 || key == 36 || key == 37 || key == 38 || key == 39 || key == 40) {
            //console.log("inputKeyDown");

            // need to check current input and parse it


            if (this.mode == 1) {
                // if we are in W mode (should be), then need to naviagte the input box first
                var caret = this.inputelembox.caret();
                var value = this.inputelembox.val();
                var vallen = value.length;
                if (key == 8 || key == 46)
                    this.lasttext = value;

                if (key == 37 || key == 8) { // left // backspace
                    if (caret.start > 0) {
                        this.changed(key, 'inputKeyDown');
                        return true;
                    }
                } else if (key == 39 || key == 46) { // right // delete
                    if (caret.start < vallen) {
                        this.changed(key, 'inputKeyDown');
                        return true;
                    }
                }
                if ((key == 37 || key == 39 || key == 35 || key == 36) && event.shiftKey) // shift left
                {
                    this.handleSelectMultiple(key);
                    this.changed();
                    return true;
                }
            }


            //if (key == 8 || key == 46 || key == 37 || key == 38 || key == 39 || key == 40)
            if (key == 32)
                this.inputAdd(" ");
            else if (typeof this.curElemSet.selectStart != "number")
                this.inputAdd("");

            this.processInput(event);

            this.lasttext = "";

            this.changed(key, 'inputKeyDown');

            this.dump();

            if (key == 32)
                return false;
        }
    },

    handleSelectMultiple: function (key) {
        if (typeof this.curElemSet.selectStart != "number") {
            this.curElemSet.selectStart = this.curElemSet.getInputPos();
        }

        if (key == 37) {
            // move input left
            var curelempos = this.curElemSet.getInputPos();
            curelempos -= 2;
            if (curelempos < 0) {
                // move to start of set
                this.moveToSetStart(this.curElemSet);
                return;
            }
            var newelem = this.curElemSet.elements[curelempos];

            this.moveToElement(newelem);
        } else if (key == 39) {
            // move input right

            var curelempos = this.curElemSet.getInputPos();
            curelempos++;
            if (curelempos >= this.curElemSet.elements.length)
                return;

            var newelem = this.curElemSet.elements[curelempos];

            this.moveToElement(newelem);
        } else if (key == 35) { // end
            var newelem = this.curElemSet.elements[this.curElemSet.elements.length - 1];

            this.moveToElement(newelem);
        } else if (key == 36) { // home
            this.moveToSetStart(this.curElemSet);
        }
    },

    getSelStartEnd: function () {
        var start = this.curElemSet.selectStart;
        var end = this.curElemSet.getInputPos();

        var ip = end;

        if (start > end) {
            var temp = end;
            end = start;
            start = temp;
        }

        if (start == end)
            return;

        if (start == ip)
            start++;
        if (end == ip)
            end--;

        if (start > end)
            return { 'start': -1, 'end': -1 };

        return { 'start': start, 'end': end };
    },

    deleteSelection: function () {
        // pressed delete on a selected set of data, so remove it

        if (typeof this.curElemSet.selectStart != "number")
            return false;

        var sel = this.getSelStartEnd();

        if (sel.start == -1)
            return;

        var count = sel.end - sel.start + 1;
        for (var i = 0; i < count; i++) {
            var elem = this.curElemSet.elements[sel.start];

            // remove elem from elemset
            this.curElemSet.elements.splice(sel.start, 1);

            // remove the elem html
            elem.html_elem.remove();
        }

        this.curElemSet.selectStart = null;
        return true;
    },

    // handle input to the wysiwyg editor
    processInput: function (event) {
        // build cursor naviagtion here
        var key = event.which;

        if (key == 8) { // backspace 
            // need to remove the element after the input. If the element is has more than just a simple main (ie things like subscripts
            // and super scripts), highlight it. then if press delete again delete it.

            if (typeof this.curElemSet.selectStart == "number") {
                return this.deleteSelection();
            }

            var elem = this.curElemSet.getElemBeforeInput();
            if (!elem) {
                this.moveToParent();
                elem = this.curElemSet.getElemBeforeInput();
                if (elem && (elem.eldata.inmatrix || elem.eldata.frac))
                    return;
            }
            if (!elem) {
                return;
            }

            // find the element before the one we are removing
            var offset = this.curElemSet.getInputPos();
            var prevelem = null;
            var prevelem_offset = offset - 2;
            if (prevelem_offset > -1)
                prevelem = this.curElemSet.elements[prevelem_offset];

            // remove elem from elemset
            this.curElemSet.elements.splice(offset - 1, 1);

            // remove the elem html
            elem.html_elem.remove();


        } else if (key == 46) { // delete
            // need to remove the element before the input. If the element is has more than just a simple main (ie things like subscripts
            // and super scripts), highlight it. then if press delete again delete it.
            if (typeof this.curElemSet.selectStart == "number") {
                return this.deleteSelection();
            }


            var offset = this.curElemSet.getInputPos();
            if (this.curElemSet.elements.length <= offset + 1)
                return;

            var elem = this.curElemSet.elements[offset + 1];

            // remove elem from elemset
            this.curElemSet.elements.splice(offset + 1, 1);

            // remove the elem html
            elem.html_elem.remove();
        } else if (key == 35) { // end
            this.curElemSet.selectStart = null;
            var offset = this.curElemSet.getInputPos();

            if (offset + 1 < this.curElemSet.elements.length) {
                // move to end of current set
                this.moveToSet(this.curElemSet, false, false);
            } else {
                // move to end of parent set
                var par = this.curElemSet.parent;
                if (par && par._name == "MEE.Row")
                    par = par.parent.parent;

                if (par)
                    par = par.parent;

                if (par)
                    this.moveToSet(par, false, false);
            }

        } else if (key == 36) { // home
            this.curElemSet.selectStart = null;
            var offset = this.curElemSet.getInputPos();

            if (offset > 0) {
                // move to end of current set
                this.moveToSetStart(this.curElemSet);
            } else {
                // move to end of parent set
                var par = this.curElemSet.parent;
                if (par && par._name == "MEE.Row")
                    par = par.parent.parent;

                if (par)
                    par = par.parent;

                if (par)
                    this.moveToSetStart(par);
            }


        } else if (key == 37) { // left 
            //////////////////////////////
            //#region LEFT LEFT LEFT LEFT LEFT //
            //////////////////////////////
            // need to move the input box left a place
            this.curElemSet.selectStart = null;

            // get position of input
            var offset = this.curElemSet.getInputPos();


            if (offset == 0) {
                // we are at the beginning of this element set, so need to move the cursor to the parent set
                var parelem = this.curElemSet.parent;
                if (!parelem)
                    return;
                var parset = parelem.parent;

                // remove the input from the current set
                this.curElemSet.removeInput();

                if (parset.isarray) {
                    // if parent element is an array, then try to move to the previous column
                    var pos = parset.getPosition(this.curElemSet);
                    this.curElemSet = parset;

                    if (pos.col > 0) {
                        // prev col available, move to it
                        var newcol = pos.col - 1;
                        this.curElemSet = this.curElemSet['row' + pos.row]['col' + newcol];
                        this.curElemSet.elements.push(this.input);
                        this.curElemSet.html_elem.append(this.inputelem);

                        return;
                    }

                    // no previous column, so move out of the array to the parent elementset
                    parelem = parset.parent;
                    parset = parelem.parent;

                }

                // nothing in this element set to move to, so move to the parent element set before the current element
                this.curElemSet = parset;
                var offset = this.curElemSet.getElementOffset(parelem);
                this.inputelem.insertBefore(this.curElemSet.elements[offset].html_elem);
                this.curElemSet.elements.splice(offset, 0, this.input);

                return;
            }

            // get previous element
            var prevelem = this.curElemSet.elements[offset - 1];

            if (!prevelem)
                return;

            this.curElemSet.removeInput();

            // is the previous element an array, if so try to move to its top row end
            if (prevelem.main && prevelem.main.isarray) {
                // array here
                this.curElemSet = prevelem.main;
                if (this.curElemSet.rows > 0 || this.curElemSet.cols > 0) {
                    this.curElemSet = this.curElemSet.row0['col' + (this.curElemSet.cols - 1)];
                    this.curElemSet.elements.push(this.input);
                    this.curElemSet.html_elem.append(this.inputelem);

                    return;
                }
            }


            if (prevelem.eldata.arg0_as_main && prevelem.main.elements) {
                // arg0_as_main on previous element, then move to the end of its elem set
                prevelem.main.elements.push(this.input);
                prevelem.main.html_elem.append(this.inputelem);
                this.curElemSet = prevelem.main;

                return;
            }

            // normal movement to previous element
            this.inputelem.insertBefore(this.curElemSet.elements[offset - 1].html_elem);
            this.curElemSet.elements.splice(offset - 1, 0, this.input);

            //#endregion
        } else if (key == 39) { // right
            ///////////////////////////////////
            //#region  RIGHT RIGHT RIGHT RIGHT RIGHT //
            ///////////////////////////////////
            this.curElemSet.selectStart = null;

            // get position of input
            var offset = this.curElemSet.getInputPos();

            // are we at the end of the current element set?
            if (offset == this.curElemSet.elements.length - 1) {
                // get parent set
                var parelem = this.curElemSet.parent;
                if (!parelem)
                    return;

                var parset = parelem.parent;


                // if parent is an array, then try to move to the previous columns
                if (parset.isarray) {
                    var pos = parset.getPosition(this.curElemSet);
                    //this.curElemSet = ;

                    if (pos.col < parset.cols - 1) {
                        var newcol = pos.col + 1;
                        var newset = parset['row' + pos.row]['col' + newcol];
                        if (newset) {
                            this.moveToSet(newset, false, false);
                        }
                        return;
                    }

                    // if we arent in an empty column, the create on and move to it
                    if (!parset.isColBlank(pos.col, true) && !parset.eldata.frac) {
                        return this.arrayAppendCol(true, parset);
                    }

                    // out the array!
                    parelem = parset.parent;
                    parset = parelem.parent;

                }

                // move to the parent set before the current element
                this.curElemSet.removeInput();
                this.curElemSet = parset;
                var offset = this.curElemSet.getElementOffset(parelem);
                this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
                this.curElemSet.elements.splice(offset + 1, 0, this.input);

                return;
            }

            var nextelem = this.curElemSet.elements[offset + 1];

            if (!nextelem)
                return;

            if (nextelem.main && nextelem.main.isarray) {
                // array here

                if (nextelem.main.rows > 0 || nextelem.main.cols > 0) {
                    this.curElemSet.removeInput();
                    this.curElemSet = nextelem.main.row0.col0;
                    this.curElemSet.elements.unshift(this.input);
                    this.curElemSet.html_elem.prepend(this.inputelem);

                    return;
                }
            }

            if (nextelem.eldata.arg0_as_main && nextelem.main.elements) {
                // arg0_as_main on previous element, then move to the end of its elem set
                this.curElemSet.removeInput();
                nextelem.main.elements.unshift(this.input);
                nextelem.main.html_elem.prepend(this.inputelem);
                this.curElemSet = nextelem.main;
            } else {
                // no arg0_as_main
                this.curElemSet.removeInput();
                this.curElemSet.elements.splice(offset + 1, 0, this.input);
                this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
            }
            /*this.inputelem.insertAfter(this.curElemSet.elements[offset+1].html_elem);
            this.curElemSet.elements.splice(offset+1, 0, this.input);*/

            //#endregion

        } else if (key == 38) { // up
            ////////////////////////////////
            //#region  UP UP UP UP UP UP UP UP UP //
            ////////////////////////////////
            this.curElemSet.selectStart = null;

            // check for a subscript element to navigate to
            var offset = this.curElemSet.getInputPos();
            var curelem = this.curElemSet.elements[offset - 1];
            if (curelem && curelem.superscript) {
                this.curElemSet.removeInput();
                this.curElemSet = curelem.superscript;
                this.curElemSet.elements.push(this.input);
                this.curElemSet.html_elem.append(this.inputelem);

                return;
            }

            // check previous element is a array or not, if so then goto its top row
            if (curelem && curelem.main && curelem.main.isarray) {
                // array here
                if (curelem.main.rows > 0 || curelem.main.cols > 0) {
                    this.curElemSet.removeInput();
                    this.curElemSet = curelem.main;
                    this.curElemSet = this.curElemSet.row0['col' + (this.curElemSet.cols - 1)];
                    this.curElemSet.elements.push(this.input);
                    this.curElemSet.html_elem.append(this.inputelem);

                    return;
                }
            }

            // get parent element and set
            var parelem = this.curElemSet.parent;
            var savedcurElem = this.curElemSet;

            while (parelem) {
                // iterate up parent elements to try and find somewhere to move to

                var parset = parelem.parent;

                // if this is a superscript, and parent is an array, try to move to the prev array row
                // TODO!!!!!

                // if parent is a superscript, then move back down to the element
                if (this.curElemSet.subscript) {
                    savedcurElem.removeInput();
                    this.curElemSet = parset;
                    var offset = this.curElemSet.getElementOffset(parelem);
                    this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
                    this.curElemSet.elements.splice(offset + 1, 0, this.input);

                    return;
                }

                // if parent is an array, then move down an item
                if (parset.isarray) {
                    var pos = parset.getPosition(this.curElemSet);

                    if (pos.row > 0) {
                        var newrow = pos.row - 1;
                        var newset = parset['row' + newrow]['col' + pos.col];
                        if (newset) {
                            savedcurElem.removeInput();

                            this.curElemSet = newset;
                            this.curElemSet.elements.unshift(this.input);
                            this.curElemSet.html_elem.prepend(this.inputelem);
                        }
                        return;
                    }
                }

                this.curElemSet = parset;
                var parelem = this.curElemSet.parent;
            }
            this.curElemSet = savedcurElem;

            //#endregion

        } else if (key == 40) { // down
            //////////////////////////////
            //#region  DOWN DOWN DOWN DOWN DOWN //
            //////////////////////////////
            // does the opposite of up basically
            this.curElemSet.selectStart = null;

            var offset = this.curElemSet.getInputPos();


            // check for a subscript element to navigate to
            var curelem = this.curElemSet.elements[offset - 1];
            if (curelem && curelem.subscript) {
                this.curElemSet.removeInput();
                this.curElemSet = curelem.subscript;
                this.curElemSet.elements.push(this.input);
                this.curElemSet.html_elem.append(this.inputelem);

                return;
            }

            // check previous element is a array or not, if so then goto its bottom row
            if (curelem && curelem.main && curelem.main.isarray) {
                // array here
                if (curelem.main.rows > 0 || curelem.main.cols > 0) {
                    this.curElemSet.removeInput();
                    this.curElemSet = curelem.main;
                    this.curElemSet = this.curElemSet['row' + (this.curElemSet.rows - 1)]['col' + (this.curElemSet.cols - 1)];
                    this.curElemSet.elements.push(this.input);
                    this.curElemSet.html_elem.append(this.inputelem);

                    return;
                }
            }
            // get parent element and set
            var parelem = this.curElemSet.parent;
            var savedcurElem = this.curElemSet;

            while (parelem) {
                // iterate up parent elements to try and find somewhere to move to

                var parset = parelem.parent;


                // if this is a subscript, and parent is an array, try to move to the next array row
                // TODO !!!!

                // if parent is a superscript, then move back down to the element
                if (this.curElemSet.superscript) {
                    savedcurElem.removeInput();
                    this.curElemSet = parset;
                    var offset = this.curElemSet.getElementOffset(parelem);
                    this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
                    this.curElemSet.elements.splice(offset + 1, 0, this.input);

                    return;
                }

                // if parent is an array, then move down an item
                if (parset.isarray) {
                    var pos = parset.getPosition(this.curElemSet);

                    if (pos.row < parset.rows - 1) {
                        var newrow = pos.row + 1;
                        var newset = parset['row' + newrow]['col' + pos.col];
                        if (newset) {
                            savedcurElem.removeInput();

                            this.curElemSet = newset;
                            this.curElemSet.elements.unshift(this.input);
                            this.curElemSet.html_elem.prepend(this.inputelem);
                        }
                        return;
                    } else {
                        // we are at the bottom of a matrix, if we are in a blank row then do notihng,
                        var row = this.curElemSet.parent;
                        var matrix = row.parent;

                        if (!matrix.isRowBlank(row.row, true) && !matrix.eldata.frac) {
                            // add a row to the matrix and move to the first column
                            return this.arrayAppendRow();
                        }
                        // if we arent in a blank row then create a new row
                    }
                }

                this.curElemSet = parset;
                var parelem = this.curElemSet.parent;
            }
            this.curElemSet = savedcurElem;
            //#endregion
        }
    },


    elemClick: function (html_elem) {
        //console.log("elemClick");
        this.curElemSet.selectStart = null;

        if (this.mode == 0)
            return;

        this.activate();
            
        // need to find the element that contains the clicked element
        var elemid = $(html_elem).attr('elem');
        if (!elemid)
            return true;

        var elem = MEE.Elem.elems[elemid];
        if (!elem)
            return true;

        this.inputAdd("");
        this.moveToElement(elem);

        this.changed(null, 'elemClick');
        return false;
    },

    elemDblClick: function (html_elem, event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        // already moved to the element, as click will be called first
        // need to select all in around the current elemetn

        var curpos = this.curElemSet.getInputPos();

        // if this element has no_auto_frac, just select it on its own
        var elem = this.curElemSet.elements[curpos - 1];

        if (elem.eldata.noautofrac) {
            this.curElemSet.selectStart = curpos - 1;
            this.changed(null, 'elemDblClick');
            return true;
        }

        var endpos = curpos - 1;
        // need to check elements either side of this one, and find the end and start of the elements with no no_auto_frac
        for (var i = curpos + 1; i < this.curElemSet.elements.length; i++) {
            var elem = this.curElemSet.elements[i];
            if (elem.eldata.noautofrac)
                break;

            endpos = i;
        }

        if (endpos == -1)
            return true;

        var endelem = this.curElemSet.elements[endpos];
        this.moveToElement(endelem);

        var startpos = curpos - 1;
        for (var i = endpos - 1; i >= 0; i--) {
            var elem = this.curElemSet.elements[i];
            if (elem.eldata.noautofrac)
                break;

            startpos = i;
        }

        if (startpos == -1)
            return;

        this.curElemSet.selectStart = startpos;

        this.changed(null, 'elemDblClick');
        return true;
    },

    emptyClick: function (html_elem) {
        //console.log("emptyClick");
        this.curElemSet.selectStart = null;

        if (this.mode == 0)
            return true;

        this.activate();
            
        // need to find the element that contains the clicked element
        var elemid = $(html_elem.parentNode).attr('elem');
        var elemset = MEE.ElemSet.elemsets[elemid];
        if (!elemset)
            return true;

        this.inputAdd("");
        this.moveToSet(elemset, false, false);

        this.changed(null, 'emptyClick');

        return false;
    },


    getNewWYSIWYGLatex: function () {
        var newlatex = this.elementset.toLatex().get();
        $(this.inputelement).val(newlatex);

        if (this.maxima) {
            var maxima = MEE.Maxima.Convert(this.elementset);
            $(this.maximaoutput).val(maxima);
        }

        this.latex = newlatex;

        if (this.undo)
            this.addUndo();
    },
    //#endregion 

    //#region Moving input element around

    moveToSet: function (newset, storeprev, single) {
        if (typeof (storeprev) == "undefined") storeprev = true;
        if (typeof (single) == "undefined") single = true;

        // get current location for later user
        var curset = this.curElemSet;
        var curelem = this.curElemSet.getElemBeforeInput();

        // remove the input element from the current element set
        this.curElemSet.removeInput();

        // move input to the end of the subscript element set
        newset.elements.push(this.input);

        // move the input html to the subscript element html
        this.inputelem.appendTo(newset.html_elem);

        // set current elemset to subscript
        this.curElemSet = newset;

        // store current position for later
        if (storeprev) {
            this.curElemSet.returnto_elem = curelem;
            this.curElemSet.returnto_set = curset;
        }
        //this.changed(null, 'moveToSet');

        this.curElemSet.single = single;
    },

    moveToSetStart: function (newset) {
        // remove the input element from the current element set
        this.curElemSet.removeInput();

        //console.log('moveToSetStart');
        this.prevlatex = "";

        // move input to the end of the subscript element set
        newset.elements.splice(0, 0, this.input);

        // move the input html to the subscript element html
        this.inputelem.prependTo(newset.html_elem);

        // set current elemset to subscript
        this.curElemSet = newset;

        //this.changed(null, 'moveToSetStart');
    },

    moveToElement: function (newelem) {
        //console.log('moveToElement');
        this.prevlatex = "";

        this.curElemSet.removeInput();
        this.curElemSet = newelem.parent;
        var offset = this.curElemSet.getElementOffset(newelem);
        this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
        this.curElemSet.elements.splice(offset + 1, 0, this.input);

        this.curElemSet.single = false;

        //this.changed(null, 'moveToElement');
    },

    // moves the input location to the parent element
    moveToParent: function (nohist) {
        //console.log('moveToParent');
        this.prevlatex = "";

        // element is only a single element, so shuffle up to the parent element set
        this.curElemSet.removeInput();

        var elem = null;
        var _set = null;

        if (this.curElemSet.returnto_set && nohist != true) {
            elem = this.curElemSet.returnto_elem;
            _set = this.curElemSet.returnto_set;
        } else {
            // need to find the parent element set, and the element that contains this element set
            elem = this.curElemSet.parent;
            if (elem && elem._name == "MEE.Row")
                elem = elem.parent.parent;
            if (elem)
                _set = elem.parent;
            else
                _set = this.elementset;
        }
        this.curElemSet = _set;

        // sort the html out
        if (elem) {
            this.inputelem.insertAfter(elem.html_elem);

            // move this.input to the correct position in curElemSet.elements
            var offset = elem.offset;
            this.curElemSet.elements.splice(offset + 1, 0, this.input);

        } else {

            this.inputelem.appendTo(_set.html_elem);
            _set.elements.push(this.input);
        }

        //this.changed(null, 'moveToParent');
        return elem;
    },

    moveToAnyArgs: function (elem) {
        if (!elem)
            return;
        // if we have arguments left to add, then automatically goto the next one
        if (elem.eldata.args > 0 /*& !elem.eldata.sarg > 0*/) {

            var ca = elem.eldata.currentarg;
            if (typeof (ca) == "undefined")
                return;

            this.moveToSet(elem.argmap[ca]);
            this.prevlatex = "";

            elem.eldata.args--;
            elem.eldata.currentarg++;
            this.curElemSet.inarg = true;
        }
    },

    insertIntoCurrentSet: function (elem) {
        elem.parent = this.curElemSet;

        var offset = this.curElemSet.insertElemBeforeInput(elem);
        this.curElemSet.insertHTMLFor(elem, offset);

        //this.changed(null, 'insertIntoCurrentSet');
    },

    // creates a new element base on token, and inserts it into the current element set
    createNewElem: function (token, tokens, i) {

        var tokens3 = new Array();
        tokens3.push(token);
        var elems = this.parser.buildelements(tokens3);
        if (elems.length == 0)
            return;

        var elem = elems[0];

        if (elem.eldata.args > 0) {
            if (elem.eldata.arg01_as_upperlower || elem.eldata.arg0_as_upper) {
                var arg1 = {
                  latex : ""
                }
                var arg2 = {
                  latex : ""
                }
                elem.AddUpperLower(arg1, arg2);
            } else if (elem.eldata.arg0_as_main) {
                var arg = {
                  latex : ""
                };
                elem.SetMain(arg);
            } else {
                for (var a = 0; a < elem.eldata.args; a++) {
                    var arg =  {
                      latex : ""
                    };
                    elem.AddArg(arg);
                }
            }
            elem.eldata.currentarg = 0;
        }

        if (elem.eldata.sarg) {
            if (elem.eldata.sarg_as_sup) {
                var arg =  {
                  latex : ""
                };
                elem.SetScript(arg, "superscript");
            } else if (elem.eldata.sarg_as_lower) {
                /*var arg = new Object();
                arg.latex = "";
                elem.SetScript(arg, "superscript");*/
            } else {
                var arg = {
                  latex : ""
                };
                elem.SetSArg(arg);
            }
        }


        return elem;
    },


    //#endregion 

    //#region Undo
    createUndo: function () {
        this.undo = new MEE.Undo();
        this.sortUndoMeun();
    },

    addUndo: function () {
        if (this.noundo)
            return;

        var lastundo = this.undo.CurrentUndo();
        if (lastundo && lastundo.latex == this.latex)
            return;

        var undoobj = {
          latex : this.latex
        }
        if (this.rawinput)
            undoobj.caret = this.rawinput.caret();

        this.undo.Add(undoobj);

        this.sortUndoMeun();
    },

    doUndo: function () {
        this.applyUndoRedo(this.undo.Undo());

        return false;
    },

    doRedo: function () {
        this.applyUndoRedo(this.undo.Redo());

        return false;
    },

    applyUndoRedo: function (undoobj) {
        this.noundo = true;
        if (undoobj != null) {
            if (this.mode == 0) {
                this.setLatex(undoobj.latex, "undo");
                this.rawinput.caret(undoobj.caret);
            } else {
                this.latex = undoobj.latex;
                this.rebuildDisplay();
            }
        }

        this.sortUndoMeun();
        this.noundo = false;
    },

    sortUndoMeun: function () {
        if(MEE.Edit.toolbar) {
          if (this.undo.canUndo()) {
              MEE.Toolbar.ApplyImage('#tb_undo_img', 'toolbar/home_undo.png');
              $('#tb_undo').css('color', '#000000');
              $('#tb_undo').children().css('color', '#000000');
          } else {
              MEE.Toolbar.ApplyImage('#tb_undo_img', 'toolbar/home_undo_g.png');
              $('#tb_undo').css('color', '#CCCCCC');
              $('#tb_undo').children().css('color', '#CCCCCC');
          }
          if (this.undo.canRedo()) {
              MEE.Toolbar.ApplyImage('#tb_redo_img', 'toolbar/home_redo.png');
              $('#tb_redo').css('color', '#000000');
              $('#tb_redo').children().css('color', '#000000');
          } else {
              MEE.Toolbar.ApplyImage('#tb_redo_img', 'toolbar/home_redo_g.png');
              $('#tb_redo').css('color', '#CCCCCC');
              $('#tb_redo').children().css('color', '#CCCCCC');
          }
        }
    },

    //#endregion
    textSize: function (string, scope) {
        if (string == "")
            return 0;

        var el = $('.mee_input_size');
        var fontsize = $(scope).css('font-size');
        el.css('font-size', fontsize);
        el.text(string);
        return el.outerWidth();
    },

    //#region Context Toolbar
    showContextRegions: function () {
        var regions = {
            'bracket_left': 0,
            'bracket_right': 0,
            'bracket_both': 0,
            'matrix': 0,
            'matrix_rows': 0,
            'matrix_cols': 0,
            'scripts': 0,
            'fraction': 0,
            'sqrt': 0,
            'fonts': 0
        };

        //regions.bracket_left = 1;
        //regions.fonts = 1;

        if (this.curElemSet) {
            // try to see if we are in a chem part. 
            var inchem = false;
            var set = this.curElemSet;

            // this is a bit of a quick bodge, but works. Should only really be checking the latex of elemnts, and not the sets
            while (set.parent) {
                if (set.latex == "ce") {
                    inchem = true;
                }
                set = set.parent;
            }

            if (inchem) {
                // If so change highlight of chem button
                if (MEE.Edit.toolbar)
                    MEE.Edit.toolbar.SetHighlighted('tbpm_item_chemmode');
            } else {
                // if no set chem button highlight back to normal
                if (MEE.Edit.toolbar)
                    MEE.Edit.toolbar.SetNormal('tbpm_item_chemmode');

            }
        }

        if (this.curElemSet) {
            var elem = this.curElemSet.getElemBeforeInput();
            if (elem) {
                regions.scripts = 1;
                // we have a selected elemnt, so do something about working out
                // which panes to display
                if (elem.latex == 'sqrt')
                    regions.sqrt = 1;

                if (elem.type == "extpair" || elem.eldata.changetype == "extpair") {
                    regions.bracket_both = 1;
                }
                if (elem.type == "extsingle") {
                    if (elem.latex == "(" || elem.latex == "[" || elem.latex.substr(0, 1) == "l") {
                        regions.bracket_left = 1;
                    } else {
                        regions.bracket_right = 1;
                    }
                }
                if (elem.latex.substr(elem.latex.length - 4) == "frac") {
                    //regions.fraction = 1;
                }
                if (elem.type == "begin") {
                    regions.matrix = 1;
                    regions.matrix_cols = 1;
                    regions.matrix_rows = 1;
                }
                if (elem.eldata.simplemain) {
                    regions.fonts = 1;
                }
            }

            if (this.curElemSet.inmatrix) {
                // this is flagging frac stuff as a matrix also
                if (this.curElemSet.parent.parent.parent.type == "begin") {
                    regions.matrix = 1;
                    regions.matrix_cols = 1;
                    regions.matrix_rows = 1;
                } else {
                    //regions.fraction = 1;
                }
            }

        }

        for (region in regions) {
            var show = regions[region];

            var id = '#mt_pane_' + region;

            if (show) {
                $(id).css('display', 'block');
                $(id).next().css('display', 'block');
            } else {
                $(id).css('display', 'none');
                $(id).next().css('display', 'none');
            }

        }
    },
    //#endregion


    //#region Handle toolbar context sensitive commands
    changeMatrixType: function (newtype) {
        var elem = this.curElemSet.getElemBeforeInput();
        if (!elem)
            return;

        if (elem.type != "begin") {
            // change to the matrix
            elem = elem.parent.parent.parent.parent;
        }
        if (elem.type != "begin")
            return;

        // change latex type
        elem.latex = newtype;

        // remove existing brackets
        if (elem.html_lb) {
            elem.html_lb.remove();
            elem.html_lb = null;
        }
        if (elem.html_rb) {
            elem.html_rb.remove();
            elem.html_rb = null;
        }

        // set up new bracket types from eldata table
        var cmdstr = "\\" + newtype;
        var neweldata = MEE.Data.commands[cmdstr];
        if (!neweldata) {
            alert("Fatal Error : Missing data for " + newtype);
        }
        elem.eldata = jQuery.extend({}, neweldata);

        // create new brackets html and elements
        elem.createLbHTML();
        elem.createRbHTML();

        // trigger update
        this.changed(null, 'changeMatrixType');
    },

    changeFracType: function (newtype) {

    },

    changeFontType: function (newtype) {
        var elem = this.curElemSet.getElemBeforeInput();
        if (!elem)
            return;

        var classList = $(elem.html_elem).attr('class').split(/\s+/);
        for (var i = 0; i < classList.length; i++) {
            var class_name = classList[i];
            if (class_name.substr(0, 9) == "mee_font_")
                $(elem.html_elem).removeClass(class_name);
        }

        var neweldata = MEE.Data.commands["\\" + newtype];
        if (!neweldata)
            return;

        elem.latex = newtype;

        if (neweldata.elemclass) {
            $(elem.html_elem).addClass(neweldata.elemclass);
        }

        this.changed(null, 'changeFontType');
    },

    changeBracket: function (side, newtype, newtype2) {
        var elem = this.curElemSet.getElemBeforeInput();
        if (!elem)
            return;

        var newleft = "";
        var left = false;
        var newright = "";
        var right = false;
        var newleft_base = "";
        var newright_base = "";

        if (side == "left") {
            left = true;
            newleft = this.findNewBracket(newtype, 'lb');
            newleft_base = newtype;
        } else if (side == "right") {
            right = true;
            newright = this.findNewBracket(newtype, 'rb');
            newright_base = newtype;
        } else if (side == "both") {
            left = true;
            newleft = this.findNewBracket(newtype, 'lb');
            right = true;
            newright = this.findNewBracket(newtype2, 'rb');
            newleft_base = newtype;
            newright_base = newtype2;
        }


        if (elem.html_lb) {
            elem.html_lb.remove();
            elem.html_lb = null;
        }
        if (elem.html_rb) {
            elem.html_rb.remove();
            elem.html_rb = null;
        }


        if (left) {
            elem.eldata.lb = newleft;
            elem.latex = newleft_base;
            if (newleft_base == "(" || newleft_base == "[")
                elem.latex = newleft_base;
        }

        if (right) {
            elem.eldata.rb = newright;
            elem.latex = "\\" + newright_base;
            if (newright_base == ")" || newright_base == "]")
                elem.latex = newright_base;
        }


        elem.createLbHTML();
        elem.createRbHTML();

        this.changed(null, 'changeBracket');
    },

    findNewBracket: function (newtype) {
        if (newtype == "none")
            return "";
        var cmdstr = "\\" + newtype;
        var neweldata = MEE.Data.commands[cmdstr];
        if (!neweldata)
            neweldata = MEE.Data.commands[newtype];
        if (!neweldata)
            return newtype;

        return neweldata.text;
    },

    changeBracketSize: function (side, newsize) {
        var elem = this.curElemSet.getElemBeforeInput();
        if (!elem)
            return;

        if (newsize == "auto")
            newsize = -1;
        if (newsize == "default")
            newsize = 0;

        var newleft = "";
        var left = false;
        var newright = "";
        var right = false;

        if (side == "left") {
            left = true;
            newleft = newsize;
        } else if (side == "right") {
            right = true;
            newright = newsize;
        } else if (side == "both") {
            left = true;
            newleft = newsize;
            right = true;
            newright = newsize;
        }


        if (elem.html_lb) {
            elem.html_lb.remove();
            elem.html_lb = null;
        }
        if (elem.html_rb) {
            elem.html_rb.remove();
            elem.html_rb = null;
        }


        if (left) {
            elem.eldata.size = newleft;
            elem.size = newleft;
            elem.eldata.sizer = newleft;
            elem.sizer = newleft;
        }

        if (right) {
            elem.eldata.size = newright;
            elem.size = newright;
            elem.eldata.sizer = newright;
            elem.sizer = newright;
        }


        elem.createLbHTML();
        elem.createRbHTML();


        this.changed(null, 'changeBracketSize');
    },

    getCurrentMatrix: function () {
        var elem = this.curElemSet.getElemBeforeInput();
        var matrix = null;
        if (elem && elem.main && elem.main.isarray) {
            matrix = elem.main;
        } else {
            var row = this.curElemSet.parent;
            matrix = row.parent;
        }

        if (!matrix.isarray)
            return null;

        return matrix;
    },

    arrayInsertRow: function (skipchanged) {
        var matrix = this.getCurrentMatrix();
        if (!matrix)
            return;

        var pos = matrix.getPosition(this.curElemSet);
        if (pos.row == -1)
            return;

        matrix.InsertRow(pos.row);

        var newset = matrix.getSetAt(pos);
        if (newset) {
            this.moveToSet(newset, false, false);
        } else {
            this.moveToSet(this.elementset, false, false);
        }

        if (skipchanged != true)
            this.changed();
    },

    arrayAppendRow: function (skipchanged) {
        // add a row to the matrix and move to the first column
        var matrix = this.getCurrentMatrix();
        if (!matrix)
            return;

        var pos = matrix.getPosition(this.curElemSet);

        matrix.AppendRow();
        pos.row = matrix.rows - 1;

        var newset = matrix.getSetAt(pos);
        if (newset) {
            this.moveToSet(newset, false, false);
        } else {
            this.moveToSet(this.elementset, false, false);
        }

        if (skipchanged != true)
            this.changed();
    },

    arrayDeleteRow: function (skipchanged) {
        var matrix = this.getCurrentMatrix();
        if (!matrix)
            return;

        var pos = matrix.getPosition(this.curElemSet);
        if (pos.row == -1)
            return;

        if (matrix.rows < 2)
            return;

        //alert("Delete Row " + pos.row);
        matrix.DeleteRow(pos.row);

        // now need to move to the row after the deleted one, if it exists
        // other wise the last row
        if (pos.row >= matrix.rows - 1)
            pos.row = matrix.rows - 1;

        var newset = matrix.getSetAt(pos);
        if (newset) {
            this.moveToSet(newset, false, false);
        } else {
            this.moveToSet(this.elementset, false, false);
        }

        if (skipchanged != true)
            this.changed();
    },

    arrayInsertCol: function (skipchanged) {
        var matrix = this.getCurrentMatrix();
        if (!matrix)
            return;

        var pos = matrix.getPosition(this.curElemSet);
        if (pos.col == -1)
            return;

        matrix.InsertCol(pos.col);

        var newset = matrix.getSetAt(pos);
        if (newset) {
            this.moveToSet(newset, false, false);
        } else {
            this.moveToSet(this.elementset, false, false);
        }

        if (skipchanged != true)
            this.changed();
    },

    arrayAppendCol: function (skipchanged, matrix) {
        if (!matrix)
            matrix = this.getCurrentMatrix();
        if (!matrix)
            return;

        // get current position
        var pos = matrix.getPosition(this.curElemSet);
        if (pos.row == -1)
            pos.row = 0;

        // add column
        matrix.AppendCol();

        // set pos to last column
        pos.col = matrix.cols - 1;

        // move to the pos
        var newset = matrix.getSetAt(pos);
        if (newset) {
            this.moveToSet(newset, false, false);
        } else {
            this.moveToSet(this.elementset, false, false);
        }

        if (skipchanged != true)
            this.changed();
    },

    arrayDeleteCol: function (skipchanged) {
        var matrix = this.getCurrentMatrix();
        if (!matrix)
            return;

        var pos = matrix.getPosition(this.curElemSet);
        if (pos.col == -1)
            return;

        if (matrix.cols < 2)
            return;

        //alert("Delete Row " + pos.row);
        matrix.DeleteCol(pos.col);

        // now need to move to the row after the deleted one, if it exists
        // other wise the last row
        if (pos.col >= matrix.col - 1)
            pos.col = matrix.col - 1;

        var newset = matrix.getSetAt(pos);
        if (newset) {
            this.moveToSet(newset, false, false);
        } else {
            this.moveToSet(this.elementset, false, false);
        }

        if (skipchanged != true)
            this.changed();
    }
    //#endregion
});
