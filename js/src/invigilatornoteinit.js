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
// Init Invigilator note screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['form', 'jquery', 'jqueryui'], function (FORM, $) {
    var noteHeight = $(document).height() - 110;
    $("#note").css('height', noteHeight + 'px')
    $("#note").focus();

    $(window).resize(function() {
        var noteHeight = $(document).height() - 110;
        $("#note").css('height', noteHeight + 'px')
    });

    // Initialise student note.
    $('#studentnote').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'do_student_note.php',
            type: "post",
            data: $('#studentnote').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    window.opener.location.href='./index.php?tab=' + $('#paperID').val() + '&remote=' + $('#remote').val();
                    window.close();
                }
            },
            error: function(xhr, textStatus) {
                alert(textStatus);
            },
        });
    });
    // Initialise paper note.
    var form = new FORM();
    form.init();
    $('#theform').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'do_paper_note.php',
            type: "post",
            data: $('#theform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    window.opener.location.href='./index.php?tab=' + $('#paperID').val() + '&remote=' + $('#remote').val();
                    window.close();
                }
            },
            error: function(xhr, textStatus) {
                alert(textStatus);
            },
        });
    });
});
