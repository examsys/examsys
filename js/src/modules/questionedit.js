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
// Paper question edit functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jsxls', 'jquery'], function(jsxls, $) {
  return function () {
    /**
     * Change tab.
     * @param object el element
     * @returns bool
     */
    this.changeTab = function (el) {
      if (!$(el).parent().hasClass('disabled')) {
        if (!$(el).parent().hasClass('on')) {
          $('.tab-area').hide();
          $('.tabs li').each(function () {
            $(this).removeClass('on');
          });
          $(el).parent().addClass('on');

          var id = $(el).attr('rel');
          $('#' + id).fadeIn();
        }
      }

      return false;
    };

    /**
     * Display new option.
     * @param object el element
     * @param object e event
     */
    this.showNextOption = function (el, e) {
      e.preventDefault();

      var elClass = 'option';

      if (typeof $(el).data('target') != 'undefined') {
        elClass = $(el).data('target');
      }

      var hiddenOptions = $('.' + elClass + '.hide');
      if (hiddenOptions.length > 0) {
        if (hiddenOptions.length == 1) {
          $(el).parents('.add-option-holder').eq(0).fadeOut('fast');
        }
        hiddenOptions.eq(0).removeClass('hide');
      }
    };

    /**
     * Display partial marks drop down dependent on score method selected.
     */
    this.showPartialMarks = function () {
      if ($('#score_method :selected').text() == jsxls.lang_string['allowpartial']) {
        $('.marks-partial').fadeIn('fast');
      } else {
        $('.marks-partial').fadeOut('fast');
      }
    };

    /**
     * Display full / Trim change display.
     */
    this.trimLongChanges = function () {
      $('a.more').click(function () {
        $(this).prev().prev().prev().toggle();
        $(this).prev().prev().slideToggle();
        if ($(this).text() == jsxls.lang_string['showmore']) {
          $(this).text(jsxls.lang_string['hidemore'])
        } else {
          $(this).text(jsxls.lang_string['showmore'])
        }
      });
    };

    /**
     * Warn user is they have added mappings but are not adding hte question to the question bank.
     * @returns bool
     */
    this.checkMapping = function () {
      var checked = $('.objectives input:checked');
      if (checked.length > 0) {
        return confirm(jsxls.lang_string['mappingwarning']);
      }

      return true;
    }

    /**
     * Display warning to user chanign mark allocations post exam.
     * @param ojbect element element
     * @param integer prev_val previous value
     * @returns bool
     */
    this.showMarksWarning = function (element, prev_val) {
      var rval = false;
      if ($('#savebutton').attr('data-postExamWarningShown') == 0) {
        rval = confirm(jsxls.lang_string['markchangewarning']);
        if (!rval) {
          element.val(prev_val);
        } else {
          $('#savebutton').attr('data-postExamWarningShown', 1);
        }
      }
      return rval;
    };
  }
});
