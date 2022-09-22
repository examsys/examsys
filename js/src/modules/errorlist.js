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
// Error list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['list'], function(LIST) {
    return function() {
        /**
         * Error item double click action event.
         * @param integer lineID list item id
         * @param object event event
         */
        this.openBug = function (lineID, event) {
            var list = new LIST();
            list.selLine(lineID, event);
            this.displayDetails(lineID, event);
        };

        /**
         * Display the error details screen.
         * @param integer lineID error id
         * @param ojbect event event
         */
        this.displayDetails = function(lineID, event) {
            event.stopPropagation();
            var details = window.open("./sys_error_details.php?errorID=" + lineID + "","properties","width=900,height=800,left="+(screen.width/2-450)+",top="+(screen.height/2-400)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                details.focus();
            }
        };
    }
});