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
// Reference tab functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Initialise reference panel.
         */
        this.init = function() {
            var scope = this;
            this.changeRef($('#refpane').val());
            $('.refhead').click(function() {
                scope.changeRef($(this).attr('data-id'));
            });
        };

        /**
         * Change reference tab.
         * @param integer refID reference id
         */
        this.changeRef = function (refID) {
            $('#refpane').val(refID);
            var refcount = $('#paper').attr('data-refcount');
            for (var i = 0; i < refcount; i++) {
                if (i == refID) {
                    $('#framecontent' + i).show();
                } else {
                    $('#framecontent' + i).hide();
                }
            }
        };
    }
});
