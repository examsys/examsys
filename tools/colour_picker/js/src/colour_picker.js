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
// Colour picker
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['user', 'jquery', 'jqueryui'], function(USER, $) {
    return function () {
        this.init = function() {
            this.textBox = '';
            var scope = this;
            $("td[id^=row]").each(function(){
                $(this).click(function() {
                    scope.setColor($(this).attr('data-colour'), $(this).attr('data-url'));
                });
            });

            $('#morecolours').click(function() {
                scope.moreColours();
            });
        };

        this.showPicker = function(pickerID, evt) {
            this.textBox = pickerID;

            var pickerSpan = $('#span_' + pickerID);
            var position = pickerSpan.offset();

            $('#picker').css('top', 20 + position.top + 'px');
            $('#picker').css('left', position.left + 'px');
            $('#picker').show();

            evt.cancelBubble = true;
        };

        this.setColor = function(color, page) {
            $('#' + this.textBox).val(color);
            $('#span_' + this.textBox).css('background-color', color);
            this.hidePicker();

            if (page == '/users/details.php' || page == '/students/settings.php') {
                var user = new USER();
                user.updateAccessDemo();
            }
        };

        this.hidePicker = function() {
            $('#picker').hide();
        };

        this.moreColours = function() {
            var notice = window.open("/tools/colour_picker/more_colours.php?swatch=" + this.textBox + "","colours","width=450,height=370,left="+(screen.width/2-225)+",top="+(screen.height/2-185)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            if (window.focus) {
                notice.focus();
            }
        };

        this.updateSwatch = function(hexval) {
            document.getElementById('r').value = parseInt(hexval.substring(0,2),16)
            document.getElementById('g').value = parseInt(hexval.substring(2,4),16)
            document.getElementById('b').value = parseInt(hexval.substring(4,6),16)
            document.getElementById('hex').value = hexval;
            document.getElementById('swatch').style.backgroundColor = '#' + hexval;
            this.currentColor = '#' + hexval;
        };


        this.returnColour = function() {
            window.opener.document.getElementById('span_' + $('#dataset').attr('data-swatch')).style.backgroundColor = this.currentColor;
            window.opener.document.getElementById($('#dataset').attr('data-swatch')).value = this.currentColor;
            window.close();
        };

        this.initialSet = function() {
            document.getElementById('current').style.backgroundColor = window.opener.document.getElementById('span_' + $('#dataset').attr('data-swatch')).style.backgroundColor;
            document.getElementById('swatch').style.backgroundColor = window.opener.document.getElementById('span_' + $('#dataset').attr('data-swatch')).style.backgroundColor;
        };
    }
});
