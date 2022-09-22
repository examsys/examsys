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
// Initialise edit user details.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['alert', 'jquery'], function(ALERT, $) {
    $('#myform').submit(function(e) {
        e.preventDefault();
        var alert = new ALERT();
        $.ajax({
            url: 'do_edit_details.php',
            type: "post",
            data: $('#myform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    window.opener.location = window.opener.parent.location.href.replace('&tab=admin', '');
                    window.opener.parent.location.reload(true);
                    window.close();
                } else {
                    $('#username').addClass('form-error');
                    $('#usernameerror').text(data);
                    $('#usernameerror').show();
                }
            },
            error: function (xhr, textStatus) {
                alert.plain(textStatus);
            },
        });
    });
});