
MEE.Elem.extend("MEE.ElemAccent",
{
// static stuff

},
{
// prototype stuff

    sortAlign: function () {
        this.align = new MEE.Align();
        this.align.width = $(this.html_main).outerWidth(true);
        this.align.height = $(this.html_main).outerHeight(true);

        if (this.args.length < 1)
        {
            $(this.html_main).css('top','-0.3em');
            return this.align; 
        }

        if (this.eldata.accent_wide)
        {
            var textlen = this.args[0].latex.length;
            if (textlen == 1) {
                $(this.html_main).css('font-family','MathJax_Size1');
                $(this.html_main).css('top','0.1em');
            } else if (textlen == 2) {
                $(this.html_main).css('font-family','MathJax_Size2');
                $(this.html_main).css('top','-0.45em');
            } else if (textlen == 3) {
                $(this.html_main).css('font-family','MathJax_Size3');
                $(this.html_main).css('top','-0.55em');
            } else if (textlen > 3) {
                $(this.html_main).css('font-family','MathJax_Size4');
                $(this.html_main).css('top','-0.85em');
            }
        }

        var accentwidth = $(this.html_main).outerWidth(true);
        var textwidth = $(this.html_arg0).outerWidth(true);

        if (textwidth > accentwidth && !this.eldata.nopadleft)
        {
            var offset = Math.floor((textwidth - accentwidth) / 2);
            $(this.html_main).css('padding-left' , offset + 'px');
        }

        if (hasTall(this.args[0].latex))
        {
            $(this.html_main).css('bottom', '0.22em');
        }

        this.align.width = $(this.html_main).outerWidth(true);
        return this.align;
    },
});
