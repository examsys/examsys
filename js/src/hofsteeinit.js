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
// Initialise Hofstee page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['hofstee', 'jquery'], function (HOFSTEE, $) {
    var hofstee_plot_pass = new HOFSTEE();
    var hofstee_plot_dist = new HOFSTEE();
    hofstee_plot_pass.init($('#dataset').attr('data-marks'), $('#dataset').attr('data-stats'), 'canvas_graph_pass','pass');
    hofstee_plot_dist.init($('#dataset').attr('data-marks'), $('#dataset').attr('data-stats'), 'canvas_graph_distinction','distinction');
    $("#checkbox").change(function () {
        hofstee_plot_pass.redraw = true;
        hofstee_plot_pass.g_redraw_canvas();
        hofstee_plot_dist.redraw = true;
        hofstee_plot_dist.g_redraw_canvas();
    });

    $(".tf").keypress(function (event) {
        if (event.keyCode <= 40 && event.keyCode >= 37) {
            hofstee_plot_pass.tfchange(event, true);
            hofstee_plot_dist.tfchange(event, true);
        }
    });

    $(".tf").blur(function (event) {
        hofstee_plot_pass.tfchange(event, false);
        hofstee_plot_dist.tfchange(event, false);
    });
});