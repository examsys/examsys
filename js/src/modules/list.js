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
// List helper js
//
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
  return function() {
    /**
     * Default initialisation of a list.
     */
    this.init = function () {
      var scope = this;
      $('body').click(this.deselLine);

      $(".l").click(function(event) {
        event.stopPropagation();
        scope.selLine($(this).attr('id'),event);
      });
    };

    /**
     * Deselect a list item.
     */
    this.deselLine = function() {
      $('.highlight').removeClass('highlight');

      $('#lineID').val();
      $('#menu1b').hide();
      $('#menu1a').show();
    };

    /**
     * Select a list item.
     * @param integer lineID item id.
     * @param ojbect event event
     */
    this.selLine = function(lineID, event) {
      $('.highlight').removeClass('highlight');

      $('#menu1a').hide();
      $('#menu1b').show();
      $('#lineID').val(lineID);

      $('#' + lineID).addClass('highlight');
      event.cancelBubble = true;
    };

    /**
     * Open url related to list item.
     * @param string url a url
     * @param integer id list item id
     */
    this.edit = function(url, id) {
      document.location.href = url + id;
    };

    /**
     * Resize the list of items to fit the popup screen.
     * @param integer y amount in px to adjust height of list by
     */
    this.resizeList = function(y) {
      $('#list').css('height', y + 'px');
    };
  }
});
