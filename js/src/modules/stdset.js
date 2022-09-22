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
// Standard Set
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery', 'jquerytablesorter'], function($) {
    return function() {
        /**
         * UI changes on selection of a standard setting review.
         * @param integer std_setID standard setting identifier
         * @param integer setterID identifier of user who created the standard setting.
         * @param string methodType standard setting type i.e. Modified Angoff
         * @param string menuID identifier of menu to display
         * @param string group is this a group review - Yes or No
         * @param ojbect evt event
         */
        this.selReview = function(std_setID, setterID, methodType, menuID, group, evt) {
            this.groupReview = group;

            var tmp_ID = $('#oldReviewID').val();
            if (tmp_ID != '') {
                $('#review' + tmp_ID).css('background-color', 'white');
            }
            $('#menu2a').hide();
            $('#menu2b').hide();
            $('#menu2c').hide();
            $('#' + menuID).show();

            $('#std_setID').val(std_setID);
            $('#setterID').val(setterID);
            $('#method').val(methodType);

            $('#review' + std_setID).css('background-color', '#FFBD69');
            $('#oldReviewID').val(std_setID);
            evt.cancelBubble = true;
        };

        /**
         * Open a window to delete a standard setting review.
         */
        this.deleteReview = function() {
            var notice=window.open("../delete/check_delete_review.php?std_setID=" + $('#std_setID').val() + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width/2-210, screen.height/2-85);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Display a screen to edit a standard setting review.
         */
        this.editReview = function() {
            var paperid = $('#dataset').attr('data-paperid');
            var module = $('#dataset').attr('data-module');
            var folder = $('#dataset').attr('data-folder');
            var methodType = '';
            if ($('#method').val() == 'Modified Angoff') {
                methodType = 'modified_angoff';
            } else if ($('#method').val() == 'Ebel') {
                methodType = 'ebel';
            } else {
                methodType = 'hofstee';
            }

            if (this.groupReview == 'No') {
                if (methodType == 'hofstee') {
                    window.location.href = "hofstee.php?paperID=" + paperid + "&std_setID=" + $('#std_setID').val() + "&method=" + methodType + "&module=" + module + "&folder=" + folder;
                } else {
                    window.location.href = "individual_review.php?paperID=" + paperid + "&std_setID=" + $('#std_setID').val() + "&method=" + methodType;
                }
            } else {
                window.location.href = "group_set_angoff.php?paperID=" + paperid + "&std_setID=" + $('#std_setID').val() + "&reviewers=" + $('#setterID').val() + "&module=" + module + "&folder=" + folder + "&method=" + methodType;
            }
        };

    }
});
