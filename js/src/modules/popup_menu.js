// JavaScript Document
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
// Popup helper functions
//
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
  return function() {
    this.overpopupmenu = false;
    this.isMenu = false;

    /**
     * Initialise popup menu overlay.
     */
    this.init = function() {
      var scope = this;
      $('#menudiv').mouseover(function() {
        scope.overpopupmenu=true;
      });

      $('#menudiv').mouseout(function() {
        scope.overpopupmenu=false;
      });

      $("tr[id^=res]").each(function(){
        $(this).click(function(e) {
          scope.popMenu(e);
        });
      });

      // hide when click on anything else.
      $('.head_title, .subheading, .graph').click(function() {
        $('#menudiv').hide();
      });

    };

    /**
     * Display the popup menu overlay.
     * @param object e event
     * @returns bool
     */
    this.popMenu = function(e) {
      if (!e) {
          e = window.event;
      }
      var scrOfX = $(document).scrollLeft();
      var scrOfY = $(document).scrollTop();

      $('#menudiv').show();

      var top_pos = e.clientY + scrOfY;
      var div_height = $('#menudiv').height() + 6;
      if (top_pos > ($(window).height() + scrOfY - div_height)) {
        top_pos = $(window).height() + scrOfY - div_height;
      }
      $('#menudiv').css('left', e.clientX + scrOfX);
      $('#menudiv').css('top', top_pos);

      this.isMenu = true;
      return false;
    };

    /**
     * Mouse select action for popup overlay.
     * @returns bool
     */
    this.mouseSelect = function() {
      if (this.isMenu) {
        if (this.overpopupmenu == false) {
          this.isMenu = false ;
          this.overpopupmenu = false;
          $('#menudiv').hide();
          return true ;
        }
        return true ;
      }
      return false;
    };
  }
});
