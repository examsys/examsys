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
// Paper question leadin validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['alert', 'jquery', 'jqueryui'], function(ALERT, $) {
    return function () {
        /**
         * Save marks.
         * @param object el element
         * @param object e event
         */
        this.updateMark = function (el, e) {
            e.preventDefault();

            this.id = $(el).data('id');
            this.action = $(el).attr('id');
            var group = $(el).closest('.student-answer-block');
            var reminders = new Array();

            group.find('.reminder:checked').each(function () {
                reminders.push($(this).val());
            });
            reminders = reminders.join('|');

            var mark = $('#mark' + this.id).val();
            var comment = $('#comment' + this.id).val();
            var scope = this;
            $.post('../ajax/reports/save_textbox_marks.php',
                {
                    paper_id: $('#paper_id').val(),
                    q_id: $('#q_id').val(),
                    log_id: $('#logrec' + scope.id).val(),
                    marker_id: $('#marker_id').val(),
                    mark: mark,
                    phase: $('#phase').val(),
                    log: $('#log' + scope.id).val(),
                    user_id: $('#username' + scope.id).val(),
                    comments: comment,
                    reminders: reminders
                },
                function(data) {
                    if (data != 'OK') {
                        $('#save_fail_message').show();
                        var alert = new ALERT();
                        alert.notification('saveerror');
                        return false;
                    } else {
                        $('#save_fail_message').hide();
                        $('#save_message').show().delay(800).slideUp('slow');
                    }

                    if ($('#mark' + scope.id).val() == 'NULL') {
                        $('#ans_' + scope.id).closest('.student-answer-block').removeClass('marked');
                    } else {
                        $('#ans_' + scope.id).closest('.student-answer-block').addClass('marked');
                    }

                    if (scope.action.indexOf('next') > -1) {
                        $('#ans_' + scope.id).closest('.student-answer-block').hide();
                        $('#ans_' + (++scope.id)).closest('.student-answer-block').show();
                    } else if (scope.action.indexOf('prev') > -1) {
                        $('#ans_' + scope.id).closest('.student-answer-block').hide();
                        $('#ans_' + (--scope.id)).closest('.student-answer-block').show();
                    } else if (scope.action.indexOf('finish') > -1) {
                        var params = scope.getURLParams();
                        var phase = params.phase;
                        var startdate = params.startdate;
                        var enddate = params.enddate;
                        var paperid = params.paperID;
                        var repcourse = params.repcourse;
                        var baseparams = 'action=mark&repmodule=&repcourse=%&sortby=name&module=&folder=&percent=100&absent=0&studentsonly=1&ordering=asc' +
                            '&meta1=First%20names=%&meta2=Forename=%&meta3=Group=%&meta4=Seminar%20Group=%&meta5=surname=%&meta6=Tutor%20Group=%&phase=';
                        var baseurl = '../reports/textbox_select_q.php?';
                        var extraParams = phase + '&repcourse=' + repcourse + '&startdate=' + startdate + '&enddate=' + enddate + '&paperID=' + paperid;
                        var textbox_select_q_url = baseurl + baseparams + extraParams;
                        window.location.replace(textbox_select_q_url);
                    }
                }
            ).fail(this.doError);
        };

        /**
         * Save failure error handler.
         */
        this.doError = function () {
            $('#save_fail_message').show();
            var alert = new ALERT();
            alert.notification('saveerror');
        };

        /**
         * Get an array of URL parameters
         * @returns array URL parameter name (array key) and value.
         */
        this.getURLParams = function () {

            var pageURL = window.location.search.substring(1);
            var params = pageURL.split('&');
            var paramsArray = [];

            for (var i = 0; i < params.length; i++) {
                var param = params[i].split('=');

                var paramName = param[0];
                var paramValue = param[1];
                paramsArray[paramName] = paramValue;
            }
            return paramsArray;
        };
    }
});
