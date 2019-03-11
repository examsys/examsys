// general element
$.Class.extend("MEE.Elem",
{
    elems: Array()

},
{
    // initialisze an element. pass in a token and the data that is associated with the token
    init: function (token, eldata) {
        this._name = 'MEE.Elem';
        this.args = new Array();
        this.argmap = new Array();
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
            this.main = new MEE.ElemSetBasic("", {}, this);
            this.eldata.lb = token.latex;
            if (eldata.text)
                this.eldata.lb = eldata.text;
        } else {
            this.main = new MEE.ElemSetBasic(token.latex, eldata, this);
        }
    },

    // set the super or subscript token for this element
    // typeor is used to override the type of the script
    SetScript: function (token, typeor, createhtml) {
        var type = token.type;
        if (typeor)
            type = typeor;

        if (type == "superscript") {
            this.superscript = new MEE.ElemSetNormal(token.latex, this);
            if (createhtml)
                this.createSuperscriptHTML();
            return this.superscript;
        } else if (type == "subscript") {
            this.subscript = new MEE.ElemSetNormal(token.latex, this);
            if (createhtml)
                this.createSubscriptHTML();
            return this.subscript;
        }
    },

    // replace the main text of an element with a full element set
    // this is used for things like \sqrt{x}, {x} will be the new main
    SetMain: function (token) {
        this.main = new MEE.ElemSetNormal(token.latex, this);
        this.argmap.push(this.main);
    },

    // add argument to a element
    AddArg: function (token) {
        var arg = new MEE.ElemSetNormal(token.latex, this);
        this.args.push(arg);
        this.argmap.push(arg);
    },

    // add a square bracket argument to an element is \sqrt[3]{x} adds 3
    SetSArg: function (token) {
        this.sarg = new MEE.ElemSetNormal(token.latex, this);
    },

    // add a upper and lower part to the element and conver the main into a arry element set
    AddUpperLower: function (upper, lower) {
        if (this.main._name != "MEE.ElemSetArray") {
            this.main = new MEE.ElemSetArray(this.eldata, null, this);
        }
        this.main.UpperLower(upper, lower);
        this.argmap.push(this.main.row0.col0);
        this.argmap.push(this.main.row1.col0);
    },

    // add an array to the elemtn
    AddArray: function (token, alignment) {
        this.main = new MEE.ElemSetArray(this.eldata, alignment, this);
        this.main.AddArray(token);
    },


    toHTML: function (depth) {

        // sort out depth
        if (this.eldata.displaystyle)
            depth = 1;
        if (this.eldata.textstyle)
            depth = 2;

        this.depth = depth;

        // if bracket size is defined in eldata, override size
        if (this.eldata.size) {
            this.size = this.eldata.size;
            this.sizer = this.eldata.size;
        }

        // create element container
        this.elemid = MEE.Elem.elems.length;
        MEE.Elem.elems[this.elemid] = this;

        if (this.eldata.elemclass)
          this.html_elem = $('<span class="mee_elem ' + this.eldata.elemclass + '" elem="' + this.elemid + '"></span>');
        else 
          this.html_elem = $('<span class="mee_elem" elem="' + this.elemid + '"></span>');

       //this.html_elem.attr('elem', this.elemid);

        

        // add left bracket if available
        if (this.eldata.lb)
            this.createLbHTML();

        // deal with a sqrt as a special case as needs a bar creating
        // TODO: change this to a flag in the eldata
        if (this.latex == "sqrt") {
            this.html_sqrt = $('<span class="mee_sqrt_bar" style="position:absolute"></span>');
            this.html_sqrt.html(MEE.Data.blankspace);
            this.html_elem.append(this.html_sqrt);
            this.html_elem.css('position', 'relative');
        }

        // create main element (this is either a basic elemset, with just a single piece of text, or a set of elements if args are used as main
        this.html_main = this.main.toHTML(depth);
        this.html_main.addClass('mee_main');
        if (this.eldata.mainclass)
            this.html_main.addClass(this.eldata.mainclass);
        this.html_elem.append(this.html_main);


        // add a [] argument if available
        if (this.sarg) {
            this.html_sarg = this.sarg.toHTML(depth);
            this.html_sarg.addClass('mee_sarg');
            if (this.eldata['sargclass'])
                this.html_sarg.addClass(this.eldata['sargclass']);
            this.html_elem.append(this.html_sarg);
        }


        // add any arguments 
        if (this.args.length > 0 && !this.eldata.arg01_as_upperlower && !this.eldata.arg0_as_main) {
            for (var i = 0; i < this.args.length; i++) {
                var arg = this.args[i].toHTML(depth);
                arg.addClass('mee_arg' + i);
                arg.addClass('mee_arg');
                if (this.eldata['arg' + i + 'class'])
                    arg.addClass(this.eldata['arg' + i + 'class']);
                this.html_elem.append(arg);
                this['html_arg' + i] = arg;
            }
        }


        // add right bracket if available
        if (this.eldata.rb)
            this.createRbHTML();

        // if we have a subscript, then add it
        if (this.subscript)
            this.createSubscriptHTML();


        // if we have a superscript then add it
        if (this.superscript)
            this.createSuperscriptHTML();


        return this.html_elem;
    },

    createLbHTML: function () {
        if (!this.eldata.lb)
            return;

        // this needs to be inserted at the start of the element set
        this.html_lb = $('<span style="position:relative"></span>');
        //this.html_lb.css('position', 'relative');
        this.html_lb.html(MEE.Data.blankspace);

        var font = '';
        if (!('scale' in this.eldata && this.eldata.scale == 1) && this.size > 0 && this.size < 5) {
            // apply static sized brackets
            font = ';font-family:' + "MathJax_Size" + this.size;
            //$(this.html_lb_inner).css('font-family', font);
        }
        
        this.html_lb_inner = $('<span style="position:absolute ' + font + '"></span>');
        //this.html_lb_inner.css('position', 'absolute');
        this.html_lb_inner.html(this.eldata.lb);

        this.html_lb.append(this.html_lb_inner);
        this.html_elem.prepend(this.html_lb);

        
    },

    createRbHTML: function () {
        if (!this.eldata.rb)
            return;

        // this needs to be inserted after the main part but before the sub and super scripts
        if (this.eldata.rb == ".")
            this.eldata.rb = "";

        this.html_rb = $('<span style="position:relative"></span>');
        this.html_rb.html(MEE.Data.blankspace);


        this.html_rb_inner = $('<span style="position:absolute"></span>');
        this.html_rb_inner.html(this.eldata.rb);

        this.html_rb.append(this.html_rb_inner);
 
        if (this.html_superscript) {
            this.html_superscript.before(this.html_rb);
        } else if (this.html_subscript) {
            this.html_subscript.before(this.html_rb);
        } else {
            this.html_elem.append(this.html_rb);
        }

        if (!('scale' in this.eldata && this.eldata.scale == 1) && this.sizer > 0 && this.sizer < 5) {
            // apply static sized brackets
            var font = "MathJax_Size" + this.sizer;
            $(this.html_rb_inner).css('font-family', font);
        }

    },

    // create the html stuff for a subscript
    createSubscriptHTML: function () {
        var ssdepth = this.depth + 1;
        if (ssdepth < 3) ssdepth = 3;
        this.html_subscript = this.subscript.toHTML(ssdepth);
        this.html_subscript.addClass('mee_subscript');
        this.html_subscript.css('position','absoluet');
        if (this.eldata.subscriptclass)
            this.html_subscript.addClass(this.eldata.subscriptclass);
        this.subscript.subscript = 1;
        this.html_elem.append(this.html_subscript);
    },

    // create the html stuff for a superscript
    createSuperscriptHTML: function () {
        var ssdepth = this.depth + 1;
        if (ssdepth < 4) ssdepth = 4;
        this.html_superscript = this.superscript.toHTML(ssdepth);
        this.html_superscript.addClass('mee_superscript');
        this.html_superscript.css('position','absoluet');
        if (this.eldata.superscriptclass)
            this.html_superscript.addClass(this.eldata.superscriptclass);
        this.superscript.superscript = 1;
        this.html_elem.append(this.html_superscript);
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

        this.align.width = this.main.align.width;
        this.align.height = this.main.align.height - (this.main.align.top + this.main.align.bottom);
        this.align.top = this.main.align.top;
        this.align.bottom = this.main.align.bottom;

        // if we have scripts, then process them
        if (this.subscript || this.superscript)
            this.alignSS();


        // if bracket size is defined in eldata, override size
        if ('size' in this.eldata) {
            this.size = this.eldata.size;
            this.sizer = this.eldata.size;
        }


        if (this.html_lb) {  // left bracket
            var lbwidth = this.alignBracket('lb', this.size, this.eldata.lb, this.html_lb, this.html_lb_inner)
            this.align.width += lbwidth; // was this.html_lb.outerWidth(true);
        }

        if (this.html_rb) { // right bracket
            var rbwidth = this.alignBracket('rb', this.sizer, this.eldata.rb, this.html_rb, this.html_rb_inner);
            this.align.width += rbwidth; // was this.html_rb.outerWidth(true);

        }

        // align a squareroot
        if (this.html_sqrt) {
            var offset = 0;
            if (this.bsize == -1) {
                
                // need to create a char string that fills the box
                var width = this.main.align.width;
                width = $(width).toEm({ 'scope': this.html_elem });
                var sqrtbartxt = MEE.Tools.HTML.BuildBarText(width, "sqrts");
                this.html_sqrt.html(sqrtbartxt);
                var top = this.html_lb.children().css('top');
                this.html_sqrt.css('top', top);
                this.html_sqrt.css('font-family', 'MathJax_Size4');
                this.html_lb.append(this.html_sqrt);
            } else {
                var width = this.main.align.width;
                width = $(width).toEm({ 'scope': this.html_elem });
                var sqrtbartxt = MEE.Tools.HTML.BuildBarText(width, "sqrt");
                this.html_sqrt.html(sqrtbartxt);
                var children = this.html_lb.children();
                var top = $(children[1]).css('top');
                this.html_sqrt.css('left', lbwidth + 'px');
                this.html_sqrt.css('top', top);
                this.html_lb.append(this.html_sqrt);
                if (this.bsize > 0)
                    this.html_sqrt.css('font-family', 'MathJax_Size' + this.bsize);
                
            }
        }

        // calculate top and bottom offsets
        // check super script
        var sswidth = 0;
        if (this.superscript) {
            var suptop = this.replacePX(this.html_superscript.css('top')) + this.superscript.align.top;
            if (suptop)
                this.align.top = Math.max(this.align.top, suptop);
            sswidth = Math.max(sswidth, this.superscript.align.width);
        }

        if (this.subscript) {
            var subbottom = this.replacePX(this.html_subscript.css('bottom')) + this.subscript.align.bottom;
            if (subbottom)
                this.align.bottom = Math.max(this.align.bottom, subbottom);
            sswidth = Math.max(sswidth, this.subscript.align.width);
        }

        if (!this.html_sqrt)
            this.align.width += sswidth;

        if (this.eldata.noheight) { // used for hphantom
            this.align.top = 0;
            this.align.bottom = 0;
        }

        this.align.height += this.align.top;
        this.align.height += this.align.bottom;

        var padleft = $(this.html_elem).css('padding-left');
        if (padleft) {
            padleft = parseInt(padleft.replace('px', ''));
            if (padleft > 0)
                this.align.width += padleft;

        }

        if (this.args.length > 0) {
            for (var i = 0; i < this.args.length; i++) {
                this.align.width += this.args[i].align.width;
            }
        }

        // check the main element for any margins and add these to the width
        var marginl = parseInt($(this.html_elem).css('margin-left'));
        if (marginl > 0) this.align.width += marginl;

        var marginr = parseInt($(this.html_elem).css('margin-right'));
        if (marginr > 0) this.align.width += marginr;
        
        return this.align;
    },

    // perform alignment on a bracket
    alignBracket: function (side, size, bracketchar, elem, inner) {

        if (bracketchar == "")
            return;

        var bdata = MEE.Data.bsizes[bracketchar];
        if (!bdata)
            bdata = MEE.Data.bsizes['('];

        // generate a hain height that is appears to be centered vertically as all brackets should be vertically centered around the base line
        var mainho = this.main.align.height - (this.main.align.top + this.main.align.bottom) + (Math.max(this.main.align.top, this.main.align.bottom) * 2);

        var isscaled = false;
        var bwidth = 0;

        if (size == undefined)
            size = 0;

        if (size == 0) { // text sized bracket, always output normal character     
            // nothing to do here, its already done

        } else if (size != -1 && !bdata.haslarge) {
            // if we have a fixed size bracket, but no large characters available, so create a scaled one
            // scale is set, so need to create a bracket that is scaled to
            mainhem = MEE.Data.bracketheights[size];
            mainho = $(mainhem).toPx({ 'scope': this.html_elem });
            bwidth = this.generateLargeBracket(bracketchar, mainho, this.html_elem, elem);
            isscaled = true;
        } else if (size == -1) {
            // we have an auto sizing bracket


            // auto size bracket
            mainhem = $(mainho).toEm({ 'scope': this.html_elem });

            // build height tablse
            var heights = {};
            if (bracketchar == '&#x221A;') { // square root table
                heights.size4 = 3;
                heights.size3 = 2.4;
                heights.size2 = 1.8;
                heights.size1 = 1.2;
                heights.size0 = 0.9;
            } else { // everything else
                heights.size4 = 3.3;
                heights.size3 = 2.7;
                heights.size2 = 2.1;
                heights.size1 = 1.5;
                heights.size0 = 0;
            }

            if (!bdata.haslarge || (mainhem > heights.size4 && bdata.canscale)) {
                // if we cant do large characters 
                // or
                // bigger than largest character and canscale
                // make scaled bracket
                bwidth = this.generateLargeBracket(bracketchar, mainho, this.html_elem, elem);
                isscaled = true;
            } else if (mainhem > heights.size3) { // 2.6
                // change back to a non large bracket if needed
                inner = this.removeLargeBaracket(side, bracketchar, inner, this.html_elem, elem);
                $(inner).css('font-family', 'MathJax_Size4');
                size = 4;
            } else if (mainhem > heights.size2) { // 1.8
                // change back to a non large bracket if needed
                inner = this.removeLargeBaracket(side, bracketchar, inner, this.html_elem, elem);
                $(inner).css('font-family', 'MathJax_Size3');
                size = 3;
            } else if (mainhem > heights.size1) { // 1.37
                // change back to a non large bracket if needed
                inner = this.removeLargeBaracket(side, bracketchar, inner, this.html_elem, elem);
                $(inner).css('font-family', 'MathJax_Size2');
                size = 2;
            } /* else if (mainhem > heights.size0) { // 1.37
                $(inner).css('font-family', 'MathJax_Size1');
                size = 1;
            }*/
            else {
                // change back to a non large bracket if needed
                inner = this.removeLargeBaracket(side, bracketchar, inner, this.html_elem, elem);
                $(inner).css('font-family', '');
                size = 0;
            }
        }

        // if we havent defined a width yet, get on
        if (bwidth == 0) {
            bwidth = bdata['size' + size].width;
            bwidth = $(bwidth).toPx({ 'scope': this.html_elem });
        }

        if (isscaled) { // when a bracket is scaled, the alignment is easy

            if (mainho > this.main.align.height) { // was  $(this.html_main).outerHeight(true); 
                this.align.top = Math.max(this.align.top, this.align.bottom);

                var largeby = Math.floor((mainho - this.main.align.height) / 2);
                if (largeby > this.align.top)
                    this.align.top = largeby;

                this.align.bottom = this.align.top;
            }

            this.align.width += bwidth;

        } else {

            // need to position the bracket and pad out the parent
            var bheight = bdata['size' + size].height;
            bheight = $(bheight).toPx({ 'scope': this.html_elem });

            $(inner).css('left', '0px');

            var bextra = 0;
            var boffset = 0;
            if (size == 4) {
                bextra = 0.9;
                boffset = 0.94;
            } else if (size == 3) {
                bextra = 0.63;
                boffset = 0.62;
            } else if (size == 2) {
                bextra = 0.3;
                boffset = 0.53;
            } else if (size == 1) {
                bextra = 0.1;
                boffset = -0.07;
            }


            $(inner).css('top', -boffset + 'em');
            $(elem).css('padding-right', bwidth - MEE.Data.blankspacesize(elem) + 'px');
            bextra = $(bextra).toPx({ 'scope': this.html_elem });
            if (bextra > 0) {
                this.align.top = Math.max(this.align.top, bextra);
                this.align.bottom = Math.max(this.align.bottom, bextra);
            }
        }
        this.bsize = size;
        return bwidth;
    },

    // align sub and super scripts (limits) of an element
    alignSS: function () {

        var mainwidth = this.main.align.width;

        var sswidth = 0;
        if (this.subscript)
            sswidth = Math.max(sswidth, this.subscript.align.width);
        if (this.superscript)
            sswidth = Math.max(sswidth, this.superscript.align.width);

        // change to side limits if we arent in the first level or displaystyle
        if (this.depth > 1)
            this.eldata.limits = "";

        // apply any forced limit changes
        if ('foreclimits' in this.eldata)
            this.eldata.limits = this.eldata.foreclimits;


        if (this.eldata.limits == "above") { // limits
            this.html_elem.css('position', 'relative');
            // sort out left and right alignment of above and below ss
            {
                if (mainwidth < sswidth) {
                    // if one of the sub or superscripts is larger than the main element, then
                    // pad the main element to size
                    var mainpadding1 = Math.floor((sswidth - mainwidth) / 2);
                    var existingleft = $(this.html_main).css('padding-left').replace('px', '');
                    if (existingleft > 0)
                        mainpadding1 += parseInt(existingleft);

                    var mainpadding2 = Math.ceil((sswidth - mainwidth) / 2);
                    var existingright = $(this.html_main).css('padding-right').replace('px', '');
                    if (existingright > 0)
                        mainpadding2 += parseInt(existingright);

                    $(this.html_main).css('padding-left', mainpadding1 + 'px');
                    $(this.html_main).css('padding-right', mainpadding2 + 'px');

                    if (this.main._name == "MEE.ElemSetBasic") {
                        $(this.main.html_inner).css('left', mainpadding1 + 'px');
                    }
                    mainwidth = sswidth;
                }

                if (this.superscript)
                    $(this.html_superscript).css('left', Math.floor((mainwidth - this.superscript.align.width) / 2) + 'px');
                if (this.subscript)
                    $(this.html_subscript).css('left', Math.floor((mainwidth - this.subscript.align.width) / 2) + 'px');
            }

            // sort out vertical alignment of above and below ss
            if (this.subscript) {
                var suph = this.subscript.align.height - this.subscript.align.top; ; // was $(this.html_superscript).outerHeight(true)
                var elemh = this.main.align.height; // was $(this.html_elem).outerHeight(true);
                elemh = Math.floor(elemh/2) + this.main.align.bottom - this.subscript.align.top;
                if($.browser.msie) 
                  elemh += suph/2;
                  
                var pad = 0.3;
                if (this.eldata.limits_l)
                    pad = 0.1;
                if (this.eldata.limits_lx)
                    pad = 0.07;

                pad = $(pad).toPx({ 'scope': this.html_elem });
                pad = -pad + elemh;
                $(this.html_subscript).css('bottom', -pad + 'px');
            }

            if (this.superscript) {
                var suph = this.superscript.align.height - this.superscript.align.top; ; // was $(this.html_superscript).outerHeight(true)
                var elemh = this.main.align.height; // was $(this.html_elem).outerHeight(true);
                elemh = Math.floor(elemh / 2) + this.main.align.top - this.superscript.align.top;
                if($.browser.msie) 
                  elemh += suph;
                  
                var pad = 0.4;
                if (this.eldata.limits_h)
                    pad = 0.2;
                if (this.eldata.limits_hx)
                    pad = 0.05;

                pad = $(pad).toPx({ 'scope': this.html_elem });
                //pad = -pad + parseInt(maintop) + suph;
                pad = pad + elemh;
                $(this.html_superscript).css('top', -pad + 'px');
            }


            // as limits are above and below, we need to clear the width of 
            // the scripts and ammend the main width accordingly
            this.align.width = mainwidth;
            if (this.subscript)
                this.subscript.align.width = 0;
            if (this.superscript)
                this.superscript.align.width = 0;

            //} else if (this.eldata.limits == "sqrt") { // special case for square root

            // nothing here
            // TODO : align large square root [] stuff

        } else { // right aligned sub and super script

            // pad the right of the element to make space for the sup and sub scripts
            //if (this.eldata.limits != "sqrt") {
            if (!this.html_sqrt)
                $(this.html_elem).css('padding-right', sswidth + 'px');

            var elemh = this.main.align.height - (this.main.align.top + this.main.align.bottom); // was $(this.html_elem).outerHeight(true);
            // sort out vertical alignment of scripts 
            if (this.subscript && this.superscript) {
                // both are present, 

                // WRONG! NEEDS TO TAKE INTO ACCOUNT THE MAIN ELEMENT HEIGHT
                var subh = this.subscript.align.height - this.subscript.align.bottom;
                var suph = this.superscript.align.height - this.superscript.align.top;

                var pad = 0.1;
                pad = $(pad).toPx({ 'scope': this.html_elem });

                var subup = subh - Math.floor(elemh / 2) + this.main.align.bottom - this.subscript.align.top;
                subup -= pad;
                var supup = suph - Math.floor(elemh / 2) + this.main.align.top - this.superscript.align.top;
                supup -= pad;

                // need to expand these if they will overlap
                var overlap = (subh + suph) - (elemh + subup + supup);
                var maxoverlap = 0.2; // this.depth / 10;
                maxoverlap = $(maxoverlap).toPx({ 'scope': this.html_elem });
                if (overlap > maxoverlap) {
                    overlap -= maxoverlap;
                    subup += Math.floor(overlap / 2);
                    supup += Math.ceil(overlap / 2);
                }

                $(this.html_elem).attr('overlap', overlap);
                $(this.html_subscript).css('bottom', -subup + 'px');
                $(this.html_superscript).css('top', -supup + 'px');

            } else if (this.subscript) {
                var subh = this.subscript.align.height - this.subscript.align.bottom;

                var subup = subh - Math.floor(elemh / 2) + this.main.align.bottom - this.subscript.align.top;

                var pad = 0.2;
                pad = $(pad).toPx({ 'scope': this.html_elem });
                subup -= pad;

                $(this.html_subscript).css('bottom', -subup + 'px');

            } else if (this.superscript) {
                var suph = this.superscript.align.height - this.superscript.align.top;

                var supup = suph - Math.floor(elemh / 2) + this.main.align.top - this.superscript.align.top;

                var pad = 0.05;
                pad = $(pad).toPx({ 'scope': this.html_elem });
                supup -= pad;

                $(this.html_superscript).css('top', -supup + 'px');
            }
            // end vert align

            if (this.eldata.ssoffsets) {
                // need to adjust the sub and super script positions based on any offsets specified

                if (this.depth == 1) {
                    this.eldata.suboffset = this.eldata.ssoffsets[1].sub;
                    this.eldata.supoffset = this.eldata.ssoffsets[1].sup;
                } else {
                    this.eldata.suboffset = this.eldata.ssoffsets[2].sub;
                    this.eldata.supoffset = this.eldata.ssoffsets[2].sup;

                }

                // work out px offset
                if (!this.eldata.supoffset)
                    this.eldata.supoffset = 0;
                if (!this.eldata.suboffset)
                    this.eldata.suboffset = 0;

                this.eldata.suboffset = $(this.eldata.suboffset).toPx({ 'scope': this.html_elem });
                this.eldata.supoffset = $(this.eldata.supoffset).toPx({ 'scope': this.html_elem });

                // get main element width
                var mainw = this.main.align.width;

                // adjust script left
                if (this.html_superscript)
                    $(this.html_superscript).css('left', mainw + this.eldata.supoffset + 'px');

                if (this.html_subscript)
                    $(this.html_subscript).css('left', mainw + this.eldata.suboffset + 'px');

                // work out max right offset


                // adjust right padding of element?

                // adjust sub and super widths accordingly
                if (this.subscript) {
                    this.subscript.align.width += this.eldata.suboffset;
                    if (this.subscript.align.width < 0)
                        this.subscript.align.width = 0;
                }
                if (this.superscript) {
                    this.superscript.align.width += this.eldata.supoffset;
                    if (this.superscript.align.width < 0)
                        this.superscript.align.width = 0;
                }
            }
        }
    },

    // if there is a large bracket, then remove it and change it to a normal bracket.
    // updates html_lb_inner etc 
    removeLargeBaracket: function (side, bracket, oldinner, scopeelem, bracketelem) {
        if (bracketelem.children.length > 2) {
            // we have a large bracket, so remove it and replace with 
            bracketelem.attr('style', '');
            bracketelem.css('position', 'relative');
            bracketelem.html(MEE.Data.blankspace);

            var newbracket = $('<span style="position:absolute">' + bracket + '</span>');
            //newbracket.css('position', 'absolute');
            //newbracket.html(bracket);
            bracketelem.append(newbracket);

            this['html_' + side + '_inner'] = newbracket;

            return newbracket;
        }

        return oldinner;
    },

    // create a large bracket
    generateLargeBracket: function (bracket, mainh, scopeelem, bracketelem) {
        
        JQbracketelem = $(bracketelem);
             
        var bi = MEE.Data.getBracket(bracket);

        // pad the bracket element out to the required size
        JQbracketelem.css('position', 'relative');
        JQbracketelem.html("");
        
        var bh = MEE.Data.getBaseSize(scopeelem);
        var pad = Math.floor((mainh - bh) / 2);
        
        if($.browser.msie &&  $.browser.version >= 8) {
          //nasty hack to fix brackets iin ie 8 and 9
          pad += 12;
        }
        JQbracketelem.css('top', -pad + 'px');

        // position the top part   
        var topcdata = MEE.Data.getCharSize(bi.top, scopeelem);
        var top = $('<span class="mee_bracket_part" style="top:' + -topcdata.top + 'px' + '">' + bi.top + '</span>');
        JQbracketelem.append(top);

        // position the bottom part
        var bottom = $('<span class="mee_bracket_part">' + bi.bottom + '</span>');
        var bottomcdata = MEE.Data.getCharSize(bi.bottom, scopeelem);
        bottom.css('top', mainh - (bottomcdata.top + bottomcdata.height) + 'px');
        JQbracketelem.append(bottom);

        var midcdata = MEE.Data.getCharSize(bi.mid, scopeelem);


        if (bi.angle) { // if we have a 4 part bracket such as {

            var middletop = topcdata.height;
            var middlebottom = mainh - bottomcdata.height;
            
            var anglecdata = MEE.Data.getCharSize(bi.angle, scopeelem);
            var angle = $('<span class="mee_bracket_part" style="top:' + (Math.floor((middletop + middlebottom - anglecdata.height) / 2) - anglecdata.top) + 'px" ></span>');
            angle.html(bi.angle);
            JQbracketelem.append(angle);

            // gap sizing
            var gapsize = Math.floor((mainh - (topcdata.height + bottomcdata.height + anglecdata.height)) / 2);


            if (gapsize > 0) { // there is a gap between the top angle and bottom

                if (gapsize < midcdata.height) { // if we need only 1 mid section
                    var mid = $('<span class="mee_bracket_part" style="top:' + (-midcdata.top + topcdata.height - Math.floor((midcdata.height - gapsize) / 2)) + 'px" ></span>');
                    mid.html(bi.mid);
                    JQbracketelem.append(mid);

                    var mid = $('<span class="mee_bracket_part" style="top:' + (-midcdata.top + mainh - bottomcdata.height - Math.floor((midcdata.height + gapsize) / 2)) + 'px" ></span>');
                    mid.html(bi.mid);
                    JQbracketelem.append(mid);

                } else { // need to fill in multiple mid parts

                    //////////////
                    // top half //
                    //////////////
                    // multiple top mid top elements
                    var last = -midcdata.top + topcdata.height + gapsize - midcdata.height;
                    var cur = -midcdata.top + topcdata.height;
                    while (cur < last && midcdata.height > 0) {
                        var mid = $('<span class="mee_bracket_part" style="top:' + cur + 'px' + '"></span>');
                        mid.html(bi.mid);
                        JQbracketelem.append(mid);
                        cur += midcdata.height;
                    }


                    // top mid bottom
                    var mid = $('<span class="mee_bracket_part"style="top:' + last + 'px' + '"></span>');
                    mid.html(bi.mid);
                    JQbracketelem.append(mid);

                    /////////////////
                    // bottom half //
                    /////////////////

                    // multiple bottom mid top 
                    var last = -midcdata.top + mainh - bottomcdata.height - midcdata.height;

                    var cur = -midcdata.top + mainh - bottomcdata.height - gapsize;
                    while (cur < last && midcdata.height > 0) {
                        var mid = $('<span class="mee_bracket_part" style="top:' + cur + 'px' + '"></span>');
                        mid.html(bi.mid);
                        JQbracketelem.append(mid);
                        cur += midcdata.height;
                    }

                    // single bottom mid bottom element
                    var mid = $('<span class="mee_bracket_part"style="top:' + last + 'px' + '"></span>');
                    mid.html(bi.mid);
                    JQbracketelem.append(mid);

                }
            }
        } else { // normal 3 part bracket

            var gapsize = mainh - (topcdata.height + bottomcdata.height);

            if (gapsize > 0) { // do we have a gap in the middle of the top and bottom part? if so fill it up

                if (gapsize < midcdata.height) { // only need a single mid character so center it
                    var mid = $('<span class="mee_bracket_part"style="top:' + (-midcdata.top + topcdata.height - Math.floor((midcdata.height - gapsize) / 2)) + 'px' + '"></span>');
                    mid.html(bi.mid);
                    JQbracketelem.append(mid);

                } else {
                    // multiple bottom mid top 
                    var last = -midcdata.top + mainh - bottomcdata.height - midcdata.height;

                    var cur = -midcdata.top + topcdata.height;
                    while (cur < last && midcdata.height > 0) {
                        var mid = $('<span class="mee_bracket_part"style="top:' + cur + 'px' + '"></span>');;
                        mid.html(bi.mid);
                        JQbracketelem.append(mid);
                        cur += midcdata.height;
                    }

                    // single bottom mid bottom element
                    var mid = $('<span class="mee_bracket_part" style="top:'+ last +'px"></span>');
                    mid.html(bi.mid);
                    JQbracketelem.append(mid);
                }
            }
        }

        // if we have a font override specified apply it to all the parts
        if (bi.font)
            JQbracketelem.children().css('font-family', bi.font);
        
        // add a relative spacer to make browsers heppy
        var spacer = $('<span></span>');
        spacer.html(MEE.Data.blankspace);
        JQbracketelem.append(spacer);
        
        // pad mid to width of the bracket
        var width = $(top).outerWidth();
        if (width == 0)
            width = '12';
            //alert("width = 0");
        $(spacer).css('padding-right', width + 'px');
        return width;
    },

    getSizeModPair: function (side, size) {
        for (var i = 0; i < MEE.Data.sizemodifiers.length; i++) {
            var sizemod = MEE.Data.sizemodifiers[i];
            if (sizemod.size == size)
                return sizemod[side];
        }
        return "";
    },

    getSizeMod: function (size) {
        for (name in MEE.Data.sizemodifiers_single) {
            var sizemod = MEE.Data.sizemodifiers_single[name];
            if (sizemod == size)
                return name;
        }
        return "";
    },

    lookupBracket: function (text, side) {
        if (text == "&#x2225;") {
            if (side == "left") return "\\lVert";
            if (side == "right") return "\\rVert";
        }
        for (var command in MEE.Data.commands) {
            if (MEE.Data.commands[command].text == text)
                return command;
        }
        return text;
    },

    toLatex: function () {
        var latex = new MEE.Latex();

        if (this._name == "MEE.ElemInput")
            return latex;
        
        if(this.latex != '') {
          //TODO: quick speed gain do we realy need to loop through this every element? quicker with a regexp??
          for (var i = 0; i < MEE.Data.pairs.length; i++) {
              if (this.latex == MEE.Data.pairs[i].pair)
                  this.type = "extpair";
          }
        }

        // if we have a size
        if (this.size && this.type.substr(0, 3) == "ext") {
            // add size to output
            if (this.type != "extsingle") {
                var size = this.getSizeModPair('left', this.size);
            } else {
                var size = this.getSizeMod(this.size);
            }
            latex.AddText(size);
        }

        if (this.type == "extpair" && this.eldata.lb) {
            latex.AddText(this.lookupBracket(this.eldata.lb, 'left') + " ");
        }

        if (this.latex == "substack") {
            latex.AddText("\\substack{");
            latex.AddElem(this.main.toLatex());
            latex.AddText("}");

        } else if (this.eldata.all_as_arg01) {
            //latex.AddMod("{");
            if (this.argmap[0])
                latex.AddElem(this.argmap[0].toLatex());
            latex.AddText(" \\" + this.latex + " ");
            if (this.argmap[1])
                latex.AddElem(this.argmap[1].toLatex());
            //latex.AddMod("}");
        } else if (this.eldata.rest_as_arg0) {
            latex.AddText("\\" + this.latex + " ");
            if (this.argmap[0])
                latex.AddElem(this.argmap[0].toLatex());
        } else if (this.eldata.arg0_as_super) {
            latex.AddText("\\" + this.latex);
            if (this.superscript) {
                latex.AddMod("{");
                latex.AddElem(this.superscript.toLatex());
                latex.AddMod("}");
            }
            if (this.main) {
                latex.AddMod("{");
                latex.AddElem(this.main.toLatex());
                latex.AddMod("}");
            }
        } else if (this.eldata.arg0_as_sub) {
            latex.AddText("\\" + this.latex);
            if (this.subscript) {
                latex.AddMod("{");
                latex.AddElem(this.subscript.toLatex());
                latex.AddMod("}");
            }
            if (this.main) {
                latex.AddMod("{");
                latex.AddElem(this.main.toLatex());
                latex.AddMod("}");
            }
        } else if (this.type == "arg") {
            latex.AddMod("{");
            latex.AddElem(this.argmap[0].toLatex());
            latex.AddMod("}");
        } else {
            if (this.type == "begin") {
                latex.AddText("\\begin{" + this.latex + "}");
                latex.AddElem(this.main.toLatex());
                latex.AddText("\\end{" + this.latex + "}");

            } else if (this.type == "command") {
                if (this.latex != "")
                    latex.AddText("\\" + this.latex);
                if (this.eldata.foreclimits == "above")
                    latex.AddText("\\limits");
                if ('foreclimits' in this.eldata && this.eldata.foreclimits == "")
                    latex.AddText("\\nolimits");
            } else if (this.type == "" && !this.eldata.blank) {
                latex.AddText(this.latex);
            } else if (this.type == "extsingle") {
                latex.AddText("\\" + this.latex);
            }
            if (this.eldata.sarg) {
                var l = null;
                if (this.eldata.sarg_as_sup) {
                    if (this.superscript)
                        l = this.superscript.toLatex();

                } else {
                    if (this.sarg)
                        l = this.sarg.toLatex();
                }
                if (l && l.latex != "") {
                    latex.AddMod("[");
                    latex.AddElem(l);
                    latex.AddMod("]");
                }
            }
            if (this.argmap && this.argmap.length > 0) {
                for (var i = 0; i < this.argmap.length; i++) {
                    if (this.argmap[i]) {
                        if (/*i == 0 &&*/this.type == "extpair") {
                            latex.AddElem(this.argmap[i].toLatex());
                        } else {
                            var latex2 = this.argmap[i].toLatex();
                            if (this.eldata.allowspaces) {
                                var text = latex2.latex;
                                var parser = new MEE.Parser();
                                text = parser.replaceAll(text, "\\;", "&nbsp;");
                                text = parser.replaceAll(text, " ", "");
                                text = parser.replaceAll(text, "&nbsp;", " ");
                                latex2.latex = text;
                            }
                            latex.AddArg(latex2);
                        }
                    }
                }
            }
        }
        // if we have a size
        if (this.sizer) {
            // add size to output
            if (this.type == "extpair") {
                var size = this.getSizeModPair('right', this.sizer);
                latex.AddText(size);
            }

        }

        // closing bracket
        if (this.type == "extpair" && this.eldata.rb) {
            var rcmd = this.lookupBracket(this.eldata.rb, 'right');
            if (rcmd.substr(0, 2) == "\\l")
                rcmd = "\\r" + rcmd.substr(2);
            latex.AddText(rcmd);
        }

        if (this.subscript && !this.eldata.arg0_as_sub) {
            var l = this.subscript.toLatex();
            if (l.latex != "") {
                latex.AddMod("_");
                latex.AddSet(l);
            }
        }
        if (this.superscript && !this.eldata.sarg_as_sup && !this.eldata.arg0_as_super) {
            var l = this.superscript.toLatex();
            if (l.latex != "") {
                latex.AddMod("^");
                latex.AddSet(l);
            }
        }
        //if (this.type == "command" && !this.eldata.rest_as_arg0)
        //latex.AddText(" ");

        return latex;
    },
    
    replacePX: function (val) {
      val = parseInt(val);
      if(!val || val == 'NaN') {
        return 0;
      } else {
        return val;
      }
    },
    ////////////////////////////
    // DEBUG STUFF BELOW HERE //
    ////////////////////////////
    dump: function () {
        var dump = "<div class='dump_elem'>";
        if (!this.parent)
            dump += "<div class='dump_no_parent'>NO PARENT</div>";

        if (this.main && this.main.isbasic) {
            dump += "<div class='dump_elem_head'>Elem Basic: " + this.latex;
            if (this.latex != this.main.latex)
                dump += "{" + this.main.latex + "}";
            dump += "</div>";
        } else {
            dump += "<div class='dump_elem_head'>Elem: " + this.latex + "</div>";
        }
        if (this.subscript)
            dump += "<div class='dump_elem_part'>Subscript: " + this.subscript.dump() + "</div>";
        if (this.superscript)
            dump += "<div class='dump_elem_part'>SuperScript: " + this.superscript.dump() + "</div>";
        if (this.main && !this.main.isbasic)
            dump += "<div class='dump_elem_part'>Main: " + this.main.dump() + "</div>";

        if (this.args)
            for (var i = 0; i < this.args.length; i++)
                dump += "<div class='dump_elem_part'>Arg " + i + ": " + this.args[i].dump() + "</div>";

        if (this.sarg)
            dump += "<div class='dump_elem_part'>SArg : " + this.sarg.dump() + "</div>";

        dump += "</div>";
        return dump;
    }
});
