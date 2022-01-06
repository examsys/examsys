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
// System tooltips js functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'helplauncher'], function($, HELPLAUNCHER) {
    return function () {
        /**
         * Initialise tooltips on screen.
         */
        this.init = function () {
            if (typeof $(document).tooltip !== 'undefined') {
                $(document).tooltip({items: ".help_tip[title]", position: {my: "top+10", at: "center+125"}});
            }
            this.addHelpLinks();
        };

        /**
         * Initialise help links on screen.
         */
        this.addHelpLinks = function () {
            $('.help-link').each(function () {
                var rel = 0;
                if ($(this).attr('rel') !== undefined) {
                    rel = $(this).attr('rel');
                }
                $(this).click(function () {
                    return HELPLAUNCHER.launchHelp(rel, 'staff');
                });
            });
        };
    }
});
