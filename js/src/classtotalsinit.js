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
// Initialise class totals page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jsxls', 'classtotals', 'popupmenu', 'jquery', 'jquerytablesorter'], function (jsxls, CLASSTOTALS, POPUP, $) {
    var classtotals = new CLASSTOTALS();
    classtotals.reportinit();
    var popup = new POPUP();
    popup.init();

    $(function() {

        if ($('#markall').val()) {
            // Fire off the request to mark_all_enhancedcalc.php
            $.ajax({
                url: "../ajax/reports/mark_all_enhancedcalc.php",
                type: "get",
                data: {paperID: $('#paperID').val(), startdate: $('#startdate').val(), enddate: $('#enddate').val()},
                timeout: 30000, // timeout after 30 seconds
                dataType: "html",
                success: function (data) {
                    data = data.replace(/(\r\n|\n|\r)/gm,"");
                    if (data == 'Complete') {
                        window.location.reload();
                    } else {
                        $("#msg").html(data);
                    }
                },
                error: function (xhr, textStatus) {
                    $("#msg").html(jsxls.lang_string['ajaxerror'] + textStatus);
                },
                fail: function (jqXHR, textStatus) {
                    $("#msg").html(jsxls.lang_string['ajaxfail'] + textStatus);
                },
            });
        }

        if ($("#maindata").find("tr").length > 1) {
            $("#maindata").tablesorter({
                // sort on the first column and third column, order asc
                dateFormat: $('#datatime').val(),
                sortList: [[2,0],[3,0]]
            });
        }
    });

});
