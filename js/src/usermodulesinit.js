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
// Initialise user modules page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['usermodules', 'form', 'alert', 'jquery'], function(USER, FORM, ALERT, $) {
    var user = new USER();
    var form = new FORM();
    $('input[type=checkbox]').change(function() {
        form.toggle($(this).attr('id'));
    });

    $('.tabon, .taboff').click(function() {
        user.showTab($(this).attr('data-tabid'));
    });

    $('#teamform').submit(function(e) {
        e.preventDefault();
        var alert = new ALERT();
        $.ajax({
            url: 'do_edit_modules.php',
            type: "post",
            data: $('#teamform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    var user = 'userID=' + $('#userID').val();
                    var mod = '&tab=modules';
                    var student = '&student_id=' + $('#student_id').val();
                    var username = '&search_username=' + $('#search_username').val();
                    var surname = '&search_surname=' + $('#search_surname').val();
                    window.opener.location.href = 'details.php?' + user + mod + student + username + surname;
                    self.close();
                }
            },
            error: function (xhr, textStatus) {
                alert.plain(textStatus);
            },
        });
    });
});