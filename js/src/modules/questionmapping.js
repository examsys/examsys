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
// Question mapped objective
//
// @author ?
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
  return function () {
    this.init = function() {
      /**
       * Initialise objective mapping screen.
       */
      $('.objectives li a').click(function () {
        $(this).nextAll('ul').slideToggle('fast');
        $(this).parent('li').toggleClass('open');
        return false;
      });

      this.openMappedTabs();
    };

    /**
     * Display mapped objectives.
     */
    this.openMappedTabs = function () {
      $('ul.objectives li.top').each(function () {
        var mappedObjs = $(this).find(':checkbox:checked').length;
        if (mappedObjs > 0) {
          $(this).addClass('open');
          $(this).children('ul').show();
        }
      });
    }
  }
});
