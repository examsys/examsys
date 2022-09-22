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
// User details functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Default textsize
         * @type {string}
         */
        this.textsize = 100;
        /**
         * Default font family
         * @type {string}
         */
        this.font = 'Arial';
        /**
         * Default background colour
         * @type {string}
         */
        this.background = '#FFFFFF';
        /**
         * Default font colour
         * @type {string}
         */
        this.foreground = '#000000';
        /**
         * Default theme font colour
         * @type {string}
         */
        this.theme = '#316AC5';
        /**
         * Default label font colour
         * @type {string}
         */
        this.label = '#C00000';
        /**
         * Default unaswered question background colour
         * @type {string}
         */
        this.unanswered = '#FFC0C0';
        /**
         * Default marks font colour
         * @type {string}
         */
        this.marks = '#808080';
        /**
         * Default global theme background colour
         * @type {string}
         */
        this.globaltheme = '#5590CF';
        /**
         * Default global theme font colour
         * @type {string}
         */
        this.globalthemefont = '#FFFFFF';
        /**
         * Default highlight text background colour
         * @type {string}
         */
        this.highlight = '#FCF6CF';

        /**
         * The default dismiss strikethrough colour
         * @type {string}
         */
        this.dismiss = '#A5A5A5';

        /**
         * Open a window to edit the user details.
         */
        this.editDetails = function() {
            var editwin = window.open("edit_details.php?userID=" + $('#dataset').attr('data-userid'),"edituser","width=600,height=450,left="+(screen.width/2-260)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Display selected tab and hide the rest.
         * @param string tabID tab identifier
         */
        this.showTab = function(tabID) {
            $('#Log_tab').hide();
            $('#Modules_tab').hide();
            $('#Admin_tab').hide();
            $('#Notes_tab').hide();
            $('#Accessibility_tab').hide();
            $('#Teams_tab').hide();
            $('#Metadata_tab').hide();
            $('#roles_tab').hide();
            $('#profileaudit_tab').hide();
            $('#' + tabID).show();
        };

        /**
         * Open a window to add a student note.
         */
        this.newStudentNote = function() {
            var note = window.open("new_student_note.php?userID="  + $('#dataset').attr('data-userid'),"note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                note.focus();
            }
        };

        /**
         * Open a window to edit the modules a user is enrol on.
         * @param integer session the academic session
         * @param string grade the users course
         * @param intger searchsid the student identifier
         * @param string searchusername the useranme
         * @param string searchsurname the surname
         */
        this.editModules = function(session, grade, searchsid, searchusername, searchsurname) {
            var student = "&student_id=" + searchsid;
            var username = "&search_username=" + searchusername;
            var surname = "&search_surname=" + searchsurname;
            var editwin = window.open("edit_modules_popup.php?userID="  + $('#dataset').attr('data-userid') + student + username + surname + "&session=" + session + "&grade=" + grade + "","editmodule","width=650,height=750,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Open window to edit staff members teams.
         * @returns bool
         */
        this.editMultiTeams = function() {
            var editwin = window.open("../module/edit_multi_teams_popup.php?userID=" + $('#dataset').attr('data-userid'),"editmodule","width=550,height=750,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
            return false;
        };

        /**
         * Open window to force reset the users password.
         */
        this.forceResetPassword = function() {
            var editwin = window.open("reset_pwd.php?userID=" + $('#dataset').attr('data-userid'),"editmodule","width=600,height=400,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Open window to email user password.
         */
        this.resetPassword = function() {
           var editwin = window.open("forgotten_password.php?email=" + $('#dataset').attr('data-email') + "","editmodule","width=600,height=400,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Update accessibility screen based on new choices.
         */
        this.updateAccessDemo = function() {
            var textsize = $('select[name="textsize"] option:selected').text();
            var textsizeval = $('select[name="textsize"] option:selected').val();
            if (textsizeval == 0) {
                textsize = '100%';
            }
            $('#demo_paper_background').css('font-size', textsize);
            $('#demo_footer').css('font-size', textsize);

            var font = $('select[name="font"] option:selected').text();
            var fontval = $('select[name="font"] option:selected').val();
            if (fontval == '') {
                font = 'Arial';
            }
            $('#demo_paper_background').css('font-family', font);
            $('#demo_header').css('font-family', font);
            $('#demo_footer').css('font-family', font);

            if ($("#bg_radio_on").is(':checked')) {
                $('#demo_paper_background').css('background-color', $('#span_background').css('background-color'));
            } else {
                $('#demo_paper_background').css('background-color', this.background);
                $('#span_background').css('background-color', this.background);
            }

            if ($("#fg_radio_on").is(':checked')) {
                $('#demo_paper_background').css('color', $('#span_foreground').css('background-color'));
            } else {
                $('#demo_paper_background').css('color', this.foreground);
                $('#span_foreground').css('background-color', this.foreground);
            }

            if ($("#theme_radio_on").is(':checked')) {
                $('#demo_theme').css('color', $('#span_themecolor').css('background-color'));
            } else {
                $('#demo_theme').css('color', this.theme);
                $('#span_themecolor').css('background-color', this.theme);
            }

            if ($("#labels_radio_on").is(':checked')) {
                $('#demo_true_label').css('color', $('#span_labelcolor').css('background-color'));
                $('#demo_false_label').css('color', $('#span_labelcolor').css('background-color'));
                $('#demo_note').css('color', $('#span_labelcolor').css('background-color'));
            } else {
                $('#demo_true_label').css('color', this.label);
                $('#demo_false_label').css('color', this.label);
                $('#demo_note').css('color', this.label);
                $('#span_labelcolor').css('background-color', this.label);
            }

            if ($("#unanswered_radio_on").is(':checked')) {
                $('#demo_unanswered').css('background-color', $('#span_unansweredcolor').css('background-color'));
            } else {
                $('#demo_unanswered').css('background-color', this.unanswered);
                $('#span_unansweredcolor').css('background-color', this.unanswered);
            }

            if ($("#marks_radio_on").is(':checked')) {
                $('#demo_marks').css('color', $('#span_marks_color').css('background-color'));
            } else {
                $('#demo_marks').css('color', this.marks);
                $('#span_marks_color').css('background-color', this.marks);
            }

            if ($("#colour_globaltheme_radio_on").is(':checked')) {
                $('#demo_header').css('background-color', $('#span_globalthemecolour').css('background-color'));
                $('#demo_footer').css('background-color', $('#span_globalthemecolour').css('background-color'));
            } else {
                $('#demo_header').css('background-color', this.globaltheme);
                $('#demo_footer').css('background-color', this.globaltheme);
                $('#span_globalthemecolour').css('background-color', this.globaltheme);
            }

            if ($("#colour_globalthemefont_radio_on").is(':checked')) {
                $('#demo_header').css('color', $('#span_globalthemefontcolour').css('background-color'));
                $('#demo_footer').css('color', $('#span_globalthemefontcolour').css('background-color'));
            } else {
                $('#demo_header').css('color', this.globalthemefont)
                $('#demo_footer').css('color', this.globalthemefont)
                $('#span_globalthemefontcolour').css('background-color', this.globalthemefont);
            }

            if ($("#colour_highlight_radio_on").is(':checked')) {
                $('#demo_highlight').css('background-color', $('#span_highlightcolour').css('background-color'));
            } else {
                $('#demo_highlight').css('background-color', this.highlight);
                $('#span_highlightcolour').css('background-color', this.highlight);
            }

            if ($("#dismiss_radio_on").is(':checked')) {
                $('#demo_dismiss').css('color', $('#span_dismisscolor').css('background-color'));
              } else {
                $('#demo_dismiss').css('color', this.dismiss);
                $('#span_dismisscolor').css('background-color', this.dismiss);
            }
        };

        /**
         * Reset settings to default values. Not saved.
         */
        this.resetToDefault = function() {
            $('#textsize').val(100);
            $('#font').val('Arial');
            $('input:radio[name=bg_radio]').prop('checked', false);
            $('input:radio[name=fg_radio]').prop('checked', false);
            $('input:radio[name=marks_radio]').prop('checked', false);
            $('input:radio[name=theme_radio]').prop('checked', false);
            $('input:radio[name=labels_radio]').prop('checked', false);
            $('input:radio[name=unanswered_radio]').prop('checked', false);
            $('input:radio[name=dismiss_radio]').prop('checked', false);
            $('input:radio[name=globalthemecolour_radio]').prop('checked', false);
            $('input:radio[name=globalthemefontcolour_radio]').prop('checked', false);
            $('input:radio[name=highlightcolour_radio]').prop('checked', false);
            $('#bg_radio_off').prop('checked', true);
            $('#fg_radio_off').prop('checked', true);
            $('#marks_radio_off').prop('checked', true);
            $('#theme_radio_off').prop('checked', true);
            $('#labels_radio_off').prop('checked', true);
            $('#unanswered_radio_off').prop('checked', true);
            $('#dismiss_radio_off').prop('checked', true);
            $('#colour_highlight_radio_off').prop('checked', true);
            $('#colour_globaltheme_radio_off').prop('checked', true);
            $('#colour_globalthemefont_radio_off').prop('checked', true);
            this.updateAccessDemo();
        }

        /**
         * Clear the current user settings storeed in the session.
         */
        this.clearUserSettings = function() {
            sessionStorage.removeItem('/students/settings/textsize');
            sessionStorage.removeItem('/students/settings/font');
            sessionStorage.removeItem('/students/settings/background');
            sessionStorage.removeItem('/students/settings/foreground');
            sessionStorage.removeItem('/students/settings/theme');
            sessionStorage.removeItem('/students/settings/label');
            sessionStorage.removeItem('/students/settings/unanswered');
            sessionStorage.removeItem('/students/settings/marks');
            sessionStorage.removeItem('/students/settings/globaltheme');
            sessionStorage.removeItem('/students/settings/globalthemefont');
            sessionStorage.removeItem('/students/settings/highlight');
            sessionStorage.removeItem('/students/settings/dismiss');
        }

        /**
         * Store the users current settings.
         */
        this.storeUserSettings = function() {
            this.clearUserSettings();
            var textsize = $('select[name="textsize"] option:selected').val();
            var font = $('select[name="font"] option:selected').val();
            var background = this.background;
            var foreground = this.foreground;
            var theme = this.theme;
            var label = this.label;
            var unanswered = this.unanswered;
            var marks = this.marks;
            var globaltheme = this.globaltheme;
            var globalthemefont = this.globalthemefont;
            var highlight = this.highlight;
            var dismiss = this.dismiss;

            if ($("#bg_radio_on").is(':checked')) {
                background = $('#span_background').css('background-color');
            }

            if ($("#fg_radio_on").is(':checked')) {
                foreground = $('#span_foreground').css('background-color');
            }

            if ($("#theme_radio_on").is(':checked')) {
                theme = $('#span_themecolor').css('background-color');
            }

            if ($("#labels_radio_on").is(':checked')) {
                label = $('#span_labelcolor').css('background-color');
            }

            if ($("#unanswered_radio_on").is(':checked')) {
                unanswered = $('#span_unansweredcolor').css('background-color');
            }

            if ($("#marks_radio_on").is(':checked')) {
                marks = $('#span_marks_color').css('background-color');
            }

            if ($("#colour_globaltheme_radio_on").is(':checked')) {
                globaltheme = $('#span_globalthemecolour').css('background-color');
            }

            if ($("#colour_globalthemefont_radio_on").is(':checked')) {
                globalthemefont = $('#span_globalthemefontcolour').css('background-color');
            }

            if ($("#colour_highlight_radio_on").is(':checked')) {
                highlight = $('#span_highlightcolour').css('background-color');
            }

            if ($("#dismiss_radio_on").is(':checked')) {
                dismiss = $('#span_dismisscolor').css('background-color');
            }

            // Store in session storage so we can revert to previous saved version.
            sessionStorage.setItem('/students/settings/textsize', textsize);
            sessionStorage.setItem('/students/settings/font', font);
            sessionStorage.setItem('/students/settings/background', background);
            sessionStorage.setItem('/students/settings/foreground', foreground);
            sessionStorage.setItem('/students/settings/theme', theme);
            sessionStorage.setItem('/students/settings/label', label);
            sessionStorage.setItem('/students/settings/unanswered', unanswered);
            sessionStorage.setItem('/students/settings/marks', marks);
            sessionStorage.setItem('/students/settings/globaltheme', globaltheme);
            sessionStorage.setItem('/students/settings/globalthemefont', globalthemefont);
            sessionStorage.setItem('/students/settings/highlight', highlight);
            sessionStorage.setItem('/students/settings/dismiss', dismiss);
        };

        /**
         * Reset user settings to previous (saved) state. Not saved.
         */
        this.resetToPrevious = function() {
            $('#textsize').val(sessionStorage.getItem('/students/settings/textsize'));
            $('#font').val(sessionStorage.getItem('/students/settings/font'));
            $('input:radio[name=bg_radio]').prop('checked', false);
            $('#bg_radio_on').prop('checked', true);
            $('#span_background').css('background-color', sessionStorage.getItem('/students/settings/background'));
            $('input:radio[name=fg_radio]').prop('checked', false);
            $('#fg_radio_on').prop('checked', true);
            $('#span_foreground').css('background-color', sessionStorage.getItem('/students/settings/foreground'));
            $('input:radio[name=theme_radio]').prop('checked', false);
            $('#theme_radio_on').prop('checked', true);
            $('#span_themecolor').css('background-color', sessionStorage.getItem('/students/settings/theme'));
            $('input:radio[name=labels_radio]').prop('checked', false);
            $('#labels_radio_on').prop('checked', true);
            $('#span_labelcolor').css('background-color', sessionStorage.getItem('/students/settings/label'));
            $('input:radio[name=unanswered_radio]').prop('checked', false);
            $('#unanswered_radio_on').prop('checked', true);
            $('#span_unansweredcolor').css('background-color', sessionStorage.getItem('/students/settings/unanswered'));
            $('input:radio[name=marks_radio]').prop('checked', false);
            $('#marks_radio_on').prop('checked', true);
            $('#span_marks_color').css('background-color', sessionStorage.getItem('/students/settings/marks'));
            $('input:radio[name=colour_globaltheme_radio]').prop('checked', false);
            $('#colour_globaltheme_radio_on').prop('checked', true);
            $('#span_globalthemecolour').css('background-color', sessionStorage.getItem('/students/settings/globaltheme'));
            $('input:radio[name=colour_globalthemefont_radio]').prop('checked', false);
            $('#colour_globalthemefont_radio_on').prop('checked', true);
            $('#span_globalthemefontcolour').css('background-color', sessionStorage.getItem('/students/settings/globalthemefont'));
            $('input:radio[name=colour_highlight_radio]').prop('checked', false);
            $('#colour_highlight_radio_on').prop('checked', true);
            $('#span_highlightcolour').css('background-color', sessionStorage.getItem('/students/settings/highlight'));
            $('input:radio[name=dismiss_radio]').prop('checked', false);
            $('#dismiss_radio_on').prop('checked', true);
            $('#span_dismisscolor').css('background-color', sessionStorage.getItem('/students/settings/dismiss'));
            this.updateAccessDemo();
        };
    }
});
