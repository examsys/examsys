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
// Initialise review scomments page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['media', 'ui', 'reviewcomment', 'review'], function (Media, UI, COMMENT, REVIEW) {
    var media = new Media();
    media.init();

    $(function() {
        $(window).scroll(function() {
            var ui = new UI();
            ui.scrollXY();
        });
        window.scrollTo(0, $('#dataset').attr('data-srcofy'));
    });

    var comment = new COMMENT();
    var review = new REVIEW();
    review.html5init();

    $('.pencil').click(function() {
        comment.editQ($(this).attr('data-qid'), $(this).attr('data-qno'));
    });
});