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
// Initialise paper finish page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['rogoconfig', 'media', 'helplauncher', 'html5', 'qarea', 'qlabelling', 'jquery'], function (Config, Media, Helplauncher, Html5, Qarea, Qlabelling, $) {
    var media = new Media();
    media.init();

    $('#randomlink').click(function () {
        Helplauncher.launchHelp(43, 'student');
    });

    $('.logoutandclose').click(function() {
        if ($('#dataset').attr('data-papertype') == '2' && $('#dataset').attr('data-remotesummative') == 1) {
            window.close();
            window.opener.location.href = Config.cfgrootpath + '/logout.php';
        }
    });

    $('.raw_textarea').each(function() {
        var boxWidth = $(this).width();
        var boxHeight = $(this).height();

        var targetID = 'div_' + $(this).attr('id');

        $('#' + targetID).width(boxWidth);
        $('#' + targetID).height(boxHeight);
    });

    $('#close').click(function() {
        window.close();
    });

    if (window.opener == null) {
        $('#close').css('display','none');
    }

    var interactive = new Html5();
    interactive.init($('#dataset').attr('data-rootpath'));
    var language = $('#dataset').attr('data-language');
    $("canvas[id^=canvas]").each(function() {
        switch ($(this).attr('class')) {
            case 'labelling':
                var label = new Qlabelling();
                label.setUpLabelling($(this).attr('data-qno'),
                    "flash" + $(this).attr('data-qno'),
                    language, $(this).attr('data-qmedia'),
                    $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "script");
                break;
            case 'area':
                var area = new Qarea();
                area.setUpArea($(this).attr('data-qno'),
                    "flash" + $(this).attr('data-qno'),
                    language, $(this).attr('data-qmedia'),
                    $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "script");
                break;
            default:
                break;
        }
    });
});
