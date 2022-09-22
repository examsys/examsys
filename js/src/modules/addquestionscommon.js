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
// Common add question helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define(['jquery'], function ($) {
    return {
        /**
         * Open question preview screen
         * @param integer q_id the question id
         */
        previewq: function (q_id) {
            window.parent.$("#previewurl").attr("src", '../view_question.php?q_id=' + q_id);
        },
        /**
         * Update the list of questions that have been selected to add to the paper.
         * @param string item selector for new question to add
         * @param array selected_q array of selected questions
         */
        updatearray: function (item, selected_q) {
            var q_id = $(item).val();
            var scope = this;
            // Question in array but user has unchecked.
            if ($.inArray(q_id, selected_q) > -1 && $(item).is(":checked") == false) {
                selected_q = scope.popitem(q_id, selected_q);
                // User has checked question but it is not in the array.
            } else if ($.inArray(q_id, selected_q) == -1 && $(item).is(":checked") == true) {
                selected_q.push($(item).attr('name'));
            }
            window.parent.$("#questions_to_add").val(selected_q.toString());
        },
        /**
         * Remove item from question list
         * @param integer needle id of questiondateFormat
         * @param array haystack list of questions
         * @returns array
         */
        popitem: function (needle, haystack) {
            var new_haystack = Array();
            for (var i = 0; i < haystack.length; i++) {
                if (haystack[i] != needle) {
                    new_haystack[new_haystack.length] = haystack[i];
                }
            }
            return new_haystack;
        },
        /**
         * Check the checkboxes of those questions in the question array.
         */
        populateTicks: function () {
            if (window.parent.$("#questions_to_add").val() != undefined) {
                var q_array = window.parent.$("#questions_to_add").val().split(',');
                for (var i = 0; i < q_array.length; i++) {
                    if (q_array[i] != '') {
                        $("#q" + q_array[i]).prop("checked", true);
                    }
                }
            }
        },
    }
});