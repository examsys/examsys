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
// Standard Set
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'jquery'], function(jsxls, $) {
    return function() {

        /**
         * Round a number.
         * @param float num
         * @param integer dec power of value
         * @returns integer
         */
        this.roundNumber = function(num, dec) {
            var result = Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
            return result;
        };

        /**
         * Recount all categories.
         */
        this.recountCategories = function() {
            var EE = 0;
            var EI = 0;
            var EN = 0;
            var ME = 0;
            var MI = 0;
            var MN = 0;
            var HE = 0;
            var HI = 0;
            var HN = 0;

            var origEE = 0;
            var origEI = 0;
            var origEN = 0;
            var origME = 0;
            var origMI = 0;
            var origMN = 0;
            var origHE = 0;
            var origHI = 0;
            var origHN = 0;

            var question_no = parseInt($('#stdIDNo').val());

            for (var i = 0; i < question_no; i++) {
                var question_marks = parseInt($('#std' + i + '_marks').val());
                switch ($('#valstd' + i).val()) {
                    case 'EE':
                        EE += question_marks;
                        break;
                    case 'EI':
                        EI += question_marks;
                        break;
                    case 'EN':
                        EN += question_marks;
                        break;
                    case 'ME':
                        ME += question_marks;
                        break;
                    case 'MI':
                        MI += question_marks;
                        break;
                    case 'MN':
                        MN += question_marks;
                        break;
                    case 'HE':
                        HE += question_marks;
                        break;
                    case 'HI':
                        HI += question_marks;
                        break;
                    case 'HN':
                        HN += question_marks;
                        break;
                }
                switch ($('#valstd' + i).val()) {
                    case 'EE':
                    case 'exclude_EE':
                        origEE += question_marks;
                        break;
                    case 'EI':
                    case 'exclude_EI':
                        origEI += question_marks;
                        break;
                    case 'EN':
                    case 'exclude_EN':
                        origEN += question_marks;
                        break;
                    case 'ME':
                    case 'exclude_ME':
                        origME += question_marks;
                        break;
                    case 'MI':
                    case 'exclude_MI':
                        origMI += question_marks;
                        break;
                    case 'MN':
                    case 'exclude_MN':
                        origMN += question_marks;
                        break;
                    case 'HE':
                    case 'exclude_HE':
                        origHE += question_marks;
                        break;
                    case 'HI':
                    case 'exclude_HI':
                        origHI += question_marks;
                        break;
                    case 'HN':
                    case 'exclude_HN':
                        origHN += question_marks;
                        break;
                }
            }
            $('#ee').val(EE + ' ' + jsxls.lang_string['marks']);
            if (origEE != EE) {
                $('#origee').val(origEE);
                $('#origee2').val(origEE);
            } else {
                $('#origee').val('');
                $('#origee2').val('');
            }

            $('#ei').val(EI + ' ' + jsxls.lang_string['marks']);
            if (origEI != EI) {
                $('#origei').val(origEI);
                $('#origei2').val(origEI);
            } else {
                $('#origei').val('');
                $('#origei2').val('');
            }

            $('#en').val(EN + ' ' + jsxls.lang_string['marks']);
            if (origEN != EN) {
                $('#origen').val(origEN);
                $('#origen2').val(origEN);
            } else {
                $('#origen').val('');
                $('#origen2').val('');
            }

            $('#me').val(ME + ' ' + jsxls.lang_string['marks']);
            if (origME != ME) {
                $('#origme').val(origME);
                $('#origme2').val(origME);
            } else {
                $('#origme').val('');
                $('#origme2').val('');
            }

            $('#mi').val(MI + ' ' + jsxls.lang_string['marks']);
            if (origMI != MI) {
                $('#origmi').val(origMI);
                $('#origmi2').val(origMI);
            } else {
                $('#origmi').val('');
                $('#origmi2').val('');
            }

            $('#mn').val(MN + ' ' + jsxls.lang_string['marks']);
            if (origMN != MN) {
                $('#origmn').val(origMN);
                $('#origmn2').val(origMN);
            } else {
                $('#origmn').val('');
                $('#origmn2').val('');
            }

            $('#he').val(HE + ' ' + jsxls.lang_string['marks']);
            if (origHE != HE) {
                $('#orighe').val(origHE);
                $('#orighe2').val(origHE);
            } else {
                $('#orighe').val('');
                $('#orighe2').val('');
            }

            $('#hi').val(HI + ' ' + jsxls.lang_string['marks']);
            if (origHI != HI) {
                $('#orighi').val(origHI);
                $('#orighi2').val(origHI);
            } else {
                $('#orighi').val('');
                $('#orighi2').val('');
            }

            $('#hn').val(HN + ' ' + jsxls.lang_string['marks']);
            if (origHN != HN) {
                $('#orighn').val(origHN);
                $('#orighn2').val(origHN);
            } else {
                $('#orighn').val('');
                $('#orighn2').val('');
            }

            $('#easy_total').val((EE + EI + EN) + ' ' + jsxls.lang_string['marks']);
            $('#medium_total').val((ME + MI + MN) + ' ' + jsxls.lang_string['marks']);
            $('#hard_total').val((HE + HI + HN) + ' ' + jsxls.lang_string['marks']);
            $('#essential_total').val((EE + ME + HE) + ' ' + jsxls.lang_string['marks']);
            $('#important_total').val((EI + MI + HI) + ' ' + jsxls.lang_string['marks']);
            $('#nice_total').val((EN + MN + HN) + ' ' + jsxls.lang_string['marks']);

            $('#easy2_total').val((EE + EI + EN) + ' ' + jsxls.lang_string['marks']);
            $('#medium2_total').val((ME + MI + MN) + ' ' + jsxls.lang_string['marks']);
            $('#hard2_total').val((HE + HI + HN) + ' ' + jsxls.lang_string['marks']);
            $('#essential2_total').val((EE + ME + HE) + ' ' + jsxls.lang_string['marks']);
            $('#important2_total').val((EI + MI + HI) + ' ' + jsxls.lang_string['marks']);
            $('#nice2_total').val((EN + MN + HN) + ' ' + jsxls.lang_string['marks']);

            $('#ee2').val(EE + ' ' + jsxls.lang_string['marks']);
            $('#ei2').val(EI + ' ' + jsxls.lang_string['marks']);
            $('#en2').val(EN + ' ' + jsxls.lang_string['marks']);
            $('#me2').val(ME + ' ' + jsxls.lang_string['marks']);
            $('#mi2').val(MI + ' ' + jsxls.lang_string['marks']);
            $('#mn2').val(MN + ' ' + jsxls.lang_string['marks']);
            $('#he2').val(HE + ' ' + jsxls.lang_string['marks']);
            $('#hi2').val(HI + ' ' + jsxls.lang_string['marks']);
            $('#hn2').val(HN + ' ' + jsxls.lang_string['marks']);

            var paper_marks = $('#total_marks').val();
            var cut_marks = 0;
            cut_marks += EE * $('#EE').val() * 100;
            cut_marks += EI * $('#EI').val() * 100;
            cut_marks += EN * $('#EN').val() * 100;
            cut_marks += ME * $('#ME').val() * 100;
            cut_marks += MI * $('#MI').val() * 100;
            cut_marks += MN * $('#MN').val() * 100;
            cut_marks += HE * $('#HE').val() * 100;
            cut_marks += HI * $('#HI').val() * 100;
            cut_marks += HN * $('#HN').val() * 100;
            var total_marks = EE + EI + EN + ME + MI + MN + HE + HI + HN;
            var cut_score = (cut_marks / paper_marks) * 100;
            $('#cut_score').val(jsxls.lang_string['papermarks'] + '=' + paper_marks + ', ' + jsxls.lang_string['reviewmarks'] + '=' + total_marks + ', ' + jsxls.lang_string['cutscore'] + '=' + this.roundNumber(cut_score/100,1) + '%');

            cut_marks = 0;
            cut_marks += EE * $('#EE2').val() * 100;
            cut_marks += EI * $('#EI2').val() * 100;
            cut_marks += EN * $('#EN2').val() * 100;
            cut_marks += ME * $('#ME2').val() * 100;
            cut_marks += MI * $('#MI2').val() * 100;
            cut_marks += MN * $('#MN2').val() * 100;
            cut_marks += HE * $('#HE2').val() * 100;
            cut_marks += HI * $('#HI2').val() * 100;
            cut_marks += HN * $('#HN2').val() * 100;
            total_marks = EE + EI + EN + ME + MI + MN + HE + HI + HN;
            cut_score = (cut_marks / paper_marks) * 100;
            $('#cut_score2').val(jsxls.lang_string['papermarks'] + '=' + document.getElementById('total_marks').value + ', ' + jsxls.lang_string['reviewmarks'] + '=' + total_marks + ', ' + jsxls.lang_string['cutscore'] + '=' + this.roundNumber(cut_score/100,1) + '%');
        };
    }
});
