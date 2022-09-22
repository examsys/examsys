// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.
//
// Question status js
//
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
  return function() {
    /**
     * Provide matching terms function.
     * @returns function
     */
    this.getCheckRow = function () {
      var collapse = $('#collapse').is(':checked');
      var regexpMod = ($('#casesensitive').is(':checked')) ? 'g' : 'gi';
      this.terms = $('#keywords').val();
      var scope = this;

      return function () {
        if (scope.terms != '') {
          var term = scope.terms.split(' and ');
          term = term.join('|');

          var regexp = new RegExp('(' + term + ')', regexpMod);
          var content = $(this).html();

          var matches = content.match(regexp);

          if (matches || !collapse) {
            if (!$(this).is(':visible')) {
              $(this).slideDown();
            }
          }

          if (matches) {
            $(this).html(content.replace(regexp, '<span class="highlight">$1</span>'));
          } else if (collapse) {
            $(this).slideUp();
          }

          if (matches) {
            scope.occurrance += matches.length;
            scope.commentMatches++;
          }
        } else {
          if (!$(this).is(':visible')) {
            $(this).slideDown();
          }
        }
      }
    };

    /**
     * Remove highlight.
     */
    this.cleanResponses = function () {
      $('li.response').each(function () {
        var content = $(this).html();
        var newcontent = content.replace(/<span class="highlight">([a-zA-Z]*)<\/span>/g, '$1');
        $(this).html(newcontent);
      });
    };

  }
});