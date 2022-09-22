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
// Calculation question edit functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'jquery'], function(config, $) {
    return function () {
        /**
         * Generate variable link button for calculation question.
         */
        this.addVariableLinks = function () {
            var scope = this;
            $('.variable-link').each(function () {
                if ($(this).attr('rel') != undefined) {
                    var target = $(this).attr('rel');
                    var icon = $(this).children(':first-child').attr('id');
                    if (!$(this).hasClass('disabled')) {
                        $(this).bind('click', {elementID: target, iconID: icon}, function (e) {
                            scope.variableLink(e)
                        });
                    } else {
                        $(this).bind('click', function (e) {
                            e.preventDefault();
                        });
                    }
                }
            });
        };

        /**
         * Open variable linking window.
         * @param object event event
         * @returns bool
         */
        this.variableLink = function (event) {
            var questionID = $('#question_id').val();
            var paperID = $('#paperID').val();
            var winHeight = screen.height - 100;
            window.open(config.cfgrootpath + "/plugins/questions/enhancedcalc/variable_link.php?paperID=" + paperID + "&elementID=" + event.data.elementID + "&q_id=" + questionID + "&iconID=" + event.data.iconID + "", "paper", "width=750,height=" + winHeight + ",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            return false;
        };

        /**
         * Copy value to variable.
         */
        this.copyValue = function() {
            var selectedRef = $('input[type=radio][name=ref]:checked').val();
            window.opener.document.getElementById($('#dataset').attr('data-elementid')).value = selectedRef;
            window.opener.document.getElementById($('#dataset').attr('data-iconid')).src = config.cfgrootpath +'/artwork/variable_link_on.png';
            window.close();
        };
    }
});