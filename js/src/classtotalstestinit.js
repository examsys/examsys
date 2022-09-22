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
// Initialise Class Totals Test.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery', 'jqueryvalidate'], function ($) {
    $('#results').hide();

    $('#start').click(function (e) {
        e.preventDefault();
        $('#the_form').validate();
        if ($('#the_form').valid()) {
            var period = $('#period').val();
            var paper = $('#paper').val();
            var passwd = $('#passwd').val();
            $.post('class_totals_with_script_ajax.php',
                {
                    period: period,
                    paper: paper,
                    passwd: passwd
                });
            $('#results').show();
            $('#form').hide();
        }
    });

    $('#status').click(function() {
        var period = $('#period').val();
        var paper = $('#paper').val();
        window.location.href = 'class_totals_with_script_status.php?period=' + period + '&paper=' + paper;
    });
});