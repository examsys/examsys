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
// Paper fill in the blank question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['editor', 'jsxls', 'jquery', 'jqueryvalidate'], function(Editor, Jsxls, $) {
    return function() {
        /**
         * Add fill in the blank validation methods to jquery-validate.
         */
        this.init = function () {
            $('#edit_form').submit(function () {
                Editor.triggerSave();
            });
            $('#edit_form').validate({
                ignore: '',
                rules: {
                    leadin: 'required',
                    option_text: 'required',
                    alt_q_media: 'required',
                },
                messages: {
                    leadin: Jsxls.lang_string['enterleadin'],
                    option_text: Jsxls.lang_string['enterquestion']
                },
                errorPlacement: function (error, element) {
                    if (element.attr('name') == 'leadin') {
                        error.insertAfter('#leadin_parent');
                        $('#leadin_tbl').css({'border-color': '#C00000'});
                        $('#leadin_tbl').css({'box-shadow': '0 0 6px rgba(200, 0, 0, 0.85)'});
                    } else if (element.attr('name') == 'option_text') {
                        error.insertAfter('#option_text_parent');
                        $('#option_text_tbl').css({'border-color': '#C00000'});
                        $('#option_text_tbl').css({'box-shadow': '0 0 6px rgba(200, 0, 0, 0.85)'});
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function () {
                    alert(Jsxls.lang_string['validationerror']);
                }
            });
        };
    }
});
