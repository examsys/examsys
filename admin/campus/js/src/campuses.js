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
// Campus admin page js functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['list', 'jquery', 'jquerytablesorter'], function(LIST, $) {
    return function() {
        this.viewoption = function() {
            document.location.href = './edit_campuses.php?campus=' + $('#lineID').val();
            $('#menu1a').show();
            $('#menu1b').hide();
        };

        this.deleteoption = function() {
            var notice = window.open("../../delete/check_delete_campus.php?campus=" + $('#lineID').val() + "", "notice", "width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250, screen.height / 2 - 100);
            if (window.focus) {
                notice.focus();
            }
        };

        this.init = function() {
            var list = new LIST();
            var scope = this;
            if ($("#maindata").find("tr").length > 1) {
                $("#maindata").tablesorter({
                    sortList: [[0, 0]]
                });
            }

            $(".l").click(function (event) {
                event.stopPropagation();
                list.selLine($(this).attr('id'), event);
            });

            $(".l").dblclick(function () {
                list.edit('./edit_campuses.php?campus=', $(this).attr('id'));
            });

            $("#delete").click(function () {
                scope.deleteoption();
            });

            $("#view").click(function () {
                scope.viewoption();
            });
        };
    }
});
