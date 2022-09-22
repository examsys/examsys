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
// Exact match paper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls'], function(jsxls) {
    return function () {
        /**
         * Check the does not exceed the max selectable number of options.
         * @param integer questionid question id
         * @param integer options_total number of options
         * @param integer selectable number of selectable options
         */
        this.multimatchingCheck = function(questionid, options_total, selectable) {
            var checked_total = 0;
            for (var i = 0; i < options_total; i++) {
                if (document.getElementById(questionid).options[i].selected == 1) {
                    checked_total++;
                }
            }
            var tmp_count = 0;
            if (checked_total > selectable) {
                alert(jsxls.lang_string['msgselectable1'] + ' ' + selectable + ' ' + jsxls.lang_string['msgselectable2']);

                for (var i = 0; i < options_total; i++) {
                    if (document.getElementById(questionid).options[i].selected == 1) {
                        tmp_count++;
                    }
                    if (tmp_count > selectable) {
                        document.getElementById(questionid).options[i].selected = 0;
                    }
                }
            }
        };
    }
});