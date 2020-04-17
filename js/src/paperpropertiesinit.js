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
// Initialise paper properties page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['rogoconfig', 'paperproperties', 'colourpicker', 'datecopy', 'form', 'alert', 'helplauncher', 'jquery', 'jqueryui'], function (Config, PROP, PICKER, DATECOPY, FORM, ALERT, HELPLAUNCHER, $) {
    var properties = new PROP();

    var picker = new PICKER();
    picker.init();

    $('.refmaterials').click(function () {
        HELPLAUNCHER.launchHelp(296, 'staff');
    });

    properties.paperid = $('#dataset').attr('data-id');
    var type = $('#dataset').attr('data-type');
    var noadd = $('#noadd').val();

    var date = new DATECOPY();
    $('.datecopy').change(function () {
        var datecheck = false;
        if ($('#remote_summative').is(':checked')) {
            if (type == 5) {
                datecheck = true;
            }
        } else {
            if (type == 2 || type == 5) {
                datecheck = true;
            }
        }
        if (datecheck) {
            date.dateCopy(this);
        }
    });

    properties.getMeta();

    var form = new FORM();
    form.init();

    $('body').click(function () {
        picker.hidePicker();
    });

    if (noadd == 'y') {
        // If 'noadd' is passed through on the URL open up the security tab automatically.
        properties.buttonclick('security','tab2');
    }

    $('.toggle').click(function () {
        form.toggle($(this).attr('data-toggleid'));
    });

    $('#paper_type').click(function () {
        properties.changeType();
    });

    $('.meta').click(function () {
        properties.getMeta();
    });

    $("td[id^=tab]").click(function() {
        properties.buttonclick($(this).attr('data-name'), $(this).attr('id'));
    });

    $('.showpicker').click(function (e) {
        e.stopPropagation();
        picker.showPicker($(this).attr('data-pickertype'), e);
    });

    $('#theform').submit(function(e) {
        e.preventDefault();
        if (properties.checkForm()) {
            var alert = new ALERT();
            $.ajax({
                url: 'update_properties.php',
                type: "post",
                data: $('#theform').serialize(),
                dataType: "json",
                success: function (data) {
                    if (data == 'SUCCESS') {
                        window.location.href = Config.cfgrootpath  + '/paper/details.php?paperID=' + $('#dataset').attr('data-id');
                    } else if (data == 'DUPLICATE_TITLE') {
                        $('#papertitle').addClass('errfield');
                        properties.buttonclick('general','tab1');
                    }
                },
                error: function (xhr, textStatus) {
                    alert.plain(textStatus);
                },
            });
        }
    });

    // Disable labs if remote summative.
    if ($('#remote_summative').is(':checked')) {
        $("input[id^=lab]").each(function() {
            $(this).prop("disabled", true);
        });
    }
    $('#remote_summative').click(function () {
        if ($(this).is(':checked')) {
            $("input[id^=lab]").each(function() {
                $(this).prop("disabled", true);
            });
        } else {
            $("input[id^=lab]").each(function() {
                $(this).prop("disabled", false);
            });
        }
    });
});
