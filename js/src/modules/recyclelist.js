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
// Recycle list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['jquery'], function($) {
    return function() {
        /**
         * Add item to selected item list.
         * @param integer qID item id
         * @param bool clearall if true add item to list
         */
        this.addQID = function(qID, clearall) {
            if (clearall) {
                $('#itemID').val(',' + qID);
            } else {
                var old_val = $('#itemID').val();
                $('#itemID').val(old_val + ',' + qID);
            }
        };

        /**
         * Remove item from selected item list.
         * @param integer qID item id
         */
        this.subQID = function(qID) {
            var tmpq = ',' + qID;
            var replace_val = $('#itemID').val().replace(tmpq, '')
            $('#itemID').val(replace_val);
        };

        /**
         * Remove all highlighting.
         */
        this.clearAll = function() {
            $('.highlight').removeClass('highlight');
        };

        /**
         * Select an item.
         * @param integer lineID line number
         * @param integer itemID item id
         * @param object evt event
         */
        this.selQ = function(lineID, itemID, evt) {
            $('#menu1a').hide();
            $('#menu1b').show();

            if (evt.ctrlKey == false && evt.metaKey == false) {
                this.clearAll();
                $('#link_' + lineID).addClass('highlight');
                this.addQID(itemID, true);
            } else {
                if ($('#link_' + lineID).hasClass('highlight')) {
                    $('#link_' + lineID).removeClass('highlight');
                    this.subQID(itemID);
                } else {
                    $('#link_' + lineID).addClass('highlight');
                    this.addQID(itemID, false);
                }
            }

            if (evt != null) {
                evt.cancelBubble = true;
            }
        };

        /**
         * Restore a items selected.
         */
        this.restoreItem = function() {
            document.location.href = 'restore_item.php?item_id=' + $('#itemID').val();
        };

    }
});