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
// Schools list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['jquery'], function($) {
    return function() {
        /**
         * Display edit school screen.
         */
        this.editSchool = function() {
            window.location.href='./edit_school.php?schoolid=' + $('#lineID').val();
        };

        /**
         * Open delete school window.
         */
        this.deleteSchool = function() {
            var notice=window.open("../delete/check_delete_school.php?schoolID=" + $('#lineID').val() + "","schools","width=450,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 225, screen.height / 2 - 100);
            if (window.focus) {
                notice.focus();
            }
        };
    }
});