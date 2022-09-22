// JavaScript Document
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
// Module page helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2015 The University of Nottingham
//
define([], function() {
    return function() {
        /**
         * Open new paper dialog.
         * @param integer module module id
         */
        this.newPaper = function(module) {
            var notice = window.open("../paper/new_paper1.php?module=" + module,"paper","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open new question dialog.
         * @param integer module module id
         */
        this.newQuestion = function(module) {
            var notice = window.open("../question/new.php?module=" + module,"question","width=800,height=500,left="+(screen.width/2-400)+",top="+(screen.height/2-250)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };
    }
});
