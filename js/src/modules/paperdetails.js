// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Paper details js
//
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['sidebar', 'jsxls', 'jquery', 'jqueryui'], function(SIDEBAR, jsxls, $) {
  return function() {

    /**
     * Dynamically set list item order attribute.
     */
    this.resetLinks = function() {
      var breaks = 0;
      $('.qline').each(function (index) {
        if ($(this).hasClass('breakline')) {
          breaks++;
          $(this).attr('data-order', 'link_break' + breaks);
        } else {
          $(this).attr('data-order', 'link_' + (index - breaks + 1));
        }
      });
    };

    /**
     * Enable the delete screen break button.
     * @param object element element
     * @param integer sid screen id
     */
    this.activateDelete = function(element, sid) {
      element.removeClass('greymenuitem');
      element.addClass('menuitem');
      element.click(this.deleteScreenBreak);
      element.data('screenID', sid);
      element.children('a').css('cursor', 'pointer');
    };

    /**
     * Disable the delete screen break button.
     * @param object element element
     */
    this.deActivateDelete = function(element) {
      $('.breakline').removeClass('line-selected');
      element.addClass('greymenuitem');
      element.removeClass('menuitem');
      element.children('a').css('cursor', 'text');
    };

    /**
     * Enable the add screen break button.
     * @param object element element
     */
    this.activateAddBreak = function(element) {
      element.removeClass('greymenuitem');
      element.addClass('menuitem');
      element.children('a').css('cursor', 'pointer');
    };

    /**
     * Disable the add screen break button.
     * @param object element element
     */
    this.deActivateAddBreak = function(element) {
      element.addClass('greymenuitem');
      element.removeClass('menuitem');
      element.children('a').css('cursor', 'text');
    };

    /**
     * Delete the screen break.
     */
    this.deleteScreenBreak = function() {
      var paperid = $('#dataset').attr('data-paperid');
      var screenNo = $(this).data('screenID').substring(10);
      $.get('../ajax/paper/delete-screen-break.php?paperID=' + paperid + '&screen=' + screenNo, function (data) {
        if (data == 'SUCCESS') {
          window.location.reload();
        } else {
          alert(jsxls.lang_string['invalidscreenbreak']);
        }
      });
    };

    /**
     * Handle ajax error.
     */
    this.showAJAXError = function() {
      alert(jsxls.lang_string['ajaxerror']);
    };

    /**
     * Add selected question to the active question list
     * @param integer qID question id
     * @param integer pID papers table id
     * @param bool clearall true if only single question selected
     */
    this.addQID = function(qID, pID, clearall) {
      if (clearall) {
        $('#questionID').val(',' + qID);
        $('#pID').val(',' + pID);
      } else {
        $('#questionID').val($('#questionID').val() + ',' + qID);
        $('#pID').val($('#pID').val() + ',' + pID);
      }
    };

    /**
     * Remove selected question to the active question list
     * @param integer qID question id
     * @param integer pID papers table id
     */
    this.subQID = function(qID, pID) {
      var tmpq = ',' + qID;
      var tmpp = ',' + pID;
      $('#questionID').val($('#questionID').val().replace(tmpq, ''));
      $('#pID').val($('#pID').val().replace(tmpp, ''));
    };

    /**
     * Remove question highlight from all questions.
     */
    this.clearAll = function() {
      $('.highlight').removeClass('highlight');
    };

    /**
     * Select a question to perform actions on.
     * @param integer questionNo the question number on the paper
     * @param integer questionID the question id
     * @param integer lineID the item id
     * @param string qType the question type
     * @param integer screenNo the screen number
     * @param integer pID the papers table id
     * @param integer current_pos the current question position on screen
     * @param integer menuID menu identifier
     * @param array subparts question parts i.e. questions for random block
     * @param object evt event
     * @param bool osce true if osce paper
     */
    this.selQ = function(questionNo, questionID, lineID, qType, screenNo, pID, current_pos, menuID, subparts, evt, osce) {
      $('#menu2a').hide();
      if (menuID == '2b') {
        $('#menu2c').hide();
      } else {
        $('#menu2b').hide();
      }
      $('#menu' + menuID).show();

      $('#questionNo').val(questionNo);
      $('#qType').val(qType);
      $('#screenNo').val(screenNo);
      $('#current_pos').val(current_pos);

      if (evt.ctrlKey == false && evt.metaKey == false) {
        this.clearAll();
        $('#link_' + lineID).addClass('highlight');
        this.addQID(questionID, pID, true);
      } else {
        if ($('#link_' + lineID).hasClass('highlight')) {
          $('#link_' + lineID).removeClass('highlight');
          this.subQID(questionID, pID);
        } else {
          $('#link_' + lineID).addClass('highlight');
          this.addQID(questionID, pID, false);
        }
      }

      if (qType == 'info') {
        $('.clarification').removeClass('menuitem');
        $('.clarification').addClass('greymenuitem');
      } else {
        $('.clarification').removeClass('greymenuitem');
        $('.clarification').addClass('menuitem');
      }

      if (qType == 'random') {
        var row = '';
        for (var i = 1; i <= subparts; i++) {
          row = document.getElementById('r' + lineID + '_' + i);
          if (row.style.display == 'none') {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        }
      }

      var sidebar = new SIDEBAR();
      sidebar.hideMenus();

      $('#stats_menu').hide();
      $('#copy_submenu').hide();
      $('#copy_from_submenu').hide();

      if (evt != null) {
        evt.cancelBubble = true;
      }

      var deleteLink = $('#delete_break');
      this.deActivateDelete(deleteLink);
      var addLink = $('#add_break');
      this.activateAddBreak(addLink);

      if (osce) {
        if ( $("#icon_" + questionNo).hasClass("info_class") ) {
          $("span.killer").addClass('greymenuitem');
        } else if ( $("#icon_" + questionNo).hasClass("killer_icon") ) {
          $("span.killer").html(jsxls.lang_string['unsetkillerquestion']);
          $("span.killer").removeClass('greymenuitem');
        } else {
          $("span.killer").html(jsxls.lang_string['setkillerquestion']);
          $("span.killer").removeClass('greymenuitem');
        }
      }

      if ($('#questionID').val() == '') {
        this.qOff();
      }
    };

    /**
     * Open edit question screen
     * @param integer questionNo question number in paper
     * @param integer questionID question id
     * @param string qType type of question i.e. mcq
     */
    this.edQ = function(questionNo, questionID, qType) {
      var paperid = $('#dataset').attr('data-paperid');
      var loc = "../question/edit/index.php?q_id=" + questionID + "&qNo=" + questionNo + "&paperID=" + paperid + "&calling=paper&scrOfY=" + $('#scrOfY').val();
      if (qType == 'random' || qType == 'keyword_based') {
        loc += '&type=' + qType;
      }
      document.location = loc;
    };

    /**
     * Disable page elements when no question selected.
     */
    this.qOff = function() {
      $('#menu2a').show();
      $('#menu2b').hide();
      $('#menu2c').hide();
      this.clearAll();

      $('#stats_menu').hide();
      $('#copy_submenu').hide();
      $('#copy_from_submenu').hide();

      var sidebar = new SIDEBAR();
      sidebar.hideMenus();

      var addLink = $('#add_break');
      this.deActivateAddBreak(addLink);
    };

    /**
     * Scroll screen to Y position
     * @param integer scrOfY y position in px
     */
    this.scrollto = function(scrOfY) {
      window.scrollTo(0, scrOfY);
    };
  }
});
