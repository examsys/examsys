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
// @author ?
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jsxls', 'jquery', 'jqueryui'], function(jsxls, $) {
  return function() {

    /**
     * Handle reordering error.
     */
    this.showReorderError = function () {
      alert(jsxls.lang_string['reorderproblem']);
    }

    /**
     * Handle reorder success
     * @param string data ajax response
     */
    this.reorderSuccess = function (data) {
      if (data != 'OK') {
        this.showReorderError();
      }
      this.deselLine();
    };

    /**
     * Select a question status
     * @param ojbect e event
     */
    this.selLine = function (e) {
      e.stopPropagation();

      $('.highlight').removeClass('highlight');

      var id = $(this).data('id');

      $('.reactive').addClass('menuitem')
          .removeClass('greymenuitem');

      $('.reactive').children('a')
          .unbind("click")
          .click(function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            url += '?id=' + id;
            if ($(this).hasClass('launchwin')) {
              var notice = window.open(url, "deleteitem", "width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
              notice.moveTo(screen.width / 2 - 210, screen.height / 2 - 85);
              if (window.focus) {
                notice.focus();
              }
            } else {
              window.location.href = url;
            }
          });

      $(this).addClass('highlight');

      $('#menu1a').hide();
      $('#menu1b').show();

    };

    /**
     * Deselect a question status.
     */
    this.deselLine = function () {
      $('.highlight').removeClass('highlight');
    };
  }
});