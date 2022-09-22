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
// Initialise calendar
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['calendar', 'tabs', 'jquery'], function (CAL, Tabs, $) {
    var calendar = new CAL();
    var tabs = new Tabs();
    tabs.init();
    $('#lab').change(function() {
        $('#theform').submit();
    });

    $('#school').change(function() {
        $('#theform').submit();
    });

    $('.day, .daycur').dblclick(function() {
        calendar.newEvent(this.id);
    });

    $('.event').click(function() {
        calendar.deleteEvent($(this).attr('data-evid'));
    });

    $('.pe').mouseout(function() {
        $('#callout2').hide();
    });

    $('.pe').mouseover(function() {
        calendar.showCallout2($(this).attr('data-cellid'), $(this).attr('data-message'));
    });

    $('.pd').mouseout(function() {
        $('#callout').hide();
    });

    $('.pd').mouseover(function() {
        calendar.showCallout($(this).attr('data-ptype'),
            $(this).attr('data-cellid'),
            $(this).attr('data-stime'),
            $(this).attr('data-etime'),
            $(this).attr('data-duration'),
            $(this).attr('data-labs'),
            $(this).attr('data-pass'),
            $(this).attr('data-tz'),
            $(this).attr('data-meta'));
    });
});
