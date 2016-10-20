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
// Plugins admin page js functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2016 The University of Nottingham
//

/*
 * Launch edit page
 * @param integer plugin id
 */
function edit(id) {
    document.location.href='./edit_plugins.php?pid=' + id;
}

/*
 * Plugin list functions:
 * sort - order the list of plugins via the header
 * highlight - highligts selected plugin
 * double click - launches edit plugin on double click
 */
$(function () {
    if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
            sortList: [[0,0]] 
        });
    }

    $(".l").click(function(event) {
        event.stopPropagation();
        selLine($(this).attr('id'),event);
    });

    $(".l").dblclick(function() {
        edit($(this).attr('id'));
    });
});