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
// Initialise osce form page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jsxls', 'osce', 'jquery'], function (jsxls, OSCE, $) {
    var osce = new OSCE();
    $(function() {
        osce.checkTotals();
    });

    $('.overall').click(function() {
        osce.overallset($(this).attr('data-qid'), $(this).attr('data-rating'), $(this).attr('data-colours'));
    });

    $('#save').replaceWith('<input id="save" type="submit" name="submitButton" value="' + jsxls.lang_string['save'] + '" style="font-size:120%; width:120px; height:35px; font-weight:bold" disabled />');
    $('#save').click(function() {
        osce.ajaxSave();
    });

    $("td[id^=c]").each(function(){
        $(this).click(function() {
            osce.ans($(this).attr('data-qid'), $(this).attr('data-rating'), $(this).attr('data-cols'));
        });
    });
});
