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
// User search
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['alert', 'rogoconfig', 'state', 'jquery', 'jqueryui'], function(ALERT, config, STATE, $) {
    return function () {
        /**
         * Remove student ID search term is staff role selected.
         * @param object el element
         */
        this.updateStudentID = function (el) {
            if ($(el).is(':checked')) {
                if ($('#student_id').val !== '') {
                    $('#student_id').val('');
                }
            }
        };

        /**
         * If student ID entered un check staff roles.
         * @param object el element
         */
        this.updateStaffChkBoxes = function (el) {
            if ($(el).val !== '') {
                $('.chkstaff').each(function() {
                    if ($(this).is(':checked')) {
                        $(this).prop('checked', false);
                        var state_name = $(this).attr('id');
                        var state = new STATE();
                        state.updateState(state_name, false);
                    }
                });
            }
        };

        /**
         * Get the last select user.
         * @param string IDs comma seperated list of users selected
         * @returns integer
         */
        this.getLastID = function(IDs) {
            var id_list = IDs.split(",");
            var last_elm = id_list.length - 1;
            return id_list[last_elm];
        };

        /**
         * Enable performance summary menu items if student.
         */
        this.checkRoles = function() {
            if ($('#roles').val().search("Student") == -1 && $('#roles').val().search("graduate") == -1) {
                $('#performancesummary2b').addClass('grey');
                $('#performancesummary2c').addClass('grey');
            } else {
                $('#performancesummary2b').removeClass('grey');
                $('#performancesummary2c').removeClass('grey');
            }
        };

        /**
         * Open a window to delete a user.
         */
        this.deleteUser = function() {
            var notice = window.open(config.cfgrootpath + "/delete/check_delete_user.php?id=" + $('#userID').val() + "","notice","width=450,height=180,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width/2-225, screen.height/2-90);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open the students performance summary.
         */
        this.performanceSummary = function() {
            if ($('#roles').val().search("Student") == -1 && $('#roles').val().search("graduate")) {
                var alert = new ALERT();
                alert.show('nonstudent');
            } else {
                window.open("../students/performance_summary.php?userID=" + this.getLastID($('#userID').val()), "_blank");
            }
        };

        /**
         * Add user id to selected user list.
         * @param integer ID user identifier
         * @param bool clearall if true list cleared if only on item selected.
         */
        this.addUserID = function(ID, clearall) {
            if (clearall) {
                $('#userID').val(',' + ID);
            } else {
                var cur_value = $('#userID').val() + ',' + ID;
                $('#userID').val(cur_value);
            }
        };

        /**
         * Remove selected user from the list.
         * @param integer ID user id
         */
        this.subUserID = function(ID) {
            var tmpuserID = ',' + ID;
            var new_value = $('#userID').val().replace(tmpuserID, '');
            $('#userID').val(new_value);
        };

        /**
         * Remove all highlighting.
         */
        this.clearAll = function() {
            $('.highlight').removeClass('highlight');
        };

        /**
         * Select a user from the list.
         * @param integer userID user identifier
         * @param integer lineID seleced line number
         * @param integer menuID menu identifier
         * @param string roles selected users role.
         * @param object evt event
         */
        this.selUser = function(userID, lineID, menuID, roles, evt) {
            $('#menu2a').hide();
            $('#menu' + menuID).show();

            if (evt.ctrlKey == false && evt.metaKey == false) {
                this.clearAll();
                $('#' + lineID).addClass('highlight');
                this.addUserID(userID, true);
            } else {
                if ($('#' + lineID).hasClass('highlight')) {
                    $('#' + lineID).removeClass('highlight');
                    this.subUserID(userID);
                } else {
                    $('#' + lineID).addClass('highlight');
                    this.addUserID(userID, false);
                }
            }
            $('#roles').val(roles);
            this.checkRoles();

            evt.stopPropagation();
        };

        /**
         * Display user details.
         * @param integer userID user identifier
         */
        this.profile = function(userID) {
            var surname = $('#dataset').attr('data-surname');
            var username = $('#dataset').attr('data-username');
            var sid = $('#dataset').attr('data-sid');
            var team = $('#dataset').attr('data-team');
            var module = $('#dataset').attr('data-module');
            var year = $('#dataset').attr('data-year');
            var students = $('#dataset').attr('data-students');
            var email = $('#dataset').attr('data-email');
            var tmp_surname = $('#dataset').attr('data-tmp_surname');
            var tmp_courseid = $('#dataset').attr('data-tmp_courseid');
            var tmp_yearid = $('#dataset').attr('data-tmp_yearid');

            document.location.href = 'details.php?search_surname=' + surname
            + '&search_username=' + username + '&student_id=' + sid
            + '&moduleID=' + team + '&module=' + module + '&calendar_year=' + year
            + '&students=' + students + '&submit=Search&userID=' + userID + '&email=' + email
            + '&tmp_surname=' + tmp_surname + '&tmp_courseID=' + tmp_courseid
            + '&tmp_yearID=' + tmp_yearid;
        };

    }
});
