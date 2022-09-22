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
// Initialise module index page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['list', 'sidebar', 'modules', 'moduleoptions', 'jquery'], function (LIST, SIDEBAR, MODULES, OPTIONS, $) {
    var sidebar = new SIDEBAR();
    sidebar.init();
    var modules = new MODULES();
    var offset = $('#list').offset();
    var winH = ($(window).height() - offset.top) - 2;
    var list = new LIST();
    list.resizeList(winH);

    $(window).resize(function(){
        list.resizeList(winH);
    });

    $('#newpaper').click(function() {
        modules.newPaper($('#newpaper').attr('data-module'));
    });

    $('#newquestion').click(function() {
        modules.newQuestion($('#newquestion').attr('data-module'));
    });
    var options = new OPTIONS();
    options.teammembers();
});