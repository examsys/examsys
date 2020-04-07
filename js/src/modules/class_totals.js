// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Class total functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'helplauncher'], function($, HELPLAUNCHER) {
    return function () {
        /**
         * Initialise class totals report screen.
         */
        this.reportinit = function() {
            var scope = this;
            $('.latelink').click(function() {
                HELPLAUNCHER.launchHelp(221, 'staff');
            });

            $('#item1').click(function() {
                scope.viewScript();
            });

            $('#item2').click(function() {
                scope.viewFeedback();
            });

            $('#item3').click(function() {
                scope.viewProfile();
            });

            $('#item4').click(function() {
                scope.newStudentNote();
            });

            $('#item5').click(function() {
                scope.reassignScript();
            });

            $('#item6').click(function() {
                scope.resetTimer();
            });

            $('#item7').click(function() {
                scope.reassignLogLate();
            });

            $('.note').click(function(event) {
                scope.view('note', $(this).attr('data-id'), $(this).attr('data-paperid'), event);
            });

            $('.accessibility').click(function(event) {
                scope.view('access', $(this).attr('data-id'), $(this).attr('data-paperid'), event);
            });

            $('.icon16_active').click(function(event) {
                scope.view('break', $(this).attr('data-id'), $(this).attr('data-paperid'), event);
            });

            $('#emailmarks').click(function() {
                scope.popupEmailTemplate();
            });

            $('#publishmarksbutton').click(function() {
                scope.popupPublishMarks();
            });

            $('.popup_row_disabled').click(function() {
                $('#menudiv').hide();
            });

            $('#noteDiv').click(function() {
                $('#noteDiv').hide();
            });

            $('#accessDiv').click(function() {
                $('#accessDiv').hide();
            });

            $('#toiletDiv').click(function() {
                $('#toiletDiv').hide();
            });

            $("tr[id^=res]").click(function() {
                scope.metadataid = $(this).data("metadataid");
                scope.userid = $(this).data("userid");
                scope.papertype = $(this).data("papertype");
                scope.reassign = $(this).data("reassign");
                scope.late = $(this).data("late");
                scope.percent = $(this).data("percent");
                scope.cryptname = $(this).data("cryptname");
                scope.logtype = $(this).data("logtype");
                scope.paperid = $(this).data("paperid");
                if (scope.metadataid == '') {
                    $('#item1').removeClass('popup_row');
                    $('#item1').addClass('popup_row_disabled');
                    $('#item2').removeClass('popup_row');
                    $('#item2').addClass('popup_row_disabled');
                } else {
                    $('#item1').addClass('popup_row');
                    $('#item1').removeClass('popup_row_disabled');
                    $('#item2').addClass('popup_row');
                    $('#item2').removeClass('popup_row_disabled');
                }

                if (scope.reassign == 'y') {
                    $('#item5').addClass('popup_row');
                    $('#item5').removeClass('popup_row_disabled');

                    $('#item3').removeClass('popup_row');
                    $('#item3').addClass('popup_row_disabled');
                } else {
                    $('#item3').addClass('popup_row');
                    $('#item3').removeClass('popup_row_disabled');

                    $('#item5').removeClass('popup_row');
                    $('#item5').addClass('popup_row_disabled');
                }

                if (scope.late == 'y') {
                    $('#item7').addClass('popup_row');
                    $('#item7').removeClass('popup_row_disabled');
                } else {
                    $('#item7').removeClass('popup_row');
                    $('#item7').addClass('popup_row_disabled');
                }
            });
        };

        /**
         * View student exam script.
         */
        this.viewScript = function () {
            $('#menudiv').hide();
            if (this.metadataid != '') {
                var winwidth = screen.width - 80;
                var winheight = screen.height - 80;
                var url = "/paper/finish.php?id=" + this.cryptname + "&userID=" + this.userid + "&metadataID=" + this.metadataid + "&log_type=" + this.papertype;
                if (this.percent != undefined) {
                    url = url + "&percent=" + this.percent;
                }
                window.open(url, "paper", "width=" + winwidth + ",height=" + winheight + ",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            }
        };

        /**
         * View student objectives feedback.
         */
        this.viewFeedback = function () {
            $('#menudiv').hide();
            if (this.metadataid != '') {
                var winwidth = screen.width - 80;
                var winheight = screen.height - 80;
                window.open("../students/objectives_feedback.php?id=" + this.cryptname + "&userID=" + this.userid + "&metadataID=" + this.metadataid + "", "feedback", "width=" + winwidth + ",height=" + winheight + ",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            }
        };

        /**
         * View students note / students accessibility information / students toilet break information.
         * @param string type type of info to view
         * @param integer id users internal id
         * @param integer paperid paper id
         * @param object e event
         */
        this.view = function(type, id, paperid, e) {
            $("#menudiv").hide(), $("#noteDiv").hide(), $("#accessDiv").hide(), e || (e = window.event);
            var currentX = e.clientX,
                currentY = e.clientY,
                scrOfX = $(document).scrollLeft(),
                scrOfY = $(document).scrollTop();

            var dataSource = '';
            var selector = '';
            var div = '';
            switch (type) {
                case "break":
                    dataSource = "../ajax/reports/getToiletBreak.php?breakID=" + id;
                    selector = "#toiletMsg";
                    div = "#toiletDiv";
                    break;
                case "access":
                    dataSource = "../ajax/reports/getAccessibility.php?userID=" + id;
                    selector = "#accessMsg";
                    div = "#accessDiv";
                    break;
                default:
                    dataSource = "../ajax/reports/getNote.php?paperID=" + paperid + "&userID=" + id;
                    selector = "#noteMsg";
                    div = "#noteDiv";
            }

            $(selector).load(dataSource, function(responseTxt, statusTxt) {
                if ("success" == statusTxt) {
                    $(div).show(), $(div).css("left", currentX + scrOfX + 16 + "px");
                    var top_pos = currentY + scrOfY - 16;
                    top_pos > $(window).height() + scrOfY - 130 && (top_pos = $(window).height() + scrOfY - 130), $(div).css("top", top_pos + "px");
                }
            });

            e.stopPropagation();
        };

        /**
         * View students profile.
         */
        this.viewProfile = function() {
            $('#menudiv').hide();
            if (this.reassign == 'n') {
                window.top.location = '../users/details.php?&userID=' + this.userid;
            }
        };

        /**
         * Add a new note to the student.
         */
        this.newStudentNote = function() {
            $('#menudiv').hide();
            var note = window.open("../users/new_student_note.php?userID=" + this.userid + "&paperID=" + this.paperid + "&calling=class_totals","note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                note.focus();
            }
        };

        /**
         * Reassign exam script to a student.
         */
        this.reassignScript = function() {
            $('#menudiv').hide();
            if (this.reassign == 'y') {
                var reassign = window.open("/reports/check_reassign_script.php?userID=" + this.userid + "&paperID=" + this.paperid,"reassign","width=600,height=500,left="+(screen.width/2-300)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
                if (window.focus) {
                    reassign.focus();
                }
            }
        };

        /**
         * Open email class window.
         */
        this.popupEmailTemplate = function() {
            var winwidth = 785;
            var winheight = 550;
            var templatewin = window.open("/reports/emailtemplate.php","templatewin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            templatewin.moveTo(screen.width/2-350,screen.height/2-275);
        };

        /**
         * Open publish marks window.
         */
        this.popupPublishMarks = function() {
            var winwidth = 785;
            var winheight = 150;
            var templatewin = window.open("/reports/publishmarks.php","templatewin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            templatewin.moveTo(screen.width/2-390,screen.height/2-75);
        };

        /**
         * Reset exam timer for student.
         */
        this.resetTimer = function() {
            // Only allow reset of timer for Progress tests and Remote Summative exams.
            if (this.papertype == '1' || (this.papertype == '2' && $('#dataset').attr('data-remotesummative') == 1)) {
                $('#menudiv').hide();
                var reassign = window.open("/reports/check_reset_timer.php?userID=" + this.userid + "&paperID=" + this.paperid + "&metadataID=" + this.metadataid + "", "reassign", "width=550,height=200,left=" + (screen.width / 2 - 275) + ",top=" + (screen.height / 2 - 100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
                if (window.focus) {
                    reassign.focus();
                }
            }
        };

        /**
         * Open log late assignment window.
         */
        this.reassignLogLate = function() {
            $('#menudiv').hide();
            if (this.late == 'y') {
                var loglate = window.open("/reports/check_reassign_log_late.php?userID=" + this.userid + "&paperID=" + this.paperid + "&metadataID=" + this.metadataid + "&log_type=" + this.papertype + "","reassign","width=600,height=480,left="+(screen.width/2-300)+",top="+(screen.height/2-240)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
                if (window.focus) {
                    loglate.focus();
                }
            }
        };
    }
});
