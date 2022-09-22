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
// Initialise help pages.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['help', 'alert', 'jquery'], function (HELP, ALERT, $) {
    var help = new HELP();
    $('#toc').scrollTop($('#dataset').attr('data-srcofy'));
    $('#back').click(function () {
        history.back();
    });

    $('#forwards').click(function () {
        history.forward();
    });

    $('#home').click(function () {
        window.location = "index.php?id=1";
    });

    $('#delete').click(function () {
        var alert = new ALERT();
        if (alert.show('confirmdelete')) {
            window.location = 'delete_page.php?id=' + $('#helpid').attr('data-id');
        }
    });

    $('#new').click(function () {
        window.location = 'new_page.php';
    });

    $('#pointer').click(function () {
        window.location = 'new_pointer.php';
    });

    $('#edit').click(function () {
        window.location = 'edit_page.php?id=' + $('#helpid').attr('data-id');
    });

    $('#recycle_bin').click(function () {
        window.location = 'recycle_bin.php';
    });

    $('#info').click(function () {
        window.location = 'stats.php';
    });

    $('#search').click(function () {
        window.location = 'search.php?searchstring=' + $('#searchbox').val();
    });

    $('.gototop').click(function () {
        $("#contents").animate({scrollTop: 0}, "slow");
    });

    $('.book').click(function () {
        var str = $(this).attr('id');
        var sectionID = str.substr(4);

        $('#submenu' + sectionID).toggle();

        var icon = ($('#button' + sectionID).attr('src') == '../open_book.png') ? '../closed_book.png' : '../open_book.png';
        $('#button' + sectionID).attr('src', icon);
    });

    $('.pointer_book').click(function () {
        var str = $(this).attr('id');
        var sectionID = str.substr(12);

        $('#pointer_submenu' + sectionID).toggle();

        var icon = ($('#pointer_button' + sectionID).attr('src') == '../open_book.png') ? '../closed_book.png' : '../open_book.png';
        $('#pointer_button' + sectionID).attr('src', icon);
    });

    $('#toc div.page').click(function () {
        var str = $(this).attr('id');
        window.location = 'index.php?id=' + str.substr(5) + '&srcOfY=' + $('#toc').scrollTop();
    });

    $('.changepage').click(function () {
        help.displayPage($(this).attr('data-show'));
    });

    var docHeight = $(document).height();
    docHeight = docHeight - 135;
    $('#edit1').css('height', docHeight + 'px');

    if ($('#dataset').attr('data-editor') != undefined && $('#dataset').attr('data-editor') != '') {
        var alert = new ALERT();
        alert.plain($('#dataset').attr('data-editor'));
    }
});