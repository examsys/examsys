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
// Initialise class totals page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['classtotals', 'popupmenu', 'jquery', 'jquerytablesorter'], function (CLASSTOTALS, POPUP, $) {
    var classtotals = new CLASSTOTALS();
    classtotals.reportinit();
    var popup = new POPUP();
    popup.init();

    $(function () {
        $("#maindata").tablesorter({
            // sort on the fourth column, order asc
            sortList: [[4,0]]
        });
    });
});