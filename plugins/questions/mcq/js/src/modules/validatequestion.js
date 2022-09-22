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
// Paper mcq question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['editor', 'jsxls', 'jquery', 'jqueryvalidate'], function(Editor, jsxls, $) {
    return function() {
        /**
         * Add mcq validation methods to jquery-validate.
         */
        this.init = function () {
            $('#edit_form').submit(function () {
                $('#leadin_ifr').removeClass('invalid');
                Editor.triggerSave();
            });
            $('#edit_form').validate({
                ignore: '',
                rules: {
                    leadin: 'required',
                    option_text1: {
                        required: {
                            depends: function (element) {
                                return ($('#option_media1').val() == '' && ($('#existing_media1').length == 0 || $('#existing_media1').val() == ''));
                            }
                        }
                    },
                    option_text2: {
                        required: {
                            depends: function (element) {
                                return ($('#option_media2').val() == '' && ($('#existing_media2').length == 0 || $('#existing_media2').val() == ''));
                            }
                        }
                    },
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
                    alt_option_media16: 'required',
                    alt_option_media17: 'required',
                    alt_option_media18: 'required',
                    alt_option_media19: 'required',
                    alt_option_media20: 'required',
                },
                messages: {
                    leadin: jsxls.lang_string['enterleadin'],
                    option_text1: '<br />' + jsxls.lang_string['enteroption'],
                    option_text2: '<br />' + jsxls.lang_string['enteroption']
                },
                errorPlacement: function (error, element) {
                    if (element.attr('name') == 'leadin') {
                        error.insertBefore('#leadin');
                        $('#leadin_ifr').addClass('invalid');
                    } else if (element.attr('name') == 'option_text1') {
                        error.insertAfter('#filepickeroption_media1');
                    } else if (element.attr('name') == 'option_text2') {
                        error.insertAfter('#filepickeroption_media2');
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
