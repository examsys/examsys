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
// OSCE report functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery', 'rogoconfig'], function($, CONFIG) {
    return function () {
        /**
         * View OSCE script.
         */
        this.viewScript = function() {
            $('#menudiv').hide();
            if (this.metadataid != '') {
                var winwidth = 750;
                var winheight = screen.height-80;
                var url = CONFIG.cfgrootpath + "/osce/view_form.php?paperID=" + this.paperid + "&userID=" + this.userid;
                window.open(url,"paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            }
        };

        /**
         * View feedback.
         */
        this.viewFeedback = function() {
            $('#menudiv').hide();
            if (this.metadataid != '') {
                var winwidth = screen.width-80;
                var winheight = screen.height-80;
                window.open("../students/objectives_feedback.php?id=" + this.cryptname + "&userID=" + this.userid + "&metadataID=" + this.metadataid + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            }
        };

        /**
         * View student profile.
         */
        this.viewProfile = function() {
            $('#menudiv').hide();
            window.location = '../users/details.php?userID=' + this.userid;
        };

        /**
         * Set rating row.
         * @param integer q_id question id
         * @param integer rating rating
         * @param integer max_col max rating available
         */
        this.ans = function(q_id, rating, max_col) {
            var colors = ['#D99694', '#E5B9B7', '#FFC169', '#C2D69B', '#C2DFFF','#5ea2ef','#4b0082', '#4b00FF','#9400d3','#9400FF'];
            $('#q' + q_id + '_val').val(rating);
            for(var i=0; i< max_col+1; i++) {
                if (rating == i) {
                    $('#c' + q_id + '_' + i).css('background-color', colors[i - 1]);
                } else {
                    $('#c' + q_id + '_' + i).css('background-color', '');
                }
            }
            this.checkTotals();
        };

        /**
         * Check if save button can be enabled.
         */
        this.checkTotals = function() {
            var rated, level1, level2, level3, level4, level5, level6, level7, level8, level9, level10;
            rated = level1 = level2 = level3 = level4 = level5 = level6 = level7 = level8 = level9 = level10 = 0;
            for (var i = 1; i <= $('#dataset').attr('data-number_of_qs'); i++) {
                if ($('#q' + i + '_val').val() == '1') {
                    level1++;
                } else if ($('#q' + i + '_val').val() == '2') {
                    level2++;
                } else if ($('#q' + i + '_val').val() == '3') {
                    level3++;
                } else if ($('#q' + i + '_val').val() == '4') {
                    level4++;
                } else if ($('#q' + i + '_val').val() == '5') {
                    level5++;
                } else if ($('#q' + i + '_val').val() == '6') {
                    level6++;
                } else if ($('#q' + i + '_val').val() == '7') {
                    level7++;
                } else if ($('#q' + i + '_val').val() == '8') {
                    level8++;
                } else if ($('#q' + i + '_val').val() == '9') {
                    level9++;
                } else if ($('#q' + i + '_val').val() == '10') {
                    level10++;
                }
            }
            rated = level1 + level2 + level3 + level4 + level5 + level6 + level7 + level8 + level9 + level10;

            $('#level1').val(level1);
            $('#level2').val(level2);
            $('#level3').val(level3);
            $('#level4').val(level4);
            $('#level5').val(level5);
            $('#level6').val(level6);
            $('#level7').val(level7);
            $('#level8').val(level8);
            $('#level9').val(level9);
            $('#level10').val(level10);


            if ($('#dataset').attr('data-marking') == '5') {
                if (rated == $('#q_no').val()) {
                    $('#save').prop('disabled', false);
                } else {
                    $('#save').prop('disabled', true);

                }
            } else {
                if (rated == $('#q_no').val() && $('#overall_val').val() != '0') {
                    $('#save').prop('disabled', false);
                } else {
                    $('#save').prop('disabled', true);

                }
            }
        };

        /**
         * Set overall rating.
         * @param integer q_id question id
         * @param integer rating overall rating
         * @param array colours rating display colours
         */
        this.overallset = function(q_id, rating, colours) {
            var colors = JSON.parse(colours);
            for (var i = 1; i <= colors.length; i++) {
                if (i == rating) {
                    $('#overall' + i).css('background-color', colors[i-1]);
                } else {
                    $('#overall' + i).css('background-color', '');
                }
            }
            $('#overall_val').val(rating);
            this.checkTotals();
        };

        /**
         * Ajax save handler.
         * @returns bool
         */
        this.ajaxSave = function () {
            var settimeout = $('#dataset').attr('data-timeout');
            var retrylimit = $('#dataset').attr('data-retry');
            var backofffactor = $('#dataset').attr('data-backoff');

            // Redirect the form
            $('#osceform').attr('action', $('#dataset').attr('data-self') + '?id=' + $('#dataset').attr('data-id') + '&dont_record=true');

            // Hide any errors
            $('#saveError').fadeOut('fast');
            // Random page ID to stop IE caching results.
            var date = new Date();
            var randomPageID = date.getTime();
            $.ajax({
                url: $('#dataset').attr('data-self') + '?id=' + $('#dataset').attr('data-id') + '&rnd=' + randomPageID + '&dont_redirect=true',
                type: 'post',
                data: $('#osceform').serialize() + '&submit=true',
                dataType: 'html',
                // Set the time out of one requst to be the maximum total time plus 5s for network latency
                // PHP handles nomal timeouts. This is just to make sure the user wont wait forever if somthing
                // weird happens.
                timeout: Math.ceil(((retrylimit * backofffactor * settimeout) + settimeout + 5)) * 1000,
                cache: false,
                tryCount : 0,
                retryLimit : retrylimit,
                beforeSend: function() {
                },
                fail: function() {
                    if (this.retry()) {
                        return;
                    } else {
                        this.saveFail();
                        return;
                    }
                },
                error: function(xhr, textStatus) {
                    if (textStatus == 'timeout' ) {
                        this.saveFail();
                        return;
                    } else if (textStatus == 'error') {
                        if (this.retry()) {
                            return;
                        } else  {
                            this.saveFail();
                            return;
                        }
                    }
                    this.saveFail();
                    return;
                },
                success: function (ret_data) {
                    if (ret_data == randomPageID) {
                        this.saveSuccess();
                        return;
                    }
                    if (this.retry()) {
                        return;
                    } else  {
                        this.saveFail();
                        return;
                    }
                },
                retry: function () {
                    // Retry if we can
                    this.tryCount++;
                    if (this.tryCount <= this.retryLimit) {
                        // Indicate the retry on the url
                        if (this.tryCount == 1) {
                            this.url = this.url + "&retry=" + this.tryCount;
                        } else {
                            this.url = this.url.replace("&retry=" + (this.tryCount - 1), "&retry=" + this.tryCount);
                        }
                        $.ajax(this);
                        return true;
                    }
                    return false
                }
            });
            return false;
        };

        /**
         * Submit the form.
         * @returns bool
         */
        this.saveSuccess = function () {
            $('#osceform').submit();
            return true;
        };

        /**
         * Handle save failure.
         * @returns bool
         */
        this.saveFail = function () {
            $('#saveError').fadeIn('fast');
            $('#savemsg').html("");
            document.body.style.cursor = 'default';
            return false;
        };

        /**
         * Open users form.
         * @param integer userID user id
         */
        this.classlist = function(userID) {
            window.location.href = "form.php?id=" + $('#dataset').attr('data-id') + "&userID=" + userID.substr(4);
        };
    }
});
