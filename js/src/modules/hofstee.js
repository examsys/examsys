// JavaScript Document
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
// Hofstee plot
//
// @author Nikodem Miranowicz
// @version 1.0
// @copyright Copyright (c) 2013 The University of Nottingham
// @package
//
define(['jquery', 'jsxls'], function($, jsxls) {
    return function() {
        this.scale_x = 3.5;
        this.scale_y = 3.5;
        this.graph_w = this.scale_x * 100;
        this.graph_h = this.scale_y * 100;
        this.graph_x = 70;
        this.graph_y = 50;
        this.dragging = false;
        this.redraw = true;
        this.boundaries = [];
        this.temp_boundaries = [];
        this.active_line = 0;
        this.d_x = 40;
        this.d_y = 200;
        this.colours = ['#C00000', '#538135', '#5B9BD5'];
        this.shadows = ['#FF8888', '#D7E3BC', ''];
        this.graph_data = [];

        /**
         * Initialise hofstee canvas
         * @param array marks paper marks
         * @param array stats paper statistics
         * @param string canvas_id canvas selector
         * @param string result_type hofstee type
         */
        this.init = function(marks, stats, canvas_id, result_type) {
            this.result_type = result_type;
            this.stats = stats;
            //recalculating data
            var data, last_data = 0;
            var j = -1;
            for (var i = 0; i < marks.length; i++) {
                data = 1 * marks[i];
                if (data != last_data) j++;
                this.graph_data[j] = [data, (i + 1) * 100 / marks.length, i];
                last_data = data;
            }

            this.temp_boundaries = [marks[0], marks[marks.length - 1], 0, 100];
            this.boundaries = [marks[0], marks[marks.length - 1], 0, 100];

            if (document.getElementById('x1_' + this.result_type).value != '') this.boundaries[0] = Number(document.getElementById('x1_' + this.result_type).value.replace('%', ''));
            if (document.getElementById('x2_' + this.result_type).value != '') this.boundaries[1] = Number(document.getElementById('x2_' + this.result_type).value.replace('%', ''));
            if (document.getElementById('y1_' + this.result_type).value != '') this.boundaries[2] = Number(document.getElementById('y1_' + this.result_type).value.replace('%', ''));
            if (document.getElementById('y2_' + this.result_type).value != '') this.boundaries[3] = Number(document.getElementById('y2_' + this.result_type).value.replace('%', ''));

            if (this.boundaries[1] < this.boundaries[0]) this.boundaries[1] = [this.boundaries[0], this.boundaries[0] = this.boundaries[1]][0];
            if (this.boundaries[3] < this.boundaries[2]) this.boundaries[3] = [this.boundaries[2], this.boundaries[2] = this.boundaries[3]][0];

            this.canvas = document.getElementById(canvas_id);

            if (this.canvas && this.canvas.getContext) {
                this.canvas.onmouseup = this.g_mouseDragUp.bind(this);
                this.canvas.onmousedown = this.g_mouseDragDown.bind(this);
                this.canvas.onmousemove = this.g_mouseDragMove.bind(this);
            }

            if (this.canvas && !this.canvas.getContext) {
                alert('canvas not supported');
            }

            if (this.canvas && this.canvas.getContext) {
                this.context = this.canvas.getContext('2d');
                window.setInterval(this.g_redraw_canvas(), 10);
            }
        };

        /**
         * Draw a line.
         * @param cc
         * @param xx
         * @param yy
         * @param ww
         * @param hh
         */
        this.drawLine = function(cc, xx, yy, ww, hh) {
            this.context.strokeStyle = cc;
            this.context.beginPath();
            this.context.moveTo(xx - 0.5, yy - 0.5);
            this.context.lineTo(xx + ww - 0.5, yy + hh - 0.5);
            this.context.stroke();
        };

        /**
         * Activate line.
         * @param integer line_nr line number
         */
        this.act = function(line_nr) {
            if (line_nr == this.active_line) {
                if (line_nr == 1 || line_nr == 2) {
                    this.context.shadowColor = this.shadows[1];
                } else {
                    this.context.shadowColor = this.shadows[0];
                }
                this.context.shadowBlur = 5;
            } else {
                this.context.shadowColor = 'white';
                this.context.shadowBlur = 0;
            }
        };

        /**
         * Private variables that should be remembered between calls of g_redraw_canvas.
         *
         * @type {Number}
         */
        var cx, cy;

        /**
         * Redraw Canvas.
         */
        this.g_redraw_canvas = function() {
            if (this.dragging || this.redraw) {
                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.context.shadowColor = 'white';
                this.context.shadowBlur = 0;

                //drawing a graph
                this.context.lineWidth = 1;
                this.drawLine('#000', this.graph_x, this.canvas.height - this.graph_y, 0, -this.graph_h);
                this.drawLine('#000', this.graph_x, this.canvas.height - this.graph_y, this.graph_w, 0);
                for (i = 1; i <= 10; i++) {
                    this.drawLine('#DDD', this.graph_x, this.canvas.height - this.graph_y - i * this.scale_y * 10, this.graph_w, 0);
                }
                var ty = this.canvas.height - this.graph_y;
                var ty_old = ty;
                this.context.beginPath();
                this.context.strokeStyle = '#000';
                this.context.moveTo(this.graph_x, ty - 0.5);
                for (i = 0; i < this.graph_data.length; i++) {
                    ty = this.canvas.height - this.graph_y - this.scale_y * this.graph_data[i][1];
                    if (ty != ty_old) this.context.lineTo(this.graph_x + this.graph_data[i][0] * this.scale_x, ty - 0.5);
                    ty_old = ty;
                }
                this.context.stroke();
                this.delta = 0.1;
                if (document.getElementById('checkbox').checked) this.delta = 1;
                if (this.delta == 1) {
                    for (i = 0; i < 4; i++) {
                        this.boundaries[i] = Math.round(this.boundaries[i]);
                    }
                }
                //position of boudary lines
                var x1 = this.graph_x + this.graph_w / 100 * this.boundaries[0];
                var x2 = this.graph_x + this.graph_w / 100 * this.boundaries[1];
                var y3 = this.canvas.height - this.graph_y - this.graph_h / 100 * this.boundaries[2];
                var y4 = this.canvas.height - this.graph_y - this.graph_h / 100 * this.boundaries[3];

                //standing labels
                this.context.font = "bold 13px Arial";
                this.context.textAlign = "center";
                this.context.fillText(jsxls.lang_string['correct'], this.graph_x + this.graph_w / 2, this.canvas.height - this.graph_y + 35);
                this.context.save();
                this.context.rotate(-Math.PI / 2);
                this.context.fillText(jsxls.lang_string['cohort'], -this.graph_h / 2 - this.graph_y, this.graph_x - 45);
                this.context.restore();

                //graph ticks and labels
                this.context.font = "11px Arial";
                this.context.textAlign = "right";
                for (i = 0; i <= 10; i++) {
                    this.drawLine('#000', this.graph_x, this.canvas.height - this.graph_y - this.scale_y * 10 * i, -5, 0);
                    this.context.fillText(i * 10 + '%', this.graph_x - 10, this.canvas.height - this.graph_y - this.scale_y * 10 * i + 3);
                }
                this.context.textAlign = "center";
                for (i = 0; i <= 10; i++) {
                    this.drawLine('#000', this.graph_x + this.scale_x * 10 * i, this.canvas.height - this.graph_y, 0, 5);
                    this.context.fillText(i * 10 + '%', this.graph_x + this.scale_x * 10 * i + 5, this.canvas.height - this.graph_y + 15);
                }

                //moving labels
                this.context.font = "13px Arial";
                this.context.textAlign = "center";
                this.context.fillStyle = this.colours[1];
                var divert = 0;
                if (Math.abs(x1 - x2) < 50) divert = (50 - Math.abs(x1 - x2)) / 2;
                if (divert > 15) divert = 15;
                this.context.fillText(Math.round(this.boundaries[0] * 10) / 10 + '%', x1, this.canvas.height - this.graph_y - this.graph_h - 5);
                this.context.fillText(Math.round(this.boundaries[1] * 10) / 10 + '%', x2, this.canvas.height - this.graph_y - this.graph_h - 5 - divert);
                this.context.textAlign = "right";
                this.context.fillStyle = this.colours[0];
                divert = 0;
                if ((y3 - y4) < 15) divert = (15 - (y3 - y4)) / 2;
                this.context.fillText(Math.round(this.boundaries[2] * 10) / 10 + '%', this.graph_x + this.graph_w + 40, y3 + 5 + divert);
                this.context.fillText(Math.round(this.boundaries[3] * 10) / 10 + '%', this.graph_x + this.graph_w + 40, y4 + 5 - divert);

                this.context.fillStyle = '#000';
                //drawing the diagonal line
                this.drawLine('#A5A5A5', x1, y4, x2 - x1, y3 - y4);

                //boxplot
                var box1 = 5, box2 = 20, box3 = box1 + box2 / 2;
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[3]), box1, 0, box2);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[1]), box1, 0, box2);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[3]), box3, Math.floor(this.scale_x * (this.stats[4] - this.stats[3])), 0);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[6]), box3, Math.floor(this.scale_x * (this.stats[1] - this.stats[6])), 0);

                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[4]), box1, 0, box2);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[8]), box1, 0, box2);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[6]), box1, 0, box2);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[4]), box1, Math.floor(this.scale_x * (this.stats[6] - this.stats[4])), 0);
                this.drawLine(this.colours[2], Math.round(this.graph_x + this.scale_x * this.stats[4]), box1 + box2, Math.floor(this.scale_x * (this.stats[6] - this.stats[4])), 0);


                //searching for intersection
                //a and b for the first line
                this.a1 = 0;
                this.b1 = x2;
                if (x2 != x1) {
                    this.a1 = (y3 - y4) / (x2 - x1);
                    this.b1 = y4 - this.a1 * x1;
                }
                this.tx2 = this.graph_x;
                this.ty2 = this.canvas.height - this.graph_y;
                this.xs = this.ys = '';
                for (var i = 0; i < this.graph_data.length; i++) {
                    this.tx1 = this.graph_x + this.graph_data[i][0] * this.scale_x;
                    this.ty1 = this.canvas.height - this.graph_y - this.scale_y * this.graph_data[i][1];

                    //a and b for the second line
                    this.a2 = 0;
                    this.b2 = this.tx1;
                    if (this.tx2 != this.tx1) {
                        this.a2 = (this.ty2 - this.ty1) / (this.tx2 - this.tx1);
                        this.b2 = this.ty1 - this.a2 * this.tx1;
                    }

                    if (this.a1 != this.a2) {
                        cx = (this.b2 - this.b1) / (this.a1 - this.a2);
                        if (this.tx2 == this.tx1) cx = this.tx1;
                        cy = this.a1 * cx + this.b1;
                    }

                    if ((cx >= this.tx2) && (cx <= this.tx1) && ((cy >= this.ty2 && cy <= this.ty1) || (cy >= this.ty1 && cy <= this.ty2))) {
                        if (cx >= x1 && cx <= x2 && cy >= y4 && cy <= y3) {
                            this.xs = (cx - this.graph_x) / this.scale_x;
                            this.xs = Math.round(this.xs * 100) / 100;
                            this.ys = -(cy - this.canvas.height + this.graph_y) / this.scale_y;
                            this.ys = Math.round(this.ys * 100) / 100;
                            var dcx = Math.round(cx);
                            var dcy = Math.round(cy);
                            for (var j = 1; j < (this.ys * this.scale_y / 5); j++) this.drawLine('#A5A5A5', dcx, dcy + 5 * j, 0, -3);
                            for (j = 1; j < (this.xs * this.scale_x / 5); j++) this.drawLine('#A5A5A5', dcx - 5 * j, dcy, 3, 0);
                            this.xs += '%';
                            this.ys += '%';
                            if (this.xs == undefined) this.xs = '';
                            if (this.ys == undefined) this.ys = '';
                        }
                    }
                    this.tx2 = this.tx1;
                    this.ty2 = this.ty1;
                }

                //textboxes outside canvas
                document.getElementById('x1_' + this.result_type).value = Math.round(this.boundaries[0] * 10) / 10 + '%';
                document.getElementById('x2_' + this.result_type).value = Math.round(this.boundaries[1] * 10) / 10 + '%';
                document.getElementById('y1_' + this.result_type).value = Math.round(this.boundaries[2] * 10) / 10 + '%';
                document.getElementById('y2_' + this.result_type).value = Math.round(this.boundaries[3] * 10) / 10 + '%';
                document.getElementById('xs_' + this.result_type).value = this.xs;
                document.getElementById('ys_' + this.result_type).value = this.ys;

                //drawing boundaries
                var gap = 0;
                this.act(1);
                this.drawLine(this.colours[1], Math.round(x1), this.canvas.height - this.graph_y - gap, 0, -this.graph_h + 2 * gap);
                this.act(2);
                this.drawLine(this.colours[1], Math.round(x2), this.canvas.height - this.graph_y - gap, 0, -this.graph_h + 2 * gap);
                this.act(3);
                this.drawLine(this.colours[0], this.graph_x + gap, Math.round(y3), this.graph_w - 2 * gap, 0);
                this.act(4);
                this.drawLine(this.colours[0], this.graph_x + gap, Math.round(y4), this.graph_w - 2 * gap, 0);

            }
            this.redraw = false;
        };

        /**
         * Boundary test.
         * @param ax
         * @param ay
         * @param bx
         * @param by
         * @param cx
         * @param cy
         * @returns bool
         */
        this.testWithin = function(ax, ay, bx, by, cx, cy) {
            var testres = false;
            if ((ax > bx) && (ax < (bx + cx)) && (ay > by) && (ay < (by + cy))) testres = true;
            return testres;
        };

        /**
         * Mouse drag down event action.
         */
        this.g_mouseDragUp = function() {
            this.dragging = false;
            if (this.testWithin(this.x, this.y, this.d_x - 5, this.d_y - 15 + 0, 20, 20)) {
                this.boundaries[0] = this.temp_boundaries[0];
                this.redraw = true;
                this.g_redraw_canvas();
            }
            if (this.testWithin(this.x, this.y, this.d_x - 5, this.d_y - 15 + 20, 20, 20)) {
                this.boundaries[1] = this.temp_boundaries[1];
                this.redraw = true;
                this.g_redraw_canvas();
            }
            if (this.testWithin(this.x,this. y, this.d_x - 5, this.d_y - 15 + 40, 20, 20)) {
                this.boundaries[2] = this.temp_boundaries[2];
                this.redraw = true;
                this.g_redraw_canvas();
            }
            if (this.testWithin(this.x, this.y, this.d_x - 5, this.d_y - 15 + 60, 20, 20)) {
                this.boundaries[3] = this.temp_boundaries[3];
                this.redraw = true;
                this.g_redraw_canvas();
            }
        };

        /**
         * Mouse drag down event action.
         */
        this.g_mouseDragDown = function() {
            this.dragging = true;
        };

        /**
         * Mouse drag move event action
         * @param object e event
         */
        this.g_mouseDragMove = function(e) {
            var rect = this.canvas.getBoundingClientRect();
            var loc_lft = rect.left;
            var loc_top = rect.top;
            var xm = e.clientX;
            var ym = e.clientY;
            this.x = xm - loc_lft;
            this.y = ym - loc_top;
            if (!this.testWithin(this.x, this.y, this.graph_x - 25, this.canvas.height - this.graph_y - this.graph_h - 25, this.graph_w + 50, this.graph_h + 50)) this.g_mouseDragUp.bind(this);
            if (this.dragging) {
                if (this.active_line == 1 || this.active_line == 2) this.boundaries[this.active_line - 1] = (this.x - this.graph_x) / this.graph_w * 100;
                if (this.active_line == 3 || this.active_line == 4) this.boundaries[this.active_line - 1] = (this.canvas.height - this.y - this.graph_y) / this.graph_h * 100;
                if (this.boundaries[this.active_line - 1] > 100) this.boundaries[this.active_line - 1] = 100;
                if (this.boundaries[this.active_line - 1] < 0) this.boundaries[this.active_line - 1] = 0;

                //swap active lines if dragging over
                if (this.boundaries[1] < this.boundaries[0]) {
                    this.boundaries[1] = [this.boundaries[0], this.boundaries[0] = this.boundaries[1]][0];
                    this.active_line = 3 - this.active_line;
                }
                if (this.boundaries[3] < this.boundaries[2]) {
                    this.boundaries[3] = [this.boundaries[2], this.boundaries[2] = this.boundaries[3]][0];
                    this.active_line = 7 - this.active_line;
                }
                if (this.active_line > -1) this.g_redraw_canvas();
            } else {
                var old_active_line = this.active_line;
                this.active_line = 0;
                var treshold = 3
                if (Math.abs(this.graph_x + this.graph_w / 100 * this.boundaries[0] - this.x) < treshold) this.active_line = 1;
                if (Math.abs(this.graph_x + this.graph_w / 100 * this.boundaries[1] - this.x) < treshold) this.active_line = 2;
                if (Math.abs(this.canvas.height - this.graph_y - this.graph_h / 100 * this.boundaries[2] - this.y) < treshold) this.active_line = 3;
                if (Math.abs(this.canvas.height - this.graph_y - this.graph_h / 100 * this.boundaries[3] - this.y) < treshold) this.active_line = 4;

                var cur = 'default';
                if (this.active_line > 0) cur = 'col-resize';
                if (this.active_line > 2) cur = 'row-resize';
                e.target.style.cursor = cur;

                if (this.active_line != old_active_line) {
                    this.redraw = true;
                    this.g_redraw_canvas();
                }
            }
        };

        /**
         * Textbox change event action
         * @param object e event
         * @param bool keys true if key event
         */
        this.tfchange = function(event, keys) {
            if (this.result_type == event.target.name.substr(3)) {
                var target = ((event.target.name[0] == 'x') ? 0 : 2) + 1 * event.target.name[1] - 1;
                var ev0 = this.boundaries[target];
                var ev = Number(event.target.value.replace('%', ''));
                if (isNaN(ev)) ev = ev0;

                if (keys) {
                    if (event.keyCode == 37 || event.keyCode == 40) ev -= this.delta;
                    if (event.keyCode == 38 || event.keyCode == 39) ev += this.delta;
                }

                if (ev < 0) ev = 0;
                if (ev > 100) ev = 100;
                this.boundaries[target] = ev;
                if (this.boundaries[1] < this.boundaries[0]) this.boundaries[1] = [this.boundaries[0], this.boundaries[0] = this.boundaries[1]][0];
                if (this.boundaries[3] < this.boundaries[2]) this.boundaries[3] = [this.boundaries[2], this.boundaries[2] = this.boundaries[3]][0];

                this.redraw = true;
                this.g_redraw_canvas();
            }
        };
    }
});