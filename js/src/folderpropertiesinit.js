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
// Initialise folder properties.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['alert', 'folderproperties', 'form', 'jquery'], function (ALERT, FOLDER, FORM, $) {
    var folder = new FOLDER();
    var form = new FORM();
    form.init();

    $('#folder').keypress(function(e) {
        folder.illegalChar(e.which);
    });

    $('input[type=checkbox]').change(function() {
        form.toggle($(this).attr('id'));
    });

    $('#theform').submit(function (e) {
        e.preventDefault();
        if (folder.checkForm()) {
            var alert = new ALERT();
            $.ajax({
                url: "update_properties.php",
                type: "post",
                data: $('#theform').serialize(),
                dataType: "json",
                success: function (data) {
                    if (data == 'SUCCESS') {
                        window.opener.location.reload();
                        window.close();
                    } else if (data == 'DUPLICATE') {
                        $('#folder').addClass('errfield');
                        $('#duplicateerror').show();
                    }
                },
                error: function (xhr, textStatus) {
                    alert.plain(textStatus);
                },
            });
        }
    });
});
