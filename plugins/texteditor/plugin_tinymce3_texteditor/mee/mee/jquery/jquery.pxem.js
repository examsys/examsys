/*-------------------------------------------------------------------- 
 * jQuery plugins: toEm() and toPx()
 * by Scott Jehl (scott@filamentgroup.com), http://www.filamentgroup.com
 * Copyright (c) Filament Group
 * Dual licensed under the MIT (filamentgroup.com/examples/mit-license.txt) and GPL (filamentgroup.com/examples/gpl-license.txt) licenses.
 * Article: http://www.filamentgroup.com/lab/update_jquery_plugin_for_retaining_scalable_interfaces_with_pixel_to_em_con/
 * Options:  	 								
 		scope: string or jQuery selector for font-size scoping		  
 * Usage Example: $(myPixelValue).toEm(); or $(myEmValue).toPx();
--------------------------------------------------------------------*/
var pxemScope = new Object();

$.fn.toEm = function (settings) {
    settings = jQuery.extend({
        scope: 'body'
    }, settings);
    if (this[0] == undefined)
        return 0;
    var that = parseInt(this[0], 10);
    var scopeVal = $(settings.scope).css('font-size');
    if (scopeVal) {
        scopeVal = scopeVal.replace('px', '');
        scopeVal = parseInt(scopeVal);
    } else {
        var scopeTest = jQuery('<div style="display: none; font-size: 1em; margin: 0; padding:0; height: auto; line-height: 1; border:0;">&nbsp;</div>').appendTo(settings.scope);
        scopeVal = scopeTest.height();
        scopeTest.remove();
    }

    return (that / scopeVal).toFixed(8);
};


$.fn.toPx = function (settings) {
    settings = jQuery.extend({
        scope: 'body'
    }, settings);
    if (this[0] == undefined)
        return 0;
    var that = parseFloat(this[0]);
    var scopeVal = $(settings.scope).css('font-size');
    if (scopeVal) {
        scopeVal = scopeVal.replace('px', '');
        scopeVal = parseInt(scopeVal);
    } else {
        var scopeTest = jQuery('<div style="display: none; font-size: 1em; margin: 0; padding:0; height: auto; line-height: 1; border:0;">&nbsp;</div>').appendTo(settings.scope);
        scopeVal = scopeTest.height();
        scopeTest.remove();
    }

    return Math.round(that * scopeVal);
};

function hasTall(text) {
    /*var tallchars = ['b', 'd', 'f', 'h', 'i', 'j', 'k', 'l', 't',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];*/
    var nontallchars = ['a','c','e','g','m','n','o','p','q','r','s','u','v','w','x','y','z'];

    for (var i = 0; i < text.length; i++) {
        if (nontallchars.indexOf(text.charAt(i)) == -1)
            return true;
    }

    return false;
}

function isArray() {
    if (typeof arguments[0] == 'object') {
        var criterion = arguments[0].constructor.toString().match(/array/i);
        return (criterion != null);
    } return false;
}