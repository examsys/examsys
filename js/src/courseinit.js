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
// Initialise course page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['alert', 'form', 'jquery'], function (ALERT, FORM, $) {
    var form = new FORM();
    form.init();

    $('#theform').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $('#dataset').attr('data-posturl'),
            type: "post",
            data: $('#theform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    window.location = 'list_courses.php';
                } else if (data != 'ERROR') {
                    $('#course').removeClass('valid');
                    $('#course').addClass('errfield');
                    var alert = new ALERT();
                    alert.notification('coursecodeinuse', data);
                }
            },
            error: function (xhr, textStatus) {
                alert(textStatus);
            },
        });
    });
});