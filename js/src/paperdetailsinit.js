// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Initialise paper details page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jsxls', 'helplauncher', 'ui', 'leadinpopup', 'papersidebar', 'paperdetails', 'jquery', 'jqueryui', 'jquerytablesorter'], function (jsxls, HELPLAUNCHER, UI, LEADIN, PAPER, DETAILS, $) {
    $('.blacklink').click(function() {
        HELPLAUNCHER.launchHelp(189, 'staff');
    });
    $(window).scroll(function() {
        var ui = new UI();
        ui.scrollXY();
    });
    var leadin = new LEADIN();
    leadin.init();
    var paper = new PAPER();
    paper.init();

    var details = new DETAILS();

    var paperid = $('#dataset').attr('data-paperid');

    $('.head_title').click(function (e) {
        e.stopPropagation();
        details.qOff();
    });

    if (!$('#dataset').attr('data-locked')) {
        $.ajaxSetup({timeout: 3000});
        $('#content').ajaxError(function () {
            details.showAJAXError();
        });

        details.resetLinks();

        $('.menu_list a').css('cursor', 'text').click(function (e) {
            e.preventDefault();
        });

        var deleteLink = $('#delete_break');
        $('html').click(function () {
            details.deActivateDelete(deleteLink);
        });

        $('.breakline:gt(0)').click(function (e) {
            e.stopPropagation();
            details.qOff();
            details.deActivateDelete(deleteLink);
            $(this).addClass('line-selected');
            if (deleteLink.hasClass('greymenuitem')) {
                details.activateDelete(deleteLink, $(this).attr('id'));
            }
        });

        $('#sortable tbody').sortable({
            items: '.qline:not(#link_break1)',
            axis: 'y',
            cancel: '#link_break1',
            appendTo: 'body',
            start: function (event, ui) {
                var keepWidth = 0;
                if (ui.item.hasClass('breakline')) {
                    keepWidth = $('tr.details-head:first').width();
                    ui.helper.find('td:first').width(keepWidth);
                } else {
                    keepWidth = $('th.q-cell:first').width();
                    ui.helper.find('td.l').width(keepWidth);
                }
            },
            beforeStop: function (event, ui) {
                if (!ui.item.hasClass('qline')) {
                    var breaks = $('.breakline').length;
                    var row = $(document.createElement('tr'));
                    row.addClass('breakline');
                    row.addClass('qline');
                    row.attr('id', 'link_break' + (breaks + 1));
                    row.attr('data-order', 'link_break' + (breaks + 1));
                    row.html('<td colspan="6"><h4><span class="opaque screen_no">' + jsxls.lang_string["screen1"] + '</span></h4></td>');
                    row.mouseover(function () {
                        $(this).find('img.handle').show();
                    });
                    row.mouseout(function () {
                        $(this).find('img.handle').hide();
                    });
                    row.triggerHandler('click');
                    $(ui.item).replaceWith(row);
                }
            },
            update: function () {
                $('.qline').css('background-color', '#fff');
                var order = $('#sortable tbody').sortable('serialize', {attribute: 'data-order'});
                $.get('../ajax/paper/order-questions.php?paperID=' + paperid + '&' + order, function (data) {
                    if (data == 'ERROR') {
                        details.showAJAXError();
                    } else {
                        window.location.reload();
                    }
                });
            }
        });
    }

    $("tr[class^=link_]").each(function(){
        $(this).click(function(e) {
            details.selQ($(this).data("question_no"), $(this).data("qid"), $(this).data("x"), $(this).data("qtype"), $(this).data("screen"), $(this).data("pid"),
                $(this).data("disppos"), $(this).data("menuid"), $(this).data("random"), e, $(this).data("osce"));
        });
        $(this).dblclick(function() {
            details.edQ($(this).data("question_no"), $(this).data("qid"), $(this).data("qtype"));
        });
    });

    $("tr[id^=r]").dblclick(function() {
        details.edQ($(this).data("question_no"), $(this).data("qid"), $(this).data("qtype"));
    });

    details.scrollto($('#dataset').attr('data-srcofy'));

    if ($('#dataset').attr('data-recache')) {
        $.post('../reports/recache_class_totals.php', {paperID: paperid});
    }
});
