
// create MEE class
$.Class.extend("MEE.Base",
{
    createElemSet: function (token) {
        if (token.type == "") {
            return new MEE.ElemSetBasic(token.latex);
        }
        var elemset = new MEE.ElemSetNormal(token.latex);
        return elemset;
    }
},
{
    init: function () {
        // build all recursive definitions
        this.buildDefs();
        // find all div, and span elements and create a MEE.Main.Display for them
        $("div.mee").each(function () {
            var meemain = new MEE.Display(this, false);
        });
        $("span.mee").each(function () {
            var meemain = new MEE.Display(this, true);
        });
        $("input.mee").each(function () {
            var meemain = new MEE.Edit(this);
        });
        //alert(MEE.Main.fullName);
    },

    buildDefs: function () {
        $.each(MEE.Parser.commands, function (cmd, data) {
            if (data.base) {
                // we have a base required to copy the data from
                var base = MEE.Parser.commands[data.base];
                if (!base)
                    return;

                $.each(base, function (basecmd, baseval) {
                    if (basecmd in data) {
                        var k = 0;
                    } else {
                        data[basecmd] = baseval;
                    }
                });
            }
        });

        var k = 0;
    }
});

$.Class.extend("MEE.Align",
{
    width: 0,
    height: 0,
    top: 0,
    bottom: 0,
    init: function() {
        this.width = 0;
        this.height = 0;
        this.top = 0;
        this.bottom = 0;
    },
    Merge: function (align) {
        this.width += align.width;
        if (this.height == 0) {
            this.height = align.height;
        } else {
            if (align.top > this.top)
                this.height += align.top - this.top;
            if (align.bottom > this.bottom)
                this.height += align.bottom - this.bottom;
        }
        this.top = Math.max(this.top, align.top);
        this.bottom = Math.max(this.bottom, align.bottom);
    }
});


$.Class.extend("MEE.Position",
{
    left: 0,
    top: 0,
    bottom: 0,
    right: 0,
    width: 0,
    height: 0,

    init: function (elem) {
        if (elem)
            this.fromElem(elem);
    },

    fromElem: function (elem) {
        if (elem)
        {
            var offset = $(elem).offset();
            this.left = offset.left;
            this.top = offset.top;
            this.width = $(elem).outerWidth(true);
            this.height = $(elem).outerHeight(true);
            var marginleft = parseInt($(elem).css('margin-left'));
            if (marginleft)
                this.left -= marginleft;
            var margintop = parseInt($(elem).css('margin-top'));
            if (margintop)
                this.top -= margintop;

            this.right = this.left + this.width;
            this.bottom = this.top + this.height;
        }
    },

    growToInclude: function (elem) {
        if (!elem)  return;

        var elempos = new MEE.Position(elem);
        if (elempos.left < this.left)
            this.left = elempos.left;

        if (elempos.right > this.right)
            this.right = elempos.right;

        if (elempos.top < this.top)
            this.top = elempos.top;

        if (elempos.bottom > this.bottom)
            this.bottom = elempos.bottom;

        this.width = this.right - this.left;
        this.height = this.bottom - this.top;
    },
});