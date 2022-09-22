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
// Performance functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Open exam script in a window.
         */
        this.viewScript = function() {
            $('#menudiv').hide();
            if (this.metadataid != '') {
                var winwidth = screen.width - 80;
                var winheight = screen.height - 80;
                window.open("../paper/finish.php?id=" + this.cryptname + "&metadataID=" + this.metadataid  + "&log_type=" + this.papertype + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            }
        };

        /**
         * Open objective mapping feedback in a window.
         */
        this.viewFeedback = function() {
            $('#menudiv').hide();
            var winwidth = screen.width - 80;
            var winheight = screen.height - 80;
            window.open("../mapping/user_feedback.php?id=" + this.cryptname + "&userID=" + this.userid + "&metadataID=" + this.metadataid  + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        };

        /**
         * View personal cohort performance report.
         */
        this.viewPersonalCohort = function() {
            window.location.href="../reports/personal_cohort_performance.php?paperID=" + this.paperid  + "&userID=" +this.userid;
        };

        /**
         * View the paper.
         */
        this.jumpToPaper = function() {
            window.location.href="../paper/details.php?paperID=" + this.paperid ;
        };
    }
});
