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
// Paper dichotomous question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['editor', 'jsxls', 'jquery', 'jqueryvalidate'], function(Editor, Jsxls, $) {
    return function() {
        /**
         * Add dichotomous validation methods to jquery-validate.
         */
        this.init = function () {
            $('#edit_form').submit(function () {
                Editor.triggerSave();
            });
            $('#edit_form').validate({
                ignore: '',
                rules: {
                    leadin: 'required',
                    alt_q_media: 'required',
                    alt_option_media1: 'required',
                    alt_option_media2: 'required',
                    alt_option_media3: 'required',
                    alt_option_media4: 'required',
                    alt_option_media5: 'required',
                    alt_option_media6: 'required',
                    alt_option_media7: 'required',
                    alt_option_media8: 'required',
                    alt_option_media9: 'required',
                    alt_option_media10: 'required',
                    alt_option_media11: 'required',
                    alt_option_media12: 'required',
                    alt_option_media13: 'required',
                    alt_option_media14: 'required',
                    alt_option_media15: 'required',
                },
                messages: {
                    leadin: Jsxls.lang_string['enterleadin'],
                },
                errorPlacement: function (error, element) {
                    if (element.attr('name') == 'leadin') {
                        error.insertAfter('#leadin_parent');
                        $('#leadin_tbl').css({'border-color': '#C00000'});
                        $('#leadin_tbl').css({'box-shadow': '0 0 6px rgba(200, 0, 0, 0.85)'});
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
