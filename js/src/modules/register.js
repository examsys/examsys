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
// Register functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['alert', 'jquery'], function(ALERT, $) {
    return function () {
        /**
         * Validate the registration form before submission.
         * @returns bool
         */
        this.checkForm = function() {
            var username = $('#new_username').val();
            if (username.indexOf('_') !== -1) {
                var alert = new ALERT();
                alert.show('usernamechars');
                return false;
            }
            return true;
        };
    }
});