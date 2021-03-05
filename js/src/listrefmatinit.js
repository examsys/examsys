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
// Initialise lists
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['rogoconfig', 'referancelist', 'list', 'jquery', 'jquerytablesorter'], function (config, REF, LIST, $) {
    var ref = new REF();
    var list = new LIST();
    list.init();
    if ($("#maindata").find("tr").length > 1) {
        $("#maindata").tablesorter({
            sortList: [[0,0]]
        });
    }

    $(".l").dblclick(function() {
        list.edit(config.cfgrootpath  + "/module/edit_ref_material.php?module=" + $('#dataset').attr('data-module') + "&refID=", $(this).attr('id'));
    });

    $('#edit').click(function() {
        list.edit(config.cfgrootpath  + "/module/edit_ref_material.php?module=" + $('#dataset').attr('data-module') + "&refID=", $('#lineID').val());
    });

    $('#delete').click(function() {
        ref.deleteReference();
    });
});
