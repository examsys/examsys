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
// Initialise user index page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['rogomodal', 'userindex', 'jsxls', 'jquery'], function (Modal, USER, jsxls, $) {
    Modal.init();
    var user = new USER();

    if ($('#dataset').attr('data-lticontext')) {
        $('body').css('background-color', 'transparent !important');
    }

    $('body').css('font-size', $('#dataset').attr('data-textsize') + '%');
    $('body').css('font-family', $('#dataset').attr('data-font') );

    $("#info_dialog_ok").click(function() {
        Modal.close('info_overlay');
    });

    if ($('#dataset').attr('data-ipmismatch')) {
        Modal.show('info_overlay');
        $("#info_submit_dialog_title").html(jsxls.lang_string['ipmismatchtitle']);
        var blurb = jsxls.lang_string['ipmismatchblurb'];
        if ($('#dataset').attr('data-remotesummative') == 1) {
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
