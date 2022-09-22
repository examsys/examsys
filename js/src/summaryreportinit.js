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
// Initialise summary report page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['peerreview', 'popupmenu', 'jquery', 'jquerytablesorter'], function (PEER, POPUP, $) {
    var peer = new PEER();

    var popup = new POPUP();
    popup.init();

    $('#item1').click(function() {
        peer.viewReviews();
    });

    $('#item2').click(function() {
        peer.viewProfile();
    });

    $("tr[id^=res]").click(function() {
        peer.userid = $(this).attr('data-userid');
        peer.paperid = $(this).attr('data-paperid');
    });

    $('#maindata').click(function() {
        $('#toprightmenu').hide();
    });

    $('.head_title').click(function() {
        $('#menudiv').hide();
        $('#toprightmenu').hide();
    })

    if ($("#maindata").find("tr").length > 1) {
        $("#maindata").tablesorter({
            sortList: [[2,0],[3,0]]
        });
    }
});
