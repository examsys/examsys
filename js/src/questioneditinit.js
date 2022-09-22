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
// Initialise question edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['media', 'questionedit', 'questionmapping', 'state', 'jquery'], function (Media, ADDEDIT, MAPPING, STATE, $) {
    var media = new Media();
    media.init();
    media.filepicker();
    var addedit = new ADDEDIT();
    var mapping = new MAPPING();
    var state = new STATE();

    if ($('#savebutton').attr('data-postExam') == 1) {
        if ($('#savebutton').attr('data-otherSummatives') == 0) {
            var prev_val;
            $('#option_marks_correct').focus(function () {
                prev_val = $(this).val();
            }).change(function () {
                $(this).blur();
                return addedit.showMarksWarning($(this), prev_val);
            });
            $('#option_marks_incorrect').focus(function () {
                prev_val = $(this).val();
            }).change(function () {
                $(this).blur();
                return addedit.showMarksWarning($(this), prev_val);
            });
            $('#option_marks_partial').focus(function () {
                prev_val = $(this).val();
            }).change(function () {
                $(this).blur();
                return addedit.showMarksWarning($(this), prev_val);
            });
        } else {
            $('#option_marks_correct').attr('disabled', 'disabled');
            $('#option_marks_incorrect').attr('disabled', 'disabled');
            $('<input type="hidden" id="option_marks_correct_hidden" name="option_marks_correct" value="' + $('#option_marks_correct').val() + '" />').insertAfter('#option_marks_correct');
            $('<input type="hidden" id="option_marks_incorrect_hidden" name="option_marks_incorrect" value="' + $('#option_marks_incorrect').val() + '" />').insertAfter('#option_marks_incorrect');
        }
    }

    $('.tabs li a').click(function() {
        addedit.changeTab(this);
    });

    $('.next-option').click(function(e) {
        addedit.showNextOption(this, e);
    });

    $('label.fullwidth input').click(function () {
        $(this).parent().toggleClass('on');
    });

    $('.media-delete').click(function () {
        var id = $(this).attr('rel');
        $('#media' + id).slideUp('slow', function () {
            $(this).html('<span class="warning">Current media will be deleted on save</span>');
            $(this).fadeIn();
        });
        $('#delete_media' + id).prop('checked', true);
        $('#existing_media' + id).val('');
        return false;
    });

    $('#score_method').change(function() {
        addedit.showPartialMarks();
    });

    addedit.trimLongChanges();

    $('#addbank').click(function() {
        addedit.checkMapping();
    });

    $('#media-labels-link').click(function() {
        $('#media-label-upload').slideToggle();
        return false;
    });

    mapping.init();
    state.init();

    var tab = $('#dataset').attr('data-tab');
    if (tab != '') {
        $(".tabs li a[rel=" + tab + "]").trigger('click');
    }
});
