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
// Init Invigilator screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['invigilator', 'jquery', 'jqueryui'], function (INV, $) {
    var inv = new INV();
    $(document).mousedown(function(e) {
        inv.mouseSelect(e);
    });

    $('.menu-time').click(function() {
        inv.extendTime();
    });

    $('.menu-note').click(function() {
        inv.newStudentNote();
    });

    $('.menu-toilet').click(function() {
        inv.newToiletBreak();
    });

    $('.menu-unfinish').click(function() {
        inv.unfinishExam();
    });

    inv.StartClock();
    inv.resizeLists();

    $(window).unload(function() {
        inv.KillClock();
    });

    $(window).resize(function() {
        inv.resizeLists();
    });

    $('.tabs li a').click(function() {
        inv.changeTab($(this));
    });

    if ($('#dataset').attr('data-tab') != '') {
        $("a[rel='paper" + $('#dataset').attr('data-tab') + "']").click();
    }

    if ($('#dataset').attr('data-clarification') == 1) {
        setInterval(inv.clarifyMethod, 10000);
    }

    $('.rubricclose').click(function() {
        $('#' + $(this).attr('data-id')).hide();
        $('#opaque').hide();
    });

    $('.papernote').click(function() {
        inv.newPaperNote($(this).attr('data-id'));
    });

    $('.viewrubric').click(function() {
        inv.viewRubric($(this).attr('data-id'));
    });

    // Student rows are dynamically re-created via an ajax call so need the event re-created.
    $(document).on('click', '.student', function(e){
        inv.popMenu($(this).attr('data-userid'), $(this).attr('data-paperid'), $(this).attr('data-timing'), e);
    });

    $('.popupmenu').mouseover(function() {
        inv.overpopupmenu = true;
    });

    $('.popupmenu').mouseout(function() {
        inv.overpopupmenu = false;
    });

    $('#medical').mouseover(function() {
        inv.showCallout($(this).attr('data-id'), $(this).attr('data-msg'));
    });

    $('#medical').mouseout(function() {
        inv.hideCallout();
    });

    $('#break').mouseover(function() {
        inv.showCallout($(this).attr('data-id'), $(this).attr('data-msg'));
    });

    $('#break').mouseout(function() {
        inv.hideCallout();
    });
});
