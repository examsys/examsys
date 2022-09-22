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
// Keyword list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
//
define(['rogoconfig', 'jquery'], function(config, $) {
    return function() {
        /**
         * Get ID of last keyword selected.
         * @param string IDs comma seperate string of ids
         * @returns integer
         */
        this.getLastID = function(IDs) {
            var id_list = IDs.split(",");
            var last_elm = id_list.length - 1;

            return id_list[last_elm];
        };

        /**
         * Add keyword id to list of selected keywords.
         * @param integer keyID keyword id
         * @param bool clearall if true only single keyword selected
         */
        this.addKeyID = function(keyID, clearall) {
            if (clearall) {
                $('#keywordID').val(',' + keyID);
            } else {
                $('#keywordID').val($('#keywordID').val() + ',' + keyID);
            }
        };

        /**
         * Remove keyword from selected keyword list.
         * @param keyID
         */
        this.subKeyID = function(keyID) {
            var tmpq = ',' + keyID;
            $('#keywordID').val($('#keywordID').val().replace(tmpq, ''));
        };

        /**
         * Remove selected highlighting from all keywords.
         */
        this.clearAll = function() {
            $('.highlight').removeClass('highlight');
        };

        /**
         * Select a keyword
         * @param integer keyID keyword id
         * @param object evt event
         */
        this.selKey = function(keyID, evt) {

            $('#menu1a').hide();
            $('#menu1b').show();

            if (evt.ctrlKey == false && evt.metaKey == false) {
                this.clearAll();
                $('#link_' + keyID).addClass('highlight');
                this.addKeyID(keyID, true);
            } else {
                if ($('#link_' + keyID).hasClass('highlight')) {
                    $('#link_' + keyID).removeClass('highlight');
                    this.subKeyID(keyID);
                } else {
                    $('#link_' + keyID).addClass('highlight');
                    this.addKeyID(keyID, false);
                }
            }
        };

        /**
         * Open a window to add a new keyword.
         */
        this.newKeyword = function() {
            var keywordwin = window.open(config.cfgrootpath + "/folder/new_keyword.php?module=" + $('#dataset').attr('data-module'),"keywords","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                keywordwin.focus();
            }
        };

        /**
         * Open a window to edit the selected keyword.
         */
        this.editKeyword = function() {
            var keywordwin = window.open(config.cfgrootpath + "/folder/edit_keyword.php?keywordID=" + this.getLastID($('#keywordID').val()) + "&module=" + $('#dataset').attr('data-module'),"keywords","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                keywordwin.focus();
            }
        };

        /**
         * Open a window to delete the selected keyword.
         */
        this.deleteKeyword = function() {
            var keywordwin = window.open(config.cfgrootpath + "/delete/check_delete_team_keyword.php?keywordID=" + $('#keywordID').val() + "&module=" + $('#dataset').attr('data-module'),"keywords","width=500,height=120,left="+(screen.width/2-250)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                keywordwin.focus();
            }
        };

    }
});