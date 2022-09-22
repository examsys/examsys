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
// Paper matrix question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['editor', 'jsxls', 'jquery', 'jqueryvalidate'], function(Editor, Jsxls, $) {
    return function() {
        /**
         * Add matrix validation methods to jquery-validate.
         */
        this.init = function () {
            $('#edit_form').submit(function () {
                Editor.triggerSave();
            });

            jQuery.validator.addMethod("matrixlabels", function (value, element) {
                // Check valid matrix labels  setup.
                var start = element.id.search('_text');
                var y = element.id.substr(start + 5);
                // Check for empty strings.
                if (value != "" && value.trim() == "") {
                    return false;
                }
                // Check the label of a selected radio button is not blank
                // Ignore if stem is also blank.
                if (value == "") {
                    for (var x = 1; x <= 10; x++) {
                        var id = 'option_correct' + x + '_' + y;
                        if ($('#' + id).is(':checked') && $('#question_stem' + x).val() != "") {
                            return false;
                        }
                    }
                    // Do not allow empty labels, apart from those not in use at the end of the list.
                    var emptylabel = false;
                    for (var x = (parseInt(y) + 1); x <= 10; x++) {
                        var id = 'option_text' + x;
                        if ($('#' + id).val() != "") {
                            emptylabel = true;
                            break;
                        }
                    }
                    if (emptylabel) {
                        return false;
                    }
                }
                return this.optional(element) || true;
            }, Jsxls.lang_string['entervalidvariable']);

            jQuery.validator.addMethod("matrixstems", function (value, element) {
                // Check valid matrix stem setup.
                var start = element.id.search('_stem');
                var x = element.id.substr(start + 5);
                // Check for empty strings.
                if (value != "" && value.trim() == "") {
                    return false;
                }
                // Check the stem of a selected radio button is not blank
                // Ignore if label is also blank.
                if (value == "") {
                    for (var y = 1; y <= 10; y++) {
                        var id = 'option_correct' + x + '_' + y;
                        if ($('#' + id).is(':checked') && $('#option_text' + y).val() != "") {
                            return false;
                        }
                    }
                    // Do not allow empty stems, apart from those not in use at the end of the list.
                    var emptystem = false;
                    for (var y = (parseInt(x) + 1); y <= 10; y++) {
                        var stemid = 'question_stem' + y;
                        if ($('#' + stemid).val() != "") {
                            emptystem = true;
                            break;
                        }
                    }
                    if (emptystem) {
                        return false;
                    }
                }
                return this.optional(element) || true;
            }, Jsxls.lang_string['entervalidvariable']);

            $('#edit_form').validate({
                rules: {
                    leadin: 'required',
                    alt_q_media: 'required',
                    option_text1: 'matrixlabels',
                    question_stem1: 'matrixstems',
                    option_text2: 'matrixlabels',
                    question_stem2: 'matrixstems',
                    option_text3: 'matrixlabels',
                    question_stem3: 'matrixstems',
                    option_text4: 'matrixlabels',
                    question_stem4: 'matrixstems',
                    option_text5: 'matrixlabels',
                    question_stem5: 'matrixstems',
                    option_text6: 'matrixlabels',
                    question_stem6: 'matrixstems',
                    option_text7: 'matrixlabels',
                    question_stem7: 'matrixstems',
                    option_text8: 'matrixlabels',
                    question_stem8: 'matrixstems',
                    option_text9: 'matrixlabels',
                    question_stem9: 'matrixstems',
                    option_text10: 'matrixlabels',
                    question_stem10: 'matrixstems',
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
