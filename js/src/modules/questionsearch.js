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
// Question search
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['menu', 'state', 'jquery', 'jqueryui', 'jquerytablesorter'], function(MENU, STATE, $) {
    return function () {
        /**
         * Initialise question search.
         * @param string datetime datetime format
         */
        this.init = function (datetime) {
            var scope = this;
            var menu = new MENU();

            if ($("#maindata").find("tr").size() > 1) {
                $("#maindata").tablesorter({
                    dateFormat: datetime,
                    sortList: [[0,0]]
                });
            }

            $('.statechange').change(function() {
                var state = new STATE()
                state.updateDropdownState(this, $(this).attr('data-type'));
            });

            $('.q').dblclick(function() {
                scope.ed();
            });

            $("tr[id^=l]").each(function(){
                $(this).click(function(e) {
                    var questionid = $(this).attr('data-qid');
                    var qtype = $(this).attr('data-qtype');
                    var lineid = $(this).attr('data-dispno');
                    if ($(this).attr('data-seltype') == "Q") {
                        scope.selQ(questionid, qtype, lineid, e);
                    } else {
                        scope.selL(questionid, qtype, lineid, e);
                    }
                });
            });

            $('#preview').click(function() {
                scope.previewQ();
            });

            $('#delete').click(function() {
                scope.deleteQuestion();
            });

            $('#information').click(function() {
                scope.questionInfo();
            });

            $('#copy').click(function() {
                scope.copyToPaper();
            });

            $('#link').click(function() {
                scope.linkToPaper();
            });

            $('#edit').click(function() {
                scope.ed();
            });

            $('.metadata').click(function() {
                menu.updateMenu(9);
            });

            $('.sections').click(function() {
                menu.updateMenu(8);
            });

            $('.modified').click(function() {
                menu.updateMenu(6);
            });

            $('.radio').click(function() {
                scope.selectDateRadio();
            });
        };

        /**
         * Display edit question screen.
         */
        this.ed = function() {
            var loc = '../question/edit/index.php?q_id=' + this.getLastID($('#questionID').val());
            if ($('#qType').val() == 'random' || $('#qType').val() == 'keyword_based') {
                loc += '&type=' + $('#qType').val();
            }
            if ($('#team').val() != '') {
                loc += '&team=' + $('#team').val();
            }
            if ($('#keyword').val() != '') {
                loc += '&keyword=' + $('#keyword').val();
            }
            document.location = loc;
        };

        /**
         * Get the last question selected.
         * @param string IDs comma seperated list of selected questions.
         * @returns integer
         */
        this.getLastID = function(IDs) {
            var id_list = IDs.split(",");
            var last_elm = id_list.length - 1;

            return id_list[last_elm];
        };

        /**
         * Add a question to the selected list
         * @param string IDs comma seperated list of selected questions.
         * @param bool clearall false if only one question selected
         */
        this.addQID = function(qID, clearall) {
            if (clearall) {
                $('#questionID').val(',' + qID);
            } else {
                $('#questionID').val($('#questionID').val() + ',' + qID);
            }
        };

        /**
         * Highight the selected question.
         * @param integer questionID question id
         * @param string qType question type
         * @param integer lineID line id
         * @param object evt event
         */
        this.highlight_line = function(questionID, qType, lineID, evt) {
            if (evt.ctrlKey == false && evt.metaKey == false) {
                this.clearAll();
                $('#l' + lineID).addClass('highlight');
                this.addQID(questionID, true);
            } else {
                if ($('#l' + lineID).hasClass('highlight')) {
                    $('#l' + lineID).removeClass('highlight');
                    this.subQID(questionID);
                } else {
                    $('#l' + lineID).addClass('highlight');
                    this.addQID(questionID, false);
                }
            }
            $('#qType').val(qType);
            $('#oldQuestionID').val(lineID);

            if (evt != null) {
                evt.cancelBubble = true;
            }
        };

        /**
         * Action to take when question selected.
         * @param integer questionID question id
         * @param string qType question type
         * @param integer lineID line id
         * @param object evt event
         */
        this.selQ = function(questionID, qType, lineID, evt) {
            $('#menu2a').hide();
            $('#menu2b').show();
            $('#deleteitem').css('display', 'none');
            this.highlight_line(questionID, qType, lineID, evt);
        };

        /**
         * Action to take when line selected.
         * @param integer questionID question id
         * @param string qType question type
         * @param integer lineID line id
         * @param object evt event
         */
        this.selL = function(questionID, qType, lineID, evt) {
            $('#menu2a').hide();
            $('#menu2b').show();
            $('#deleteitem').css('display', 'block');
            this.highlight_line(questionID, qType, lineID, evt);
        };

        /**
         * Remove all highlighting.
         */
        this.clearAll = function() {
            $('.highlight').removeClass('highlight');
        };

        /**
         * Remove questions for stored question list.
         * @param integer qID question id.
         */
        this.subQID = function(qID) {
            var tmpq = ',' + qID;
            $('#questionID').val($('#questionID').val().replace(tmpq, ''));
        };

        /**
         * Open a window to preview the question.
         */
        this.previewQ = function() {
            window.open("../question/view_question.php?q_id=" + this.getLastID($('#questionID').val()) + "","preview","left=10,top=10,width=800,height=600,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
        };

        /**
         * Open a window to delete a question
         */
        this.deleteQuestion = function() {
            var notice = window.open("../delete/check_delete_q_original.php?q_id=" + $('#questionID').val() + "&divID=" + $('#oldQuestionID').val() + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250, screen.height / 2 - 100);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open a window to display question information.
         */
        this.questionInfo = function() {
            var notice = window.open("../question/info.php?q_id=" + this.getLastID($('#questionID').val()) + "","question_info","width=700,height=600,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250, screen.height / 2 - 250);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open a window to copy a question onto a paper.
         */
        this.copyToPaper = function() {
            var url = "../question/copy_onto_paper.php?q_id=" + $('#questionID').val();
            if ($('#qType').val() != '' && $('#module').val()) {
                url += '&type=' + $('#qType').val() + '&module=' + $('#module').val();
            }
            var notice = window.open(url,"notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 300, 10);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Open a window to link a question to a paper.
         */
        this.linkToPaper = function() {
            var url = "../question/link_to_paper.php?q_id=" + $('#questionID').val();
            if ($('#qType').val() != '' && $('#module').val()) {
                url += '&type=' + $('#qType').val() + '&module=' + $('#module').val();
            }
            var notice = window.open(url,"linktopaper","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 300, 10);
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Action to take when specific date selected.
         */
        this.selectDateRadio = function() {
            $('#question_date5').attr('checked', true);
        };
    }
});
