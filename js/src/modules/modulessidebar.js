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
// Modules list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['alert', 'rogoconfig', 'list', 'jquery'], function(ALERT, config, LIST, $) {
    return function() {
        /**
         * Initialise module sidebar.
         */
        this.init = function() {
            var scope = this;
            var list = new LIST();
            list.init();

            $('.sms').click(function() {
                var imageid = "syncimage" + $(this).attr('id');
                $(this).append('<img id="' + imageid + '" src="../artwork/working.gif" class="busyicon" />');
                var alert = new ALERT();
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: "post",
                    data: {session: $(this).attr('data-session'), id: $(this).attr('data-id')},
                    dataType: "json",
                    success: function (data) {
                        if (data['response'] == 'ERROR') {
                            alert.notification('syncerror');
                        }
                    },
                    error: function (xhr, textStatus) {
                        alert.plain(textStatus);
                    },
                    complete: function () {
                        $('#' + imageid).hide();
                    },
                });
            });

            $(".editmodule").click(function() {
                list.edit('./edit_module.php?moduleid=', $('#lineID').val());
            });

            $(".deletemodule").click(function() {
                scope.deleteModule();
            });

            $(".modulecohort").click(function() {
                scope.studentCohort();
            });

            $(".jumpmodule").click(function() {
                scope.jumpToModule();
            });
        };

        /**
         * Display module index screen.
         */
        this.jumpToModule = function() {
            window.location = config.cfgrootpath + '/module/index.php?module=' + $('#lineID').val();
        };

        /**
         * Display module student cohort.
         */
        this.studentCohort = function() {
            window.location = config.cfgrootpath + '/users/search.php?search_surname=&search_username=&student_id=&module=' + $('#lineID').val() + '&calendar_year=<?php echo $current_session ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_courseID=&tmp_yearID=';
        };

        /**
         * Open window to delete module.
         */
        this.deleteModule = function() {
            var notice=window.open("../delete/check_delete_module.php?idMod=" + $('#lineID').val() + "","notice","width=450,height=180,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 225, screen.height / 2 - 90);
            if (window.focus) {
                notice.focus();
            }
        };
    }
});