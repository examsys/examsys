// array row
$.Class.extend("MEE.Row",
{
    cols: 0,

    init: function (eldata, row, par) {
        this._name = 'MEE.Row';
        this.row = row;
        this.parent = par;
        if (par.isedit)
            this.isedit = true;
        this.eldata = jQuery.extend({}, eldata);
    },

    addElem: function (token) {
        if (!token) return;
        this['col' + this.cols] = new MEE.ElemSetNormal(token.latex, this);
        this['col' + this.cols].inmatrix = this.eldata.inmatrix;
        this.cols++;
    },

    addRowText: function (latex) {
        if (this.eldata.nosplit) {
            this['col0'] = new MEE.ElemSetNormal(latex, this);
            this['col0'].inmatrix = this.eldata.inmatrix;
            this['col0'].col = 0;
            this.cols = 1;
            return;
        }

        var cols = latex.split("&");

        for (var i = 0; i < cols.length; i++) {
            this['col' + i] = new MEE.ElemSetNormal(cols[i], this);
            this['col' + i].inmatrix = this.eldata.inmatrix;
            this['col' + i].col = i;
        }

        this.cols = cols.length;
    },

    toHTML: function (depth, subdepth) {
        // if we arent the first row and column, then get the depth info from the first one
        this.elemid = MEE.ElemSet.elemsets.length;
        MEE.ElemSet.elemsets[this.elemid] = this;
        
        this.html_elem = $('<span class="mee_row" elem="' + this.elemid + '"></span>');
        this.depth = depth;
        this.subdepth = subdepth;

        for (var i = 0; i < this.cols; i++) {
            this.createColHTML(i, subdepth);
        }

        // do we have a hline tag associated with the row?
        // this can only occur normally with a apply_to_parent tag
        if (this.eldata.hline) {
            if (this.row + 1 == this.parent.rows && this.eldata.hline && this.cols == 1 && $.trim(this.col0.latex) == "\\hline") { // if the last row is nothing but \hline then set the entire thing to have a single border
                this.parent.eldata.border = 1;

            } else { // hline above this row
                this.html_line = $('<span class="mee_hline"></span>');
                this.html_line.html(MEE.Data.blankspace);
                this.html_elem.append(this.html_line);
            }
        }

        return this.html_elem;
    },

    createColHTML: function (colno, subdepth) {
        if (this.html_elem) {
            this['html_col' + colno] = this['col' + colno].toHTML(subdepth);
            this['html_col' + colno].addClass('mee_col');
            this.html_elem.append(this['html_col' + colno]);
            if (this.eldata.colclass)
                this['html_col' + colno].addClass(this.eldata.colclass);
        }
    },

    sortAlign: function () {
        this.align = new MEE.Align();
        for (var i = this.cols;i--;) {
            var colalign = this['col' + i].sortAlign();
            //if (this['col' + i].haselements || this['col' + i].hasinput)
            this.align.Merge(colalign);
        }
 //       if (this.align.top)
 //         $(this.html_elem).css('margin-top', this.align.top + 'px');
 //       if (this.align.bottom)
 //         $(this.html_elem).css('margin-bottom', this.align.bottom + 'px');

        //this.html_elem.attr('al', this.align.toString());
        return this.align;
    },

    addCols: function (cols) {
        for (var c = this.cols; c < cols; c++) {
            this['col' + c] = new MEE.ElemSetNormal("", this);
            this['col' + c].inmatrix = this.eldata.inmatrix;
            this['col' + c].col = c;
            this['col' + c].depth = this.subdepth;
            this.createColHTML(c, this.subdepth);
        }
        this.cols = cols;
    },

    dump: function () {
        var dump = "";
        if (!this.parent)
            dump += "<div class='dump_no_parent'>NO PARENT</div>";

        for (var i = 0; i < this.cols; i++) {
            dump += "<td>";
            dump += this['col' + i].dump();
            dump += "</td>";
        }
        return dump;
    }
});

