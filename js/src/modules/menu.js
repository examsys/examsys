// JavaScript Document
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
//
// Menu functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'state', 'jquery'], function(config, STATE, $) {
    return function () {
        /**
         * Display/Hide menu sections.
         * @param integer ID section id
         */
        this.updateMenu = function (ID) {
            $('#menu' + ID).toggle();
            var icon = ($('#icon' + ID).attr('src').indexOf('down_arrow_icon.gif') != -1) ? config.cfgrootpath + '/artwork/up_arrow_icon.gif' : config.cfgrootpath + '/artwork/down_arrow_icon.gif';
            var alttag = ($('#icon' + ID).attr('alt') == 'Hide') ? 'Show' : 'Hide';
            $('#icon' + ID).attr('src', icon);
            $('#icon' + ID).attr('alt', alttag);
            var state = new STATE();
            state.updateState('menu' + ID, $('#menu' + ID).css('display'));
        };
    }
});