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
// Review comments
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Open question edit screen on the comments tab.
         * @param integer qid question id
         * @param integer qno questiono number on paper
         */
        this.editQ = function(qid, qno) {
            var folder = $('#dataset').attr('data-folder');
            var module = $('#dataset').attr('data-module');
            var paperid = $('#dataset').attr('data-paperid');
            var type = $('#dataset').attr('data-type');
            location.href='../question/edit/index.php?q_id=' + qid + '&qNo=' + qno + '&paperID=' + paperid + '&folder=' + folder + '&module=' + module + '&calling=' + type + '_comments&scrOfY=' + $('#scrOfY').val() + '&tab=comments';
        };
    }
});