// array element set
MEE.ElemSet.extend("MEE.ElemSetArray",
{
    rows: 0,
    cols: 0,
    alignment: '',

    init: function (eldata, alignment, par) {
        this._name = 'MEE.ElemSetArray';
        this.parent = par;
        this.eldata = jQuery.extend({}, eldata);
        if (alignment)
            this.alignment = alignment;
        if (par.isedit)
            this.isedit = true;

        this.isarray = 1;
    },

    UpperLower: function (upper, lower) {
        // create 2 rows with 1 element each
        if (!this.row0) {
            this.row0 = new MEE.Row(this.eldata, 0, this);
            this.row1 = new MEE.Row(this.eldata, 1, this);
            this.row0.row = 0;
            this.row1.row = 1;
            this.rows = 2;
            this.cols = 1;
        }

        if (upper)
            this.row0.addElem(upper);
        if (lower)
            this.row1.addElem(lower);
    },

    AddArray: function (token) {
        // adding a matrix here, need to split the token by \\ for rows and & for cols

        // some begin and end combos dont split, so parse em as such
        if (this.eldata.nosplit) {
            this['row0'] = new MEE.Row(this.eldata, 0, this);
            this['row0'].addRowText(token.latex);
            this['row0'].row = 0;
            this.cols = Math.max(this.cols, this['row0'].cols);
            this.rows = 1;
            return;
        }
        var rows = token.latex.split("\\\\");

        for (var i = 0; i < rows.length; i++) {
            this['row' + i] = new MEE.Row(this.eldata, i, this);
            this['row' + i].addRowText(rows[i]);
            this['row' + i].row = i;
            this.cols = Math.max(this.cols, this['row' + i].cols);
        }

        this.rows = rows.length;
        this.fillInBlankCols();
    },

    createRowHTML: function (i, subdepth) {
        this['html_row' + i] = this['row' + i].toHTML(this.depth, subdepth);
        this.html_elem.append(this['html_row' + i]);
        this['html_row' + i].css({left:'0px', position:'absolute'});

        if (i == 0 && this.eldata.upperclass)
            this['html_row' + i].addClass(this.eldata.upperclass);
        if (i == 1 && this.eldata.lowerclass)
            this['html_row' + i].addClass(this.eldata.lowerclass);
        if (this.eldata.rowclass)
            this['html_row' + i].addClass(this.eldata.rowclass);
    },

    fillInBlankCols: function () {
        for (var r = 0; r < this.rows; r++) {
            if (this['row' + r].cols < this.cols) {
                this['row' + r].addCols(this.cols);
            }
        }
    },

    toHTML: function (depth) {
        this.validateColWidths();

        this.html_elem = $('<span class="mee_elemsetarray" style="position: relative"></span>');
        //this.html_elem.addClass('mee_elemsetarray');
        //this.html_elem.css('position', 'relative');

        if (this.eldata.lend) {
            this.html_lend = $('<span class="mee_barend"></span>');
            this.html_lend.html(this.eldata.lend);
            this.html_elem.append(this.html_lend);
        }

        if (this.eldata.rend) {
            this.html_rend = $('<span class="mee_barend"></span>');
            this.html_rend.html(this.eldata.rend);
            this.html_elem.append(this.html_rend);
        }

        this.html_padding = $('<span style="position: relative"></span>');
        this.html_padding.html(MEE.Data.blankspace);
        this.html_elem.append(this.html_padding);

        // sort depth out

        if ($.browser.msie && document.documentMode > 7) {
            this.depth = depth + 1;
            if (this.eldata.nodepth) {
                this.depth = depth;
            }
            this.html_elem.attr('depth', this.depth);
            var subdepth = this.depth;
        } else {
            this.depth = depth;
            this.html_elem.attr('depth', depth);
            var subdepth = depth + 1;
            if (this.eldata.nodepth) {
                subdepth = depth;
            } else if (this.eldata.extradepth) {
                subdepth = depth + 2;
            }
        }
        var dodepth = true;
        if (this.parent && this.parent.depth == this.depth)
            dodepth = false;

        if (dodepth) {
            var size = MEE.Data.fontsizes[this.depth];
            $(this.html_elem).css('font-size', size);
        }



        if (this.eldata.bar) {
            this.html_bar = $('<span class="mee_bar"></span>');
            this.html_elem.append(this.html_bar);
        }

        for (var i = 0; i < this.rows; i++) {
            this.createRowHTML(i, subdepth);
        }

        // general border around entire array
        if (this.eldata.border) {
            this.html_line = $('<span class="mee_array_border"></span>');
            this.html_line.html(MEE.Data.blankspace);
            this.html_elem.append(this.html_line);
        }

        // vertical lines between columns
        for (col in this.vlines) {
            if (col < this.cols) {
                this['html_vline' + col] = $('<span class="mee_vline"></span>');
                this['html_vline' + col].html(MEE.Data.blankspace);
                this.html_elem.append(this['html_vline' + col]);
            }
        }
        return this.html_elem;
    },


    sortAlign: function () {
        this.align = new MEE.Align();

        // sort the all contained elements widths and heights
        // also add up total height while doing it
        var lendwidth = 0;
        if (this.eldata.lend) {
            lendwidth = MEE.Data.arrowendwidths[this.eldata.lend];
            lendwidth = $(lendwidth).toPx({ 'scope': this.html_elem });
        }
        var rendwidth = 0;
        if (this.html_rend) {
            rendwidth = MEE.Data.arrowendwidths[this.eldata.rend];
            rendwidth = $(rendwidth).toPx({ 'scope': this.html_elem });
        }
        if (this.eldata.evenpos) {
            // need to process fractions and binoms height differently, should be positions evenly above the baseline

            // this should only happen with 2 rows, so only process the first 2 rows

            // normal height adjustments
            var row0align = this['row0'].sortAlign();
            var row1align = this['row1'].sortAlign();



            var mainheight = MEE.Data.getBaseSize(this.html_padding);
            this.align.height = mainheight;
            
            try {
                var tpad = 0.1;
                tpad = $(tpad).toPx({ 'scope': this.html_elem });
                var bpad = 0.1;
                bpad = $(bpad).toPx({ 'scope': this.html_elem });
                //tpad = 0;
            } catch (err) {
                //do nothing!
            }
            // need to align the top half so the bottom of it is slightly above the base line and set align.top to the ammount
            // that this sits above the main element
            var top = row0align.height - Math.floor(mainheight / 2) + tpad - row0align.top;
            var bottom = Math.floor(mainheight / 2) + row1align.top + bpad;

            //var top = -tpad + Math.floor(mainheight / 2);
            if(this['html_row1'])
              this['html_row0'].css('top', -top + 'px');
            if(this['html_row1'])
              this['html_row1'].css('top', bottom + 'px');

            this.align.top = top + row0align.top;
            this.align.bottom = row1align.height - Math.floor(mainheight / 2) + bpad - 1;

        } else {

            // normal height adjustments
            var totalheight = 0;
            for (var i = 0; i < this.rows; i++) {
                this['row' + i].sortAlign();
            }

            // remove any blank columns 
            this.RemoveBlanks();

            // work out the height
            for (var i = 0; i < this.rows; i++) {
                totalheight += this['row' + i].align.height;
            }

            var mainheight = MEE.Data.getBaseSize(this.html_padding);
            this.align.height = mainheight;

            var above = -Math.floor((totalheight - mainheight) / 2);

            this.align.top = Math.abs(above);

            for (var i = 0; i < this.rows; i++) {
                this['html_row' + i].css('top', above + this['row' + i].align.top + 'px');
                above += this['row' + i].align.height;
            }

            this.align.bottom = above - mainheight;
        }

        var totalwidth = 0;
        var coloffsets = {};

        // sort out column sizings
        for (var col = 0; col < this.cols; col++) {
            var maxwidth = 0;
            for (var row = 0; row < this.rows; row++) {
                var rowc = this['row' + row];
                if (!rowc)
                    continue;

                var colc = rowc['col' + col];
                if (!colc)
                    continue;

                var elwidth = colc.align.width;

                maxwidth = Math.max(maxwidth, elwidth);
            }

            // pad as specified in the element data
            if ('padding' in this.eldata) {
                var pad = this.eldata.padding;
                pad = $(pad).toPx({ 'scope': this.html_elem });
                maxwidth += pad;
            }

            totalwidth += maxwidth;
            coloffsets[col] = totalwidth;
            for (row = 0; row < this.rows; row++) {
                var rowc = this['row' + row];
                if (!rowc)
                    continue;

                var colc = rowc['col' + col];
                if (!colc)
                    continue;

                var elwidth = colc.align.width;

                if (elwidth < maxwidth) {
                    if (this.colalign[col] == "l") {
                        $(colc.html_elem).css('padding-right', maxwidth - elwidth + 'px');
                    } else if (this.colalign[col] == "r") {
                        $(colc.html_elem).css('padding-left', maxwidth - elwidth + 'px');
                    } else {
                        var padl = Math.floor((maxwidth - elwidth) / 2);
                        var padr = Math.ceil((maxwidth - elwidth) / 2);
                        $(colc.html_elem).css({ 'padding-left': padl + lendwidth + 'px',
                                                'padding-right': padr + 'px'
                                              });
                    }
                } else {
                    $(colc.html_elem).css({ 
                                            'padding-right': '1px',
                                            'padding-left': lendwidth + 'px'
                                          });
                }
            }
        }

        totalwidth += lendwidth + rendwidth;

        var padright = totalwidth;
        $(this.html_elem).css('padding-right', padright - MEE.Data.blankspacesize(this.html_elem) + 'px');

        // size hlines
        for (var row = 0; row < this.rows; row++) {
            if (this['row' + row] && this['row' + row].html_line) {
                $(this['row' + row].html_line).css({
                                                      'padding-right': padright + 'px',
                                                      'top': -this['row' + row].align.top + 'px'
                                                   });
            }
        }

        // size and bar (\frac etc)
        if (this.html_bar) {
            var barpadding = 0.07;
            barpadding = $(barpadding).toPx({ 'scope': this.html_elem });
            //barpadding = 0;
            var barwidth = totalwidth - (2 * barpadding) - lendwidth - rendwidth;
            barwidth = $(barwidth).toEm({ 'scope': this.html_elem });

            var bartext = MEE.Tools.HTML.BuildBarText(barwidth, this.eldata.bartype);

            this.html_bar.html(bartext);

            this.html_bar.css('margin-left', barpadding + lendwidth + 'px');
        }

        if (this.html_rend) {
            var offset = totalwidth - this.html_rend.outerWidth();
            this.html_rend.css('padding-left', offset + 'px');
        }

        // work out final align
        /*if (this.align.top)
        $(this.html_elem).css('margin-top', this.align.top + 'px');
        if (this.align.bottom)
        $(this.html_elem).css('margin-bottom', this.align.bottom + 'px');*/

        this.align.height += this.align.top + this.align.bottom;
        this.align.width = totalwidth;

        var bl = 0.05;
        bl = $(bl).toPx({ 'scope': this.html_elem });

        // size whole border
        if (this.eldata.border) {
            var borderheight = MEE.Data.getBaseSize(this.html_line);
            this.html_line.css({
                                  'left': '0px',
                                  'top': -this.align.top + 'px',
                                  'padding-right': padright - bl + 'px',
                                  'padding-bottom': this.align.height - borderheight - bl + 'px'
                                });
        }

        // vertical lines between columns
        for (col in this.vlines) {
            if (col < this.cols) {
                var offset = coloffsets[col - 1];
                var borderheight = MEE.Data.getBaseSize($(this['html_vline' + col]));
                this['html_vline' + col].css({
                                              'left': offset + 'px',
                                              'top': -this.align.top + 'px',
                                              'padding-bottom': this.align.height - borderheight + 'px'
                                            });
            }
        }
        return this.align;
    },

    validateColWidths: function () {
        this.colalign = new Array();
        var align = this.eldata.align;
        if (this.alignment)
            align = this.alignment;

        this.vlines = {};

        if (align) {
            align = align.replace(new RegExp(' ', 'g'), '');
            var colno = 0;
            for (var i = 0; i < align.length; i++) {
                var alignchar = align.charAt(i);
                if (alignchar == "*") {
                    var prevchar = align.charAt(i - 1);
                    for (k = i; k < this.cols; k++) {
                        this.colalign[k] = prevchar;
                    }
                    if (i + 1 < align.length)
                        this.colalign[this.cols - 1] = align.charAt(align.length - 1);
                    break;
                } else {
                    if (alignchar == '|') {
                        this.vlines[colno] = 1;
                    } else {
                        this.colalign[colno++] = alignchar;
                    }
                }
            }
        }

        for (var i = 0; i < this.cols; i++) {
            if (!this.colalign[i]) {
                if (align && align.length > 0)
                    this.colalign[i] = align.charAt(align.length - 1);
                else
                    this.colalign[i] = 'c';
            }
        }
    },

    ///////////////////////////
    // EDIT STUFF BELOW HERE //
    ///////////////////////////
    // find an elements position
    getPosition: function (elementset) {
        for (var r = 0; r < this.rows; r++) {
            for (var c = 0; c < this.cols; c++) {
                if (this['row' + r]['col' + c] == elementset) {
                    return { row: r, col: c };
                }
            }
        }
        return { row: -1, col: -1 };
    },

    toLatex: function () {
        var latex = new MEE.Latex();

        if (this.eldata.custalign)
            latex.AddText("{" + this.alignment + "}");

        for (var r = 0; r < this.rows; r++) {
            var row = this['row' + r];
            if (row) {
                if (row.eldata.hline)
                    latex.AddText("\\hline ");
                for (var c = 0; c < row.cols; c++) {

                    var col = row['col' + c];
                    if (col) {
                        var coll = col.toLatex();
                        if (coll.latex != "") {
                            latex.AddElem(coll);
                        }
                    }
                    if (c < row.cols - 1)
                        latex.AddMod(" & ");
                }
            }
            if (r < this.rows - 1)
                latex.AddMod(" \\\\ ");
        }

        //if (this.eldata.border)
        //    latex.AddText("\\hline");

        return latex;
    },

    isColBlank: function (i, ignoreinput) {
        var colisblank = true;
        for (var r = 0; r < this.rows; r++) {
            var col = this['row' + r]['col' + i];
            if (ignoreinput) {
                if (col && col.haselements)
                    colisblank = false;
            } else {
                if (col && (col.haselements || col.hasinput))
                    colisblank = false;
            }
        }

        return colisblank;
    },

    isRowBlank: function (i, ignoreinput) {
        var rowisblank = true;
        for (var c = 0; c < this.cols; c++) {
            var col = this['row' + i]['col' + c];
            if (ignoreinput) {
                if (col && col.haselements)
                    rowisblank = false;
            } else {
                if (col && (col.haselements || col.hasinput))
                    rowisblank = false;
            }
        }

        return rowisblank;
    },

    RemoveBlanks: function () {
        if (this.eldata.frac)
            return;

        for (var i = this.cols - 1; i > 0; i--) {
            var colisblank = this.isColBlank(i);

            if (colisblank) {
                for (var r = 0; r < this.rows; r++) {
                    var row = this['row' + r];
                    if (row && row['col' + i] && row['col' + i].html_elem) {
                        row['col' + i].html_elem.remove();
                        row['col' + i] = null;
                        row.cols--;
                    }
                }
                this.cols--;
            } else {
                break;
            }
        }

        for (var i = this.rows - 1; i > 0; i--) {
            var rowisblank = this.isRowBlank(i);

            if (rowisblank) {
                var row = this['row' + i];
                if (row) {
                    row.html_elem.remove();
                    row = null;
                }

                this.rows--;
            } else {
                break;
            }
        }
    },

    DeleteRow: function (row) {
        if (this.eldata.frac)
            return;

        // delete the row
        this['row' + row].html_elem.remove();
        this['row' + row] = null;
        this['html_row' + row] = null;
        //console.log("Remove row " + row);

        // go through the rest of the row and renumber them
        for (var i = row + 1; i < this.rows; i++) {

            this['row' + (i - 1)] = this['row' + i];
            this['row' + (i - 1)].row = i - 1;
            this['row' + i] = null;

            this['html_row' + (i - 1)] = this['html_row' + i];
            this['html_row' + i] = null;

            //console.log("Renumber row " + i + " as " + (i - 1));
        }
        this.rows--;
    },

    AppendRow: function () {
        if (this.eldata.frac)
            return;

        var row = new MEE.Row(this.eldata, this.rows, this);
        row.row = this.rows;
        row.cols = 0;
        row.depth = this.depth;
        row.subdepth = this.depth + 1;
        this['row' + this.rows] = row;
        this.createRowHTML(this.rows, this.depth + 1);
        this.rows++;
        this.fillInBlankCols();
    },

    InsertRow: function (row) {
        if (this.eldata.frac)
            return;

        //shuffle rest of rows after insert position along one
        for (var i = this.rows - 1; i >= row; i--) {
            //console.log("Move row " + i + " to " + (i + 1));

            this['row' + (i + 1)] = this['row' + i];
            this['row' + (i + 1)].row = i + 1;
            this['row' + i] = null;

            this['html_row' + (i + 1)] = this['html_row' + i];
            this['html_row' + i] = null;
        }

        var rowe = new MEE.Row(this.eldata, this.rows, this);
        this['row' + row] = rowe;
        this['row' + row].row = row;
        this['row' + row].cols = 0;
        this['row' + row].depth = this.depth;
        this['row' + row].subdepth = this.depth + 1;
        this.createRowHTML(row, this.depth + 1);
        this.rows++;
        this.fillInBlankCols();
    },

    DeleteCol: function (col) {
        if (this.eldata.frac)
            return;

        // delete the col
        for (var r = 0; r < this.rows; r++) {
            var row = this['row' + r];
            row['col' + col].html_elem.remove();
            row['col' + col] = null;
            row['html_col' + col] = null;
            row.cols--;
        }

        // go through the rest of the row and renumber them
        for (var i = col + 1; i < this.cols; i++) {
            for (var r = 0; r < this.rows; r++) {
                var row = this['row' + r];
                row['col' + (i - 1)] = row['col' + i];
                row['col' + (i - 1)].col = i - 1;
                row['col' + i] = null;

                row['html_col' + (i - 1)] = row['html_col' + i];
                row['html_col' + i] = null;
            }
        }

        this.cols--;
    },

    AppendCol: function () {
        if (this.eldata.frac)
            return;

        this.cols++;
        this.fillInBlankCols();
    },

    InsertCol: function (col) {
        if (this.eldata.frac)
            return;

        //shuffle rest of rows after insert position along one
        for (var i = this.cols - 1; i >= col; i--) {
            //console.log("Move col " + i + " to " + (i + 1));
            for (var r = 0; r < this.rows; r++) {
                var row = this['row' + r];
                row['col' + (i + 1)] = row['col' + i];
                row['col' + (i + 1)].row = i + 1;
                row['col' + i] = null;

                row['html_col' + (i + 1)] = row['html_col' + i];
                row['html_col' + i] = null;
            }

        }

        for (var r = 0; r < this.rows; r++) {
            var row = this['row' + r];
            // insert column at col in each row

            row['col' + col] = new MEE.ElemSetNormal("", row);
            row['col' + col].inmatrix = row.eldata.inmatrix;
            row['col' + col].col = col;

            row['html_col' + col] = row['col' + col].toHTML(row.subdepth);
            row['html_col' + col].addClass('mee_col');
            if (row.eldata.colclass)
                row['html_col' + col].addClass(row.eldata.colclass);

            // need to insert into correct place in row.html_elem
            var nextcol = row['html_col' + (col + 1)];
            nextcol.before(row['html_col' + col]);

            row.cols++;
            //row.createColHTML(col, row.depth);
        }

        this.cols++;
        this.fillInBlankCols();
    },

    findElement: function (latex) {
        for (var r = 0; r < this.rows; r++) {
            var row = this['row' + r];
            if (!row)
                continue;
            for (var c = 0; c < row.cols; c++) {
                var col = row['col' + c];
                if (!col)
                    continue;

                var res = col.findElement(latex);
                if (res) return res;
            }
        }

        return null;
    },
    getSetAt: function (pos) {
        if (this['row' + pos.row] && this['row' + pos.row]['col' + pos.col])
            return this['row' + pos.row]['col' + pos.col];

        return null;
    },
    ////////////////////////////
    // DEBUG STUFF BELOW HERE //
    ////////////////////////////
    dump: function () {
        var dump = "<div class='dump_set'>";
        if (!this.parent)
            dump += "<div class='dump_no_parent'>NO PARENT</div>";
        dump += "<table style='font-size:100%;'>";

        for (var i = 0; i < this.rows; i++) {
            dump += "<tr>";
            dump += this['row' + i].dump();
            dump += "</tr>";
        }
        dump += "</table>";
        dump += "</div>";

        return dump;
    }
});
