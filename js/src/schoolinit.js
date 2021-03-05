// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Initialise schools page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['schoolslist', 'list', 'jquery', 'jquerytablesorter'], function (SCHOOLS, LIST, $) {
    var schools = new SCHOOLS();
    var list = new LIST();
    list.init();
    if ($("#maindata").find("tr").length > 1) {
        $("#maindata").tablesorter({
            sortList: [[0,0]]
        });
    }

    $(".l").dblclick(function() {
        list.edit('./edit_school.php?schoolid=', $(this).attr('id'));
    });

    $(".editschool").click(function() {
        schools.editSchool();
    });

    $(".deleteschool").click(function() {
        schools.deleteSchool();
    });
});
