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
// Updater functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2017 The University of Nottingham
//
define(['alert', 'jquery'], function(ALERT, $) {
    return function () {
        /**
         * Resize user answers list to fit window.
         */
        this.resizeList = function () {
            var winW = 630, winH = 460;
            if (document.body && document.body.offsetWidth) {
                winW = document.body.offsetWidth;
                winH = document.body.offsetHeight;
            }
            if (document.compatMode == 'CSS1Compat' && document.documentElement && document.documentElement.offsetWidth) {
                winW = document.documentElement.offsetWidth;
                winH = document.documentElement.offsetHeight;
            }
            if (window.innerWidth && window.innerHeight) {
                winW = window.innerWidth;
                winH = window.innerHeight;
            }
            winH -= 140;
            $('#list').height(winH);
        };

        /**
         * Handle error message.
         */
        this.doError = function () {
            var alert = new ALERT();
            alert.show('saveerror');
        };

        /**
         * Save all mark changes to the database.
         */
        this.saveAllRows = function() {
            var scope = this;
            var alert = new ALERT();
            $('.save-row').each(function () {
                var logID = $(this).data('logid');
                var newMark = $('input[name=mark_' + logID + ']:checked').val();
                var reason = $('#reason_' + logID).val();
                var logType = $('#log_type_' + logID).val();
                var userID = $('#user_id_' + logID).val();
                if (typeof newMark == 'undefined') {
                    alert.show('nomarkmsg');
                } else {
                    var row = $(this).parents('tr');
                    $.post('../ajax/reports/save_enhancedcalc_override.php',
                        {
                            log_id: logID,
                            user_id: userID,
                            q_id: $('#q_id').val(),
                            paper_id: $('#paper_id').val(),
                            marker_id: $('#marker_id').val(),
                            mark_type: newMark,
                            reason: reason,
                            log: logType
                        },
                        function (data) {
                            if (data != 'OK') {
                                alert.show('saveerror');
                                return false;
                            }
                            row.addClass('overridden').effect("highlight", {}, 1500);
                        }
                    ).fail(scope.doError);
                }
            });
        };
    }
});
