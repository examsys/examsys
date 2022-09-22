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
// Question link functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['alert', 'jquery'], function(ALERT, $) {
    return function () {
        /**
         * Validate the form before submission.
         * @returns bool
         */
        this.checkForm = function() {
            var checkOption = $('input:radio[name=property_id]:checked').val();

            if (typeof checkOption == 'undefined') {
                var alert = new ALERT();
                alert.notification("validateform");
                return false;
            }
            $('#working').show();
            return true;
        };

        /**
         * Get the selected learning outcomes
         * @returns string
         */
        this.getSelectedOutcomes = function() {
            var outcomes = {};
            $('.check_type:checked').each(function() {
                var ids = $(this).data('ids');
                if (ids != '') {
                    ids = ids.toString().split(',');
                    for (var i = 0; i < ids.length; i++) {
                        outcomes[ids[i].toString()] = $(this).val();
                    }
                }
            });
            return JSON.stringify(outcomes);
        };
    }
});