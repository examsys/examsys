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
// Admin index page function
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Open review screens in window
         * @param integer paperID the paper id
         * @param bool fullsc flag to open in fullscreen or not
         */
        this.startPaper = function(paperID, fullsc) {
            var winwidth = screen.width-80;
            var winheight = screen.height-80;
            if (fullsc == 0) {
                window.open("./reviews/start.php?id="+paperID+"&review=1","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            } else {
                window.open("./reviews/start.php?id="+paperID+"&review=1","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            }
        };
        /**
         * Hide the system announcement
         * @param integer announcementID the announcement internal id
         */
        this.hideAnnouncement = function(announcementID) {
            $('#announcement' + announcementID).hide();

            $.ajax({
                url: "./ajax/staff/hide_announcement.php",
                type: "get",
                data: {announcementID: announcementID},
                timeout: 30000, // timeout after 30 seconds
                dataType: "html",
            });
        };
    }
});
