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
// Mapping sidebar helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['list', 'alert', 'sidebar', 'jquery'], function(LIST, ALERT, SIDEBAR, $) {
    return function() {
        /**
         * Initialise mapping sidebar menu.
         */
        this.init = function() {
            var scope = this;
            var sidebar = new SIDEBAR();
            sidebar.init();
            var list = new LIST();

            $("#edit").click(function() {
                list.edit('./edit_session.php?module=' + $('#dataset').attr('data-module') + '&calendar_year=' + $('#session').val() + '&identifier=', $('#identifier').val());
            });

            $("#delete").click(function() {
                var notice=window.open("../delete/check_delete_session.php?moduleID=" + $('#dataset').attr('data-module') + "&session=" + $('#session').val() + "&identifier=" + $('#identifier').val() + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
                notice.moveTo(screen.width / 2 - 210, screen.height / 2 - 85);
                if (window.focus) {
                    notice.focus();
                }
            });

            $('.show_copy_menu').click(function(e) {
                scope.showSessCopyMenu(e);
            });

            $('.addsession').click(function() {
                scope.addSession($('#dataset').attr('data-vle'), $('#dataset').attr('data-vlename'), $('#dataset').attr('data-vlehumanname'));
            });

            $('#editvle').click(function() {
                scope.editVLESession($('#dataset').attr('data-vle'), $('#dataset').attr('data-vlename'), $('#dataset').attr('data-vlehumanname'));
            });

            $('#deletevle').click(function() {
                scope.deleteVLESession($('#dataset').attr('data-vle'), $('#dataset').attr('data-vlename'), $('#dataset').attr('data-vlehumanname'));
            });

            $('#copyform').submit(function() {
                return scope.checkCopyYearForm();
            });
        };

        /**
         * Display session copy overlay menu.
         * @param ojbect e event
         */
        this.showSessCopyMenu = function(e) {
            $('#session_copy_menu').show();
            e.cancelBubble = true;
        };

        /**
         * Display add session screen if internal mapping in use.
         * @param string vle mapping system - id empty string internal mapping in use
         * @param string vlename mapping system system name
         * @param string vlehumanname mapping system human readable name
         */
        this.addSession = function(vle, vlename, vlehumanname) {
            if (vle != '') {
                var alert = new ALERT();
                var args = new Array(vlehumanname, vlename);
                alert.notification('vlemapping', args);
            } else {
                document.location.href="add_session.php?module=" + $('#dataset').attr('data-module');
            }
        };

        /**
         * Display error message id user attempts to edit an external mappign systems session.
         * @param string vle mapping system - id empty string internal mapping in use
         * @param string vlename mapping system system name
         * @param string vlehumanname mapping system human readable name
         */
        this.editVLESession = function(vle, vlename, vlehumanname) {
            if (vle != '') {
                var alert = new ALERT();
                var args = new Array(vlehumanname, vlename);
                alert.notification('vlemappingedit', args);
            }
        };

        /**
         * Display error message id user attempts to delete an external mappign systems session.
         * @param string vle mapping system - id empty string internal mapping in use
         * @param string vlename mapping system system name
         * @param string vlehumanname mapping system human readable name
         */
        this.deleteVLESession = function(vle, vlename, vlehumanname) {
            if (vle != '') {
                var alert = new ALERT();
                var args = new Array(vlehumanname, vlename);
                alert.notification('vlemappingdelete', args);
            }
        };

        /**
         * Validate the copying of a session.
         * @returns bool
         */
        this.checkCopyYearForm = function() {
            var formOK = true;

            var sourceList = document.getElementById('source_y');
            var destList = document.getElementById('dest_y');

            var sourceYear = sourceList.options[sourceList.selectedIndex].value;
            var destYear = destList[destList.selectedIndex].value;

            var alert = new ALERT();
            if (sourceYear !== undefined && destYear != undefined && sourceYear != '' && destYear != '') {
                if (sourceYear == destYear) {
                    alert.notification('msg1');
                    formOK = false;
                }
            } else {
                alert.notification('msg2');
                formOK = false;
            }
            return formOK;
        };
    }
});
