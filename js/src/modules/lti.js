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
// LTI key helper js
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Initialise lti functions.
         */
        this.init = function() {
            var scope = this;
            $("#edit").click(function() {
                scope.editLTIkeys();
            });
            $("#delete").click(function() {
                scope.deleteLTIkeys();
            });
            $("#search").click(function() {
                scope.searchUserLinks();
            });
        };

        /**
         * Open edit lti key screen.
         */
        this.editLTIkeys = function() {
            window.location.href = './edit_LTIkeys.php?LTIkeysid=' + $('#lineID').val();
        };

        /**
         * Open a window to delete a lti key.
         */
        this.deleteLTIkeys = function() {
            var notice = window.open("../delete/check_delete_LTIkeys.php?LTIkeysID=" + $('#lineID').val() + "", "LTIkeyss", "width=520,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 270, screen.height / 2 - 85);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open the search lti user screen.
         */
        this.searchUserLinks = function() {
            window.location.href = './search_users.php?LTIkeysid=' + $('#lineID').val();
        };
    }
});
