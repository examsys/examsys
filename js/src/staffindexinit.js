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
// Initialise admin index page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['alert', 'admin', 'sidebar', 'folderproperties', 'form', 'jquery'], function (ALERT, ADMIN, SIDEBAR, FOLDER, FORM, $) {
    var sidebar = new SIDEBAR();
    sidebar.init();

    var admin = new ADMIN();

    var folder = new FOLDER();

    var form = new FORM();
    form.init();

    $('#announce').click(function() {
        admin.hideAnnouncement($(this).attr('data-id'));
    });

    $('#folder_name').keypress(function(e) {
        folder.illegalChar(e.which);
    });

    $('.review').click(function() {
        admin.startPaper($(this).attr('data-id'), $(this).attr('data-fs'));
    });

    if ($('#dataset').attr('data-duplicate')) {
        var alert = new ALERT();
        alert.notification('duplicatefoldername');
    }
});