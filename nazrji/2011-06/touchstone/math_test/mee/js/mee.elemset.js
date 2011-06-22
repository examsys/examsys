

$.Class.extend("MEE.ElemSet",
{
    toHTML: function () {

    },

    maxOffsets: function (off1, off2) {
        var max = new Object();
        max.top = Math.max(off1.top, off2.top);
        max.bottom = Math.max(off1.bottom, off2.bottom);
        return max;
    },

    minFontSize: function (minsize) {
        for (var i = 0; i < this.elements.length; i++) {
            this.elements[i].minFontSize(minsize);
        }
        //alert(minsize);
    },

    sortAlign: function () {
        this.align = new MEE.Align();

        if (this.elements) {
            for (var i = 0; i < this.elements.length; i++) {
                var elalign = this.elements[i].sortAlign();
                this.align.Merge(elalign);
            }
        }

        //this.padToContent(this.elements);
        if (this.align.top)
            $(this.html_elem).css('margin-top', this.align.top +'px');
        if (this.align.bottom)
            $(this.html_elem).css('margin-bottom', this.align.bottom +'px');

        return this.align;
    },

});
