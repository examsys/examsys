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
// Polyfill functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(function() {
    return function() {
        /**
         * Initiate polyfill functions for old browser support.
         */
        this.init = function() {
            // endsWith has no IE11 support
            // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/endsWith
            if (!String.prototype.endsWith) {
                String.prototype.endsWith = function(search, this_len) {
                    if (this_len === undefined || this_len > this.length) {
                        this_len = this.length;
                    }
                    return this.substring(this_len - search.length, this_len) === search;
                };
            }
        };
    }
});
