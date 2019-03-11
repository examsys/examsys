(function( jQuery ) {

var getScript = jQuery.getScript;

jQuery.getScript = function( resources, callback ) {

var // reference declaration & localization
length = resources.length,
handler = function() { counter++; },
deferreds = [],
counter = 0,
idx = 0;

for ( ; idx < length; idx++ ) {
deferreds.push(
getScript( resources[ idx ], handler )
);
}

jQuery.when.apply( null, deferreds ).then(function() {
callback && callback();
});
};

})( jQuery );

var deps = [ "http://documentcloud.github.com/backbone/backbone-min.js",
             "http://documentcloud.github.com/underscore/underscore-min.js" ];


jQuery.getScript( deps, function( jqXhr ) {
console.log( jqXhr );
console.log( [ _, Backbone ] );
});