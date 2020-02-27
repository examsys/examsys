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
// UI helper functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'jquery'], function(config, $) {
    return function () {
        /**
         * Refresh screen if option selected.
         */
        this.refreshPage = function() {
            if ($('#refresh').is(':checked')) {
                window.location = location.href;
            }
        };

        // Load config page.
        this.go_config = function() {
            window.location= config.cfgrootpath  + '/admin/config.php';
        };

        /**
         * Check if HMLL5 canvas is supported bu browser.
         * @returns bool
         */
        this.isCanvasSupported = function(){
            var elem = document.createElement('canvas');
            return !!(elem.getContext && elem.getContext('2d'));
        };

        /**
         * Store scroll position.
         */
        this.scrollXY = function() {
            $('#scrOfY').val($(window).scrollTop());
        };

        /**
         * Initialise common UI features.
         */
        this.init = function() {
            /**
             * Cancel button.
             */
            $('.cancel').click(function () {
                if ($(this).attr('id') != 'submit-cancel') {
                    // Submit-cancel buttons call redirect code so do nothing.
                    if ($(this).attr('data-popupid')) {
                        // Otherwise hide if we are a popup.
                        $('#' + $(this).attr('data-popupid')).hide();
                    } else if (window.opener == undefined || $(this).attr('data-help')) {
                        // Or move back in history.
                        history.back();
                    } else {
                        // Do nothing.
                        if ($(this).attr('id') != 'dialog_cancel') {
                            // Non paper dialog so close window.
                            window.close();
                        }
                    }
                }
            });

            /**
             * Home button.
             */
            $('#home').click(function () {
                window.location = config.cfgrootpath  + '/index.php';
            });
        };

    }
});


