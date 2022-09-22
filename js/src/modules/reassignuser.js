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
// Reassign user functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
    return function() {
        /**
         * Reassign script.
         * @param integer targetid target user id
         * @param integer userid guest user id
         * @param string username target user username
         */
        this.doReassign = function(targetid, userid, username) {
            window.location = "do_reassign_script.php?temp_userID=" + userid + "&userID=" + targetid + "&assigned_account=" + username;
        };

        /**
         * Resize the user list.
         */
        this.do_resize = function() {
            var tmp_height = $(document).height() - 200;
            $("#userlist").height(tmp_height);
        };
    }
});
