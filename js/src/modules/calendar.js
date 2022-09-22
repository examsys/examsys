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
//
// Calendar functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
//
define(['jsxls', 'jquery'], function(jsxls, $) {
    return function () {
        /**
         * Display the paper summary information
         * @param string type paper type
         * @param string cellID selector for paper item
         * @param string start_time paper start time
         * @param string end_time paper end time
         * @param string duration paper duration
         * @param string labs comma seperated list of associated labs
         * @param string password papers access password
         * @param string timezone papers timezone
         * @param string metadata paper metadata
         */
        this.showCallout = function(type, cellID, start_time, end_time, duration, labs, password, timezone, metadata) {
            var lab_names = JSON.parse($('#dataset').attr('data-lab_names'));
            var p = $('#p' + cellID);
            var position = p.offset();

            var left_pos = position.left;
            if (left_pos + 302 > $(window).width()) {
                left_pos = $(window).width() - 302;
                $('.notch').css('left', '180px');
            } else {
                $('.notch').css('left', '20px');
            }

            var top_pos = position.top;
            if (top_pos - $(window).scrollTop() > ($(window).height() - 135)) {
                top_pos -= 135;
                $('.notch').hide();
            } else {
                $('.notch').show();
            }

            $('#callout').css('left', left_pos);
            $('#callout').css('top', top_pos + p.height() + 12);
            $('#duration').html(duration + ' ' + jsxls.lang_string['mins']);

            if (start_time == end_time) {
                $('#start_time2').html(start_time);
                $('#end_time2').html(end_time);
                $('#start_time_warning').show();
                $('#end_time_warning').show();
                $('#start_time_ok').hide();
                $('#end_time_ok').hide();
            } else {
                $('#start_time1').html(start_time);
                $('#end_time1').html(end_time);
                $('#start_time_ok').show();
                $('#end_time_ok').show();
                $('#start_time_warning').hide();
                $('#end_time_warning').hide();
            }

            // Type 4 is OSCE.
            if (duration == '' && type != '4') {
                $('#duration_warning').show();
                $('#duration_ok').hide();
            } else {
                $('#duration_ok').show();
                $('#duration_warning').hide();
            }

            if (timezone != $('#dataset').attr('data-timezone')) {
                $('#timezone').html(timezone);
                $('#timezone_row').show();
            } else {
                $('#timezone_row').hide();
            }

            var lab_html = '';
            if (labs == '' && password == '' && type != '4') {
                $('#lab_warning').show();
                $('#lab_ok').hide();
            } else {
                $('#lab_ok').show();
                $('#lab_warning').hide();
                if (labs != '') {
                    var lab_parts = labs.split(",");
                    $.each(lab_parts, function(key, value) {
                        if (lab_html == '') {
                            lab_html = lab_names[value];
                        } else {
                            lab_html += '<br />' + lab_names[value];
                        }
                    });
                }
            }
            $('#labs').html(lab_html);
            $('#password').html(password);
            if (password == '') {
                $('#pw_row').hide();
            } else {
                $('#pw_row').show();
            }
            $('#metadata').html(metadata);
            if (metadata == '') {
                $('#metadata_row').hide();
            } else {
                $('#metadata_row').show();
            }
            $('#callout').show();
        };

        /**
         * Display event information
         * @param string cellID selector for event item
         * @param string message the event message
         */
        this.showCallout2 = function(cellID, message) {
            var p = $('#p' + cellID);
            var position = p.offset();

            var left_pos = position.left;
            if (left_pos + 302 > $(window).width()) {
                left_pos = $(window).width() - 302;
                $('.notch').css('left', '180px');
            } else {
                $('.notch').css('left', '20px');
            }

            var top_pos = position.top;
            if (top_pos - $(window).scrollTop() > ($(window).height() - 80)) {
                top_pos -= 80;
                $('.notch').hide();
            } else {
                $('.notch').show();
            }
            $('#callout2').css('left', left_pos);
            $('#callout2').css('top', top_pos + p.height() + 12);
            $('#message').html(message);
            $('#callout2').show();
        };

        /**
         * Launch create new event window
         * @param integer id event date
         */
        this.newEvent = function(id) {
            var notice = window.open("add_event.php?default=" + id + "","event","width=800,height=500,left="+(screen.width/2-400)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Launch delete event window
         * @param integer eventID event id
         */
        this.deleteEvent = function(eventID) {
            var notice = window.open("../delete/check_delete_event.php?eventID=" + eventID + "","event","width=420,height=170,left="+(screen.width/2-210)+",top="+(screen.height/2-85)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };
    }
});
