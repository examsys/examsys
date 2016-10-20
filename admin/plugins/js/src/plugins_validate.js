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
// Plugins admin page validate js functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2016 The University of Nottingham
//

/*
 * Plugin properties page functions:
 * validate - enforces valid input
 * cancel - close the properties page
 */
$(function () {
    $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
            return true;
        }
    });
    $('form').removeAttr('novalidate');
    $('#cancel').click(function() {
        history.back();
    });
});