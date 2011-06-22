

MEE.ElemSet.extend("MEE.ElemSetBasic",
{
    html_elem: null,
    latex: null,
    eldata: null,

    // prototype stuff
    init: function (latex, eldata) {
        //alert(latex);
        this.latex = latex;
        this.eldata = eldata;
    },

    toHTML: function () {
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elemsetbasic');
        if ('text' in this.eldata) {
            this.html_elem.html(this.eldata.text);
        } else {
            this.html_elem.html(this.latex);
        }
        if (this.eldata.mainclass)
            this.html_elem.addClass(this.eldata.mainclass);
        return this.html_elem;
    },

    sortAlign: function () {
        this.align = new MEE.Align();
        this.align.width = $(this.html_elem).outerWidth(true);
        this.align.height = $(this.html_elem).outerHeight(true);
        return this.align;
    },

});
