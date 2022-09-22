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
// Folder helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Open delete folder window.
         */
        this.deleteFolder = function() {
            var notice=window.open("../delete/check_delete_folder.php?folderID=" + $('#dataset').attr('data-folder'),"notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width/2-210,screen.height/2-105);
            if (window.focus) {
                notice.focus();
            }
        };
        /**
         * Open folder properties window.
         */
        this.folderProperties = function() {
           var notice=window.open("properties.php?folder=" + $('#dataset').attr('data-folder'),"properties","width=600,height=600,left="+(screen.width/2-300)+",top="+(screen.height/2-300)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };
    }
});
