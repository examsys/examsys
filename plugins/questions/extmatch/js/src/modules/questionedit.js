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
// Exact match question edit functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Update correct answers available for stems.
         * @param object el element
         */
        this.updateExtMatchOptions = function (el) {
            var index = $(el).attr('rel');
            var raw_text = $(el).val();
            var text = parseInt(index) + '. ' + raw_text;
            var opt_text = '';

            if (index != undefined) {
                $('.extmatch-correct').each(function () {
                    var options = $(this).children('option');
                    if (index > options.length) {
                        if (raw_text != '') {
                            for (var i = options.length + 1; i <= index; i++) {
                                opt_text = (i == index) ? text : i + '.';
                                $(this).append('<option value="' + i + '">' + opt_text + '</option>');
                            }
                        }
                    } else {
                        options.get(index - 1).text = text;
                    }
                });
            }
        };
    }
});