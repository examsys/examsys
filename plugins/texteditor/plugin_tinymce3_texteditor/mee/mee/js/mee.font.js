$.Class.extend("MEE.Font",
{
    fontsLoaded: Array(),
    inited: false,

    init: function () {
        $().ready(function () {
            MEE.Font.fontdiv = $('<div>');
            MEE.Font.fontdiv.html("YAY!");
            $('.testme').append(MEE.Font.fontdiv);

            var base = $('<span>');
            MEE.Font.fontdiv.append(base);
            base.html("ml&#x02DC;&#x2211;");
            base.css('font-size', '72px');
            MEE.Font.base_width = base.outerWidth(true);
            MEE.Font.base_height = base.outerHeight(true);
            base.remove();
        });
    },

    isLoaded: function (font) {
        if (font in this.fontsLoaded)
            return true;

        if (this.checkFontLoaded(font)) {
            this.fontsLoaded[font] = 1;
            return true;
        }

        return false;
    },

    checkFontLoaded: function (font) {
        var base = $('<span>');
        this.fontdiv.append(base);
        base.css('font-family', font);
        base.html("ml&#x02DC;&#x2211;");
        base.css('font-size', '72px');
        var width = base.outerWidth(true);
        var height = base.outerHeight(true);
        base.remove();

        if (width == this.base_width && height == this.base_height) {
            return false;
        }
        this.fontsLoaded[font] = 1;
        return true;
        
    }
},
{

});