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
// Paper calculation question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jsxls', 'jquery', 'jqueryvalidate'], function(jsxls, $) {
    return function() {
        /**
         * Add calculation validation methods to jquery-validate.
         */
        this.init = function() {
            $.validator.addMethod("calcanswer", function (value, element) {
                return this.optional(element) || /^[+-]?[0-9]+[\.]?[0-9]*(.)*$/.test(value);
            }, jsxls.lang_string['entervalidcalcanswer']);
            $.validator.addClassRules('ecalc-answer', {
                calcanswer: true
            });
            $('#qForm').validate({
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.insertBefore(element);
                }
            });
        };
    }
});