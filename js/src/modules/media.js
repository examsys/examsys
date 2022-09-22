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
// Media functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['mejs', 'jquery'], function (mejs, $) {
    return function () {
        /**
         * Initialise mediaelement player.
         */
        this.init = function () {
            var lang = $('#dataset').attr('data-language');
            var mediaElements = $('video, audio');
            var supported_langs = ['cs', 'pl', 'sk'];
            if ($.inArray(lang, supported_langs) != -1) {
                requirejs(['mejs_' + lang], function () {
                    mejs.i18n.language(lang);
                    for (var i = 0, total = mediaElements.length; i < total; i++) {
                        new mejs.MediaElementPlayer(mediaElements[i]);
                    }
                });
            } else {
                for (var i = 0, total = mediaElements.length; i < total; i++) {
                    new mejs.MediaElementPlayer(mediaElements[i]);
                }
            }
        };

        /**
         * Initialise file picker.
         */
        this.filepicker = function () {
            $('.filepicker').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('id');
                $('#filepickersection' + id.substr(10)).toggle();
            });
            $('#file input').prop('disabled', true);
            $('#alt textarea').prop('disabled', true);
            $('#agreement input').change(function() {
                var id = $(this).attr('id').substr(10);
                if ($(this).is(':checked')) {
                    $('#' + id).prop('disabled', false);
                    $('#alt_' + id).prop('disabled', false);
                } else {
                    $('#' + id).prop('disabled', true);
                    $('#alt_' + id).prop('disabled', true);
                }
            });
            $('#agreement p').click(function() {
                var id = $(this).attr('id').substr(1);
                $('#agreement_' + id).click();
            });
            $('#decorative input').change(function() {
                var id = $(this).attr('id').substr(4);
                if ($(this).is(':checked')) {
                    $('#alt_' + id).prop('disabled', true);
                } else if ($('#agreement_' + id).is(':checked')){
                    $('#alt_' + id).prop('disabled', false);
                }
            });
            $('#decorative p').click(function() {
                var id = $(this).attr('id').substr(4);
                $('#dec_' + id).click();
            });
        };
    }
});
