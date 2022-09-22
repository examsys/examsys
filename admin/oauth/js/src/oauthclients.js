// JavaScript Document
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
//
// Oauth client admin page js functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2016 The University of Nottingham
//
define(['list', 'jquery', 'jquerytablesorter'], function(LIST, $) {
    return function() {
        this.viewoption = function () {
            window.location.href = './edit_oauthclient.php?client=' + $('#lineID').val();
        };

        this.deleteoption = function () {
            var notice = window.open("../../delete/check_delete_oauthclient.php?client=" + $('#lineID').val() + "", "LTIkeyss", "width=520,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 270, screen.height / 2 - 85);
            if (window.focus) {
                notice.focus();
            }
        };

        this.init = function () {
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
                list.edit('./edit_oauthclient.php?client=', $(this).attr('id'));
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
