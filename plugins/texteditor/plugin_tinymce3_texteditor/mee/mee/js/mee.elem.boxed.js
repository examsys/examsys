
// handles boxed stuff
MEE.Elem.extend("MEE.ElemBoxed",
{
    toHTML: function (depth) {
        var res = this._super(depth);

        this.html_box = $('<span>');
        this.html_box.html(MEE.Data.blankspace);
        this.html_box.addClass('mee_boxed');
        this.html_elem.append(this.html_box);
        return res;
    },

    sortAlign: function () {
        var res = this._super();

        var pad = 0.1;
        pad = $(pad).toPx({ 'scope': this.html_box });

        this.html_box.css('left', pad + 'px');
        this.html_box.css('top', -this.main.align.top - pad + 'px');
        this.html_box.css('padding-right', (this.main.align.width - MEE.Data.blankspacesize(this.html_box) - 3) + pad + pad + 'px');
        this.html_box.css('padding-top', this.main.align.top + pad + 'px');
        this.html_box.css('padding-bottom', this.main.align.bottom + pad + 'px');

        this.html_main.css('padding-left', 2*pad + 'px');
        this.html_main.css('padding-right', 2*pad + 'px');

        this.align.width += pad * 4;
        if (this.align.top < pad*2)
            this.align.top = pad * 2;
        if (this.align.bottom < pad * 2)
            this.align.bottom = pad * 2;
        return res;
    }
});
