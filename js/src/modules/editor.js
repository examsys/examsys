
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
// Ebel grid list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['rogoconfig', 'tinyMCE'], function(Config, Tinymce) {
    return {
        /**
         * Trigger save.
         */
        triggerSave: function () {
            if (typeof(Tinymce) != "undefined") {
                Tinymce.triggerSave();
            }
        },

        /**
         * Init tinymce.
         */
        init: function (textareaselector) {
            Tinymce.init({selector:textareaselector});
        },

        /**
         * Hide all instances of the editor on screen. Useful for printing.
         */
        hide: function() {
            Tinymce.editors.forEach(function(item) {item.hide()});
        }
    }
});
