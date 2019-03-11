
$.Class.extend("MEE.Data",
{
    init: function () {

    },

    // build a table containing all the data required by each bracket (uses bracketheights and bracketwidths). 
    buildBracketSizes: function () {
        MEE.Data.bsizes = {};

        for (var i = 0; i < MEE.Data.bracketwidths.length; i++) {
            var data = MEE.Data.bracketwidths[i];
            for (var k = 0; k < data.brackets.length; k++) {
                var bracket = data.brackets[k];
                var bsize = {};
                for (var s = 0; s < 5; s++) {
                    var size = {
                      width : data[s],
                      height : MEE.Data.bracketheights[s]
                    }
                    bsize['size' + s] = size;
                }
                bsize.scalew = data.s;
                bsize.haslarge = data.haslarge;
                bsize.canscale = data.canscale;

                MEE.Data.bsizes[bracket] = bsize;
            }
        }
        var t = 0;
    },

    /*MEE.Data.namedops = [
    'sin',
    'cos'
    ];*/

    buildNamedOps: function () {
        MEE.Data.namedops = [];

        $.each(MEE.Data.commands, function (cmd, data) {
            if (data.cantype) {
                if (cmd.substr(0, 1) == "\\") {
                    MEE.Data.namedops.push(cmd.substr(1));
                }
            }
        });
    },

    // go through all the tex data, and copy any base attributes into the elements
    buildDefs: function () {
        $.each(MEE.Data.commands, function (cmd, data) {
            if (data.base) {
                // we have a base required to copy the data from
                var base = MEE.Data.commands[data.base];
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
    },
    // lookup a character size for building a extensible bracket
    getCharSize: function (ch, scope) {
        if (!ch in MEE.Data.charsizes) {
            var size = {
              top : 0,
              height : 0
            }
            return size;
        }
        var size = jQuery.extend({}, MEE.Data.charsizes[ch]);
        size.top = $(size.top).toPx({ 'scope': scope });
        size.height = $(size.height).toPx({ 'scope': scope });

        return size;
    },

    // get the sizing data for a large character such as sin
    getLargeCharData: function (ch, scope, size) {
        var array = 'largechars';
        if (size && size != 2)
            array = 'largechars_size' + size;

        if (!ch in MEE.Data[array]) {
            var size = {
              top : 0,
              bottom : 0,
              width : 0,
              offset : 0
            }
            return size;
        }

        //'&#x2211;': { top: 0.2, bottom: 0.2, height: 2, width: 1.5, offset: -0.29 },
        var size = jQuery.extend({}, MEE.Data[array][ch]);
        if (!size.top) size.top = 0;
        if (!size.bottom) size.bottom = 0;
        if (!size.width) size.width = 0;
        if (!size.offset) size.offset = 0;

        size.top = $(size.top).toPx({ 'scope': scope });
        size.bottom = $(size.bottom).toPx({ 'scope': scope });
        size.width = $(size.width).toPx({ 'scope': scope });
        size.offset = $(size.offset).toPx({ 'scope': scope });

        return size;
    },

    // get extensible bracket data
    getBracket: function (bracket) {
        var r = MEE.Data.extbrackets[bracket];
        if (!r)
            return MEE.Data.extbrackets['('];
        return r;
    },

    // work out the base height of a element based on its font size in px
    getBaseSize: function (elem) {
        if (!elem)
            return 0;
        if (elem.length == 0)
            return 0;

        var fontsize = $(elem).css('font-size');
        if (!fontsize)
            return 0;
        fontsize = fontsize.replace('px', '');
        fontsize = fontsize / 0.853;
        return Math.round(fontsize);
    }
},
{
});
