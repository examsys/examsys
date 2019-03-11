MEE.ElemSet.extend("MEE.ElemSetBasic",
{
    basicelems: Array()
},
{
    html_elem: null,
    latex: null,
    eldata: null,
    isbasic: 1,

    // prototype stuff
    init: function (latex, eldata, par) {
        this._name = 'MEE.ElemSetBasic';
        //alert(latex);
        this.latex = latex;
        this.eldata = jQuery.extend({}, eldata);
        this.isbasic = 1;
        if (par.isedit)
            this.isedit = true;
        this.parent = par;
    },

    toHTML: function (depth) {
        this.elemid = MEE.ElemSetBasic.basicelems.length;
        MEE.ElemSetBasic.basicelems[this.elemid] = this;
        this.depth = depth;
        
        this.html_elem = $('<span class="mee_elemsetbasic" elem="' + this.elemid + '" depth="' + depth + '"></span>');
        
        this.text = "";
        if ('text' in this.eldata) {
            this.text = this.eldata.text;
        } else {
            this.text = this.latex;
        }

        this.basetext = this.text;
        this.html_elem.attr('basetext', this.basetext);

        // some characters are automatically replace, so check for this here
        if (MEE.Data.replace[this.text]) this.text = MEE.Data.replace[this.text];

        // large chars are handled differently, with specified width, height etc
        if (MEE.Data.largechars[this.basetext] && this.eldata.large) {
            var chardata = MEE.Data.getLargeCharData(this.basetext, this.html_elem);

            this.html_inner = $('<span class="mee_basic_large_inner" style="position:absolute; left:0px; padding-right200px"></span>');
            this.html_inner.html(this.text);

            this.html_elem.append(this.html_inner);
            this.html_elem.css('position', 'relative');

            var space = $(MEE.Data.blankspace);
            this.html_elem.append(space);

            this.html_elem.addClass('mee_basic_large_outer');
        } else {
            this.html_elem.html(this.text);
        }

        if (this.eldata.mainclass)
            this.html_elem.addClass(this.eldata.mainclass);
        return this.html_elem;
    },

    sortAlign: function () {
        this.align = new MEE.Align();

        if (MEE.Data.largechars[this.basetext] && this.eldata.large) {
            var size = 2;
            if (this.eldata.size1_font_if_depth && this.depth > 1) {
                this.html_inner.css('font-family', 'MathJax_Size1');
                this.html_inner.addClass('mee_large_shrunk');
                size = 1;
            }

            var chardata = MEE.Data.getLargeCharData(this.basetext, this.html_elem, size);

            this.html_inner.css('top', chardata.offset + 'px');

            this.html_elem.css('padding-right', chardata.width - MEE.Data.blankspacesize(this.html_elem) + 'px');

            this.align.width = chardata.width + 1;
            this.align.top = chardata.top;
            this.align.bottom = chardata.bottom;
            this.align.height = MEE.Data.getBaseSize(this.html_elem) + this.align.top + this.align.bottom; //$(this.html_elem).outerHeight(true);// OUTER_OK

        } else if (MEE.Data.largechars[this.basetext]) {
            var size = parseInt(this.html_elem.css('font-family').replace('MathJax_Size', ''));
            if (!(size > 0)) {
                this.align.width = $(this.html_elem).outerWidth(true);
                this.align.height = MEE.Data.getBaseSize(this.html_elem);
            } else {
                var chardata = MEE.Data.getLargeCharData(this.basetext, this.html_elem, size);
                this.align.width = chardata.width + 1;
                this.align.top = chardata.top;
                this.align.bottom = chardata.bottom;
                this.align.height = MEE.Data.getBaseSize(this.html_elem) + this.align.top + this.align.bottom; //$(this.html_elem).outerHeight(true);// OUTER_OK
            }
        } else {
            // need to check the font is loaded
            this.align.width = $(this.html_elem).outerWidth(true); // OUTER_OK
            this.align.height = MEE.Data.getBaseSize(this.html_elem); // OUTER_OK
        }

        return this.align;
    },


    toLatex: function () {
        var latex = new MEE.Latex();
        latex.AddText(this.text);
        return latex;
    },

    ////////////////////////////
    // DEBUG STUFF BELOW HERE //
    ////////////////////////////
    dump: function () {
        var dump = "Basic: " + this.latex;
        if (!this.parent)
            dump += "<div class='dump_no_parent'>NO PARENT</div>";
        return dump;
    }
});
