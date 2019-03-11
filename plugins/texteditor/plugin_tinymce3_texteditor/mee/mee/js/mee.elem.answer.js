// input element used for editor
MEE.Elem.extend("MEE.ElemAnswer",
{
    init: function () {
        this._name = 'MEE.ElemAnswer';
        this.eldata = {};
    },

    toHTML: function () {
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elem_answer');

        this.html_inner = $('<input>');
        this.html_inner.addClass('mee_elem_answer_input');
        this.html_inner.attr('size', '8');
        this.html_elem.append(this.html_inner);

        return this.html_elem;
    },

    sortAlign: function () {
        var fontsize = this.html_elem.css('font-size').replace('px', '');
        fontsize = Math.floor(fontsize * 0.7);
        this.html_inner.css('font-size', fontsize + 'px');
        this.html_elem.css('padding-left', '0.05em');
        this.html_inner.css('position', 'relative');
        this.html_inner.css('top', '-3px');

        this.align = new MEE.Align();
        this.align.width = $(this.html_elem).outerWidth(true); // OUTER_OK
        this.align.height = $(this.html_elem).outerHeight(true); // OUTER_OK
        this.align.top = 10;
        this.align.bottom = 10;
        this.align.height += 20;
        return this.align;
    },

    dump: function () {
        var dump = "<div class='dump_elem'>";
        dump += "<div class='dump_elem_head'>ANSWER</div>";
        return dump;
    }

});
