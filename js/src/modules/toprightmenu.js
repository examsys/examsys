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
// Menu functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['rogoconfig', 'jquery'], function(config, $) {
    return function() {
        /**
         * Initialise top right menu.
         */
        this.init = function () {
            var scope = this;

            // Add keyboard event listeners.
            var menubutton = document.getElementById('toprightmenu_icon');
            if (menubutton != null) {
                menubutton.addEventListener('keydown', function (event) {
                    if (event.keyCode && event.keyCode == 13) {
                        if ($('#toprightmenu').is(':visible')) {
                            $('#toprightmenu').fadeOut();
                        } else {
                            $('#toprightmenu').fadeIn();
                        }
                    }
                });
            }

            $(document).click(function () {
                $('#toprightmenu').fadeOut();
            });

            $('#toprightmenu_icon').click(function () {
                if ($('#toprightmenu').is(':visible')) {
                    $('#toprightmenu').fadeOut();
                } else {
                    $('#toprightmenu').fadeIn();
                }
                return false;
            });

            $('.header').click(function () {
                if ($('#toprightmenu').is(':visible')) {
                    $('#toprightmenu').fadeOut();
                }
            });

            $('#admintools').click(function () {
                scope.launchAdmin();
            });

            $('#signout').click(function () {
                scope.logout();
            });

            $('#displaycredits').click(function () {
                scope.launchCredits();
            });

            $('#aboutrogo').click(function () {
                scope.launchCredits();
            });

            $('#userprofile').click(function () {
                scope.launchProfile();
            });

            $('#admintools').keydown(function (event) {
                if (event.keyCode && event.key == "Enter") {
                    scope.launchAdmin();
                }
            });

            $('#signout').keydown(function (event) {
                if (event.keyCode && event.key == "Enter") {
                    scope.logout();
                }
            });

            $('#aboutrogo').keydown(function (event) {
                if (event.keyCode && event.key == "Enter") {
                    scope.launchCredits();
                }
            });

            $('#userprofile').keydown(function (event) {
                if (event.keyCode && event.key == "Enter") {
                    scope.launchProfile();
                }
            });
        };

        /**
         * Open a window with the ExamSys credits.
         */
        this.opencredits = function () {
            var notice = window.open(config.cfgrootpath + "/credits/index.php", "credits", "width=696,innerwidth=708,height=510,innerheight=560,scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=0,menubar=0");
            notice.moveTo(screen.width / 2 - 350, screen.height / 2 - 255)
            if (window.focus) {
                notice.focus();
            }
        };

        this.launchAdmin = function() {
            $('#toprightmenu').hide();
            location.href = config.cfgrootpath + '/admin/index.php';
        }

        this.logout = function() {
            $('#toprightmenu').hide();
            location.href = config.cfgrootpath + '/logout.php';
        }

        this.launchCredits = function() {
            $('#toprightmenu').hide();
            this.opencredits();
        }

        this.launchProfile = function() {
            $('#toprightmenu').hide();
            location.href = config.cfgrootpath + '/students/settings.php';
        }
    }
});
