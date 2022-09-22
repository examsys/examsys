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
// Dichotomous question edit functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Change stem possible answers based on display type.
         * @param object el element
         */
        this.updateDichotomousLabels = function(el) {
            var positive = 'T';
            var negative = 'F';

            if ($(el).val().substr(0, 2) == 'YN') {
                positive = 'Y';
                negative = 'N';
            }

            $('.dichotomous-true').html(positive);
            $('.dichotomous-false').html(negative);
        }
    }
});