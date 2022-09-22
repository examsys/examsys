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
    $(function () {
        $('.datecopy').change(function() {
            var datecheck = false;
            if ($('#remote_summative').is(':checked')) {
                if (type == 'offline') {
                    datecheck = true;
                }
            } else {
                if (type == 'summative' || type == 'offline') {
                    datecheck = true;
                }
            }
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

    // Central controlled summatives: If remote summative no campus required.
    if ($('#dataset').attr('data-central') == 1) {
        $('#remote_summative').click(function () {
            if ($(this).is(':checked')) {
                $('#campus').prop("disabled", true);
            } else {
                $('#campus').prop("disabled", false);
            }
        });
    }

    // Listen for changes to the remote summative checkbox.
    // If it is turned off change the date so that the end day matches the start day.
    $('#remote_summative').change(function() {
        if (!$('#remote_summative').is(':checked')) {
            date.dateCopy($('#fday'));
            date.dateCopy($('#fmonth'));
            date.dateCopy($('#fyear'));
        }
    });
});
