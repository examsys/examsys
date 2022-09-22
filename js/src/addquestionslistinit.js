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
// Initialise add questions list section.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['addquestionscommon', 'state', 'alert', 'jquery'], function (COMMON, STATE, ALERT, $) {
    var state = new STATE()
    state.init();

    COMMON.populateTicks();

    $('#searchterm').focus();

    $('.viewq').click(function() {
        COMMON.previewq($(this).attr('data-qid'));
    });

    $('#search').submit(function() {
        if ($('#searchterm').val() == '' || ($('#searchtype').val() == '%' && $('#owner').val() == '')) {
            var alert = new ALERT();
            alert.notification("msg1");
            return false;
        }
    });

    $(":checkbox").change(function() {
        var selected_q = window.parent.$("#questions_to_add").val().split(',');
        COMMON.updatearray(this, selected_q);
    });

    $('.statechange').change(function() {
        state.updateDropdownState(this, $(this).attr('data-type'));
    });
});