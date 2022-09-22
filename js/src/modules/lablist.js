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
// Labs list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
//
define(['jquery'], function($) {
    return function() {
        /**
         * Select a lab.
         * @param integer labID lab id
         * @param string labNo lab position in list
         * @param object evt event
         */
        this.selLab = function(labID, labNo, evt) {
            var tmp_ID = $('#oldLabNo').val();
            if (tmp_ID != '') {
                $('#' + tmp_ID).css('background-color', 'white');
                $('#' + tmp_ID).css('color', 'black');
            }
            $('#' + labNo).css('background-color', '#316AC5');
            $('#' + labNo).css('color', 'white');

            $('#menu1a').hide();
            $('#menu1b').show();

            $('#labID').val(labID);
            $('#labNo').val(labNo);
            $('#oldLabNo').val(labNo);

            evt.cancelBubble = true;
        };

        /**
         * Deselect a lab.
         */
        this.deselLab = function() {
            var tmp_ID = $('#oldLabNo').val();
            if (tmp_ID != '') {
                $('#' + tmp_ID).css('background-color', 'white');
                $('#' + tmp_ID).css('color', 'black');
            }
            $('#menu1a').show();
            $('#menu1b').hide();
        };

        /**
         * View selected labs details.
         */
        this.viewDetails = function() {
            document.location.href='./lab_details.php?labID=' + $('#labID').val();
            $('#menu1a').show();
            $('#menu1b').hide();
        };

        /**
         * Open a window to delete the selected lab.
         */
        this.deleteLab = function() {
            var notice=window.open("../delete/check_delete_lab.php?labID=" + $('#labID').val() + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 250, screen.height / 2 - 100);
            if (window.focus) {
                notice.focus();
            }

        };
    }
});