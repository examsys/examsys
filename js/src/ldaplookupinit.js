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
// Initialise ldap lookup
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['alert', 'jquery'], function(ALERT, $) {
    $('.l').click(function() {
        var alert = new ALERT();
        $.ajax({
            url: 'applylookup.php?LOOKUP=' + $(this).attr('id'),
            type: "get",
            dataType: "json",
            success: function (data) {
                if (data['type']== 'SUCCESS') {
                    window.opener.$('#new_users_title').val(data['title']);
                    window.opener.$('#new_surname').val(data['surname']);
                    window.opener.$('#new_first_names').val(data['firstname']);
                    window.opener.$('#new_username').val(data['username']);
                    window.opener.$('#new_email').val(data['email']);
                    window.opener.$('#new_grade').val(data['coursecode']);
                    window.opener.$('#new_gender').val(data['gender']);
                    window.opener.$('#new_yos').val(data['yearofstudy']);
                    window.opener.$('#new_studentid').val(data['studentID']);
                    window.close();
                }
            },
            error: function (xhr, textStatus) {
                alert.plain(textStatus);
            },
        });
    });
});