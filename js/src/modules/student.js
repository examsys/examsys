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
// Student functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
    return function() {
        /**
         * Change academic year displayed.
         * @param integer toShow year to display
         * @param array sessions array of available academic sessions
         */
        this.switchYear = function(toShow, sessions) {
            var years = JSON.parse(sessions);
            for (var i = 0; i < years.length; i++) {
                var target = $('#papers-' + years[i]);
                var link = $('#button-' + years[i]);
                if (years[i] == toShow) {
                    target.show();
                    link.css({'background-color': '#1E3C7B'});
                } else {
                    target.hide();
                    link.css({'background-color': '#517DBF'});
                }
            }
        };
    }
});
