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
// Initialise faculty page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['facultieslist', 'list', 'jquery', 'jquerytablesorter'], function (FACULTY, LIST, $) {
    var faculty = new FACULTY();
    var list = new LIST();
    list.init();
    if ($("#maindata").find("tr").length > 1) {
        $("#maindata").tablesorter({
            sortList: [[0,0]]
        });
    }

    $(".l").dblclick(function() {
        faculty.editFaculty();
    });

    $('#sms').click(function() {
        $('#sms').append('<img src="../artwork/working.gif" class="busyicon" />');
    });

    $(".addfaculty").click(function() {
        faculty.addFaculty();
    });

    $(".editfaculty").click(function() {
        faculty.editFaculty();
    });

    $(".deletefaculty").click(function() {
        faculty.deleteFaculty();
    });
});
