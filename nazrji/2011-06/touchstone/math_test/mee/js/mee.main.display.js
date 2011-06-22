
MEE.Main.extend("MEE.Display",
{
    inline: false,
    elementset: null, // set of elements

    // class extensions for using as an editor
    init: function (element, inline) {
        //alert("Init MEE.Display " + inline);
        this.inline = inline;
        //$(element).click(this.callback('doclick'));

        var showcomp = 1;
        if ($(element).hasClass('nocomp'))
            showcomp = 0;
        if ($(element).hasClass('comp'))
            showcomp = 1;

        var latex = element.innerHTML;
        if (latex.substr(0, 2) == "\\[") {
            latex = latex.substr(2, latex.length - 4);
        }

        this.elementset = new MEE.ElemSetNormal(latex);

        var res = this.elementset.toHTML();

        if (showcomp) {
            //$(element).html("<div style='color:blue;'>" + latex + "</div><br /><br />");
            $(element).html("");
            var table = $('<table>');
            var tr = $('<tr>');
            var td1 = $('<td>');
            td1.attr("width", "50%");
            var td2 = $('<td>');
            td2.attr("width", "50%");
            td2.css('border-left', '1px solid blue');
            td2.css('padding-left', '4px');
            td2.css('vertical-align', 'top');
            var span = $('<div>');
            /*span.css('border', '1px solid blue');*/

            table.append(tr);
            $(table).attr('width', '100%');
            $(table).css('font-size', '200%');
            tr.append(td1);
            tr.append(td2);

            var img = $('<img>');
            img.attr('src', 'http://latex.codecogs.com/gif.latex?\\LARGE ' + latex);
            $(td2).append(img);

            var eqn = $('<div>');
            eqn.css('font-size','14px');
            eqn.text(latex);

            $(element).append(table);
            $(span).append(res);
            $(td1).append(span);
            $(td1).append(eqn);
eqn.css('padding-top', '20px');
eqn.css('padding-bottom', '15px');

            var fontsize = $(res).css('font-size').replace("px", "");
            this.elementset.minFontSize(fontsize * 0.55);

            this.elementset.sortAlign();

            var height = $(res).outerHeight();
            $(span).css('height',height + 'px');

            var padtop = $(res).css('margin-top').replace('px','');
            $(span).css('padding-top', padtop + 'px');

            var padbottom = $(res).css('margin-bottom').replace('px','');
            $(span).css('padding-bottom', padbottom + 'px');

        } else {

            $(element).html("");
            //$(element).css('font-size', '200%');
            $(element).append(res);
            var fontsize = $(res).css('font-size').replace("px", "");
            this.elementset.minFontSize(fontsize * 0.55);

            this.elementset.sortAlign();

            var height = $(res).outerHeight();
            $(element).css('height',height + 'px');

            var padtop = $(res).css('margin-top').replace('px','');
            $(element).css('padding-top', padtop + 'px');

            var padbottom = $(res).css('margin-bottom').replace('px','');
            $(element).css('padding-bottom', padbottom + 'px');

            /*$(element).css('border', '1px solid green');*/

        }

       
        // for all elements, add a border that contains it
        //Highlight();

        /*this.elementset.sortWidth();
        $(this.elementset.html_main).css('padding-top', vert.top + 'px');
        $(this.elementset.html_main).css('padding-bottom', vert.bottom + 'px');*/
    },

    /*doclick: function (element, event) {
    alert(element + event);
    }*/

    
});

function Highlight()
{
    $('.highlight').remove();

    $('.mee_elem').each(function () {
        var pos = new MEE.Position(this);
        var div = $('<div>');
        div.addClass('highlight');
        div.css('position', 'absolute');
        div.css('left', pos.left + 'px');
        div.css('top', pos.top + 'px');
        div.css('width', pos.width + 'px');
        div.css('height', pos.height + 'px');
        div.css('border', '1px solid red');
        $(document.body).append(div);
    });

    $('.mee_elemset').each(function () {
        var pos = new MEE.Position(this);
        var div = $('<div>');
        div.addClass('highlight');
        div.css('position', 'absolute');
        div.css('left', pos.left-1 + 'px');
        div.css('top', pos.top-1 + 'px');
        div.css('width', pos.width+2 + 'px');
        div.css('height', pos.height+2 + 'px');
        div.css('border', '1px solid blue');
        $(document.body).append(div);
    });

    /*$('.mee_elemsetarray').each(function () {
        var pos = new MEE.Position(this);
        var div = $('<div>');
        div.addClass('highlight');
        div.css('position', 'absolute');
        div.css('left', pos.left-1 + 'px');
        div.css('top', pos.top-1 + 'px');
        div.css('width', pos.width+2 + 'px');
        div.css('height', pos.height+2 + 'px');
        div.css('border', '1px solid yellow');
        $(document.body).append(div);
    });*/

}

function HighlightArray()
{
    $('.mee_elemsetarray').each(function () {
        var pos = new MEE.Position(this);
        var div = $('<div>');
        div.addClass('highlight');
        div.css('position', 'absolute');
        div.css('left', pos.left + 'px');
        div.css('top', pos.top + 'px');
        div.css('width', pos.width + 'px');
        div.css('height', pos.height + 'px');
        div.css('border', '1px solid orange');
        $(document.body).append(div);
    });

}

function HighlightRows()
{
    $('.mee_row').each(function () {
        var pos = new MEE.Position(this);
        var div = $('<div>');
        div.addClass('highlight');
        div.css('position', 'absolute');
        div.css('left', pos.left + 'px');
        div.css('top', pos.top + 'px');
        div.css('width', pos.width + 'px');
        div.css('height', pos.height + 'px');
        div.css('border', '1px solid red');
        $(document.body).append(div);
    });

}

function HighlightCols()
{

    $('.mee_col').each(function () {
        var pos = new MEE.Position(this);
        var div = $('<div>');
        div.addClass('highlight');
        div.css('position', 'absolute');
        div.css('left', pos.left-1 + 'px');
        div.css('top', pos.top-1 + 'px');
        div.css('width', pos.width+2 + 'px');
        div.css('height', pos.height+2 + 'px');
        div.css('border', '1px solid blue');
        $(document.body).append(div);
    });

}

function HighlightClear()
{
    $('.highlight').remove();
}