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
// Question search
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['list', 'jquery', 'jqueryui', 'jquerytablesorter'], function(LIST, $) {
    return function () {
        /**
         * Action to take when question type selected/deselected.
         */
        this.check_checkboxes = function() {
            $(".q").hide();
            $('input[type=checkbox].check_type:checked').each(function () {
                var q_type = (typeof $(this).data('ids') == 'undefined') ? $(this).val() : $(this).data('ids').toString().replace(/,/g, ',.');
                $("." + q_type).show();
                if (!$('#check_locked').prop("checked")) {
                    $("." + q_type + '.lock').hide();
                }
            });

            this.count_questions();
        };

        /**
         * Count the number of visible questions.
         */
        this.count_questions = function() {
            var n = $(".q:visible").length;
            var count = n.toLocaleString($('#language').val());
            // Old browsers use and old implementation of toLocaleString so we need to check for decimal places
            // in the string and remove them.
            var dp = count.indexOf('.');
            if (dp !== -1) {
                count = count.slice(0,dp);
            }
            $("#q_count").text('(' + count + ')');
        };

        /**
         * Hide unmapped objectives.
         */
        this.hide_unmapped_obs = function() {
            $('.check_type').parent().addClass('hidden');
            $('.check_type').each(function() {
                var ids = $(this).data('ids');
                ids = ids.toString().split(',');
                for (var i = 0; i < ids.length; i++) {
                    if ($('.q.' + ids[i]).length > 0) {
                        $(this).parent().removeClass('hidden');
                        break;
                    }
                }
            });
        };
    }
});
