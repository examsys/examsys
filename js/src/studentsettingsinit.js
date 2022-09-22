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
// Initialise student settings page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2021 The University of Nottingham
//
requirejs(['user', 'colourpicker', 'jquery'], function (User, Picker, $) {
    var user = new User();
    var picker = new Picker();
    picker.init();

    user.updateAccessDemo();

    $('.access').on("change", function() {
        user.updateAccessDemo();
    });

    $('.showpicker').click(function(e) {
        picker.showPicker($(this).attr('data-pickertype'), e);
        switch ($(this).attr('data-pickertype')) {
            case 'background':
                $('#bg_radio_on').prop('checked', true);
                break;
            case 'foreground':
                $('#fg_radio_on').prop('checked', true);
                break;
            case 'marks_color':
                $('#marks_radio_on').prop('checked', true);
                break;
            case 'themecolor':
                $('#theme_radio_on').prop('checked', true);
                break;
            case 'labelcolor':
                $('#labels_radio_on').prop('checked', true);
                break;
            case 'unansweredcolor':
                $('#unanswered_radio_on').prop('checked', true);
                break;
            case 'dismisscolor':
                $('#dismiss_radio_on').prop('checked', true);
                break;
            case 'globalthemecolour':
                $('#colour_globaltheme_radio_on').prop('checked', true);
                break;
            case 'highlightcolour':
                $('#colour_highlight_radio_on').prop('checked', true);
                break;
            case 'globalthemefontcolour':
                $('#colour_globalthemefont_radio_on').prop('checked', true);
                break;
            default:
                break;
        }
    });

    $('#resettodefault').click(function() {
        user.resetToDefault();
    });

    user.storeUserSettings();

    $('#resettoprevious').click(function() {
        user.resetToPrevious();
    });
});
