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
// Initialise create user page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['form', 'typecoursefilter', 'jquery'], function (FORM, TYPECOURSEFILTER, $) {
    var form = new FORM();
    form.init();

    $('#ldaplookup').click(function () {
        var notice = window.open("ldaplookup.php", "ldap", "width=650,height=300,left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        notice.moveTo(screen.width / 2 - 325, screen.height / 2 - 150);

        if (window.focus) {
            notice.focus();
        }
    });

    // Call for Type/Course dropdown
    var options = {
        parentField: "#new_roles",
        childField: "#new_grade",
        parentLabel: "#typecourse"
    };
    var filter = new TYPECOURSEFILTER();
    filter.init(options);
});