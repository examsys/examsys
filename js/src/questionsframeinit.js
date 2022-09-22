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
// Initialise questions frame.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery', 'addquestionsbuttons'], function ($, BUTTONS) {
    var buttons = new BUTTONS();
    buttons.init();

    $('#button_unused').click(function() {
        buttons.buttonclick('unused','add_questions_list.php?type=unused');
    });

    $('#button_alphabetic').click(function() {
        buttons.buttonclick('alphabetic','add_questions_list.php?type=all');
    });

    $('#button_keywords').click(function() {
        buttons.buttonclick('keywords','add_questions_keywords_frame.php');
    });

    $('#button_status').click(function() {
        buttons.buttonclick('status','add_questions_by_status.php');
    });

    $('#button_papers').click(function() {
        buttons.buttonclick('papers','add_questions_paper_types.php');
    });

    $('#button_team').click(function() {
        if (!$(this).hasClass( "grey" )) {
            buttons.buttonclick('team', 'add_questions_team_list.php');
        }
    });

    $('#button_search').click(function() {
        buttons.buttonclick('search','add_questions_list_search.php');
    });

    $(function () {
        $('#addquestions').submit(function(e) {
            // Stop the form submission.
            e.preventDefault();
            var paperid = $("#dataset").attr('data-paperid');
            var module = $("#dataset").attr('data-module');
            var folder = $("#dataset").attr('data-folder');
            var disp = $("#dataset").attr('data-disp');
            var srcofy = $("#dataset").attr('data-srcofy');
            var max = $("#dataset").attr('data-max');
            var url = "do_add_questions.php?paperID=" + paperid + "&display_pos=" + disp + "&module=" + module + "&folder=" + folder
                + "&scrOfY=" + srcofy + "&max_screen=" + max;
            var data = $('#addquestions').serialize();
            $.post(url, data).done(function() {
                opener.location.reload();
                window.close();
            });
        });
    });
});
