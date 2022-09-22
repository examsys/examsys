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
// Init install screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['ui', 'jquery', 'jqueryvalidate', 'jqueryui'], function (UI, $) {
    var ui = new UI();
    // Validate ldap options, and toggle extra settings.
    $("#installForm").validate();
    $('#useLdap').change(function () {
        $('#ldapOptions').toggle();
    });
    $('#uselookupLdap').change(function () {
        $('#ldaplookupOptions').toggle();
    });

    $('#config').click(function() {
        ui.go_config();
    });
});
