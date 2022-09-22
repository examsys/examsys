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
// Mapping functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define([], function() {
    return function() {
        /**
         * Open window to map objectives to a question.
         * @param integer qNo question number in paper
         * @param integer pid paper id
         * @param integer qid question id
         * @param integer session academic year
         */
        this.mapQuestion = function(qNo, pid, qid, session) {
            var mapWindow = window.open('./map_question.php?qNo=' + qNo + '&paperID=' + pid + '&q_id=' + qid + '&session=' + session, "",'height=' + (screen.height - 300) + ',width=' + (screen.width - 300) + ',scrollbars=yes,resizable=yes,statusbar=no');
            mapWindow.moveTo(100,100);
        };
    }
});
