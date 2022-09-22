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
// Module options helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'jquery'], function(config, $) {
    return function() {
        /**
         * Open team members dialog window.
         */
        this.teammembers = function () {
            $('#addteammember').click(function () {
                var notice = window.open(config.cfgrootpath + "/module/edit_team_popup.php?module=" + $('#dataset').attr('data-module') + "&calling=paper_list&folder=" + $('#dataset').attr('data-folder'), "properties", "width=450,height=" + (screen.height - 200) + ",left=" + (screen.width / 2 - 325) + ",top=10,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
                if (window.focus) {
                    notice.focus();
                }
            });
        };
    }
});