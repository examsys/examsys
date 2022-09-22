// JavaScript Document
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
// Paper type page helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Open create new paper window.
         */
        this.newPaper = function() {
            var notice = window.open("../paper/new_paper1.php?module=" + $('#dataset').attr('data-module') + "&type=" +  $('#dataset').attr('data-type'),"paper","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Update the paper count displayed.
         */
        this.updatePaperCount = function() {
            var n = $(".file:visible").length;
            var papercount = n.toLocaleString($('#dataset').attr('data-language'));

            var decimals = papercount.indexOf('.');
            if (decimals !== -1) {
                papercount = papercount.slice(0, decimals);
            }
            $("#paper_count").text(papercount);
        };
    }
});
