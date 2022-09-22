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
// JS translation module.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define (['jquery', 'log'], function($, log) {
    var strings = {};
    // Try to fetch the strings.
    var json_strings = $('#jsutils').attr('data-xls');
    if (json_strings) {
        // Language strings on the page, parse them for use.
        strings = JSON.parse(json_strings);
    } else {
        // Warn the the strings are not loaded on the page, so we can find and fix it.
        log('jsxls: Strings not found', 'warn');
    }
    return {
        lang_string: strings,
    }
});