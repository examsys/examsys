// Define a jQuery global function for extracting values form a query string
// It does not implement method chaining for jQuery
//
// Usage:
//  Get a named value from the query string:
//    var x = $.rQuerySstring.getValue('myvalue');
//
//  Get all values from the query string as a JS object:
//    var xobj = $.rQuerySstring.extract('myvalue');
//
//  Both methods can optionally accept the query string as a string, excluding the question mark, e.g.:
//    var x = $.rQuerySstring.getValue('val2', 'val1=345&val2=hello');
//


jQuery.rQuerySstring = {
  getValue: function(search, querystring) {
    var result = '';

    if (typeof querystring == 'undefined') {
      querystring = '&' + window.location.search.substring(1);
    }
    querystring = '&' + querystring;

    var re = new RegExp('&' + search + '=');
    if (querystring.length > 0 && querystring.match(re) != null) {
      var parts = querystring.split(search);
      var parts2 = parts[1].substring(1).split('&');
      result = parts2[0];
    }

    return result;
  },

  setValue: function (key, value, querystring) {
    if (typeof querystring == 'undefined') {
      querystring = window.location.search.substring(1);
    }
    querystring = '&' + querystring + '&';

    var matchString = '&' + key + '=';
    var matchRE = new RegExp(matchString);
    if (querystring.length > 0 && querystring.match(matchRE) != null) {
      var replRE = new RegExp(matchString + '.*?&');
      querystring = querystring.replace(replRE, matchString + value + '&');
    } else {
      if (querystring.length == 2) querystring = '&';
      querystring += key + '=' + value + '&';
    }

    return querystring.substring(1, querystring.length - 1);
  },

  extract: function(querystring) {
    var result = {};

    if (typeof querystring == 'undefined') {
      querystring = window.location.search.substring(1);
    }

    if (querystring.length > 0) {
      var parts = querystring.split('&');
      var keyval;
      for (var i = 0; i < parts.length; i++) {
        keyval = parts[i].split('=');
        result[keyval[0]] = keyval[1];
      }
    }

    return result;
  }
}

