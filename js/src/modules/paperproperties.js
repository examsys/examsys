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
// Paper properties
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'alert', 'jquery', 'jqueryui'], function(JsXls, ALERT, $) {
    return function () {
        /**
         * Get the paper metadata.
         */
        this.getMeta = function() {
            var scope = this;
            var mod_codes = '';
            var module_no = $('#module_no').val();

            for (var i = 0; i < module_no; i++) {
                if ($('#mod' + i).attr('checked')) {
                    if (mod_codes == '') {
                        mod_codes = $('#mod' + i).val();
                    } else {
                        mod_codes += ',' + $('#mod' + i).val();
                    }
                }
            }

            // Load metadata.
            $('#metadata_security').load('getMetdataSecurity.php', 'modules=' + mod_codes + '&paperID=' + this.paperid + '&session=' + $('#session').val(), function() {
                    $('#meta_dropdown_no').attr('data-loaded', 1);
                    scope.enablesubmit();
                }
            );
            // Load reference list.
            $('#reference_list').load('getAvailableRefMaterial.php', 'modules=' + mod_codes + '&paperID=' + this.paperid, function() {
                    $('#reference_no').attr('data-loaded', 1);
                    scope.enablesubmit();
                }
            );
        };

        /**
         * Enable submit button only if metadata and reference list loaded.
         */
        this.enablesubmit = function() {
            if ($('#meta_dropdown_no').attr('data-loaded') == 1 && $('#reference_no').attr('data-loaded') == 1) {
                $('#submitpropeties').prop('disabled', false);
            }
        };

        /**
         * Properties window tab selection actions.
         * @param integer sectionID section id
         * @param integer tabID tab id
         */
        this.buttonclick = function(sectionID, tabID) {
            $('#general').hide();
            $('#security').hide();
            $('#seb').hide();
            $('#reviewers').hide();
            $('#feedback').hide();
            $('#rubric').hide();
            $('#prologue').hide();
            $('#postscript').hide();
            $('#reference').hide();
            $('#changes').hide();

            $('#' + sectionID).show();

            $('.tab').each(function() {
                $(this).removeClass('tabon');
            });
            $('.tabon').each(function() {
                $(this).removeClass('tabon');
                $(this).addClass('tab');
            });
            $('#' + tabID).removeClass('tab');
            $('#' + tabID).addClass('tabon');
        };

        this.checkForm = function() {
            var alert = new ALERT();
            if ($('#fyear').val() > $('#tyear').val()) {
                alert.notification('availablefromyear');
                return false;
            } else if ($('fyear').val() == $('#tyear').val() && $('#fmonth').val() > $('#tmonth').val()) {
                alert.notification('availablefrommonth');
                return false;
            } else if ($('#fyear').val() == $('#tyear').val() && $('#fmonth').val() == $('#tmonth').val() && $('#fday').val() > $('#tday').val()) {
                alert.notification('availablefromday');
                return false;
            } else if ($('#fyear').val() == $('#tyear').val() && $('#fmonth').val() == $('#tmonth').val() && $('#fday').val() == $('#tday').val() && $('#fhour').val() > $('#thour').val()) {
                alert.notification('availablefromhour');
                return false;
            } else if ($('#fyear').val() == $('#tyear').val() && $('#fmonth').val() == $('#tmonth').val() && $('#fday').val() == $('#tday').val() && $('#fhour').val() == $('#thour').val() && $('#fminute').val() > $('#tminute').val()) {
                alert.notification('availablefromminute');
                return false;
            }

            var module_no = $('#module_no').val();
            var moduleList = '';
            for (var i = 0; i < module_no; i++) {
                var objectID = 'mod' + i;
                if ($('#' + objectID).attr('checked')) {
                    if (moduleList == '') {
                        moduleList = $('#' + objectID).val();
                    } else {
                        moduleList += ',' + $('#' + objectID).val();
                    }
                }
            }
            if (moduleList == '') {
                alert.notification('msg1');
                return false;
            }

            if ($('#paper_type').val() == '2' && $('#remote_summative').is(':checked') == 0) {
                if ($('#fday').val() != $('#tday').val() || $('#fmonth').val() != $('#tmonth').val() || $('#fyear').val() != $('#tyear').val()) {
                    alert.notification('msg2');
                    return false;
                }
                if ($('#exam_duration_hours').val() == 'NULL' || $('#exam_duration_mins').val() == 'NULL') {
                    alert.notification('msg3');
                    return false;
                }

                // Check from time has been set.
                if ($('#fhour').val() === '' || $('#fminute').val() === '') {
                    alert.notification('missingfromtime');
                    return false;
                }

                // Check to time has been set.
                if ($('#thour').val() === '' || $('#tminute').val() === '') {
                    alert.notification('missingtotime');
                    return false;
                }

                if ($('#session').val() == '') {
                    alert.notification('msg4');
                    return false;
                }
            }

            if ($('#paper_type').val() == '2') {
                // Calculate the minimum hour and minutes based on student accomodations.
                var minavilability = $('#dataset').attr('data-minavail');
                var monthsdiff = parseInt($('#tmonth').val()) - parseInt($('#fmonth').val());
                // Only do the check if exam is in the same month.
                if (monthsdiff == 0) {
                    var daysdiff = parseInt($('#tday').val()) - parseInt($('#fday').val());
                    // Only do check if exam is within a day.
                    if (daysdiff <= 1) {
                        var daysdiffhour = daysdiff * 24;
                        if (minavilability > 60) {
                            var minavilability_hour = Math.floor(minavilability / 60);
                            var minavilability_min = minavilability % 60;
                        } else {
                            minavilability_hour = 0;
                            minavilability_min = minavilability;
                        }
                        // Map minimum hour and minute to 'to hour / to minute'
                        var calculated_min_thours = parseInt($('#fhour').val()) + parseInt(minavilability_hour);
                        var calculated_min_tminutes = parseInt($('#fminute').val()) + parseInt(minavilability_min);
                        if (calculated_min_tminutes > 60) {
                            calculated_min_thours += 1;
                            calculated_min_tminutes -= 60;
                        }
                        // Check that availability meets the duration requirement.
                        var durationnotmet = false;
                        if (parseInt($('#thour').val() + daysdiffhour) < calculated_min_thours) {
                            durationnotmet = true;
                        }
                        if (parseInt($('#thour').val() + daysdiffhour) === calculated_min_thours && parseInt($('#tminute').val()) < calculated_min_tminutes) {
                            durationnotmet = true;
                        }
                        if (durationnotmet) {
                            alert.notification('durationnotmet');
                            return false;
                        }
                    }
                }
            }

            if ($('#paper_type').val() == '4') {
                module_no = $('#module_no').val();

                moduleList = '';
                for (var l = 0; l < module_no; l++) {
                    var osceobjectID = 'mod' + l;
                    if ($('#' + osceobjectID).attr('checked')) {
                        if (moduleList == '') {
                            moduleList = $('#' + osceobjectID).val();
                        } else {
                            moduleList += ',' + $('#' + osceobjectID).val();
                        }
                    }
                }
                if (moduleList == '') {
                    alert.notification('msg5');
                    return false;
                }

                if ($('#session').val() == '') {
                    alert.notification('msg4');
                    return false;
                }
            }

            var external_set = false;
            for (var j = 0; j < $('#examiner_no').val(); j++) {
                var examobjectID = 'examiner' + j;
                if ($('#' + examobjectID).attr('checked')) {
                    external_set = true;
                }
            }
            if (external_set == true) {
                if ($('#ext_tmonth').val() == '') {
                    alert.notification('msg6');
                    return false;
                } else if ($('#ext_tday').val() == '') {
                    alert.notification('msg6');
                    return false;
                } else if ($('#ext_tyear').val() == '') {
                    alert.notification('msg6');
                    return false;
                }
            }

            var internal_set = false;
            for (var k = 0; k < $('#internal_no').val(); k++) {
                var internalobjectID = 'internal' + k;
                if ($('#' + internalobjectID).attr('checked')) {
                    internal_set = true;
                }
            }
            if (internal_set == true) {
                if ($('#int_tmonth').val() == '') {
                    alert.notification('msg6a');
                    return false;
                } else if ($('#int_tday').val() == '') {
                    alert.notification('msg6a');
                    return false;
                } else if ($('#int_tyear').val() == '') {
                    alert.notification('msg6a');
                    return false;
                }
            }
            return true;
        };

        /**
         * Change feedback options given paper type.
         */
        this.changeType = function() {
            if ($('#paper_type').val() == '0') {
                $('#feedback_on').show();
                $('#feedback_off').hide();
            } else {
                $('#feedback_on').hide();
                $('#feedback_off').show();
            }
        };

        /**
         * Update the availability warning message.
         */
        this.updateAvailability = function () {
            var alert = new ALERT();
            $.ajax({
                url: $('#dataset').attr('data-rootpath') + '/paper/get_min_availability.php',
                type: "post",
                data: {paperid: this.paperid, exam_duration_hours: $('#exam_duration_hours').val(), exam_duration_mins: $('#exam_duration_mins').val()},
                dataType: "json",
                success: function (data) {
                    if (data[0] == 'SUCCESS') {
                        $('#minavail').html(JsXls.lang_string['minavailability'].replace('%s', data[1]));
                        $('#minavail').css('display','block');
                        $('#minavailflag').css('display','contents');
                    }
                },
                error: function (xhr, textStatus) {
                    alert.plain(textStatus);
                },
            });
        };
    }
});
