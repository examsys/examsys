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
// QTI functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
    return function () {
        /**
         * Initialise QTI import/export screens.
         */
        this.init = function() {
            var scope = this;
            $('.openpopup').click(function() {
                scope.newPopup($(this).attr('data-url'));
                return false;
            });

            $('#back').click(function() {
                window.location = '../paper/details.php?paperID=' + $('#dataset').attr('data-id') + '&module=' + $('#dataset').attr('data-module');
            });
        };

        /**
         * Open url in new window.
         * @param string url url to qti info
         */
        this.newPopup = function (url) {
            var notice = window.open(url, "properties", "width=827,height=510,left=" + (screen.width / 2 - 325) + ",top=" + (screen.height / 2 - 250) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        /**
         * Debug screen expand button
         * @param integer id identifier
         */
        this.print_nice_expand = function(id) {
            $('#print_nice_' + id).css('display', 'inline');
        };

        /**
         * Debug screen contract button
         * @param integer id identifier
         */
        this.print_nice_contract = function(id) {
            $('#print_nice_' + id).css('display', 'none');
        };

        /**
         * Debug screen expand all button
         * @param integer id identifier
         */
        this.print_nice_expand_all = function(id) {
            this.print_nice_expand(id)
        };

        /**
         * Debug screen toggle raw output button
         * @param integer id identifier
         */
        this.print_nice_toggle_raw = function(id) {
            if ($('#print_nice_raw_' + id).css('display') == 'none') {
                $('#print_nice_raw_' + id).css('display', 'inline');
            } else {
                $('#print_nice_raw_' + id).css('display', 'none');
            }
        };
    }
});