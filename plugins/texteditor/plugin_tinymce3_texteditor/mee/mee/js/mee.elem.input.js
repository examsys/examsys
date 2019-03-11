// input element used for editor
MEE.Elem.extend("MEE.ElemInput",
{
    init: function () {
        this._name = 'MEE.ElemInput';
        // create html for input
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elem_input');

        this.html_elem.html('&#x200b;');

        this.html_elem.append(this.html_inner);
    },

    toHTML: function () {

        return this.html_elem;
    },

    sortAlign: function () {

        this.align = new MEE.Align();
        this.align.width = $(this.html_elem).outerWidth(true); // OUTER_OK
        this.align.height = $(this.html_elem).outerHeight(true); // OUTER_OK

        if (this.size) {
            this.align.width = this.size;
            this.html_elem.css('padding-right', this.size + 'px');
        }
        return this.align;
    },

    dump: function () {
        var dump = "<div class='dump_elem'>";
        dump += "<div class='dump_elem_head'>INPUT</div>";
        return dump;
    }

});
