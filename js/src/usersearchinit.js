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
// Initialise user search section.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['usersearch', 'state', 'menu', 'jquery', 'jquerytablesorter'], function (SEARCH, STATE, MENU, $) {
    var search = new SEARCH();

    var menu = new MENU();

    if ($("#maindata").find("tr").length > 1) {
        $("#maindata").tablesorter({
            // sort on the third column, order asc
            sortList: [[3, 0]]
        });
    }

    $('#performancesummary2b').click(function() {
        search.performanceSummary();
    });

    $('#performancesummary2c').click(function() {
        search.performanceSummary();
    });

    $('.viewprofile').click(function() {
        document.location.href='details.php?userID=' + search.getLastID($('#userID').val());
    });

    $('#student_id').on("input propertychange paste", function() {
        search.updateStaffChkBoxes(this);
    });

    $('.chkstaff').click(function() {
        search.updateStudentID(this);
    });

    $('#advancedmenu').click(function() {
        menu.updateMenu($(this).attr('data-menuid'));
    });

    $('#deleteuser').click(function() {
        search.deleteUser();
    });

    $(".l").click(function(e) {
        search.selUser($(this).attr('data-userid'), $(this).attr('data-lineid'), $(this).attr('data-menuid'), $(this).attr('data-roles'), e);
    });

    $(".l").dblclick(function() {
        search.profile($(this).attr('data-userid'));
    });

    var state = new STATE();
    state.init();
});
