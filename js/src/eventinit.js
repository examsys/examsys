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
// Initialise event page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery', 'jqueryui'], function ($) {
    $('.swatch').click(function() {
        var current = $('#color').val();
        $('#' + current).css('border-color', '#F1F5FB');

        var newvalue = $(this).attr('id');
        $('#' + newvalue).css('border-color', '#FFBD69');
        $('#color').val(newvalue)
    });

    $('#theform').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "do_add_event.php",
            type: "post",
            data: $('#theform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    window.opener.location.reload();
                    window.close();
                }
            },
            error: function (xhr, textStatus) {
                alert(textStatus);
            },
        });
    });
});