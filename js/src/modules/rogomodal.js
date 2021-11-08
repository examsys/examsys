// JavaScript Document
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
//
// Modal functions
// A wrapper so modals can work in browsers that do not support modern web standards.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2021 The University of Nottingham
//
define(['micromodal', 'log', 'jquery'], function(Modal, Log, $) {
    return {
        supported: function () {
            if ("assign" in Object && "from" in Array) {
                return true;
            } else {
                Log('Browser does not support modern web standards. Falling back to basic modals.', 'info');
                return false;
            }
        },
        /**
         * Init Modal
         */
        init: function () {
            if (this.supported()) {
                Modal.init();
            }
        },
        /**
         * Close Modal
         * @param {String} modal
         */
        close: function (modal) {
            if (this.supported()) {
                Modal.close(modal);
            } else {
                $("#" + modal).removeClass('is-open');
            }
        },
        /**
         * Show Modal
         * @param {String} modal
         */
        show: function (modal) {
            if (this.supported()) {
                Modal.show(modal);
            } else {
                $("#" + modal).addClass('is-open');
            }
        }
    }
});
