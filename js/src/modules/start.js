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
// Start page functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['editor', 'html5', 'qarea', 'qlabelling', 'jsxls', 'jquery'], function(Editor, Html5, Qarea, Qlabelling, Jsxls, $) {
    return function() {
        var scope = this;

        /**
         * Initialise html5 questions.
         */
        this.html5init = function () {
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
                            $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "answer");
                        break;
                    case 'area':
                        var area = new Qarea();
                        area.setUpArea($(this).attr('data-qno'),
                            "flash" + $(this).attr('data-qno'),
                            language, $(this).attr('data-qmedia'),
                            $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "answer");
                        break;
                    default:
                        break;
                }
            });
        };

        /**
         * Display dialog box overlay.
         * @param string msg message to display.
         */
        this.info_dialog = function(msg) {
            $("#info_overlay").show();
            $("#info_submit_dialog_msg").html(msg);
            $("#info_submit_dialog").css('left', (($(window).width() / 2) - 250) + 'px');
            $("#info_submit_dialog").css('top', (($(window).height() / 2) - 100) + 'px');
        };

        /**
         * Update the clock
         * @param integer hours hour
         * @param integer minutes minute
         * @param integer seconds second
         * @constructor
         */
        this.UpdateClock = function(hours, minutes, seconds) {
            scope.KillClock();

            if ( hours == 0 ){
                hours   = '';
                minutes = ( ( minutes  < 10 ) ? "0" : "" ) + minutes;
            } else {
                hours   = ( ( hours < 10 ) ? "0" : "" ) + hours;
                minutes = ( ( minutes  < 10 ) ? ":0" : ":" ) + minutes;
            }
            seconds = ( ( seconds < 10 ) ? ":0" : ":" ) + seconds;

            $('#theTime').html("" + hours + minutes + seconds);
        };

        /**
         * Update the break timer clock
         * @param integer hours hour
         * @param integer minutes minute
         * @param integer seconds second
         */
        this.UpdateBreakClock = function(hours, minutes, seconds) {
            scope.KillBreakClock();

            if ( hours == 0 ){
                hours   = '';
                minutes = ( ( minutes  < 10 ) ? "0" : "" ) + minutes;
            } else {
                hours   = ( ( hours < 10 ) ? "0" : "" ) + hours;
                minutes = ( ( minutes  < 10 ) ? ":0" : ":" ) + minutes;
            }
            seconds = ( ( seconds < 10 ) ? ":0" : ":" ) + seconds;

            $('#info_submit_dialog_extra').html(Jsxls.lang_string['breakremaining'] + " " + hours + minutes + seconds);
        };

        /**
         * Regular heartbeat that confirms remaining time if missed.
         */
        this.heartbeat = function() {
            var now = new Date();
            var nowtime = now.getTime();
            var diff = nowtime - scope.lastheartbeat;
            // Expeted to be one second since last heartbeat.
            var offBy = diff - 1000;
            scope.lastheartbeat = now;
            // Give some wiggle room.
            if (offBy > 100) {
                if (!scope.paused) {
                    // Not paused so just need to update the exam timer.
                    //   - taking into account time since last heartbeat with the break time added.
                    var remaingtime = Math.floor((scope.endtime.getTime() - nowtime) / 1000);
                    var breakdiff = $('#dataset').attr('data-breaks') - scope.breaktime;
                    scope.UpdateTimerWithRemainingTime(remaingtime + breakdiff, true);
                } else {
                    // Paused so need to update the break timer.
                    //   - taking into account time since last heartbeat.
                    var breakremaining = Math.floor(scope.breaktime - (diff / 1000));
                    scope.UpdateBreakTimerWithRemainingTime(breakremaining);
                    // If break time used up by outage then adjust exam timer as well.
                    if (breakremaining < 0) {
                        scope.UpdateTimerWithRemainingTime(scope.examtime + breakremaining, true);
                    }
                }
            }
        }

        /**
         * Performs countdown.
         * @param integer remaining_time time remaining to finish exam
         * @param bool close if true saves and closes the exam when time left is 0.
         * @constructor
         */
        this.UpdateTimerWithRemainingTime = function(remaining_time, close) {
            var resume = true;
            if ($('#breaks').hasClass('play')) {
                resume = false;
            }

            if (resume) {
                var minutes = Math.floor(remaining_time / 60);
                minutes = Math.round(minutes);
                var seconds = remaining_time % 60;
                scope.examtime = remaining_time;
                scope.UpdateClock(0, minutes, seconds);

                if (remaining_time == 0 && close == true) {
                    scope.KillClock();
                    scope.forceSave();
                    return;
                }
                if (remaining_time > 0) {
                    remaining_time = remaining_time - 1;
                }
            }
            scope.clockID = setTimeout(scope.UpdateTimerWithRemainingTime, 1000, remaining_time, close);
        };

        /**
         * Performs break timer countdown.
         * @param integer remaining_time time remaining to finish exam
         */
        this.UpdateBreakTimerWithRemainingTime = function(remaining_time) {
            var resume = false;
            if ($('#breaks').hasClass('play')) {
                resume = true;
            }

            if (resume) {
                var minutes = Math.floor(remaining_time / 60);
                minutes = Math.round(minutes);
                var seconds = Math.round(remaining_time % 60);
                scope.breaktime = remaining_time;
                scope.UpdateBreakClock(0, minutes, seconds);

                if (remaining_time <= 0) {
                    scope.KillBreakClock();
                    $('#breaks').removeClass('play');
                    $('#breakstext').html(Jsxls.lang_string['pause']);
                    $("#info_overlay").hide();
                    scope.paused = false;
                    $('#breakstext').css('display', 'none');
                    $('#breaktimeremaining').css('display', 'none');
                    scope.saveBreaks(0);
                    return;
                }
                if (remaining_time > 0) {
                    remaining_time = remaining_time - 1;
                }
            }
            scope.breakID = setTimeout(scope.UpdateBreakTimerWithRemainingTime, 1000, remaining_time);
        };

        /**
         * Update the clock.
         */
        this.UpdateClockWithCurrentTime = function() {

            var tDate   = new Date();

            var hours   = tDate.getHours();
            var minutes = tDate.getMinutes();
            var seconds = tDate.getSeconds();

            scope.UpdateClock(hours, minutes, seconds);

            scope.clockID = setTimeout(scope.UpdateClockWithCurrentTime, 1000);
        };

        /**
         * Start the timer
         * @param integer remaining_time remaining time in seconds
         * @param bool close if true exam closed when time remainign reaches 0
         * @constructor
         */
        this.StartTimer = function(remaining_time, close) {
            scope.clockID = setTimeout(scope.UpdateTimerWithRemainingTime, 500, remaining_time, close);
        };

        /**
         * Start the break timer
         * @param integer remaining_time remaining time in seconds
         * @param bool close if true exam closed when time remainign reaches 0
         */
        this.StartBreakTimer = function(remaining_time) {
            scope.breakID = setTimeout(scope.UpdateBreakTimerWithRemainingTime, 500, remaining_time);
        };

        /**
         * Start the clock.
         */
        this.StartClock = function() {
            scope.clockID = setTimeout(scope.UpdateClockWithCurrentTime, 500);
        };

        /**
         * Stop the clock.
         */
        this.KillClock = function() {
            if (scope.clockID) {
                clearTimeout(scope.clockID);
                scope.clockID = 0;
            }
        };

        /**
         * Stop the break timer clock.
         */
        this.KillBreakClock = function() {
            if (scope.breakID) {
                clearTimeout(scope.breakID);
                scope.breakID = 0;
            }
        };

        /**
         * Strikethrough an option in a question
         * @param string objID selector
         */
        this.onoff = function(objID) {
            var parts = objID.split("_");
            var questionID = parts[0];
            var itemID = parts[1];
            var setting = '0';

            if ($('#' + objID).hasClass("act")) {
                $('#' + objID).addClass("inact")
                $('#' + objID).removeClass("act")
                setting = '1';
            } else {
                $('#' + objID).addClass("act")
                $('#' + objID).removeClass("inact")
            }
            objID = 'dismiss' + questionID;
            var current_value = $('#' + objID).val();
            var new_value = current_value.slice(0,itemID-1) + setting + current_value.slice(itemID,current_value.length);
            $('#' + objID).val(new_value);
        };

        /**
         * Wrapper for window.close, if not a popup we move back a page.
         */
        this.close_window = function() {
            if (window.opener == null) {
                parent.history.back();
            } else {
                window.close();
            }
        };

        /**
         * Validate screen before submission.
         * @param object event event
         */
        this.confirmSubmit = function(event) {
            var el = document.getElementById('paper');
            if (el.dataset.submittype === 'preview') {
                scope.confirmSubmitPreview(event);
            } else if (el.dataset.submittype === 'linear') {
                scope.confirmSubmitLinear(event);
            } else {
                scope.confirmSubmitBiDirectional(event);
            }
        };

        /**
         * If preview mode we just submit the screen.
         * @param object event event
         */
        this.confirmSubmitPreview = function (event) {
            scope.conductSave(event);
        };

        /**
         * If a linear paper we inform the user they cannot go back once submitted and warn about linked questions.
         */
        this.confirmSubmitLinear = function() {
            if ($('#button_pressed').val() === 'finish') {
                scope.showDialog(Jsxls.lang_string['javacheck2']);
            } else {
                var msg = Jsxls.lang_string['javacheck1'];
                if ($('.ecalc-answer').length > 0) {
                    var ecalcQuestions = [];
                    $('.ecalc-answer').each(function(){
                        if ($(this).attr('data-linkparent') == true) {
                            ecalcQuestions[ecalcQuestions.length] = $(this).attr('data-assignednum');
                        }
                    });
                    if (ecalcQuestions.length > 0) {
                        msg = Jsxls.lang_string['javacheck3'].replace('[X]', ecalcQuestions.join());
                    }
                }
                scope.showDialog(msg);
            }

            $("#dialog_ok").click(function(event) {
                $('body').css('cursor','wait');
                scope.submitted = true;
                $("#overlay").hide();
                scope.conductSave(event);
            });
        };

        /**
         * If a bi-directional paper we warn about linked questions.
         * @param object event event
         */
        this.confirmSubmitBiDirectional = function(event) {
            if (scope.submitted === true) {
                return false;
            }
            if ($('#button_pressed').val() === 'finish') {
                scope.showDialog(Jsxls.lang_string['javacheck2']);
                $("#dialog_ok").click(function(event) {
                    $('body').css('cursor','wait');
                    scope.submitted = true;
                    $("#overlay").hide();
                    scope.conductSave(event);
                });
            } else {
                var ecalcQuestions = [];
                $('.ecalc-answer').each(function(){
                    if ($(this).val() === '' && $(this).attr('data-linkparent') == true) {
                        ecalcQuestions[ecalcQuestions.length] = $(this).attr('data-assignednum');
                    }
                });

                if (ecalcQuestions.length > 0) {
                    var msg = Jsxls.lang_string['answerrequired'] + '<br/><br/><strong>' + Jsxls.lang_string['answerrequired_confirm'] + '</strong>';
                    msg = msg.replace('[X]', ecalcQuestions.join());
                    scope.showEnhancedcalcWarning(msg);
                    $("#enhancedcalc_warning_ok").click(function(event) {
                        scope.submitted = true;
                        $('body').css('cursor','wait');
                        $("#overlay").hide();
                        scope.conductSave(event);
                    });
                } else {
                    scope.conductSave(event);
                }
            }
        };

        /**
         * Normal user submit by clicking on next, previous, finish or jump screen
         * @param object event event
         */
        this.checkSubmit = function(event) {
            scope.stopAutoSave();
            Editor.triggerSave();
            if (event === null) {
                $('#button_pressed').attr('value', event.target.id);
            }

            $("#dialog_cancel, #enhancedcalc_warning_cancel").click(function() {
                if ($('#button_pressed').val() === 'jumpscreen') {
                    $('#jumpscreen option').each(function () {
                        if (this.defaultSelected) {
                            this.selected = true;
                            return false;
                        }
                    });
                }
                $('#savemsg').html("");
                $('body').css('cursor','default');
                $("#overlay").hide();
            });
            scope.confirmSubmit(event);
        };

        /**
         * Save the users responses.
         * @param object event event
         */
        this.conductSave = function() {
            var el = document.getElementById('paper');
            Editor.triggerSave();
            scope.stopAutoSave();
            $('#saveError').fadeOut('slow');
            $('#savemsg').html("<img src=\"../artwork/busy.gif\" class=\"busyicon\" />");
            // Log which method the users submitted the page via
            if ($('#button_pressed').val() === 'finish') {
                $('#qForm').attr('action',"finish.php?id=" + el.dataset.pid + el.dataset.urlmod + "&dont_record=true");
            } else {
                $('#qForm').attr('action',"start.php?id=" + el.dataset.pid + el.dataset.urlmod + "&dont_record=true");
            }
            scope.ajaxSave(1, 'userSubmit');
        };

        /**
         * Display an overlay with a message.
         * @param string msg the message
         */
        this.showDialog = function(msg) {
            $("#dialog_cancel").focus();
            $("#submit_dialog_msg").html(msg);
            $("#submit_dialog").css('left', (($(window).width() / 2) - 250) + 'px');
            $("#submit_dialog").css('top', (($(window).height() / 2) - 100) + 'px');
            $(".dialogs").hide();
            $("#submit_dialog").show();
            $("#overlay").show();
        };

        /**
         * Display the link question warning message.
         * @param string msg the message
         */
        this.showEnhancedcalcWarning = function(msg) {
            $("#enhancedcalc_warning_cancel").focus();
            $("#enhancedcalc_warning_msg").html(msg);
            $("#enhancedcalc_warning").css('left', (($(window).width() / 2) - 250) + 'px');
            $("#enhancedcalc_warning").css('top', (($(window).height() / 2) - 200) + 'px');
            $(".dialogs").hide();
            $("#enhancedcalc_warning").show();
            $("#overlay").show();
        };

        /**
         * When a user runs out of time save what they have done.
         */
        this.forceSave = function() {
            var el = document.getElementById('paper');
            scope.stopAutoSave();
            scope.ajaxSave(1, 'forcedSubmit');
            scope.info_dialog(Jsxls.lang_string['forcesave']);
            $('#qForm').attr('action',"finish.php?id=" + el.dataset.pid + el.dataset.urlmod + "&dont_record=true");
            $('#qForm').submit();
        };

        /**
         * Save action called periodically.
         */
        this.autoSave = function() {

            // This could take longer than the autosave timeout stop auto save to stop duplicate events.
            scope.stopAutoSave();

            // Save any data from wysiwyg
            Editor.triggerSave();
            var formData = $('#qForm').serialize();

            // Only auto save if the data has changed, OR 20 minutes has elapsed - stop sessions expiring. ?>
            var now_milliseconds = (new Date).getTime();
            var save_diff = now_milliseconds - scope.last_save_point;
            if (scope.last_saved_user_answers !== formData) {
                $('#savemsg').html("<img src=\"../artwork/busy.gif\" class=\"busyicon\" />");
                scope.ajaxSave(1, 'autoSave');
                scope.last_save_point = (new Date).getTime();
            } else if (save_diff > (1000 * 1200)) {
                scope.ajaxSave(0, 'autoSave');
                scope.last_save_point = (new Date).getTime();
            } else {
                // Re-register the autosave timer
                scope.startAutoSave();
            }
        };

        /**
         * Start the auto save.
         */
        this.startAutoSave = function() {
            var el = document.getElementById('paper');
            clearTimeout(scope.autoSaveRef);// Cancel any outstanding timeouts to make sure only one auto save is ever registered.
            if (typeof el.dataset.savefreq !== 'undefined') {
                // Only autosave if a timeout is defined.
                scope.autoSaveRef = setTimeout(scope.autoSave, el.dataset.savefreq);
            }
        };

        /**
         * Stop the auto save.
         */
        this.stopAutoSave = function() {
            clearTimeout(scope.autoSaveRef);
        };

        /**
         * Save user responses.
         * @param bool ans_changed true if the answers on the screen have changed since the last save.
         * @param string submitType type of submission user action or automatic.
         */
        this.ajaxSave = function(ans_changed, submitType) {
            var el = document.getElementById('paper');
            // Hide any errors
            $('#saveError').fadeOut('fast');
            // Random page ID to stop IE caching results.
            var date = new Date();
            var randomPageID = date.getTime();
            $('#randomPageID').val(randomPageID);
            Editor.triggerSave();
            $.ajax({
                url: 'save_screen.php?id=' + el.dataset.pid + el.dataset.urlmod + '&ans_changed=' + ans_changed + '&submitType=' + submitType + '&rnd=' + randomPageID + el.dataset.urlmod,
                type: 'post',
                data: $('#qForm').serialize(),
                dataType: 'html',
                timeout: el.dataset.savetimeout,
                cache: false,
                tryCount : 0,
                retryLimit : el.dataset.saveretry,
                beforeSend: function() {},
                fail: function() {
                    if (this.retry()) {
                        return;
                    } else  {
                        scope.saveFail('fail', this.url, '', submitType);
                        return;
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    if (xhr.status === 403) {
                        scope.info_dialog(xhr.responseText)
                        scope.stop();
                        return;
                    }
                    if (textStatus === 'error') {
                        if (this.retry()) {
                            return;
                        } else {
                            // Status is the response code and errorThrown is the HTTP response text.
                            if (errorThrown !== '') {
                                scope.saveFail(textStatus + ': ' + xhr.status, this.url, errorThrown, submitType);
                                return;
                            }
                        }
                    }
                    // Just use the xhr status.
                    scope.saveFail(textStatus + ': ' + xhr.status, this.url, '', submitType);
                    return;
                },
                success: function (ret_data) {
                    if (ret_data == randomPageID) {
                        $('#save_failed').val('');
                        //Cache the form data to look for changes on next auto save
                        scope.last_saved_user_answers = this.data;
                        scope.saveSuccess(submitType);
                        return;
                    }
                    if (this.retry()) {
                        return;
                    } else {
                        // marking_funcions.inc record_marks failed after retry.
                        // red_data can be ERROR, a random generated number or a html page.
                        if (ret_data === 'ERROR' || (!isNaN(parseFloat(ret_data)) && isFinite(ret_data))) {
                            // record the returned random number or ERROR.
                            scope.saveFail('record_marks', this.url, ret_data, submitType);
                        } else {
                            // Strip out the title of the html as thats is all we are interested in.
                            var htmlstart = ret_data.indexOf("<title>") + 7;
                            var htmlend = ret_data.indexOf("</title>");
                            var htmltitle = ret_data.substring(htmlstart, htmlend);
                            if (htmltitle === '') {
                                htmltitle = Jsxls.lang_string['htmlresp'];
                            }
                            scope.saveFail('record_marks', this.url, htmltitle, submitType);
                        }
                        return;
                    }
                },
                retry: function (){
                    // Retry if we can
                    this.tryCount++;
                    if (this.tryCount <= this.retryLimit) {
                        // Indicate the retry on the url
                        if (this.tryCount === 1) {
                            this.url = this.url + "&retry=" + this.tryCount;
                        } else {
                            this.url = this.url.replace("&retry=" + (this.tryCount - 1), "&retry=" + this.tryCount);
                        }
                        $.ajax(this);
                        return true;
                    }
                    return false;
                }
            });
            return;
        };

        /**
         * Actions on successful save.
         * @param string submitType type of submission user action or automatic.
         * @returns bool
         */
        this.saveSuccess = function(submitType) {
            // Re-register the autosave timer ?>
            scope.startAutoSave();
            if (submitType === 'userSubmit') {
                $('#qForm').submit();
                return true;
            } else if(submitType === 'forcedSubmit') {
                $('#qForm').submit();
            } else {
                // Clear auto save message ?>
                $('#savemsg').html("");
            }
        };

        /**
         * Actions on failed save.
         * Save information in temp div to save to db when able.
         * @param string textStatus error text response from save attempt
         * @param string url url of failed save attmpet
         * @param string ret_data response string from save attempt
         * @param string submitType type of submission user action or automatic.
         * @returns bool
         */
        this.saveFail = function(textStatus, url, ret_data, submitType) {
            // Re-register the autosave timer
            scope.startAutoSave();

            var current_val =  $('#save_failed').val();
            var unix_now = Math.round($.now() / 1000);
            if (current_val === '') {
                $('#save_failed').val(unix_now  + '-' + textStatus + '-' + url + '-' + ret_data);
            } else {
                $('#save_failed').val(current_val + '\n' + unix_now + '-' + textStatus + '-' + url + '-' + ret_data);
            }
            $('#savemsg').html("");
            // usersubmit always warns, auto save only on application error.
            if (submitType !== 'autoSave' || textStatus === 'record_marks') {
                $('#saveError').fadeIn('fast');
            }
            $('body').css('cursor','default');
            scope.submitted = false;

            return false;
        };

        /**
         * Stop the auto save.
         * @returns bool
         */
        this.stop = function() {
            scope.stopAutoSave();
            $('#savemsg').html("");
            $('body').css('cursor','default');
            scope.submitted = false;
            return false;
        };

        /**
         * Generate the css for the paper.
         */
        this.generatePaperCss = function() {
            var bgcolor = $('#css').attr('data-bgcolor');
            var special_needs = $('#css').attr('data-special_needs');
            var fgcolor = $('#css').attr('data-fgcolor');
            var font = $('#css').attr('data-font');
            var textsize = $('#css').attr('data-textsize');
            var themecolor = $('#css').attr('data-themecolor');
            var marks_color = $('#css').attr('data-marks_color');
            var unanswered_color = $('#css').attr('data-unanswered_color');
            var dismiss_color = $('#css').attr('data-dismiss_color');
            if (special_needs && bgcolor !== '#FFFFFF' && bgcolor !== 'white') {
                $('select,input').css('background-color', bgcolor);
                $('select,input').css('color', fgcolor);
                $('select,input').css('font-family', font + ",sans-serif");
            }
            if ((bgcolor !== '#FFFFFF' && bgcolor !== 'white') || (fgcolor !== '#000000' && fgcolor !== 'black') || textsize !== 90) {
                $('body').css('background-color', bgcolor);
                $('body').css('color', fgcolor);
                $('body').css('font-size', textsize + "%");
            }
            if (font !== 'Arial') {
                $('body').css('font-family', font + "',sans-serif");
                $('pre').css('font-family', font + "',sans-serif");
            }
            if (themecolor !== '#316AC5') {
                $('.theme').css('color', themecolor);
            }
            if (marks_color  !== '#808080') {
                $('.mk').css('color', marks_color);
            }
            if (fgcolor !== '#000000' && fgcolor !== 'black') {
                $('.act').css('color', fgcolor);
            }
            if (unanswered_color !== '#FFC0C0') {
                $('.unans').css('background-color', unanswered_color);
                $('.scr_un').css('background-color', unanswered_color);
            }
            if (dismiss_color !== '#A5A5A5') {
                $('.inact').css('color', dismiss_color);
            }
        };

        /**
         * Pause the exam.
         * @param int paperid paper identifier
         */
        this.pause = function(paperid) {
            scope.paused = true;
            scope.StartBreakTimer(scope.breaktime);
            // Record break.
            $.post($('#dataset').attr('data-rootpath') + "/ajax/invigilator/paper_break.php",
                {
                    paperID: paperid,
                    type: 1
                });
            $('#breaks').removeClass('pause');
            $('#breaks').addClass('play');
            $('#breakstext').html(Jsxls.lang_string['resume']);
            scope.info_dialog(Jsxls.lang_string['paperpaused']);
        }

        /**
         * Save break time left
         * @param int remaining_time the remaining break time
         */
        this.saveBreaks = function(remaining_time) {
            var el = document.getElementById('paper');
            $.post($('#dataset').attr('data-rootpath') + "/paper/save_breaks.php",
                {
                    paperID: el.dataset.paperid,
                    time: Math.round(remaining_time)
                });
        }

        /**
         * Format the break time remaing for display.
         */
        this.formatTimeRemaining = function(breaktime_remaining) {
            var minutes = Math.floor(breaktime_remaining / 60);
            minutes = Math.round(minutes);
            var seconds = Math.round(breaktime_remaining % 60);
            minutes = ( ( minutes  < 10 ) ? "0" : "" ) + minutes;
            seconds = ( ( seconds < 10 ) ? ":0" : ":" ) + seconds;
            $('#breaktimeremaining').html(Jsxls.lang_string['breakremaining'] + ' <span class="thetime">' + minutes + seconds + '</span>');
        }
    }
});
