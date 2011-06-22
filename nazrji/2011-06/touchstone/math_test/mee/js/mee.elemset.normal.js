

MEE.ElemSet.extend("MEE.ElemSetNormal",
{
    html_elem: null,
    latex: null,

    // prototype stuff
    init: function (latex) {
        //alert(latex);
        this.latex = latex;

        var parser = new MEE.Parser();
        this.elements = parser.parse(latex);

        /*for (var i = 0; i < elements.length; i++) {
        var elem = elements[i];
        //if (elem.
        }*/
    },

    toHTML: function () {
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elemset');

        for (var i = 0; i < this.elements.length; i++) {
            var elemhtml = this.elements[i].toHTML();
            this.html_elem.append(elemhtml);
        }

        return this.html_elem;
    },

    /*sortWidth: function () {
        // calculate the widths of the child elements, and return the value
        // any elements that require size sorting should also be processed here
        var width = 0;

        for (var i = 0; i < this.elements.length; i++) {
            width += this.elements[i].sortWidth();
        }

        return width;
    },

    sortHeight: function () {
        // calculate the height of the element and its children. 
        // This needs to pad the container both top and bottom to the contents
        var vert = new Object();
        vert.top = 0;
        vert.bottom = 0;

        //debug_text += "B - elemset.sortHeight - '" + this.latex + "'";

        for (var i = 0; i < this.elements.length; i++) {
            //debug_text += "<div class='indent'>";
            var newvert = this.elements[i].sortHeight();
            vert = this.maxOffsets(vert, newvert);
            //debug_text += "</div>";
        }

        // need to grow the elemset container to contain all of its child elements

        var par = this.html_elem[0].parentNode;

        if (vert.top && $(this.html_elem)) {
            $(this.html_elem).css('padding-top', Math.ceil(vert.top) + 'px');
            $(par).css('padding-top', Math.ceil(vert.top) + 'px');
        }

        if (vert.bottom && $(this.html_elem)) {
            $(this.html_elem).css('padding-bottom', Math.ceil(vert.bottom) + 'px');
            $(par).css('padding-bottom', Math.ceil(vert.bottom) + 'px');
        }

        //$(document.body).append(elem);

        return vert;
    },*/
});
