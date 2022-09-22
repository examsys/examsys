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
// Initialise performance report page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jquery', 'performance', 'popupmenu'], function ($, PERF, POPUP) {
    var performance = new PERF();
    var popup = new POPUP();
    popup.init();

    $('#item1').click(function() {
        performance.viewScript();
    });
    $('#item2').click(function() {
        performance.viewFeedback();
    });
    $('#item3').click(function() {
        performance.viewPersonalCohort();
    });
    $('#item4').click(function() {
        performance.jumpToPaper();
    });
    $("tr[id^=res]").each(function(){
        $(this).click(function() {
            performance.papertype = $(this).attr('data-papertype');
            performance.cryptname = $(this).attr('data-cryptname');
            performance.paperid = $(this).attr('data-paperid');
            performance.metadataid = $(this).attr('data-metadataid');
            performance.userid = $(this).attr('data-userid');
        });
    });

    document.onmousedown = popup.mouseSelect;
});