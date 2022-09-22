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
// Initialise mapping sessions page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['list', 'mappingsidebar', 'mappingsessionlist', 'jquery'], function (LIST, SIDEBAR, MAPPING, $) {
    var mapping = new MAPPING();
    var list = new LIST();
    var sidebar = new SIDEBAR();
    sidebar.init();
    $(".l").click(function(event) {
        mapping.selSession($(this).attr('id'), $(this).attr('data-identifier'), $(this).attr('data-year'), $(this).attr('data-vle'), event);
        event.stopPropagation();
    });

    $(".l").dblclick(function() {
        list.edit('./edit_session.php?module=' + $('#dataset').attr('data-module') + '&calendar_year=' + $(this).attr('data-year') + '&identifier=', $(this).attr('data-id'));
    });
});