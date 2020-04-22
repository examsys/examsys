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

// Help launcher functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
    return function() {
        this.isMenu = false;
        this.overpopupmenu = false;
        this.clockID = 0;

        /**
         * Mouse select event action
         * @param object e event
         * @returns bool
         */
        this.mouseSelect = function() {
            if (this.isMenu) {
                if (this.overpopupmenu == false) {
                    this.isMenu = false;
                    this.overpopupmenu = false;
                    $('#menudiv').hide();
                    return true;
                }
                return true;
            }
        };

        /**
         * Display overlay menu
         * @param integer tmpUserID user menu relates to
         * @param integer paperID paper menu relates to
         * @param bool showExtension if true time extension option available
         * @param bool remote true if remote summative
         * @param object e event
         * @returns bool
         */
        this.popMenu = function(tmpUserID, paperID, showExtension, remote, e) {
            if ($('#old_highlightID').val() != '') {
                $('#l' + $('#old_highlightID').val()).css('background-color', 'white');
            }

            $('#old_highlightID').val(paperID + '_' + tmpUserID);
            $('#old_highlightColor').val( $('#l' + paperID + '_' + tmpUserID).css('background-color') );

            $('#l' + paperID + '_' + tmpUserID).css('background-color', '#FFBD69');

            if (!e) {
                e = window.event;
            }
            var currentX = e.clientX;
            var currentY = e.clientY;
            var scrOfX = $('body,html').scrollLeft();
            var scrOfY = $('body,html').scrollTop();

            $('#userID').val(tmpUserID);
            $('#paperID').val(paperID);
            $('#remote').val(remote);

            var top_pos = currentY + scrOfY;

            if (top_pos > ($(window).height() + scrOfY - 130)) {
                top_pos = $(window).height() + scrOfY - 130;
            }

            if (showExtension) {
                $('.menu-time').show();
            } else {
                $('.menu-time').hide();
            }

            $('#menudiv').css('left', currentX + scrOfX);
            $('#menudiv').css('top', top_pos);

            $('#menudiv').show();

            this.isMenu = true;
            return false;
        };

        /**
         * Display callout overlay
         * @param integer cellID selector related to overlay
         * @param string displayTxt message to display in overlay
         */
        this.showCallout = function(cellID, displayTxt) {
            var p = $('#p' + cellID);
            var position = p.position();

            var left_pos = position.left;
            if (left_pos + 302 > $(window).width()) {
                left_pos = $(window).width() - 302;
            }
            $('#callout').css('left', left_pos);
            $('#callout').css('top', position.top + p.height() + 12);

            $('#calloutTxt').text(displayTxt);
            $('#callout').show();
        };

        /**
         * Hide the overlay callout.
         */
        this.hideCallout = function() {
            $('#callout').hide();
        };

        /**
         * Update the invigilator clock.
         */
        this.UpdateClock = function() {
            var scope = this;
            if (this.clockID) {
                clearTimeout(this.clockID);
                this.clockID = 0;
            }
            var tDate = new Date();
            $('.theTime').text(((tDate.getHours() < 10) ? "0" : "") + tDate.getHours() +
                ((tDate.getMinutes() < 10) ? ":0" : ":") + tDate.getMinutes() +
                ((tDate.getSeconds() < 10) ? ":0" : ":") + tDate.getSeconds());
            this.clockID = setTimeout(function(){ scope.UpdateClock() }, 1000);
        };

        /**
         * Start the invigilator clock.
         */
        this.StartClock = function() {
            var scope = this;
            this.clockID = setTimeout(function(){ scope.UpdateClock() }, 500);
        };

        /**
         * Clear the invigilator clock.
         */
        this.KillClock = function() {
            if (this.clockID) {
                clearTimeout(this.clockID);
                this.clockID = 0;
            }
        };

        /**
         * Open student note window.
         */
        this.newStudentNote = function() {
            $('#menudiv').hide();
            var studentnote = window.open("new_student_note.php?userID=" + $('#userID').val() + "&paperID=" + $('#paperID').val() + "&remote=" + $('#remote').val() + "", "studentnote", "width=650,height=430,left=" + (screen.width / 2 - 300) + ",top=" + (screen.height / 2 - 200) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

            if (window.focus) {
                studentnote.focus();
            }
        };

        /**
         * Add toilet break flag to student.
         */
        this.newToiletBreak = function() {
            var scope = this;
            $('#menudiv').hide();
            $.post("../ajax/invigilator/toilet_break.php",
                {
                    userID:$('#userID').val(),
                    paperID:$('#paperID').val()
                },
                function() {
                    scope.refreshCohortList($('#paperID').val(), $('#remote').val());
                });
        };

        /**
         * Open unfinish exam window.
         */
        this.unfinishExam = function() {
            $('#menudiv').hide();
            var unfinish = window.open("check_unfinish_exam.php?userID=" + $('#userID').val() + "&paperID=" + $('#paperID').val() + "&remote=" + $('#remote').val() + "", "unfinish", "width=450,height=200,left=" + (screen.width / 2 - 275) + ",top=" + (screen.height / 2 - 100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

            if (window.focus) {
                unfinish.focus();
            }
        };

        /**
         * Refresh the cohort list.
         * @param integer paperID the paper id
         * @param boolean remote flag to indicate remote summative
         */
        this.refreshCohortList = function(paperID, remote) {
            var dataSource = "../ajax/invigilator/refresh_cohort_list.php?paperID=" + paperID + '&remote=' + remote;

            $("#cohortlist_" + paperID).load(dataSource);
        };

        /**
         * Open paper note window.
         * @param integer paperID the paper id
         * @param boolean remote flag to indicate remote summative
         */
        this.newPaperNote = function(paperID, remote) {
            var papernote = window.open("new_paper_note.php?paperID=" + paperID + "&remote=" + remote+ "","papernote","width=650,height=410,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                papernote.focus();
            }
        };

        /**
         * View the rubric for this paper.
         * @param integer paperID the paper id
         */
        this.viewRubric = function(paperID) {
            $('#opaque').show();
            $('#rubric_' + paperID).show();
        };

        /**
         * Open the extend time window.
         */
        this.extendTime = function() {
            $('#menudiv').hide();
            var papernote = window.open("extend_time.php?paperID=" + $('#paperID').val() + "&userID=" + $('#userID').val(), "extendtime", "width=250,height=150,left=" + (screen.width / 2 - 300) + ",top=" + (screen.height / 2 - 200) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

            if (window.focus) {
                papernote.focus();
            }
        };

        /**
         * Resize the cohort list based on window size.
         */
        this.resizeLists = function() {
            var myHeight = $(window).height() - 230;
            var mysheet = document.styleSheets[0];
            var totalrules = mysheet.cssRules ? mysheet.cssRules.length : mysheet.rules.length
            if (mysheet.deleteRule) { //if Firefox
                mysheet.insertRule(".cohortlist {height:" + myHeight + "px; overflow:auto}", totalrules);
                mysheet.insertRule(".clarifymsgtbl {height:" + myHeight + "px; overflow:auto}", totalrules);
            } else if (mysheet.removeRule) { //else if IE
                document.styleSheets[0].addRule(".cohortlist", "height:" + myHeight + "px; overflow:auto");
                document.styleSheets[0].addRule(".clarifymsgtbl", "height:" + myHeight + "px; overflow:auto");
            }
        };

        /**
         * Check for exam announcements and display them to the invigilator.
         */
        this.clarifyMethod = function() {
             $('.tab').each(function() {
                var paperid = $(this).attr('data-id');
                $.get("check_exam_announcements.php", {paperID:paperid}, function(data) {
                    var tmp = document.createElement("DIV");
                    tmp.innerHTML = data;
                    var stripped = tmp.textContent || tmp.innerText || "";
                    if ($('#' + paperid).html() != stripped) {
                        $('#msg' + paperid).html(data);
                        $('#store_' + paperid).html(stripped);
                        $('#msg' + paperid).effect("highlight", {}, 20000);
                    }
                });
            });
        };

        /**
         * Switch between paper tabs.
         * @param string tab selector for paper tab
         * @returns bool
         */
        this.changeTab = function(tab) {
            if (!tab.parent().hasClass('disabled')) {
                if (!tab.parent().hasClass('on')) {
                    $('.tab-area').hide();
                    $('.tabs li').each(function () {
                        $(this).removeClass('on');
                    });
                    tab.parent().addClass('on');

                    var id = tab.attr('rel');
                    $('#' + id).fadeIn();
                }
            }

            return false;
        };
    }
});
