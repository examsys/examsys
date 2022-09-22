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
// Summative management functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @version 1.0
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'jquery'], function(config, $) {
    return function() {
        /**
         * Open a window to edit the summative papers properties.
         */
        this.editProperties = function() {
            var properties=window.open(config.cfgrootpath + "/paper/properties.php?paperID=" + $('#dataset').attr('data-paperid') + "&caller=scheduling&noadd=y","properties","width=888,height=650,left="+(screen.width/2-444)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            properties.focus();
        };

        /**
         * Open a window to convert the summative paper into a formative paper.
         */
        this.convtFormative = function() {
            var notice=window.open(config.cfgrootpath + "/admin/check_convert_formative.php?paperID=" + $('#dataset').attr('data-paperid'),"notice","width=350,height=150,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width/2-175,screen.height/2-75);
            if (window.focus) {
                notice.focus();
            }
        };
    }
});
