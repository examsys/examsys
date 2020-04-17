// JavaScript Document
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
//
// Paper details helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'jsxls', 'state', 'sidebar', 'jquery'], function(config, jsxls, STATE, SIDEBAR, $) {
    return function() {
        /**
         * Initialise paper sidebar.
         */
        this.init = function() {
            var scope = this;
            var sidebar = new SIDEBAR();
            sidebar.init();
            this.paperid = $('#dataset').attr('data-paperid');
            this.module = $('#dataset').attr('data-module');
            this.folder = $('#dataset').attr('data-folder');

            $('.startpaper').click(function() {
                scope.startPaper($(this).attr('data-fullscreen'), $(this).attr('data-preview'));
            });

            $('.addquestions').click(function() {
                scope.addQuestions($(this).attr('data-dispno'), $(this).attr('data-screen'));
            });

            $('.edit').click(function() {
                scope.editQuestion();
            });

            $('#delete').click(function() {
                scope.deleteQuestion();
            });

            $('.killerq').click(function() {
                scope.killerq();
            });

            $('.deletepaper').click(function() {
                scope.deletePaper();
            });

            $('.retirepaper').click(function() {
                scope.retirePaper();
            });

            $('.copy').click(function() {
                scope.copyToPaper();
            });

            $('.link').click(function() {
                scope.linkToPaper();
            });

            $('.information').click(function() {
                scope.questionInfo();
            });

            $('.clarification').click(function() {
                scope.examClarification();
            });

            $('.stats').click(function() {
                scope.showAssStatsMenu();
            });

            $('#copypaper').click(function() {
                scope.showCopyMenu();
            });

            $('#copyfrompaper').click(function() {
                scope.showCopyFromMenu();
            });

            $(".changepapertype").change(function() {
                scope.changeButton();
            });

            $('#next_button').click(function() {
                scope.nextTable();
            });

            $('#checkcopy').submit(function() {
                scope.checkCopyForm();
            });

            $('#add_break').click(function() {
                scope.incScreen();
            });

            $('#direction').change(function() {
                scope.checkAll();
            });

            $('.reports').click(function(e) {
                scope.go($(this).attr('data-url'), e);
            });
            
            $('.studentcohort').click(function(e) {
                e.preventDefault();
                scope.students();
            });
        };

        /**
         * Open report
         * @param string url url of report
         * @param object evt event
         * @returns bool
         */
        this.go = function(url, evt) {
            var extra_data = '';
            if ($('#dataset').attr('data-papertype') == '3') {
                $('#stats_menu').hide();
                var completerpt = 0;
                if ($('#completerpt').is(':checked')) {
                    completerpt = 1;
                }
                window.location.href = url + "paperID=" + $('#paperID').val() + "&startdate=" + $('#start_year').val() + $('#start_month').val() + $('#start_day').val() + $('#start_hour').val() + $('#start_minute').val() + "00" + "&enddate=" + $('#end_year').val() + $('#end_month').val() + $('#end_day').val() + $('#end_hour').val() + $('#end_minute').val() + "00" + "&repcourse=" + $('#repcourse').val() + "&repyear=" + $('#repyear').val() + "&sortby=surname&module=" + this.module + "&folder=" + this.folder + "&complete=" + completerpt;
                evt.cancelBubble = true;
                return false;
            } else if ($('#dataset').attr('data-papertype') == '6') {
                for (var i = 1; i < $('#meta_no').val(); i++) {
                    var revmeta_ref = 'meta' + i;
                    extra_data += '&meta' + i + '=' +$('#' + revmeta_ref).val();
                }

                $('#stats_menu').hide();
                window.location.href = url + "paperID=" + $('#paperID').val() + "&startdate=" + $('#start_year').val() + $('#start_month').val() + $('#start_day').val() + $('#start_hour').val() + $('#start_minute').val() + "00" + "&enddate=" + $('#end_year').val() + $('#end_month').val() + $('#end_day').val() + $('#end_hour').val() + $('#end_minute').val() + "00" + "&repmodule=" + $('#repmodule').val() + "&repcourse=" + $('#repcourse').val() + extra_data + "&module=" + this.module;
            } else {
                for (var j = 1; j < $('#meta_no').val(); j++) {
                    var meta_ref = 'meta' + j;
                    extra_data += '&meta' + j + '=' + $('#' + meta_ref).val();
                }

                $('#stats_menu').hide();
                var absent = '&absent=0';
                if ($('#absent').is(':checked')) {
                    absent = '&absent=1';
                }

                var studentsonly = '&studentsonly=0';
                if ($('#studentsonly').is(':checked')) {
                    studentsonly = '&studentsonly=1';
                }
                window.location.href = url + "paperID=" + $('#paperID').val() + "&startdate=" + $('#start_year').val() + $('#start_month').val() + $('#start_day').val() + $('#start_hour').val() + $('#start_minute').val() + "00" + "&enddate=" + $('#end_year').val() + $('#end_month').val() + $('#end_day').val() + $('#end_hour').val() + $('#end_minute').val() + "00" + "&repmodule=" + $('#repmodule').val() + "&repcourse=" + $('#repcourse').val() + "&sortby=name&module=" + this.module + "&folder=" + this.folder + "&percent=" + $('#percent').val() + absent + studentsonly + "&ordering=" + $('#direction').val() + extra_data;

                evt.cancelBubble = true;
                return false;
            }
        };

        /**
         * Select all cohort option.
         */
        this.checkAll = function() {
            if ($('#direction')[0].selectedIndex == 0) {
                $("#percent").prop("selectedIndex", 7);
            }
        };

        /**
         * Display paper/question preview screen.
         * @param integer fullscreen diplay in full screen mode if 1
         * @param integer preview question preview if 1
         */
        this.startPaper = function(fullscreen, preview) {
            var urlMod = '';
            if (preview == 1) {
                urlMod = '&q_id=' + this.getLastID($('#questionID').val()) + '&qNo=' + $('#questionNo').val();
            }

            if ($('#dataset').attr('data-papertype') == '4') {
                window.open(config.cfgrootpath + "/osce/form.php?id=" + $('#dataset').attr('data-cryptname') + "&username=test","paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            } else if ($('#dataset').attr('data-papertype') == '6') {
                window.open(config.cfgrootpath + "/peer_review/form.php?id=" + $('#dataset').attr('data-cryptname'),"paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            } else {
                if (fullscreen == 0) {
                    window.open(config.cfgrootpath + "/paper/start.php?id=" + $('#dataset').attr('data-cryptname') + "&mode=preview" + urlMod,"paper","width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
                } else {
                    window.open(config.cfgrootpath + "/paper/start.php?id=" + $('#dataset').attr('data-cryptname') + "&mode=preview" + urlMod, "paper", "fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
                }
            }
        };

        /**
         * Get last question selected
         * @param string IDs comma seperated string of selected questions
         * @returns integer
         */
        this.getLastID = function(IDs) {
            var id_list = IDs.split(",");
            var last_elm = id_list.length - 1;

            return id_list[last_elm];
        };

        /**
         * Open question bank window
         * @param integer display next available question number on paper
         * @param integer screen_no last screen number on paper
         */
        this.addQuestions = function(display, screen_no) {
            var winH = screen.height - 100;
            var winW = screen.width - 80;
            var notice = window.open(config.cfgrootpath + "/question/add/add_questions_frame.php?paperID=" + this.paperid + "&module=" +this.module + "&folder=" + this.folder + "&scrOfY=" + $('#scrOfY').val() + "&display_pos=" + display + "&max_screen=" + screen_no + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Edit the selected question.
         */
        this.editQuestion = function() {
            var loc = config.cfgrootpath + '/question/edit/index.php?q_id=' + this.getLastID($('#questionID').val()) + '&qNo=' + $('#questionNo').val() + '&paperID=' + this.paperid + '&folder=' + this.folder + '&module=' +this.module + '&calling=paper&scrOfY=' + $('#scrOfY').val();
            if ($('#qType').val() == 'random' || $('#qType').val() == 'keyword_based') {
                loc += '&type=' + $('#qType').val();
            }
            document.location = loc;
        };

        /**
         * Open window to delete the selected question.
         */
        this.deleteQuestion = function() {
            var notice = window.open( config.cfgrootpath + "/delete/check_delete_q_pointer.php?questionID=" + $('#questionID').val() + "&pID=" + $('#pID').val() + "&paperID=" + this.paperid + "&module=" +this.module + "&folder=" + this.folder + "&scrOfY=" + $('#scrOfY').val() + "","notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250,screen.height / 2 - 105);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Set question as a killer question.
         */
        this.killerq = function() {
            var qID = this.getLastID($('#questionID').val());
            var qNumber = $('#questionNo').val();
            var url = '../ajax/paper/set_unset_killer_question.php';

            if ( $("#icon_" + qNumber).hasClass("killer_icon") ) {
                $("#icon_" + qNumber).removeClass("killer_icon");
            } else {
                $("#icon_" + qNumber).addClass("killer_icon");
            }

            $.post(url, { paperID: this.paperid, q_id: qID, qNumber: qNumber } );
        };

        /**
         * Open windoew to delete the paper.
         */
        this.deletePaper = function() {
            var notice = window.open(config.cfgrootpath + "/delete/check_delete_paper.php?paperID=" + this.paperid + "&module=" + this.module + "&folder=" + this.folder,"notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250,screen.height / 2 - 105);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open window to retire the paper.
         */
        this.retirePaper = function() {
            var notice = window.open(config.cfgrootpath + "/paper/check_retire_paper.php?paperID=" + this.paperid + "&module=" + this.module + "&folder=" + this.folder,"notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250,screen.height / 2 - 105);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Increment the number of screens on the paper.
         */
        this.incScreen = function() {
            var screenNo = $('#screenNo').val();
            screenNo++;
            window.location = config.cfgrootpath + '/paper/change_screen_no.php?questionID=' + this.getLastID($('#pID').val()) + '&paperID=' + this.paperid + '&screen=' + screenNo + '&display_pos=' + document.PapersMenu.current_pos.value + '&folder=' + this.folder + '&module=' + this.module + '&scrOfY=' + $('#scrOfY').val();
        };

        /**
         * Open window to copy the question on to a paper
         */
        this.copyToPaper = function() {
            var notice = window.open(config.cfgrootpath + "/question/copy_onto_paper.php?q_id=" + $('#questionID').val() + "","notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 300,10);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open window to link the question to a paper.
         */
        this.linkToPaper = function() {
            var notice = window.open(config.cfgrootpath + "/question/link_to_paper.php?q_id=" + $('#questionID').val() + "","linktopaper","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 300,10);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open the question info window.
         */
        this.questionInfo = function() {
            var notice = window.open(config.cfgrootpath + "/question/info.php?q_id=" + this.getLastID($('#questionID').val()) + "","notice","width=700,height=660,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 350,screen.height / 2 - 330);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open the exam clarification window for the question.
         */
        this.examClarification = function() {
            if ($('#qType').val() != 'info') {
                var notice = window.open(config.cfgrootpath + "/question/exam_clarification.php?paperID=" + this.paperid + "&q_id=" + this.getLastID($('#questionID').val()) + "&questionNo=" + $('#questionNo').val() + "&screenNo=" + $('#screenNo').val() + "","notice","width=800,height=450,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
                notice.moveTo(screen.width / 2 - 400,screen.height / 2 - 225);
                if (window.focus) {
                    notice.focus();
                }
            }
        };

        /**
         * Display stats menu overlay.
         */
        this.showAssStatsMenu = function() {
            $('#stats_menu').show()
        };

        /**
         * Display copy paper menu.
         */
        this.showCopyMenu = function() {
            $('#copy_submenu').show();
        };

        /**
         * Display copy from paper menu.
         */
        this.showCopyFromMenu = function() {
            $('#copy_from_submenu').show();
        };

        /**
         * Change copy paper buttons dependent on paper type selected.
         */
        this.changeButton = function() {
            if ($("#paper_type").val() == '2') {
                $('#next_button').show();
                $('#submit_button').hide();
            } else {
                $('#submit_button').show();
                $('#next_button').hide();
            }
        };

        /**
         * Validate paper copy form.
         */
        this.nextTable = function() {
            var paper_name = $('#new_paper').val();
            $.post("../ajax/paper/check_name.php", {name:paper_name}, function(data) {
                if (data == 'unique') {
                    $('#table1div').hide();
                    $('#table2div').show();
                    $('#new_paper').subClass('errfield');
                } else {
                    $('#new_paper').addClass('errfield');
                    alert(jsxls.lang_string['namewarning']);
                }
            });
        };

        /**
         * Validate form before submission.
         * @returns bool
         */
        this.checkCopyForm = function() {
            if ($("#paper_type").val() == '2') {
                if ($('#period').val() == '') {
                    alert (jsxls.lang_string['msg7']);
                    return false;
                }

                if ($('#duration_hours').val() == '' || $('#duration_mins').val() == '') {
                    alert (jsxls.lang_string['msg8']);
                    return false;
                }

                if ($('#cohort_size').val() == '') {
                    alert (jsxls.lang_string['msg9']);
                    return false;
                }
            }
        };

        /**
         * Open the list of students for the paper.
         */
        this.students = function() {
            var url = config.cfgrootpath + "/users/paper.php?paperID=" + this.paperid;
            var left = screen.width / 2 - 431;
            var top = screen.height / 2 - 325;
            var properties = "width=888,height=650,left=" + left + ",top=" + top + ",scrollbars=no,toolbar=no,location=no,"
                    + "directories=no,status=no,menubar=no,resizable";
            var notice = window.open(url, "properties", properties);
            if (window.focus) {
                notice.focus();
            }
        };
    }
});
