

$.Class.extend("MEE.Row",
{
    cols: 0,
    eldata: null,

    init: function (eldata) {
        this.eldata = eldata;  
    },
    addElem: function(token) {
        if (!token) return;
        this['col' + this.cols] = new MEE.ElemSetNormal(token.latex);
        this.cols++;
    },

    addRowText: function (latex) {
        if (this.eldata.nosplit)
        {
            this['col0'] = new MEE.ElemSetNormal(latex);
            this.cols = 1;
            return;
        }

        var cols = latex.split("&");

        for (var i = 0 ; i < cols.length ; i++)
        {
            this['col' + i] = new MEE.ElemSetNormal(cols[i]);
        }

        this.cols = cols.length;
    },

    toHTML: function() {
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_row');

        for (var i = 0 ; i < this.cols ; i++)
        {
            this['html_col' + i] = this['col' + i].toHTML();
            this['html_col' + i].addClass('mee_col');
            this.html_elem.append(this['html_col' + i]);
            if (this.eldata.colclass)
                this['html_col' + i].addClass(this.eldata.colclass);
          //this['html_col' + i].css('padding-right', 10 + 'px');
        }

        return this.html_elem;
    },
    
    sortAlign: function () {
        this.align = new MEE.Align();
        for (var i = 0 ; i < this.cols ; i++)
        {
            this.align.Merge(this['col' + i].sortAlign());
        }
        if (this.align.top)
            $(this.html_elem).css('margin-top', this.align.top + 'px');
        if (this.align.bottom)
            $(this.html_elem).css('margin-bottom', this.align.bottom + 'px');
        return this.align;
    },
});

