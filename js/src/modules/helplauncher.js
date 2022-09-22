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
// Help launcher functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define(['rogoconfig', 'jquery'], function (config, $) {
    return {
        /**
         * Open help file
         * @param integer helpID help file id
         * @param string role user role
         * @returns bool
         */
        launchHelp: function (helpID, role) {
            if (role == 'staff' || role == 'student') {
                var helpwin = window.open(config.cfgrootpath + "/help/" + role + "/index.php?id=" + helpID + "", "help", "width=" + (screen.width - 100) + ",height=" + (screen.height - 100) + ",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
                helpwin.moveTo(10, 10);
                if (window.focus) {
                    helpwin.focus();
                }
            }
            $('#toprightmenu').hide();
            return false;
        }
    }
});