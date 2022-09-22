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
// Initialise new paper page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['form', 'newpaperform', 'jquery', 'jqueryui'], function (FORM, PAPER, $) {
    var paper = new PAPER();
    var form = new FORM();
    form.init();

    paper.activate($('#dataset').attr('data-type'));

    $('.icon').click(function() {
        paper.activate($(this).attr('id'));
    });
    $('.icon').mouseover(function() {
        paper.over($(this).attr('id'));
    });

    $('.icon').mouseout(function() {
        paper.out($(this).attr('id'));
    });


    $('#theform').submit(function() {
        if (paper.checkForm()) {
            return true;
        }
        return false;
    });

    $(function () {
        if ($('#warning').attr('data-name') != '') {
            $('#warning').show();
        }
        if ($('#paper_type').val() != '') {
            $('#' + $('#paper_type').val()).css('background-color', '#FFBD69');
        }
    });
});