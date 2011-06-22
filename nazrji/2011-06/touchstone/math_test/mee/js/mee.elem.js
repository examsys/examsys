
$.Class.extend("MEE.Elem",
{
// static stuff

},
{
    text: null,
    type: null,
    subscript: null,
    superscript: null,
    args: null,
    sarg: null,
    eldata: null,

    init: function (token, eldata) {
        this.args = new Array();
        this.latex = token.latex;
        this.type = token.type;
        this.eldata = jQuery.extend({}, eldata);
        this.size = token.size;
        this.sizer = 0;
        if (token.closing) {
            this.eldata.rb = token.closing;
        }
        if (token.sizer)
            this.sizer = token.sizer;
        if (token.type == "extsingle") {
            this.main = new MEE.ElemSetBasic("", new Object());
            this.eldata.lb = token.latex;
            if (eldata.text)
                this.eldata.lb = eldata.text;
        } else {
            this.main = new MEE.ElemSetBasic(token.latex, eldata);
        }
    },

    // prototype stuff
    SetScript: function (token, typeor) {
        var type = token.type;
        if (typeor)
            type = typeor;

        if (type == "superscript") {
            this.superscript = new MEE.ElemSetNormal(token.latex);
        } else if (type == "subscript") {
            this.subscript = new MEE.ElemSetNormal(token.latex);
        }
    },

    // replace the main text of an element with a full element set
    // this is used for things like \frac{x}{y}, {x} will be the new main
    SetMain: function (token) {
        if (this.eldata.simplemain) {
            this.main = new MEE.ElemSetBasic(token.latex, this.eldata);
        } else {
            this.main = new MEE.ElemSetNormal(token.latex);
        }
    },

    AddArg: function (token) {
        this.args.push(new MEE.ElemSetNormal(token.latex));
    },

    SetSArg: function (token) {
        this.sarg = new MEE.ElemSetNormal(token.latex);
    },

    AddUpperLower: function (upper, lower) {
        this.main = new MEE.ElemSetArray(this.eldata);
        this.main.UpperLower(upper, lower);
    },

    AddArray: function (token, alignment) {
        this.main = new MEE.ElemSetArray(this.eldata, alignment);
        this.main.AddArray(token);
    },

    toHTML: function () {

        // if bracket size is defined in eldata, override size
        if ('size' in this.eldata) {
            this.size = this.eldata.size;
            this.sizer = this.eldata.size;
        }

        // create element container
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elem');
        if (this.eldata.elemclass)
            this.html_elem.addClass(this.eldata.elemclass);

        // add left bracket if available
        if (this.eldata.lb) {
            this.html_lb = $('<span>');
            this.html_lb.html(this.eldata.lb);
            this.html_elem.append(this.html_lb);
            this.html_lb.css('padding-top', '0.1em');
            this.html_lb.css('padding-bottom', '0.1em');
            if (this.size > 0 && this.size < 5) {
                // apply static sized brackets
                var font = "MathJax_Size" + this.size;
                $(this.html_lb).css('font-family', font);

            } else if (this.size == -1) {
                // automatically size the brackets
            }
        }

        // create main element (this is either a basic elemset, with just a single piece of text, or a set of elements if args are used as main
        this.html_main = this.main.toHTML();
        this.html_main.addClass('mee_main');
        this.html_elem.append(this.html_main);

        // add a [] argument if available
        if (this.sarg) {
            this.html_sarg = this.sarg.toHTML();
            this.html_sarg.addClass('mee_sarg');
            if (this.eldata['sargclass'])
                this.html_sarg.addClass(this.eldata['sargclass']);
            this.html_elem.append(this.html_sarg);
        }

        // add any arguments 
        if (this.args.length > 0) {
            for (var i = 0; i < this.args.length; i++) {
                var arg = this.args[i].toHTML();
                arg.addClass('mee_arg' + i);
                arg.addClass('mee_arg');
                if (this.eldata['arg' + i + 'class'])
                    arg.addClass(this.eldata['arg' + i + 'class']);
                this.html_elem.append(arg);

                this['html_arg' + i] = arg;
            }
        }

        // add left bracket if available
        if (this.eldata.rb) {
            this.html_rb = $('<span>');
            this.html_rb.html(this.eldata.rb);
            this.html_rb.css('padding-top', '0.1em');
            this.html_rb.css('padding-bottom', '0.1em');
            this.html_elem.append(this.html_rb);

            if (this.sizer > 0 && this.sizer < 5) {
                // apply static sized brackets
                var font = "MathJax_Size" + this.sizer;
                $(this.html_rb).css('font-family', font);

            } else if (this.sizer == -1) {
                // automatically size the brackets
            }

        }

        // if we have a subscript, then add it
        if (this.subscript) {
            this.html_subscript = this.subscript.toHTML();
            this.html_subscript.addClass('mee_subscript');
            if (this.eldata.subscriptclass)
                this.html_subscript.addClass(this.eldata.subscriptclass);
            this.html_elem.append(this.html_subscript);
        }

        // if we have a superscript then add it
        if (this.superscript) {
            this.html_superscript = this.superscript.toHTML();
            this.html_superscript.addClass('mee_superscript');
            if (this.eldata.superscriptclass)
                this.html_superscript.addClass(this.eldata.superscriptclass);
            this.html_elem.append(this.html_superscript);
        }

        return this.html_elem;
    },

    sortAlign: function () {
        this.align = new MEE.Align();

        // call sort align on all sub components
        this.main.sortAlign();

        if (this.subscript)
            this.subscript.sortAlign();

        if (this.superscript)
            this.superscript.sortAlign();

        if (this.sarg)
            this.sarg.sortAlign();

        if (this.args.length > 0) {
            for (var i = 0; i < this.args.length; i++) {
                this.args[i].sortAlign();
            }
        }

        // calculate brackets height if there are any


        // sort out main height
        var maintop = Math.abs(this.html_main.css('margin-top').replace('px', ''));
        var mainbottom = Math.abs(this.html_main.css('margin-bottom').replace('px', ''));
        if (maintop)
            this.align.top = Math.max(this.align.top, maintop);
        if (mainbottom)
            this.align.bottom = Math.max(this.align.bottom, mainbottom);



        // if we have scripts, then process them
        if (this.subscript || this.superscript)
            this.alignSS();

        // calculate brackets height offsets   
        {
            if (this.html_lb) {  // left bracket
		// CHANGE THIS TO ALLOW DIFFERENT OFFSETS ON BOTH TOP AND BOTTOM
		// FRACTIONS WITHIN BRACKETS NEED A TOP ADDING AND ALIGNMENT CHANGING
                var lbh = 0.1;
                lbh = $(lbh).toPx({ 'scope': this.html_elem });
                lbh += $(this.html_lb).outerHeight(true);
                //var lbh = $(this.html_lb).outerHeight(true);
                var mainh = $(this.html_main).outerHeight(true);

                var lbextra = Math.floor((lbh - mainh) / 2);
                if (lbextra > 0) {
                    this.align.top = Math.max(this.align.top, lbextra);
                    this.align.bottom = Math.max(this.align.bottom, lbextra);
                }
            }

            if (this.html_rb) { // right bracket
                var rbh = 0.1;
                rbh = $(rbh).toPx({ 'scope': this.html_elem });
                rbh += $(this.html_rb).outerHeight(true);
                //var rbh = $(this.html_rb).outerHeight(true);
                var mainh = $(this.html_main).outerHeight(true);

                var rbextra = Math.floor((rbh - mainh) / 2);
                if (rbextra > 0) {
                    this.align.top = Math.max(this.align.top, rbextra);
                    this.align.bottom = Math.max(this.align.bottom, rbextra);
                }
            }
        }

        // calculate width
        {
            // add main width
            this.align.width = this.main.align.width;

            // add bracket widths
            if (this.html_lb)
                this.align.width += this.html_lb.outerWidth(true);
            if (this.html_rb)
                this.align.width += this.html_rb.outerWidth(true);

            // add sub and super script widths
            var sswidth = 0;
            if (this.subscript)
                sswidth = Math.max(sswidth, this.subscript.align.width);
            if (this.superscript)
                sswidth = Math.max(sswidth, this.superscript.align.width);
            this.align.width += sswidth
        }

        // calculate top and bottom offsets
        {
            // check super script
            if (this.superscript) {
                var suptop = Math.abs(this.html_superscript.css('top').replace('px', ''));
                if (suptop)
                    this.align.top = Math.max(this.align.top, suptop);
            }
            if (this.subscript) {
                var subbottom = Math.abs(this.html_subscript.css('bottom').replace('px', ''));
                if (subbottom)
                    this.align.bottom = Math.max(this.align.bottom, subbottom);
            }
        }

        // calculate new height of the element
        this.align.height = $(this.html_elem).outerHeight(true);
        this.align.height += this.align.top;
        this.align.height += this.align.bottom;

        // pad the top and bottom margins to accomodate the contents
        if (this.align.top)
            $(this.html_elem).css('margin-top', this.align.top + 'px');
        if (this.align.bottom)
            $(this.html_elem).css('margin-bottom', this.align.bottom + 'px');

        var padleft = parseInt($(this.html_elem).css('padding-left').replace('px', ''));
        if (padleft > 0)
            this.align.width += padleft;

        if (this.args.length > 0) {
            for (var i = 0; i < this.args.length; i++) {
                this.align.width += this.args[i].align.width;
            }
        }
        return this.align;
    },

    // align sub and super scripts (limits) of an element
    alignSS: function () {

        var mainwidth = this.main.align.width;

        var sswidth = 0;
        if (this.subscript)
            sswidth = Math.max(sswidth, this.subscript.align.width);
        if (this.superscript)
            sswidth = Math.max(sswidth, this.superscript.align.width);

        if (this.eldata.limits == "above") {

            // sort out left and right alignment of above and below ss
            {
                if (mainwidth < sswidth) {
                    // if one of the sub or superscripts is larger than the main element, then
                    // pad the main element to size
                    var mainpadding = (sswidth - mainwidth) / 2;
                    $(this.html_main).css('padding-left', mainpadding + 'px');
                    $(this.html_main).css('padding-right', mainpadding + 'px');
                    mainwidth = sswidth;
                }

                if (this.superscript)
                    $(this.html_superscript).css('left', (mainwidth - this.superscript.align.width) / 2 + 'px');
                if (this.subscript)
                    $(this.html_subscript).css('left', (mainwidth - this.subscript.align.width) / 2 + 'px');
            }

            // sort out vertical alignment of above and below ss
            {
                if (this.subscript) {
                    var subh = $(this.html_subscript).outerHeight(true);
                    var elemh = $(this.html_elem).outerHeight(true);

                    // THESE NEED CHANGING TO BE SPEICIFED IN THE TEX FILE
                    var pad = 0.9;
                    if (this.eldata.limits_l)
                        pad = 1.1;
                    if (this.eldata.limits_lx)
                        pad = 1.5;
                    pad = $(pad).toPx({ 'scope': this.html_elem });
                    pad -= (elemh - subh);

                    $(this.html_subscript).css('bottom', -pad + 'px');
                }

                if (this.superscript) {
                    var suph = $(this.html_superscript).outerHeight(true);
                    var elemh = $(this.html_elem).outerHeight(true);

                    // THESE NEED CHANGING TO BE SPEICIFED IN THE TEX FILE
                    var pad = 0.75;
                    if (this.eldata.limits_h)
                        pad = 0.95;
                    if (this.eldata.limits_hx)
                        pad = 1.4;
                    pad = $(pad).toPx({ 'scope': this.html_elem });
                    pad -= (elemh - suph);

                    $(this.html_superscript).css('top', -pad + 'px');
                }

            }

        } else if (this.eldata.limits == "sqrt") {

            //$(this.html_superscript).css("left", "0.4em");

        } else {

            // pad the right of the element to make space for the sup and sub scripts
            $(this.html_elem).css('padding-right', sswidth + 'px');


            // sort out vertical alignment of scripts 
            if (this.subscript && this.superscript) {
                // both are present, 
                var subh = this.subscript.align.height;
                var suph = this.superscript.align.height;
                var elemh = $(this.html_elem).outerHeight(true);

                var pad = 0.1;
                pad = $(pad).toPx({ 'scope': this.html_elem });

                var subup = subh - Math.floor(elemh / 2);
                subup -= pad;
                $(this.html_subscript).css('bottom', -subup + 'px');

                var supup = suph - Math.floor(elemh / 2);
                supup -= pad;
                $(this.html_superscript).css('top', -supup + 'px');

            } else if (this.subscript) {
                var subh = this.subscript.align.height;
                var elemh = $(this.html_elem).outerHeight(true);

                var subup = subh - Math.floor(elemh / 2);

                var pad = 0.2;
                pad = $(pad).toPx({ 'scope': this.html_elem });
                subup -= pad;

                $(this.html_subscript).css('bottom', -subup + 'px');

            } else if (this.superscript) {
                var suph = this.superscript.align.height;
                var elemh = $(this.html_elem).outerHeight(true);

                var supup = suph - Math.floor(elemh / 2);

                var pad = 0.05;
                pad = $(pad).toPx({ 'scope': this.html_elem });
                supup -= pad;

                $(this.html_superscript).css('top', -supup + 'px');
            }
            // end vert align
        }
    },

    minFontSize: function (minsize) {
        if (this.subscript)
            this.subscript.minFontSize(minsize);
        if (this.superscript)
            this.superscript.minFontSize(minsize);

        if (this.html_main.css('font-size').replace('px', '') < minsize)
            this.html_main.css('font-size', minsize + 'px');

        if (this.html_subscript && this.html_subscript.css('font-size').replace('px', '') < minsize)
            this.html_subscript.css('font-size', minsize + 'px');

        if (this.html_superscript && this.html_superscript.css('font-size').replace('px', '') < minsize)
            this.html_superscript.css('font-size', minsize + 'px');
    }
});
