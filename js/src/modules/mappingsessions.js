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
// Mapping Session functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
//
define(['jsxls', 'jquery', 'jqueryui'], function(jsxls, $) {
    return function() {
        /**
         * Add new objective to session.
         * @param integer ulId objective list id
         */
        this.addNew = function(ulId) {
            var maxid = 0;
            $('#' + ulId).children().each(function() {
                if(parseInt($(this).attr('id').substring(3)) > maxid) {
                    maxid = parseInt($(this).attr('id').substring(3));
                }
            });
            var id = maxid + 1;
            $('#' + ulId).append('<li class="ui-state-default" id="li_' + id + '" style="margin:0.5em; margin-left:3.5em"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input class="editBox" name="objnew_' + id + '" id="objnew_' + id + '" type="text" value="" placeholder="' + jsxls.lang_string['msg1'] + '" /></li>');
            $('#objectives').val($('#objList').sortable("serialize"));
        };
    }
});