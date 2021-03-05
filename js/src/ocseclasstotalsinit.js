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
// Initialise OSCE class totals page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['popupmenu', 'osce', 'jquery', 'jquerytablesorter'], function (POPUP, OSCE, $) {
    var popup = new POPUP();
    popup.init();
    var osce = new OSCE();
    // View OSCE Script
    $('#item1').click(function() {
        osce.viewScript(this);
    });

    // View Feedback
    $('#item2').click(function() {
        osce.viewFeedback(this);
    });

    // View student profile
    $('#item3').click(function() {
        osce.viewProfile(this);
    });

    $("tr[id^=res]").click(function() {
        osce.cryptname = $(this).attr('data-cryptname');
        osce.paperid = $(this).attr('data-paperid');
        osce.metadataid = $(this).attr('data-metadataid');
        osce.userid = $(this).attr('data-userid');
        if ($(this).attr('data-metadataid')== '') {
            $('#item1').removeClass('popup_row');
            $('#item1').addClass('popup_row_disabled');
            $('#item2').removeClass('popup_row');
            $('#item2').addClass('popup_row_disabled');
        } else {
            $('#item1').addClass('popup_row');
            $('#item1').removeClass('popup_row_disabled');
            $('#item2').addClass('popup_row');
            $('#item2').removeClass('popup_row_disabled');
        }
    });

    $(function () {
        if ($("#maindata").find("tr").length > 1) {
            $("#maindata").tablesorter({
                // sort on the second column and third column, order asc
                dateFormat: $('#datetime').val(),
                sortList: [[2,0],[3,0]]
            });
        }
    });
});
