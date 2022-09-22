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
// Add Questions Buttons
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Initialise the buttons module.
         */
        this.init = function () {
            this.selectedButton = 'unused';
        };
        /**
         * Actions to take when button clicked.
         * @param integer sectionID the section id
         * @param string scriptName the php script to load
         */
        this.buttonclick = function(sectionID, scriptName) {
            $("#iframeurl").attr("src", scriptName);
            $("#previewurl").attr("src", 'preview_default.php');

            $('.tab').removeClass('tabon');

            $('.tabon').each(function() {
                $(this).removeClass('tabon');
                $(this).addClass('tab');
            });
            $('#button_'+sectionID).removeClass('tab');
            $('#button_'+sectionID).addClass('tabon');


            this.selectedButton = sectionID;
        };
        /**
         * Actions to take when mouse over button
         * @param integer buttonID the button id
         */
        this.buttonover = function(buttonID) {
            if (buttonID != this.selectedButton) {
                $('#button_'+buttonID).css('backgroundColor','#FFE7A2');
            }
        };
        /**
         * Actions to take when mouse no longer over button
         * @param integer buttonID the button id
         */
        this.buttonout = function(buttonID) {
            if (buttonID != this.selectedButton) {
                $('#button_'+buttonID).css('backgroundColor','white');
            }
        };
    }
});
