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
//
// Faculties list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['jquery'], function($) {
    return function() {
        /**
         * Open add faculty window.
         */
        this.addFaculty = function() {
            var facultywin=window.open("add_faculty.php","faculties","width=450,height=220,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                facultywin.focus();
            }
        };
        /**
         * Open edit faculty window.
         */
        this.editFaculty = function() {
            var facultywin=window.open("edit_faculty.php?facultyID=" + $('#lineID').val() + "","faculties","width=450,height=220,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                facultywin.focus();
            }
        };
        /**
         * Open delete faculty window.
         */
        this.deleteFaculty = function() {
            var notice=window.open("../delete/check_delete_faculty.php?facultyID=" + $('#lineID').val() + "","faculties","width=450,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width/2-225, screen.height/2-100);
            if (window.focus) {
                notice.focus();
            }
        };
    }
});
