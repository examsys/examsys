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
// Create new paper form functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'alert', 'jquery', 'jqueryui'], function(jsxls, ALERT, $) {
    return function () {
        /**
         * Mouse over paper type option actions
         * @param string id paper type
         */
        this.over = function(id) {
            if (id != $('#paper_type').val()) {
                $('#' + id).css('background-color', '#FFE7A2');
            }
            switch (id) {
                case 'formative':
                    $('#description').html(jsxls.lang_string['description0']);
                    break;
                case 'progress':
                    $('#description').html(jsxls.lang_string['description1']);
                    break;
                case 'summative':
                    $('#description').html(jsxls.lang_string['description2']);
                    break;
                case 'survey':
                    $('#description').html(jsxls.lang_string['description3']);
                    break;
                case 'osce':
                    $('#description').html(jsxls.lang_string['description4']);
                    break;
                case 'offline':
                    $('#description').html(jsxls.lang_string['description5']);
                    break;
                case 'peer_review':
                    $('#description').html(jsxls.lang_string['description6']);
                    break;
            }
        };

        /**
         * Mouse out paper type option actions
         * @param string id paper type
         */
        this.out = function(id) {
            if (id != $('#paper_type').val()) {
                $('#' + id).css('background-color', 'white');
            }
        };

        /**
         * Mouse active paper type option actions
         * @param string id paper type
         */
        this.activate = function(id) {
            if (id !== undefined) {
                $('#formative').css('background-color', 'white');
                $('#progress').css('background-color', 'white');
                $('#summative').css('background-color', 'white');
                $('#survey').css('background-color', 'white');
                $('#osce').css('background-color', 'white');
                $('#offline').css('background-color', 'white');

                $('#' + id).css('background-color', '#FFBD69');
                $('#paper_type').val(id);
            }
        };

        /**
         * Validate the first screen.
         * @returns bool
         */
        this.checkForm = function() {
            var alert = new ALERT();
            if ($('#paper_type').val() == '') {
                alert.notification("msg1");
                return false;
            }
            if ($('#dataset').attr('data-warn') == '1' && $('#paper_type').val() == 'summative') {
                return alert.show("msg3");
            }
            return true;
        };

        /**
         * Construct a comma seperated list of modules the paper is to be availble to.
         * @returns string
         */
        this.moduleList = function() {
            var module_no = $('#module_no').val();
            var moduleList = '';
            for (var i = 0; i < module_no; i++) {
                var objectID = 'mod' + i;
                if ($('#' + objectID).prop('checked')) {
                    if (moduleList == '') {
                        moduleList = $('#' + objectID).val();
                    } else {
                        moduleList += ',' + $('#' + objectID).val();
                    }
                }
            }
            return moduleList;
        };

        /**
         * Validate the second screen.
         * @returns {boolean}
         */
        this.step2checkForm = function() {
            var alert = new ALERT();

            if (this.moduleList() == '') {
                alert.notification('msg4');
                return false;
            }
            // Check that the end datetime is greater than the start datetime
            var from = String($('#fyear').val()) + String($('#fmonth').val()) + String($('#fday').val()) + String($('#ftime').val());
            var to = String($('#tyear').val()) + String($('#tmonth').val()) + String($('#tday').val()) + String($('#ttime').val());
            if (to <= from) {
                alert.notification('msg10');
                return false;
            }
            return true;
        };

        /**
         * Validate the summative central control form.
         * @returns {boolean}
         */
        this.step2checkSummativeForm = function() {
            var alert = new ALERT();
            if ($('#period').val() == '') {
                alert.notification('msg7');
                return false;
            }

            if ($('#duration_hours').val() == '' || $('#duration_mins').val() == '') {
                alert.notification('msg8');
                return false;
            }

            if ($('#cohort_size').val() == '') {
                alert.notification('msg9');
                return false;
            }

            if (this.moduleList() == '') {
                alert.notification('msg4');
                return false;
            }
            return true;
        };

        /**
         * Create the paper.
         */
        this.createpaper = function() {
            var alert = new ALERT();
            $.ajax({
                url: 'new_paper3.php',
                type: "post",
                data: $('#myform').serialize(),
                dataType: "json",
                success: function (data) {
                    if (data[0] == 'SUCCESS') {
                        if (data[3] != '') {
                            window.opener.location = "details.php?paperID=" + data[1] + "&folder=" + data[3];
                        } else {
                            window.opener.location = "details.php?paperID=" + data[1] + "&module=" + data[2];
                        }
                        window.close();
                    }
                },
                error: function (xhr, textStatus) {
                    alert.plain(textStatus);
                },
            });
        };
    }
});


