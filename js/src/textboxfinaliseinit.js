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
// Init text box finalise marks screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['textboxfinalise', 'jquery'], function (TEXTBOX, $) {
    var textbox = new TEXTBOX();
    // Check select all button if all primary mark radio buttons selected on load.
    textbox.selectall();

    $("input:radio").click(function() {
        var str = $(this).attr('id');
        var dropdownID = str.replace('mark', 'override');
        $("#" + dropdownID).val('NULL');
    });

    // Override selected.
    $("select").click(function() {
        var str = $(this).attr('id');
        var radioID = str.replace('override', 'mark');
        $('input:radio[name=' + radioID + ']').removeAttr('checked');
        $("#selectallprimary").prop("checked", false);
    });

    // Select all primary marks radio buttons.
    $("#selectallprimary").change(function() {
        if ($("#selectallprimary").is(':checked')) {
            $(".primarychk").prop("checked", true);
            $('select[id^="override"]').each(function() {
                $(this).val('NULL');
            });
        } else {
            $(".primarychk").prop("checked", false);
        }
    });
    // Check select all button if all primary mark radio buttons selected.
    $(".primarychk").click(function() {
        textbox.selectall();
    });
    // Uncheck select all button if a secondary mark has been selected.
    $(".secondarychk").click(function() {
        $("#selectallprimary").prop("checked", false);
    });

    // Highlight which mark is being used.
    $('td>input[type="radio"]:checked').parent().addClass('marked');

    $('td>select').each(function() {
        if($(this).val() && $(this).val() != 'NULL') {
            $(this).parent().addClass('marked');
        }
    });

    $('td>input[type="radio"], td>select, td>input[type="checkbox"]').change(function() {
        $('td>input[type="radio"]', $(this).parents('tr')).each(function() {
            if ($(this).prop('checked')) {
                $(this).parent().addClass('marked');
            } else {
                $(this).parent().removeClass('marked');
            }
        });
        $('td>select', $(this).parents('tr')).each(function() {
            if ($(this).val() && $(this).val() != 'NULL') {
                $(this).parent().addClass('marked');
            } else {
                $(this).parent().removeClass('marked');
            }
        });
        $('td>input[type="checkbox"]', $(this).parents('tr')).each(function() {
            if ($(this).prop('checked')) {
                $(".primary").addClass('marked');
                $(".override").removeClass('marked');
            }
        });
    });
});
