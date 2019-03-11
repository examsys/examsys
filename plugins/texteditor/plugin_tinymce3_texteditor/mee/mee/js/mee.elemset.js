$.Class.extend("MEE.ElemSet",
{
    elemsets: Array()
},
{
    single: false,
    isarray: 0,

    toHTML: function () {

    },

    sortAlign: function () {
        this.align = new MEE.Align();

        this.align.height = MEE.Data.getBaseSize(this.html_elem);
        this.haselements = false;
        this.hasinput = false;
        if (this.elements) {
            var l = this.elements.length;
            for (var i = 0; i < l; i++) {
                if (this.elements[i]._name != "MEE.ElemInput") {
                    this.haselements = true;
                } else {
                    this.hasinput = true;
                }

                this.elements[i].sortAlign();
                this.align.Merge(this.elements[i].align);

                //this.elements[i].html_elem.attr('al', this.elements[i].align.toString());
            }
        }

        // sort out display of empty element set marker
        if (this.isedit && (this.html_elem.hasClass('mee_elemset_empty') || !this.haselements)) {
            var minwidth = MEE.Data.emptywidth;
            minwidth = $(minwidth).toPx({ 'scope': this.html_elem });
            if (this.align.width < minwidth) {
                this.html_elem.append(this.html_elem.find('.mee_elemset_empty_inner'));
                this.html_elem.find('.mee_elemset_empty_inner').css('padding-right', (minwidth - this.align.width) - MEE.Data.blankspacesize(this.html_elem) - 2 + 'px');
                this.align.width = minwidth;
            }
            // empty!
        }

        return this.align;
    },


    ///////////////////////////
    // EDIT STUFF BELOW HERE //
    ///////////////////////////
    insertElemBeforeInput: function (elem) {
        // find input element
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            if (curelem._name == "MEE.ElemInput") {
                // we have found the input element, insert the elem before it
                elem.offset = i;
                this.elements.splice(i, 0, elem);
                return i;
            }
        }
        alert("Cannot find input element");
        this.elements.push(elem);
        elem.offset = this.elements.length - 1;
        return this.elements.length - 1;
    },

    getInputPos: function () {
        // find input element
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            if (curelem._name == "MEE.ElemInput") {
                return i;
            }
        }
        return -1;
    },

    insertHTMLFor: function (elem, index) {
        var html = elem.toHTML(this.depth);

        var input = this.elements[index + 1];
        html.insertBefore(input.html_elem);
    },

    getElemBeforeInput: function () {
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            if (curelem._name == "MEE.ElemInput") {
                if (i > this.elements.length) {
                    return null;
                }
                if (i == 0)
                    return null;
                // we have found the input element, insert the elem before it
                this.elements[i - 1].offset = i - 1;
                return this.elements[i - 1];
            }
        }
    },

    removeInput: function () {
        // parse bond if available
        if (this.parent && this.parent.eldata && this.parent.eldata.object == 'Bond') {
            this.parent.SortBondInner();
        }

        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            if (curelem._name == "MEE.ElemInput") {
                // we have found the input element, insert the elem before it
                this.elements.splice(i, 1);
                break;
            }
        }

        $('.mee_elem_input_box').val("");

        this.sortBlanks();

        this.html_elem.css('background-color', '');
    },

    sortBlanks: function () {
        if (!this.isedit)
            return;

        var haselements = false;
        var hasinput = false;
        for (var i = 0; i < this.elements.length; i++) {
            if (this.elements[i]._name != "MEE.ElemInput") {
                haselements = true;
                break;
            } else {
                hasinput = true;
            }
        }

        if (!haselements /*this.elements.length == 0*/) {
            if (!this.html_elem.hasClass('mee_elemset_empty')) {
                var blank = $('<span class="mee_elemset_empty_inner"></span>');
                blank.html(MEE.Data.blankspace);
                this.html_elem.append(blank);
                this.html_elem.addClass('mee_elemset_empty');
            }
        } else {
            this.html_elem.children('.mee_elemset_empty_inner').remove();
            this.html_elem.removeClass('mee_elemset_empty');
        }
    },

    Highlight: function () {
        if (!this.isedit)
            return;

        if (typeof this.selectStart == "number") {
            var hldiv = $('.mee_edit_highlight_elem');
            hldiv.css('display', 'none');

            var hldiv = $('.mee_edit_highlight_multi');
            hldiv.css('display', 'block');

            var startelem = this.elements[this.selectStart].html_elem;
            var inputpos = this.getInputPos();

            var endelem = this.elements[inputpos].html_elem;

            MEE.Tools.HTML.AlignElementOver([startelem, endelem], hldiv, this.align);

            return;
        }

        var hldiv = $('.mee_edit_highlight_multi');
        hldiv.css('display', 'none');

        var elem = this.getElemBeforeInput();
        /*if ($.browser.msie && document.documentMode == 7) {
        $('.mee_ie7_highlight').removeClass('mee_ie7_highlight');
        if (elem)
        elem.html_elem.addClass('mee_ie7_highlight');
        return;
        }*/
        if (this.single && !this.inmatrix) {
            var hldiv = $('.mee_edit_highlight');
            MEE.Tools.HTML.AlignElementOver(this.html_elem, hldiv, this.align);
            hldiv.css('background-image', 'url(' + mee_baseurl + 'images/edit/highlight_red.png)');
            hldiv.css('display', 'block');
        } else {
            var hldiv = $('.mee_edit_highlight');
            hldiv.css('display', 'none');
        }

        // need to get the element before the input if there is one and highlight it
        if (elem) {

            elem_html = elem.html_elem;
            var hldiv = $('.mee_edit_highlight_elem');
            MEE.Tools.HTML.AlignElementOver(elem_html, hldiv, elem.align);
            hldiv.css('display', 'block');
        } else {
            var hldiv = $('.mee_edit_highlight_elem');
            hldiv.css('display', 'none');
        }
    },
    getElementOffset: function (elem) {
        for (var i = 0; i < this.elements.length; i++) {
            if (elem == this.elements[i])
                return i;
        }
        return -1;
    },

    findElement: function (latex) {
        if (this.elements && this.elements.length > 0) {
            for (var i = 0; i < this.elements.length; i++) {
                var elem = this.elements[i];
                if (elem.latex == latex) {
                    return { 'set': this, 'elem': elem };
                }

                if (elem.main) {
                    var res = elem.main.findElement(latex);
                    if (res) return res;
                }
                if (elem.sarg) {
                    var res = elem.sarg.findElement(latex);
                    if (res) return res;
                }
                if (elem.superscript) {
                    var res = elem.superscript.findElement(latex);
                    if (res) return res;
                }
                if (elem.subscript) {
                    var res = elem.subscript.findElement(latex);
                    if (res) return res;
                }
                if (elem.args && elem.args.length > 0) {
                    for (var k = 0; k < elem.args.length; k++) {
                        var res = elem.args[k].findElement(latex);
                        if (res) return res;
                    }
                }
            }
        }

        return null;
    },

    toLatex: function () {
        var latex = new MEE.Latex();

        if (this.eldata.displaystyle)
            latex.AddText("\\displaystyle");
        if (this.eldata.textstyle)
            latex.AddText("\\textstyle");

        for (var i = 0; i < this.elements.length; i++) {
            latex.AddElem(this.elements[i].toLatex());
        }
        return latex;
    }
});
