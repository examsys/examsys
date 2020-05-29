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
// Initialise start page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jsxls', 'media', 'reference', 'start', 'jquery'], function (Jsxls, Media, REF, START, $) {
    var media = new Media();
    media.init();
    var start = new START();
    var ref = new REF();
    ref.init();
    $(function() {
        start.generatePaperCss();

        var el = document.getElementById('paper');
        var el2 = document.getElementById('user');

        // Disable the back button if linear exam.
        if (el.dataset.submittype === 'linear') {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function () {
                window.history.pushState(null, "", window.location.href);
            };
        }

        if (el.dataset.timed) {
            start.StartTimer(el2.dataset.remaining_time, true);
        } else {
            start.StartClock();
        }

        if (el2.dataset.student) {
            $('body').on('contextmenu', function(){
                return false;
            });
            $('body').on('close', function(){
                start.KillClock();
            });
        } else {
            $('body').on('unload', function(){
                start.KillClock();
            });
        }

        $('#previous').click(function() {
            $('#button_pressed').val('previous');
        });

        $('#finish').click(function() {
            $('#button_pressed').val('finish');
        });

        $('.act').click(function() {
            start.onoff($(this).attr('id'));
        });

        $('.inact').click(function() {
            start.onoff($(this).attr('id'));
        });

        $('#jumpscreen').change(function (event) {
            $('#button_pressed').val('jumpscreen');
            $('#qForm').attr('action',"start.php?id=" + el.dataset.pid + "&dont_record=true");
            return start.checkSubmit(event);
        });

        //Stop forms being submitted with ENTER
        $('input[type=text]').keydown(function (event) {
            event = event || window.event;
            if (event.keyCode === 13) {
                event.preventDefault();
                return false;
            } else {
                return true;
            }
        });

        $("#info_dialog_ok").click(function() {
            $("#info_overlay").hide();
        });

        $('#next').click(start.checkSubmit);

        $('#previous').click(start.checkSubmit);
        $('#finish').click(start.checkSubmit);

        start.autoSaveRef = '';
        start.last_save_point = (new Date).getTime();
        start.last_saved_user_answers = null; // Holds the data of the last successful auto save
        start.submitted = false;

        // Setup autosave
        start.startAutoSave();

        $('#fire_exit').click(function() {
            if ($('#dataset').attr('data-remotesummative') == 0) {
                $('#button_pressed').val('fire_exit');
                $('#qForm').attr('action', "fire_evacuation.php?id=" + el.dataset.pid + "&dont_record=true");
                start.ajaxSave(1, 'userSubmit');
            }
        });

        if (el.dataset.unanswered) {
            $('#unansweredkey').show();
        }

        $('#breaks, #breakstext').click(function() {
            if ($('#dataset').attr('data-remotesummative') == 1 && $('#dataset').attr('data-breaks') == 1) {
                if ($('#breaks').hasClass('pause')) {
                    // Pause exam.
                    start.pause(el.dataset.paperid);
                } else {
                    // Re-start exam.
                    $('#breaks').removeClass('play');
                    $('#breaks').addClass('pause');
                    $('#breakstext').html(Jsxls.lang_string['pause']);
                }
            }
        });

        start.html5init();
    });
});
