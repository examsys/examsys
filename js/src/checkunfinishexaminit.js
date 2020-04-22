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
// Initialise random questions controls.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery'], function ($) {
    $(function () {
        $('#unfinishexam').click(function() {
            $.ajax({
                url: "do_unfinish_exam.php",
                type: "post",
                data: {userID: $('#userID').val(), paperID: $('#paperID').val()},
                dataType: "json",
                success: function () {
                    window.opener.location.href='./index.php?tab=' + $('#paperID').val() + '&remote=' + $('#remote').val();
                    window.close();
                },
                error: function(xhr, textStatus) {
                    alert(textStatus);
                },
            });
        });
    });
});
