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
// Initialise question list section.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['list', 'questionlist', 'questionsearch', 'leadinpopup', 'sidebar'], function (LIST, QUESTION, SEARCH, LEADIN, SIDEBAR) {
    var search = new SEARCH();
    search.init($('#dataset').attr('data-datetime'));
    var question = new QUESTION();

    question.check_checkboxes();

    $(".check_type").click(function() {
        question.check_checkboxes();
    });

    $("#check_locked").click(function() {
        question.check_checkboxes();
    });

    $(".q").click(function(evt) {
        var passed_id = $(this).attr('id');
        var remainder = passed_id.substring(1);
        var parts = remainder.split("_");
        var questionID = parts[0];

        $('#menu2a').hide();
        if ($('#' + passed_id).hasClass('lock')) {
            $('#menu2b').show();
            $('#deleteitem').css('display', 'none');
        } else {
            $('#menu2b').show();
            $('#deleteitem').css('display', 'block');
        }

        if (evt.ctrlKey == false && evt.metaKey == false) {
            search.clearAll();
            $('#' + passed_id).addClass('highlight');
            search.addQID(questionID, true);
        } else {
            if ($('#' + passed_id).hasClass('highlight')) {
                $('#' + passed_id).removeClass('highlight');
                search.subQID(questionID);
            } else {
                $('#' + passed_id).addClass('highlight');
                search.addQID(questionID, false);
            }
        }
    });

    if ($('#type').val() == 'objective') {
        question.hide_unmapped_obs();

        $('#filter').keyup(function() {
            var match = $(this).val().toLowerCase();

            if (match.length > 2) {
                $('.check_type').not('.hidden').each(function() {
                    if ($(this).next('label').text().toLowerCase().indexOf(match) == -1) {
                        $(this).closest('div').addClass('filter');
                    } else {
                        $(this).closest('div').removeClass('filter');
                    }
                });
            }
        });

        $('#filter_clear').click(function() {
            $('.filter').removeClass('filter');
            $('#filter').val('');
        })
    }

    if ($('#type').val() == 'all') {
        question.count_questions();
    } else {
        question.check_checkboxes();
    }

    var list = new LIST();
    var offset = $('#list').offset().top;
    var winH = ($(window).height() - offset) - 2;

    list.resizeList(winH);

    $(window).resize(function(){
        list.resizeList(winH);
    });

    var leadin = new LEADIN();
    leadin.init();
    var sidebar = new SIDEBAR();
    sidebar.init();
});
