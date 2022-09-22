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
// Paper random block question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jsxls', 'jquery', 'jqueryvalidate'], function(jsxls, $) {
    return function() {
        /**
         * Add random block validation methods to jquery-validate.
         */
        this.init = function () {
            $.validator.addMethod('havequestions', function () {
                var rval = true;
                if ($('.random-q:checked').length == 0) {
                    rval = false;
                }
                return rval;
            }, 'Wrong password');

            $('#edit_form').validate({
                rules: {
                    leadin: 'required',
                    questioncheck: 'havequestions'
                },
                messages: {
                    leadin: jsxls.lang_string['enterdescription'],
                    questioncheck: jsxls.lang_string['randomenterquestion']
                },
                errorPlacement: function (error, element) {
                    if (element.attr('name') == 'questioncheck') {
                        error.insertAfter('#qlist-holder');
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function () {
                    alert(jsxls.lang_string['validationerror']);
                }
            });
        };
    }
});