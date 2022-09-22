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
// Initialise more colours picker.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['colourpicker', 'jquery'], function (PICKER, $) {
    var picker = new PICKER();
    picker.initialSet();
    window.opener.document.getElementById('picker').style.display = 'none';

    $('#r').prop("readonly", true);

    $('#g').prop("readonly", true);

    $('#b').prop("readonly", true);

    $('#hex').prop("readonly", true);

    $('.colour').click(function(){
        picker.updateSwatch($(this).attr('data-colour'));
    });

    $('#ok').click(function() {
        picker.returnColour();
    });
});