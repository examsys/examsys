// spacing element
MEE.Elem.extend("MEE.ElemSpace",
{
    sortAlign: function () {
        this.align = new MEE.Align();
        
        $(this.html_main).html("");
        $(this.html_main).css('padding-right',this.eldata.space);
        
        this.align.width = $(this.html_main).outerWidth(true);// 
        this.align.height = $(this.html_main).outerHeight(true);// 

        return this.align;        
    }

    
});
