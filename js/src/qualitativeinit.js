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
// Initialise qualitative page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['qualitative', 'jsxls', 'jquery'], function (QUAL, jsxls, $) {
    var qual = new QUAL();
    $('#highlight').click(function (e) {
        e.preventDefault();
        qual.cleanResponses();
        $('ul.response-list').each(function () {
            var checkRow = qual.getCheckRow();
            qual.occurrance = 0;
            qual.commentMatches = 0;
            $(this).children('li.response').each(checkRow);

            if (qual.terms != '') {
                $(this).next('div.comments').html(jsxls.lang_string['occurencesof'].replace(/%d/, qual.occurrance).replace(/%s/, qual.terms).replace(/%d/, qual.commentMatches));
            } else {
                $(this).next('div.comments').html(jsxls.lang_string['comments'].replace(/%d/, $(this).children('li.response').length));
            }
        });
    })
});