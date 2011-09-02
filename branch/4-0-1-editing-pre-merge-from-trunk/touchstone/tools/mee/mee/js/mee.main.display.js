MEE.Main.extend("MEE.Display",
{
    id: 0
},
{
    inline: false,
    elementset: null,

    // class extensions for using as an editor
    init: function (element, inline, mcedoc) {
        if (mcedoc)
            return this.TinyMCEinit(element, inline, mcedoc);

        this.element = element;

        this.inline = inline;
        this.fonts = new Object();
        this.baseid = $(element).attr('id');
        if (typeof this.baseid == "undefined")
            this.baseid = "";

        var showcomp = 1;
        var large = "\\LARGE ";
        var border = 0;
        var showlatex = 0;

        if ($(element).hasClass('nocomp')) showcomp = 0;
        if ($(element).hasClass('comp')) showcomp = 1;

        var latex = element.innerHTML;
        if (latex.substr(0, 2) == "\\[") {
            latex = latex.substr(2, latex.length - 4);
        }

        if (latex.charAt(0) == "<")
            return;

        this.elementset = new MEE.ElemSetNormal(latex, null);
        $(element).attr('title', latex);
        var depth = 1;
        if (inline) depth = 2;
        var res = this.elementset.toHTML(depth);

        //$(element).css('color', 'red');

        if (inline) {
            $(element).html("");
            $(element).append(res);

            //setTimeout("this.Align()",1000);
            /*this.elementset.sortAlign();
            $(element).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(element).css('margin-top', this.elementset.align.top + 'px');
                $(element).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(element).css('padding-top', this.elementset.align.top + 'px');
                $(element).css('padding-bottom', this.elementset.align.bottom + 'px');
            }*/


        } else if (showcomp) {

            // show side by side comparison with alernate render
            $(element).html("");
            var table = $('<table>');
            table.css('border-top', '1px solid blue');
            var tr = $('<tr>');
            var td1 = $('<td>');
            td1.attr("width", "50%");
            var td2 = $('<td>');
            td2.attr("width", "50%");
            td2.css('border-left', '1px solid blue');
            td2.css('padding-left', '4px');
            td2.css('vertical-align', 'top');
            var span = $('<div>');
            if (border)
                span.css('border', '1px solid blue');

            table.append(tr);
            $(table).attr('width', '100%');
            if (large)
                $(table).css('font-size', '200%');
            tr.append(td1);
            tr.append(td2);

            var img = $('<img>');
            var lt2 = MEE.Tools.HTML.html_entity_decode(latex);
            lt2 = MEE.Tools.HTML.replaceAll(lt2,"&lt;","<");
            lt2 = MEE.Tools.HTML.replaceAll(lt2,"&gt;",">");
            img.attr('src', 'http://latex.codecogs.com/gif.latex?' + large + lt2);
            $(td2).append(img);

            
            $(element).append(table);
            $(span).append(res);
            $(td1).append(span);

            if (showlatex) {
                var eqn = $('<div>');
                eqn.css('font-size', '14px');
                eqn.text(latex);
                $(td1).append(eqn);
            }

            this.element = span;
            //setTimeout("this.Align()",1000);
            /*this.elementset.sortAlign();
            //$(span).css('height',this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(span).css('margin-top', this.elementset.align.top + 'px');
                $(span).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(span).css('padding-top', this.elementset.align.top + 'px');
                $(span).css('padding-bottom', this.elementset.align.bottom + 'px');
            }*/

        } else {

            $(element).html("");
            $(element).append(res);

            
            /*this.elementset.sortAlign();

            //$(element).css('height',this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(element).css('margin-top', this.elementset.align.top + 'px');
                $(element).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(element).css('padding-top', this.elementset.align.top + 'px');
                $(element).css('padding-bottom', this.elementset.align.bottom + 'px');
            }*/
        }

        this.ListFonts(res);
        //debug.log(this.fonts);
    },

    Align: function () {
        if (!this.elementset)
            return;
        this.elementset.sortAlign();
        $(this.element).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
        if ($.browser.msie && document.documentMode == 7) {
            $(this.element).css('margin-top', this.elementset.align.top + 'px');
            $(this.element).css('margin-bottom', this.elementset.align.bottom + 'px');
        } else {
            $(this.element).css('padding-top', this.elementset.align.top + 'px');
            $(this.element).css('padding-bottom', this.elementset.align.bottom + 'px');
        }

        // apply id to any input boxed
        var baseid = this.baseid;

        $(this.elementset.html_elem).find('.mee_elem_answer_input').each(function (no) {
            $(this).attr('name', baseid + no);
        });
    },

    ListFonts: function (node) {
        var font = $(node).css('font-family');
        this.fonts[font] = 0;
        $(node).children().each(this.callback('ListFonts'));
    },

    FontsLoaded: function () {
        for (font in this.fonts)
        {
            if (!MEE.Font.isLoaded(font))
                return false;
        }
        return true;
    },
    // tiny mce handles the display a little differently, places the eqns above the page in a floating div so tiny mce 
    // cant select the text
    TinyMCEinit: function (element, inline, mcedoc) {
        this.inline = inline;

        var latex = element.innerHTML;
        if (latex.substr(0, 2) == "\\[") {
            latex = latex.substr(2, latex.length - 4);
        }

        if (latex.charAt(0) == "<")
            return;

        this.elementset = new MEE.ElemSetNormal(latex, null);

        //$(element).attr('latex', latex);

        $(element).attr('title', latex);
        var depth = 1;
        if (inline) depth = 2;
        var res = this.elementset.toHTML(depth);

        var cont = $('<div>');
        $(mcedoc.body).append(cont);
        cont.append(res);
        if (inline) {

            $(element).html("");
            //$(element).append(res);
            this.elementset.sortAlign();
            $(element).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(element).css('margin-top', this.elementset.align.top + 'px');
                $(element).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(element).css('padding-top', this.elementset.align.top + 'px');
                $(element).css('padding-bottom', this.elementset.align.bottom + 'px');
            }

            //$(this.tinymce).append(res);
            //$(res).remove();
            $(element).css('height', this.elementset.align.height + 'px');
            //$(element).css('width', '100%');
            //$(element).css('border', '1px solid green');
            $(element).html(MEE.Data.blankspace);

        } else {

            $(element).html("");
            //$(element).append(res);
            this.elementset.sortAlign();

            if ($.browser.msie && document.documentMode == 7) {
                $(element).css('margin-top', this.elementset.align.top + 'px');
                $(element).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(element).css('padding-top', this.elementset.align.top + 'px');
                $(element).css('padding-bottom', this.elementset.align.bottom + 'px');
            }
            //$(res).remove();
            $(element).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            $(element).css('width', '100%');
            $(element).html(MEE.Data.blankspace);
            //$(element).css('width', this.elementset.align.width + 'px');
            //$(element).css('border', '1px solid green');
        }
        
        cont.css('position', 'absolute');
        cont.css('z-index', -100);
        cont.addClass('mee_tinymce_cont');

        var id = MEE.Display.id++;

        $(element).attr('id', 'mee_elem_' + id);
        $(cont).attr('id', 'mee_cont_' + id);
        
        /*var pos = $(element).offset();
        if (inline) {
            cont.css('left', pos.left + 'px');
            $(element).css('padding-right', $(cont).outerWidth() + 'px');

        } else {
            var contwidth = $(element).outerWidth();
            cont.css('left', Math.floor((contwidth - this.elementset.align.width) / 2) + 'px')
        }
        cont.css('top', pos.top + this.elementset.align.top + 'px');*/
    }
});
