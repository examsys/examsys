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
// MRQ paper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'jquery'], function(jsxls, $) {
    return function () {
        /**
         * Warn user if more options selected than allowed.
         * Uncheck abstain option if another option is selected.
         * @param integer questionid question id
         * @param integer part_id question part id
         * @param integer options_total number of options
         * @param integer selectable number of allowed selections
         */
        this.MRQ = function(questionid, part_id, options_total, selectable) {
            var abstainExist = document.getElementById("q" + questionid + "_abstain");
            if (abstainExist != null) {
                $("#q" + questionid + "_abstain").prop("checked", false);
            }

            var checked_total = 0;
            for (var i = 1; i <= options_total; i++) {
                var currentid = "q" + questionid + "_" + i;
                if ($('#' + currentid).prop("checked")) {
                    checked_total++;
                }
            }
            if (checked_total > selectable) {
                alert(jsxls.lang_string['msgselectable1'] + ' ' + selectable + ' ' + jsxls.lang_string['msgselectable2']);
                $("#q" + questionid + "_" + part_id).prop("checked", false);
            }
        };

        /**
         * Uncheck all other options if abstain is selected.
         * @param integer questionid question id
         * @param integer options_total number of options
         */
        this.MRQabstain = function(questionid, options_total) {
            for (var i = 1; i <= options_total; i++) {
                $("#q" + questionid + "_" + i).prop("checked", false);
            }
        };
    }
});