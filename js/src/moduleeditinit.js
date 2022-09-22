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
// Initialise module edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['moduleform', 'modulessidebar', 'jquery'], function (FORM, SIDEBAR, $) {
    var sidebar = new SIDEBAR();
    sidebar.init();

    var form = new FORM();
    form.init();

    form.setSidebarMenu();

    // Display sms sync options if available to module.
    var externalid = $('#externalid').val();

    if (externalid == '') {
        $('#syncoptions').hide();
    } else {
        $('#syncoptions').show();
    }

    $('#sms').attr('data-id', externalid);
    $('#sms2').attr('data-id', externalid);
    $('#sms3').attr('data-id', externalid);

});
