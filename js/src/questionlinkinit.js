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
// Initialise question link section.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['questionlink', 'list', 'jquery'], function (LINK, LIST, $) {
    var link = new LINK();
    $(".cancel").click(function() {
        window.close();
    });

    $("#gotopaper").click(function() {
        window.opener.location.href = '../paper/details.php?paperID=' + $(this).attr('data-paperid');
        window.close();
    });

    var list = new LIST();
    var winH = $(window).height() - 150;

    list.resizeList(winH);

    $(window).resize(function() {
        list.resizeList(winH);
    });

    if ($('#dataset').attr('data-outcomes')) {
        $('#outcomes').val(link.getSelectedOutcomes());
    }

    $('#theForm').submit(function() {
        return link.checkForm();
    });
});