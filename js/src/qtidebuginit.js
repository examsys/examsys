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
// Initialise start page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['qti', 'jquery'], function (QTI, $) {
    var qti = new QTI();
    $('.expand').click(function() {
        qti.print_nice_expand($(this).attr('data-id'));
    });
    $('.contract').click(function() {
        qti.print_nice_contract($(this).attr('data-id'));
    });
    $('.all').click(function() {
        qti.print_nice_expand_all($(this).attr('data-id'));
    });
    $('.raw').click(function() {
        qti.print_nice_toggle_raw($(this).attr('data-id'));
    });
});