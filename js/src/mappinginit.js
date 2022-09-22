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
// Initialise mapping page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['mapping', 'jquery'], function (MAPPING, $) {
    var mapping = new MAPPING();
    var paper = $('#dataset').attr('data-paper');
    var folder = $('#dataset').attr('data-folder');
    var module = $('#dataset').attr('data-module');
    $('.mapping').click(function() {
        mapping.mapQuestion($(this).attr('data-qno'), $(this).attr('data-pid'), $(this).attr('data-qid'), $(this).attr('data-session'));
    });

    $('#byquestion').click(function() {
        if ($(this).hasClass('taboff')) {
            window.location.href='paper_by_question.php?paperID=' + paper + '&folder=' + folder + '&module=' + module;
        }
    });

    $('#bysession').click(function() {
        if ($(this).hasClass('taboff')) {
            window.location.href='paper_by_session.php?paperID=' + paper + '&folder=' + folder + '&module=' + module;
        }
    });

    $('#byyear').click(function() {
        if ($(this).hasClass('taboff')) {
            window.location.href='paper_by_year.php?paperID=' + paper + '&folder=' + folder + '&module=' + module;
        }
    });
});