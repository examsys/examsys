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
// RANK paper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['start', 'jsxls', 'jquery'], function(START, jsxls, $) {
    return function () {
        /**
         * Check user does no select the same option more than once.
         * @param object el element
         */
        this.rankCheck = function(el) {
            var sel = $(el).val();
            var classlist =  '.' + $(el).attr('class').replace(' ', '.');
            var count = 0;
            var loopSel = '';

            $(classlist).each(function () {
                loopSel = $(this).val();
                if(loopSel != '0' && loopSel != 'u' && loopSel == sel) {
                    count++;
                }
            });
            if (count > 1) {
                var start = new START();
                start.info_dialog(jsxls.lang_string['msgselectable3'] + ' ' + sel  + jsxls.lang_string['msgselectable4']);
                $(el).val('u');
            }
        };
    }
});