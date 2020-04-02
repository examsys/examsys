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
// Initialise user index page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['userindex', 'jsxls', 'jquery'], function (USER, jsxls, $) {
    var user = new USER();

    $("#overlay").hide();

    $("#info_dialog_ok").click(function() {
        $("#info_overlay").hide();
    });

    if ($('#dataset').attr('data-ipmismatch')) {
        $("#info_overlay").show();
        $("#info_submit_dialog_title").html(jsxls.lang_string['ipmismatchtitle']);
        var blurb = jsxls.lang_string['ipmismatchblurb'];
        if ($('#dataset').attr('data-remotesummative')) {
            blurb = jsxls.lang_string['remoteipmismatchblurb'];
        }
        $("#info_submit_dialog_msg").html(blurb);
        $("#info_submit_dialog").css('left', (($(window).width() / 2) - 250) + 'px');
        $("#info_submit_dialog").css('top', (($(window).height() / 2) - 100) + 'px');
    }

    $("#start").click(function() {
        user.startPaper();
    });

    $("#start").keypress(function() {
        user.startPaper();
    });
});
