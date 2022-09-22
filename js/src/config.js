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
// Requirement functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2017 The University of Nottingham
//
requirejs(['jquery'], function ($) {
  $(function () {
    // Toggle IMS blurb if checked on load.
    if ($('#cfg_ims_enabled').is(':checked')) {
      $('#display_ims').toggle();
    }

    // Toggle IMS blurb when checkbox activated.
    $('#cfg_ims_enabled').change(function () {
      $('#display_ims').toggle();
    });
  });
});