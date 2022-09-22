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
// Initialise module page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['list', 'modulessidebar', 'jquery', 'jquerytablesorter'], function (LIST, MODULE, $) {
    var module = new MODULE();
    module.init();
    if ($("#maindata").find("tr").length > 1) {
        $("#maindata").tablesorter({
            sortList: [[0,0]]
        });
    }
    var list = new LIST();
    list.init();

    // Display sms sync options if available to module.
    $(".l").click(function() {
        var externalid = $(this).attr('data-externalid');
        var syncprevious = $(this).attr('data-syncprevious');
        var session = $(this).attr('data-session');
        var nextsession = parseInt(session) + 1;
        var prevsession = parseInt(session) - 1;
        var currentaccsession = session + '/' + nextsession.toString().substring(2);
        var nextaccsession = nextsession + '/' + (nextsession + 1).toString().substring(2);
        var prevacccsession = prevsession + '/' + session.toString().substring(2);

        if (externalid == '') {
            $('#syncoptions').hide();
        } else {
            $('#syncoptions').show();
        }

        $('#sms').attr('data-id', externalid);
        $('#sms2').attr('data-id', externalid);
        $('#sms3').attr('data-id', externalid);
        $('#sms').attr('data-session', session);
        $('#sms2').attr('data-session', nextsession);
        $('#sms3').attr('data-session', prevsession);
        $('#sms div').html('(' + currentaccsession + ')');
        $('#sms2 div').html('(' + nextaccsession + ')');
        $('#sms3 div').html('(' + prevacccsession + ')');

        // Display previous year syn if enabled for the module.
        if (syncprevious == '1') {
            $('#sms3').show();
        } else {
            $('#sms3').hide();
        }
    });

    $(".l").dblclick(function() {
        list.edit('./edit_module.php?moduleid=', $(this).attr('id'));
    });
});
