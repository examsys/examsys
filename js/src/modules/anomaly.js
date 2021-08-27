// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
//
// Anomaly functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2021 The University of Nottingham
//
//
define(['jquery'], function($) {
    return function () {
        /**
         * Log an anomaly
         * @param int paperid the paper
         * @param int screen the screen number
         * @param int previous the last heartbeat time
         * @param int current the latest hearbeat time
         */
        this.log = function(paperid, screen, previous, current) {
            $.post("../ajax/paper/anomaly.php",
                {
                    paperID: paperid,
                    screen: screen,
                    previous: previous,
                    current: current
                });
        };
    }
});
