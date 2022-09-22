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
// Display a non-modal popup, called on hover events in question search results
//
// @author ?
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['mathjax', 'jquery', 'jqueryui'], function(MathJax, $) {
    return function() {
        /**
         * Initialise leadin overlay display.
         */
        this.init = function () {
            $('.extended-leadin').hover(function () {
                var extendedLeadin = $(this).data('extended-leadin');
                // Delete any extra ones that may have appeared
                $('.question-leadin-popup').remove();
                $('<p class="question-leadin-popup" id="question-leadin-popup-' + $(this).parent().attr('id') + '"></p>')
                    .html(extendedLeadin)
                    .appendTo('body')
                    .fadeIn(200);
                MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
            }, function () {
                var popupId = $(this).parent().attr('id');
                $('#question-leadin-popup-' + popupId).fadeOut(200, function () {
                    $('#question-leadin-popup-' + popupId).remove();
                });
            }).mousemove(function (e) {
                $('#question-leadin-popup-' + $(this).parent().attr('id')).css({top: e.pageY + 20, left: e.pageX + 10});
            });
        };
    }
});
