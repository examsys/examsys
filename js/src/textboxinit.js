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
// Initialise textbox marking page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['media', 'textboxmarking', 'jquery', 'jqueryui'], function (Media, TEXTBOX, $) {
    var media = new Media();
    media.init();
    window.location.hash = $('#dataset').attr('data-hash');
    var textbox = new TEXTBOX();
    $.ajaxSetup({timeout: 3000});
    $('#content').ajaxError(function () {
        textbox.doError();
    });

    $('.tbmark').click(function(e) {
        textbox.updateMark(this, e);
    });

    $('#hidemarked').click(function() {
        $.ajax({
            url: "../include/set_state.php",
            type: "post",
            data: {state_name: 'hidemarked', content: $('#hidemarked').is(':checked'), page: document.URL},
            dataType: "html",
            success: function () {
                $("#theform").submit();
            },
        });
    });
});
