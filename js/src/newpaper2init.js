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
// Initialise new paper page - step 2.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['datecopy', 'form', 'newpaperform', 'jquery'], function (DATECOPY, FORM, PAPER, $) {
    var paper = new PAPER();
    var date = new DATECOPY();
    var form = new FORM();
    var type = $('#paper_type').val();
    var datecheck = false;
    if ($('#dataset').attr('data-remotesummative')) {
        if (type == 'offline') {
            datecheck = true;
        }
    } else {
        if (type == 'summative' || type == 'offline') {
            datecheck = true;
        }
    }
    $(function () {
        $('.datecopy').change(function() {
            if (datecheck) {
                date.dateCopy(this);
            }
        });
    });

    $('#myform').submit(function(e) {
        e.preventDefault();
        if (type == 'summative') {
            if (paper.step2checkSummativeForm()) {
                paper.createpaper();
            }
        } else {
            if (paper.step2checkForm()) {
                paper.createpaper();
            }
        }
    });

    $("input[id^=mod]").click(function() {
        form.toggle($(this).attr('data-mod'));
    });
});
