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
// SCT question edit functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Update SCT type.
         * @param object el element
         */
        this.updateSctType = function (el) {
            var sct_types = JSON.parse($('#sctdataset').attr('data-sct_types'));
            var type_index = $(el).val() - 1;
            $('#sct-hypothesis').text(sct_types[type_index][0]);

            $('.sct-option').each(function (i) {
                $(this).val(sct_types[type_index][i + 1]);
            })
        };
    }
});
