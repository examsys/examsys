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
// Initialise lab page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jsxls', 'form', 'jquery'], function (jsxls, FORM, $) {
    var form = new FORM();
    form.init();

    $('#theform').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: $('#dataset').attr('data-posturl'),
            type: "post",
            data: $('#theform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data[0] == 'OK') {
                    window.location = 'lab_details.php?labID=' + data[1];
                } else if (data[0] == 'INVALID') {
                    if (data[1].length > 0) {
                        $('.invalidlab').html(jsxls.lang_string['badaddressesinvalid'].replace('%s', data[1].join(',')));
                        $('.invalidlab').show();
                    }
                    if (data[2].length > 0) {
                        $('.inuselab').html(jsxls.lang_string['badaddressesinuse'].replace('%s', data[2].join(',')));
                        $('.inuselab').show();
                    }
                }
            },
            error: function(xhr, textStatus) {
                alert(textStatus);
            },
        });
    });
});