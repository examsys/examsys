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
// Peer review functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * View user profile.
         */
        this.viewProfile = function() {
            $('#menudiv').hide();
            window.location = '/users/details.php?userID=' + this.userid;
        };

        /**
         * Open user reviews in a window.
         */
        this.viewReviews = function() {
            $('#menudiv').hide();
            var winwidth = screen.width - 80;
            var winheight = screen.height - 80;
            window.open("/peer_review/display_form.php?paperID=" + this.paperid + "&userID=" + this.userid + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        };

        /**
         * Change the group displayed.
         */
        this.changeGroup = function() {
            window.location = "form.php?id=" + $('#dataset').attr('data-id') + "&group=" + $('#group').val();
        };
    }
});
