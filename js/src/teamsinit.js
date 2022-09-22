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
// Initialise teams page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['form', 'alert', 'jquery'], function(FORM, ALERT, $) {
    var form = new FORM();
    $("input[id^=mod]").each(function() {
        $(this).click(function () {
            form.toggle($(this).attr('id'));
        });
    });

    $("input[id^=staff]").each(function() {
        $(this).click(function () {
            form.toggle($(this).attr('id'));
        });
    });

    $('#teamform').submit(function(e) {
        e.preventDefault();
        var alert = new ALERT();
        $.ajax({
            url: $('#dataset').attr('data-posturl'),
            type: "post",
            data: $('#teamform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    if ($('#dataset').attr('data-posturl') == 'do_edit_multi_team.php') {
                        window.opener.location = '../users/details.php?userID=' + $('#userID').val() + '&tab=teams';
                    } else {
                        window.opener.location = '../module/index.php?module=' + $('#moduleID').val();
                    }
                    window.close();
                }
            },
            error: function (xhr, textStatus) {
                alert.plain(textStatus);
            },
        });
    });
});