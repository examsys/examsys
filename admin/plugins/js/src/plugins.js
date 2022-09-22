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
// Plugins admin page js functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2016 The University of Nottingham
//
//
define(['list', 'jquery', 'jquerytablesorter'], function(LIST, $) {
    return function() {
        /*
         * Plugin list functions:
         * sort - order the list of plugins via the header
         * highlight - highligts selected plugin
         * double click - launches edit plugin on double click
         */
        this.init = function () {
            var list = new LIST();
            if ($("#maindata").find("tr").length > 1) {
                $("#maindata").tablesorter({
                    sortList: [[0, 0]]
                });
            }

            $(".l").click(function (event) {
                event.stopPropagation();
                list.selLine($(this).attr('id'), event);
            });

            $(".l").dblclick(function () {
                list.edit('./edit_plugins.php?pid=', $(this).attr('id'));
            });
        };
    }
});
