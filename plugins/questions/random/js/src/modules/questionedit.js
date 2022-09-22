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
// Random question edit functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Open question bank.
         */
        this.addQuestion = function () {
            var winH = screen.height - 80
            var winW = screen.width - 80
            var notice = window.open("../add/add_random_questions_frame.php?q_no=1&questionlist=questionlist&question_no=question_no", "notice", "width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Add question selected in question bank window to random question block.
         * @param questions
         */
        this.addQuestionsToList = function (questions) {
            var current_qns = window.top.opener.$('#questionlist li');
            var num_options = current_qns.length + 1;

            var i = 0;
            $.each(questions, function(key, value) {
                window.top.opener.$('#questionlist').append('<li><label for="option_text' + (num_options + i) + '" class="fullwidth"><input id="option_text' + (num_options + i) + '" name="option_text' + (num_options + i) + '" value="' + key + '" type="checkbox" checked="checked" class="random-q" /> ' + value + '</label><input name="optionid' + (num_options + i) + '" value="-1" type="hidden" /></li>');
                i++;
            });

            window.top.opener.$('#questioncheck').valid();
        };
    }
});