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
// Initialise student note page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['alert', 'form', 'studentnote', 'jquery'], function (ALERT, FORM, NOTE, $) {
    var note = new NOTE();
    var form = new FORM();
    form.init();

    note.resizeTextbox();
    $("#note").focus();

    $(window).resize(function() {
        note.resizeTextbox();
    });

    if ($("#paperID").val() == '') {
        $("#note").attr("disabled", "disabled");
    }

    $('#theform').submit(function(e) {
        e.preventDefault();
        var alert = new ALERT();
        if ($("#paperID").val() == '') {
            alert.notification('namecheck');
            return false;
        }

        if ($("#note").val() == '') {
            alert.notification('notecheck');
            return false;
        }

        $.ajax({
            url: "update_student_note.php",
            type: "post",
            data: $('#theform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data['response'] == 'SUCCESS') {
                    if (data['type'] == 'class_totals') {
                        window.opener.location.reload();
                        window.close();
                    } else {
                        window.opener.location = "details.php?userID=" + $('#userID').val() + "&tab=notes";
                        window.close();
                    }
                } else {
                    alert.notification(data['type']);
                }
            },
            error: function (xhr, textStatus) {
                alert(textStatus);
            },
        });
    });

    $('#paperID').change(function () {
        window.location.href = 'new_student_note.php?userID=' + $('#userID').val() + '&paperID=' + $('#paperID').val();
    });
});
