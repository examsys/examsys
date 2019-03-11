
// handles accents
MEE.Elem.extend("MEE.ElemAccent",
{
    areLoadLargeFonts: false,

    LoadLargeFonts: function (elem) {
        if (this.areLoadLargeFonts)
            return;

        this.areLoadLargeFonts = true;

        $(elem).append("<span class='mee_large_font_loader' style='font-family: MathJax_Size1'>&#x02DC;</span>");
        $(elem).append("<span class='mee_large_font_loader' style='font-family: MathJax_Size2'>&#x02DC;</span>");
        $(elem).append("<span class='mee_large_font_loader' style='font-family: MathJax_Size3'>&#x02DC;</span>");
        $(elem).append("<span class='mee_large_font_loader' style='font-family: MathJax_Size4'>&#x02DC;</span>");
    },

    RemoveLargeFonts: function () {
        if (this.areRemoveLargeFonts)
            return true;

        this.areRemoveLargeFonts = true;
        $('.mee_large_font_loader').remove();
    }
},
{
    // create the accent in html
    // if its a wide accent then increase the font size
    // TODO: inplement scaled accents here such as arrows
    toHTML: function (depth) {
        if (this.args.length < 1) {
            var token = {
                latex : ''
              }
            this.AddArg(token);
        }

        var res = this._super(depth);

        this.html_elem.css('position', 'relative');
        MEE.ElemAccent.LoadLargeFonts(this.html_elem);
        return res;
    },

    // sort out the alignment of the element
    sortAlign: function () {
        this.align = new MEE.Align();
        MEE.ElemAccent.RemoveLargeFonts();

        this.args[0].sortAlign();

        // work out accent size needed
        var tall = '-0.35em';
        var top = '-1.1';
        var topoffset = 0;
        var topmax = 0.1;

        // work out the font size to use based on the size of the content
        if (this.eldata.accent_wide) {
            var textlen = this.args[0].align.width;
            textlen = $(textlen).toEm({ 'scope': this.args[0].html_elem });
           if (textlen < 0.6) {
            } else if (textlen < 1) {
                $(this.html_main).css('font-family', 'MathJax_Size1');
                $(this.html_main).css('top', '0.1em');
                tall = '-0.1em';
                topoffset = 0;
            } else if (textlen < 1.46) {
                $(this.html_main).css('font-family', 'MathJax_Size2');
                $(this.html_main).css('top', '-0.45em');
                tall = '-0.65em';
                topoffset = 0.05;
            } else if (textlen < 1.9) {
                $(this.html_main).css('font-family', 'MathJax_Size3');
                $(this.html_main).css('top', '-0.55em');
                tall = '-0.75em';
                topoffset = 0.05;
            } else {
                $(this.html_main).css('font-family', 'MathJax_Size4');
                $(this.html_main).css('top', '-0.85em');
                tall = '-1.05em';
                topoffset = 0.05;
            }
        }

        this.main.sortAlign();

        this.align.height = this.main.align.height;
        this.align.width = this.main.align.width;

        // align scripts
        if (this.subscript)
            this.subscript.sortAlign();

        if (this.superscript)
            this.superscript.sortAlign();

        var accentwidth = this.main.align.width;
        var textwidth = this.args[0].align.width;

        // vector character in MathJax_Main is boggered, so this.eldata.nopadleft was added to get around this.
        // changed font to Arial Unicode so no need for it anymore
        if (this.eldata.handledots) { // deal with multiple . chars instead of a sinlge unicode char
            $(this.html_main).css('position', 'absolute');
            $(this.html_main).css('top', '-0.57em');
            var offset = Math.floor((accentwidth - textwidth) / 2);
            if (offset > 0) {
                $(this.html_arg0).css('padding-left', offset + 'px');
                $(this.html_elem).css('padding-right', offset + 'px');
            } else {
                offset = Math.abs(offset);
                $(this.html_main).css('padding-left', offset + 'px');
            }

            if (hasTall(this.args[0].latex)) {
                $(this.html_main).css('top', "-0.79em");
            }

        } else if (this.eldata.nopadleft) { // not pad left, for vector
            var accentwidth = 0.45;
            accentwidth = $(accentwidth).toPx({ 'scope': this.html_elem });
            var accentoffset = 0.5;
            accentoffset = $(accentoffset).toPx({ 'scope': this.html_elem });
            var offset = Math.floor((textwidth - accentwidth) / 2);
            $(this.html_main).css('position', 'relative');
            $(this.html_main).css('left', offset + accentoffset + 'px');

        } else if (textwidth > accentwidth) { // change size accordingly if they arent the same
            var offset = Math.floor((textwidth - accentwidth) / 2);
            //offset = 0;
            $(this.html_main).css('padding-left', offset + 'px');
        } else if (accentwidth > textwidth) {
            var offset = Math.floor((accentwidth - textwidth) / 2);
            //offset = 0;
            $(this.html_main).css('left', -offset + 'px');
        }

        // find the text content of the arg, this doesnt take into account a whole bunch of 
        // stuff, will only work on normal text, not stuff like fractions
        var text = this.args[0].html_elem.text();
        while (text.indexOf('!') != -1)
            text = text.replace("!", "");

        // do we have a content that has an align top sepcified (ie, its tall)
        // if so update the top padding accordingly
        if (this.args[0].align.top > 0) {

            top = $(this.html_main).css('top').replace("em", "").replace("px", "");
            top -= 0.4;
            top = $(top).toPx({ 'scope': this.args[0].html_elem });
            top -= this.args[0].align.top;
            $(this.html_main).css('top', top + 'px');

            //topoffset += $(top).toPx({ 'scope': this.args[0].html_elem });
        } else if (hasTall(text) && !this.eldata.handledots) { // do we have tall text, ie t char
            if($.browser.msie) {
              tall = '-1.22em';
            }
            $(this.html_main).css('top', tall);
            topoffset += 0.22;
        }
        topoffset += 0.22;
        
        // sort out top of align
        top = $(this.html_main).css('top');
        if (top.indexOf("e") != -1) {
            top = top.replace("em", "");
            top = $(top).toPx({ 'scope': this.args[0].html_elem });
        } else {
            top = top.replace("px", "");
        }

        // if we have any extra height, then add it to top
        topoffset = $(topoffset).toPx({ 'scope': this.args[0].html_elem });
        this.html_main.attr("topoffset", topoffset);
        this.html_main.attr("topmax", topmax);
        if (topoffset > topmax) {
            this.align.top = topoffset - topmax;
        }

        // if we have scripts, then process them
        if (this.subscript || this.superscript)
            this.alignSS();

        this.align.width = Math.max(textwidth, accentwidth);
        return this.align;
    }
});
