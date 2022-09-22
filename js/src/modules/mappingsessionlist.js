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
// Mapping session list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['jquery'], function($) {
    return function() {
        /**
         * Select a session.
         * @param integer divID list id
         * @param integer identifier session id
         * @param integer session academic session
         * @param string VLE mapping system used
         * @param ojbet evt event
         */
        this.selSession = function(divID, identifier, session, VLE, evt) {
            var tmp_ID = $('#divID').val();
            if (tmp_ID != '') {
                $('#' + tmp_ID).css('background-color', 'white');
            }

            if (VLE != '') {
                $('#menu1a').hide();
                $('#menu1c').show();
            } else {
                $('#menu1a').hide();
                $('#menu1b').show();
            }

            $('#divID').val(divID);

            $('#identifier').val(identifier);
            $('#session').val(session);
            $('#VLE').val(VLE);

            $('#' + divID).css('background-color', '#FFBD69');
            evt.cancelBubble = true;
        };
    }
});