MEE.ElemSet.extend("MEE.ElemSetArray",
{
    html_elem: null,
    latex: null,
    eldata: null,
    rows: 0,
    cols: 0,
    alignment: '',

    // prototype stuff
    init: function (eldata, alignment) {
        this.eldata = eldata;
        if (alignment)
            this.alignment = alignment;
    },

    UpperLower: function (upper, lower) {
        // create 2 rows with 1 element each
        this.row0 = new MEE.Row(this.eldata);
        this.row0.addElem(upper);
        this.row1 = new MEE.Row(this.eldata);
        this.row1.addElem(lower);
        this.rows = 2;
    },

    AddArray: function (token) {
        // adding a matrix here, need to split the token by \\ for rows and & for cols

        // some begin and end combos dont split, so parse em as such
        if (this.eldata.nosplit)
        {
            this['row0'] = new MEE.Row(this.eldata);
            this['row0'].addRowText(token.latex);
            this.rows = 1;
            return;
        }
        var rows = token.latex.split("\\\\");

        for (var i = 0 ; i < rows.length ; i++)
        {
            this['row' + i] = new MEE.Row(this.eldata);
            this['row' + i].addRowText(rows[i]);
        }

        this.rows = rows.length;
    },

    toHTML: function () {
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elemsetarray');
        
        this.html_padding = $('<span>');
        this.html_padding.html("&nbsp;");
        this.html_padding.css('position', 'relative');
        this.html_elem.append(this.html_padding);
        
        if (this.eldata.bar)
        {
            this.html_bar = $('<span>');
            this.html_bar.css('position', 'absolute');
            this.html_bar.css('left', '0px');
            this.html_bar.css('border-bottom','2px solid black');
            this.html_elem.append(this.html_bar);
        }

        for (var i = 0 ; i < this.rows ; i++)
        {
            this['html_row' + i] = this['row' + i].toHTML();
            this.html_elem.append(this['html_row' + i]);
            //if (i > 0) 
            {
                this['html_row' + i].css('position', 'absolute' );
                //this['html_row' + i].css('left', '20px' );
            //} else {
             //   this['html_row' + i].css('position', 'relative' );
            }
            this['html_row' + i].css('left', '0px');

            if (i == 0 && this.eldata.upperclass)
                this['html_row' + i].addClass(this.eldata.upperclass);
            if (i == 1 && this.eldata.lowerclass)
                this['html_row' + i].addClass(this.eldata.lowerclass);
            if (this.eldata.rowclass)
                this['html_row' + i].addClass(this.eldata.rowclass);
        }

        //this.html_elem.css('padding-right', '150px');
        //this.html_elem.css('padding-left', '20px');
        return this.html_elem;
    },

    
    sortAlign: function () {
        this.align = new MEE.Align();

        // sort the all contained elements widths and heights
        // also add up total height while doing it

        
        if (this.eldata.evenpos) {
            // need to process fractions and binoms height differently, should be positions evenly above the baseline

            // this should only happen with 2 rows, so only process the first 2 rows

            // normal height adjustments
            var row0align = this['row0'].sortAlign();
            var row1align = this['row1'].sortAlign();

            var mainheight = $(this.html_padding).outerHeight(true);

            var tpad = 0.1;
            tpad = $(tpad).toPx({ 'scope': this.html_elem });
            var bpad = 0.1;
            bpad = $(bpad).toPx({ 'scope': this.html_elem });

            this.cols = Math.max(this['row0'].cols,this['row1'].cols); 

            // need to align the top half so the bottom of it is slightly above the base line and set align.top to the ammount
            // that this sits above the main element
            var top = row0align.height - Math.floor(mainheight / 2) + tpad;
            this['html_row0'].css('top', -top + 'px');

            var bottom = Math.floor(mainheight / 2)/* + row1align.top*/ + bpad;
            this['html_row1'].css('top', bottom + 'px');
            
            this.align.top = top;
            this.align.bottom = row1align.height - Math.floor(mainheight / 2) + bpad - 1; // THIS IS WRONG I THINK!!!!!

        } else {

            // normal height adjustments
            var totalheight = 0;
            for (var i = 0 ; i < this.rows ; i++)
            {
                var rowalign = this['row' + i].sortAlign();
                totalheight += rowalign.height;

                this.cols = Math.max(this.cols,this['row' + i].cols); 
            }

            var mainheight = $(this.html_elem).outerHeight(true);
            var above = -Math.floor((totalheight - mainheight) / 2);

            this.align.top = Math.abs(above);

            for (var i = 0 ; i < this.rows ; i++)
            {
                this['html_row' + i].css('top', above + 'px');
                above += this['row' + i].align.height;
            }

            this.align.bottom = above - mainheight;
        }

        this.validateColWidths();

        var totalwidth = 0;
        // sort out column sizings
        for (var col = 0 ; col < this.cols ; col++)
        {
            var maxwidth = 0;
            for (var row = 0 ; row < this.rows ; row++)
            {
                var rowc = this['row' + row];
                if (!rowc)
                    continue;

                var colc = rowc['col' + col];
                if (!colc)
                    continue;
                
                var elwidth = colc.align.width;

                maxwidth = Math.max(maxwidth, elwidth);
            }
            var pad = 0.25;
            pad = $(pad).toPx({ 'scope': this.html_elem });
            maxwidth += pad;

            totalwidth += maxwidth;

            for (row = 0 ; row < this.rows ; row++)
            {
                var rowc = this['row' + row];
                if (!rowc)
                    continue;

                var colc = rowc['col' + col];
                if (!colc)
                    continue;
                
                var elwidth = colc.align.width;

                if (elwidth < maxwidth)
                {
                    if (this.colalign[col] == "l") {
                        $(colc.html_elem).css('padding-right', maxwidth - elwidth + 'px');
                    } else if (this.colalign[col] == "r") {
                        $(colc.html_elem).css('padding-left', maxwidth - elwidth + 'px');
                    } else {
                        var padl = Math.floor((maxwidth - elwidth) / 2);
                        var padr = Math.ceil((maxwidth - elwidth) / 2);
                        $(colc.html_elem).css('padding-left', padl + 'px');
                        $(colc.html_elem).css('padding-right', padr + 'px');
                    }
                }                
            }
        }

        var padright = totalwidth - $(this.html_padding).outerWidth(true);
        $(this.html_elem).css('padding-right', padright + 'px');

        
        if (this.html_bar) {
            var barpadding = 0.07;
            barpadding = $(barpadding).toPx({ 'scope': this.html_elem });
            var barwidth = totalwidth - (2 * barpadding);
            this.html_bar.css('padding-right',barwidth + 'px');
            this.html_bar.css('top',Math.floor($(this.html_elem).outerHeight(true) / 2) + 'px');
            this.html_bar.css('margin-left',barpadding + 1 + 'px');
        }
        if (this.align.top)
            $(this.html_elem).css('margin-top', this.align.top +'px');
        if (this.align.bottom)
            $(this.html_elem).css('margin-bottom', this.align.bottom +'px');

        this.align.height += this.align.top + this.align.bottom;
        this.align.width = totalwidth;
        return this.align;
    },

    validateColWidths: function () {
        this.colalign = new Array();
        var align = this.eldata.align;
        if (this.alignment)
            align = this.alignment;

        
        if (align) {
            while (align.indexOf(' ') != -1)
                align = align.replace(' ','');

            for (var i = 0 ; i < align.length ; i++)
            {
                var alignchar = align[i];
                if (alignchar == "*") {
                    var prevchar = align[i-1];
                    for (k = i ; k < this.cols ; k++)
                    {
                        this.colalign[k] = prevchar;
                    }
                    if (i+1 < align.length)
                        this.colalign[this.cols-1] = align[align.length-1];
                    break;
                } else {
                    this.colalign[i] = alignchar;
                }
            }
        }

        for (var i = 0 ; i < this.cols ; i++)
        {
            if (!this.colalign[i])
            {
                if (align && align.length > 0)
                    this.colalign[i] = align[align.length-1];
                else
                    this.colalign[i] = 'c';
            }
        }
    },

});
