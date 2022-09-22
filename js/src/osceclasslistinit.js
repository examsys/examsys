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
// Initialise OSCE class list page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['osce', 'jsxls', 'jquery'], function (OSCE, jsxls, $) {
    var osce = new OSCE();
    var id = $('#dataset').attr('data-id');

    $('.bl').on('click', function() {
        osce.classlist($(this).attr('id'));
    });

    $('.l').on('click', function() {
        osce.classlist($(this).attr('id'));
    });

    $('.qlink.td').on('click', function () {
        var letter = $(this).data('val');
        if (letter == 'All') {
            window.location.reload();
        } else {
            $.getJSON("user_list.php?id=" + id + "&initial=" + letter, function (data) {
                var user_list = $('#user_list');
                user_list.empty().append('<tr><td colspan="3" class="letter"><a name="' + letter + '"></a>' + letter + '</td></tr>');
                if (data.length == 0) {
                    user_list.append('<tr><td>' + jsxls.lang_string['user_list'] + '<td></tr>');
                } else {
                    $.each(data, function (key, value) {
                        if (value[5] == null) {
                            user_list.append('<tr class="bl" id="user' + value[0] + '"><td class="indent">' + value[3] + '</td><td>' + value[1] + ',  <span class="n">' + value[2] + '</span></td><td>' + value[4] + '</td></tr>')
                        } else {
                            user_list.append('<tr class="l" id="user' + value[0] + '"><td class="indent">' + value[3] + '</td><td>' + value[1] + ',  <span class="n">' + value[2] + '</span></td><td>' + value[4] + '</td></tr>')
                        }
                    });
                    // Binding the event after loading the ajax content
                    $('.bl').on('click', function () {
                        osce.classlist($(this).attr('id'));
                    });
                    $('.l').on('click', function () {
                        osce.classlist($(this).attr('id'));
                    });
                }
            });
        }
    });
});