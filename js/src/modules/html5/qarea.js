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
// html5 area question
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

/* eslint no-unused-vars: "warn" */

define(['html5images', 'qsharedf', 'jsxls'], function(Images, Qsharedf, Jsxls) {
    return function() {
        this.isMouseOutsiceCanvas = false;
        this.doorId
        this.nikotest = 0;
        this.test;
        this.x, this.y, this.oy;
        this.sub_x, this.sub_y;
        this.oldx, this.oldy;
        this.mouse_moved = false;
        this.isCtrl = this.isShift = false;
        this.ShiftChange = false;
        this.drag_box_id = -1; //index of box beeing dragged
        this.menu_ready = 1;
        this.do_the_test = true;
        this.do_the_test_calc = false;
        this.timgp = [];
        this.timga = [];
        this.test_result = '';
        this.answerBox = new Array(); // sublevels of this keep all the answer data
        this.buttonBox = new Array(); // sublevels of this keep all the buttons data
        this.qa_panelBox = new Array(); // sublevels of this keep the panels data
        this.buttonBoxNames = new Array(); // transcription of button names into its index in ButtonBox (?)
        this.buttonClicked = -1;  // index of the button that was clicked
        this.buttonOver = -1; // index of the button the mouse is over
        this.allUnaswered = false;
        this.global_zoom = true;
        this.global_delpoint = false;
        this.global_delpoint_avail = false;
        this.global_clearpnl = false;
        this.global_dblclick = false;
        this.global_your_answer = false;
        this.global_corect_answer = false;
        this.global_show_error = false;
        this.panel_buttons = new Array();
        this.panel_button_selected = '';
        //vars for polygon
        this.handler_dot = this.handler_sqr = this.handler_clk = this.access_sel = -1;
        this.poly_temp = '';
        this.freehand = false;
        this.angle1, this.angle2, this.distn, this.dx, this.dy;
        this.poly_temp_points = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0); //first point, second last point, last point, last down, last up ... mouse points
        this.draw_limit = new Array(); //used to limit polygon, ellipse and sqare positions
        this.any_overlaping = this.overlapping_show = false;
        this.yoffset;
        this.yoffset_fix_value = 0; // special fix for data from flash (menubar in flash was smaller)
        this.yoffset_fiy_value = 0;
        this.is_an_answer = false;
        this.q_Num;
        this.currentColours = Array('#FFFFFF', '#3F3F3F', '#000000', '#FF0000'); // fill, line, text colours
        // draggable labels and manually drawn
        // lines / arrows (in pixels)
        this.fontSizes = Array(11, 12, 14, 16, 18, 20, 22); // font size equivalent in Flash (not standard sizes)
        this.fontSizePos = 1; // current font size for labels (index from array above);
        this.dragging = false;
        this.redraw_once = false;
        this.gen_img, this.menu_img;
        this.gen_img_loaded = false;
        this.menu_img_loaded = false;
        this.loc_lft = this.loc_top = 0;
        this.canv_rect;
        this.mov_id = -1;
        this.context;
        this.canvas;
        this.marks_per_correct = 1;
        this.marks_per_incorrect = 0;
        this.marking_method = 'Mark per Option';
        this.qmode, this.qanswer, this.qconfig, this.qtest;
        this.last_answer, this.last_config;
        this.imgdata, this.imgdatab, this.imgdatac;
        this.err_image, this.err_image_final;
        this.display_students_response = true;
        this.display_correct_answer = true;
        this.hide_feedback_ifunanswered = false;
        this.imageerrordisplay = 0;

        this.qa_gen_img_onload = function() {
            this.gen_img_loaded = true;
            this.redraw_once = true;
            this.qa_redraw_canvas;
        }

        this.menu_img_onload = function() {
            this.menu_img_loaded = true;
            this.menu_ready++;
            this.redraw_once = true;
            this.qa_redraw_canvas;
        }

        this.setUpArea = function(num, doorId, lang, image, config, answer, extra, colour, mode) {
            mode = Qsharedf.getmode(mode);
            this.canvas = document.getElementById('canvas' + num);
            this.draw_limit = new Array(0, 27, this.canvas.width - 2, this.canvas.height - 2);
            if (this.canvas && this.canvas.getContext) {
                this.canvas.onmouseup = this.qa_mouseDragUp.bind(this);
                this.canvas.onmousedown = this.qa_mouseDragDown.bind(this);
                this.canvas.onmousemove = this.qa_mouseDragMove.bind(this);
                document.onmousemove = this.qa_mouseDragMoveOutside.bind(this);
                this.canvas.tabIndex = 1000; //force keyboard events
                if (document.addEventListener) { //FF+, IE10+, Ch+
                    document.addEventListener("keydown", this.qa_mouseDragMove.bind(this), false);
                    document.addEventListener("keyup", this.qa_mouseDragMove.bind(this), false);
                    document.addEventListener("keypress", this.qa_mouseDragMove.bind(this), false);
                } else if (document.attachEvent) { //FF--, IE10-, IE9-, Ch--
                    document.attachEvent("onkeydown", this.qa_mouseDragMove.bind(this));
                    document.attachEvent("onkeyup", this.qa_mouseDragMove.bind(this));
                    document.attachEvent("onkeypress", this.qa_mouseDragMove.bind(this));
                } else { //FF-, IE10-, IE9-, Ch-
                    document.onkeydown = this.qa_mouseDragMove.bind(this);
                    document.onkeyup = this.qa_mouseDragMove.bind(this);
                    document.onkeypress = this.qa_mouseDragMove.bind(this);
                }
                window.setInterval(this.qa_redraw_canvas.bind(this), 10);
            }
            if (this.canvas && !this.canvas.getContext) {
                alert(Jsxls.lang_string['errorcanvas']);
            }

            if (this.canvas && this.canvas.getContext) {
                this.context = this.canvas.getContext('2d');
                this.context.lineWidth = 1;

                this.q_Num = num;
                this.doorId = doorId;

                //gen_img
                this.gen_img = new Image();

                this.gen_img.onload = this.qa_gen_img_onload.bind(this);
                this.gen_img.src = ((mode == 'edit') ? '../' : '') + '../getfile.php?type=media&filename=' + image;

                //---------- mode
                this.yoffset = 25; //ofset of top edge of the image
                this.qmode = mode;
                if (this.qmode == 'script') this.global_zoom = false;

                //---------- config,
                this.qconfig = this.yoffset_fix(config, this.yoffset_fix_value, this.yoffset_fiy_value);
                //this.yoffset_fix
                if (config != '') this.global_delpoint_avail = true;

                //---------- answer,
                if (answer != "" && answer != undefined && answer != "undefined" && answer != null && answer != "null" && answer != "u") {
                    this.is_an_answer = true;
                    var answer_l1 = answer.split("|");
                    for (var i = 0; i < answer_l1.length; i++) {
                        this.answerBox[i] = this.yoffset_fix(answer_l1[i], this.yoffset_fix_value, this.yoffset_fiy_value).split(",");
                    }
                }
                if (answer == 'u') this.allUnaswered = true;
                this.qanswer = answer;

                //---------- colour
                this.currentColours[3] = colour;

                //---------- extra
                // flash modes 1: answer, 2: edit, 3: script
                if (this.qmode == 'script') {
                    if (typeof (extra[0]) != 'undefined' && extra[0] == '0' || this.qanswer == '' || this.qanswer == 'u') this.display_students_response = false;
                    if (typeof (extra[1]) != 'undefined' && extra[1] == '0') this.display_correct_answer = false;
                    if (typeof (extra[4]) != 'undefined' && extra[4] == '1') this.hide_feedback_ifunanswered = true;
                    if (this.hide_feedback_ifunanswered && !(this.is_an_answer)) {
                        this.display_students_response = false;
                        this.display_correct_answer = false;
                    }
                }

                //menubar
                this.menu_img = new Image();

                this.menu_img.onload = this.menu_img_onload.bind(this);
                this.menu_img.src = ((this.qmode == 'edit') ? '../' : '') + '../js/images/combined.png';
            }
        };

        this.yoffset_fix = function(data, fix, fiy) {
            var data_out = '';
            if (data != '') {
                var data_in = data.split(',');
                for (var n = 0; n < data_in.length / 2; n++) {
                    data_out += Math.round(parseInt(data_in[n * 2 + 0].trim(), 16) + fix).toString(16) + ',';
                    data_out += Math.round(parseInt(data_in[n * 2 + 1].trim(), 16) + fiy).toString(16) + ',';
                }
                data_out = data_out.substr(0, data_out.length - 1);
            }
            return data_out;
        };

        this.qa_menuBuild = function() {
            if (this.qmode == 'test') {
                var spac = 3;
                var posx = 3;
                var posy = 3;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_cross_off.png', posx, posy, 0, '', Jsxls.lang_string['But_Delete_point'], Jsxls.lang_string['tt_Delete_point'], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/vert_2.png', posx, posy, 0, '', '', '', this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_erase.png', posx, posy, 0, '', Jsxls.lang_string['But_Clear_All'], Jsxls.lang_string['tt_Clear_All'], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/vert_2.png', posx, posy, 0, '', '', '', this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_zoom.png', posx, posy, 0, '', Jsxls.lang_string['Magnify'], Jsxls.lang_string['tt_Magnify'], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_help.png', this.canvas.width - 23, posy, 0, '', '', '', this) + spac;
            }

            if (this.qmode == 'script') {
                spac = 3;
                posx = 3;
                posy = 3;
                if (this.display_students_response) {
                    posx = Qsharedf.menuBuild_icons('toolbar/ico_area.png', posx, posy, 2, '+', Jsxls.lang_string['But_your_answer'], Jsxls.lang_string['But_your_answer'], this) + spac;
                    this.global_your_answer = true;
                } else {
                    posx = Qsharedf.menuBuild_icons('toolbar/ico_area.png', posx, posy, 0, '-', Jsxls.lang_string['But_your_answer'], Jsxls.lang_string['But_your_answer'], this) + spac;
                    this.global_your_answer = false;
                }
                if (this.display_correct_answer) {
                    posx = Qsharedf.menuBuild_icons('toolbar/ico_tick.png', posx, posy, 2, '+', Jsxls.lang_string['But_correct_answer'], Jsxls.lang_string['But_correct_answer'], this) + spac;
                    this.global_corect_answer = true;
                } else {
                    posx = Qsharedf.menuBuild_icons('toolbar/ico_tick.png', posx, posy, 0, '-', Jsxls.lang_string['But_correct_answer'], Jsxls.lang_string['But_correct_answer'], this) + spac;
                    this.global_corect_answer = false;
                }
                if (this.display_correct_answer && this.display_students_response) {
                    posx = Qsharedf.menuBuild_icons('toolbar/ico_warn.png', posx, posy, 0, '+', Jsxls.lang_string['But_show_error'], Jsxls.lang_string['But_show_error'], this) + spac;
                    this.global_show_error = false;
                } else {
                    posx = Qsharedf.menuBuild_icons('toolbar/ico_warn.png', posx, posy, 0, '-', Jsxls.lang_string['But_show_error'], Jsxls.lang_string['But_show_error'], this) + spac;
                    this.global_show_error = false;
                }
            }
            if (this.qmode == 'edit' || this.qmode == 'answer') {
                spac = 3;
                posx = 3;
                posy = 3;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_cross_off.png', posx, posy, 0, '+', Jsxls.lang_string['But_Delete_point'], Jsxls.lang_string['tt_Delete_point'], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/vert_2.png', posx, posy, 0, '', '', '', this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_erase.png', posx, posy, 0, '', Jsxls.lang_string['But_Clear_All'], Jsxls.lang_string['tt_Clear_All'], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/vert_2.png', posx, posy, 0, '', '', '', this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_zoom.png', posx, posy, 0, '+', Jsxls.lang_string['Magnify'], Jsxls.lang_string['tt_Magnify'], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_help.png', this.canvas.width - 23, posy, 0, '-', '', '', this) + spac;

                //zoom button pressed by default
                this.buttonBox[this.buttonBoxNames['toolbar/ico_zoom.png']][6] = 2;
                this.buttonBox[this.buttonBoxNames['toolbar/ico_zoom.png']][5] = 2;
            }
        };

        this.qa_test = function(type) {
            //type = data or image
            this.do_the_test = false;
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.context.globalAlpha = 0.5;
            var col = '#CC0000';
            if (this.qanswer != '') Qsharedf.polyDrawH(this.context, '', col, -0.5, this.yoffset - 0.5, this.qanswer.split(','), 't', this);
            col = '#0000FF';
            if (this.qconfig != '') Qsharedf.polyDrawH(this.context, '', col, -0.5, this.yoffset - 0.5, this.qconfig.split(','), 't', this);
            this.context.globalAlpha = 1;

            var timgd = this.context.getImageData(1, 1, this.canvas.width - 2, this.canvas.height - 2);
            if (type == 'data') {
                this.timgp = timgd.data;
                this.do_the_test_calc = true;
                return this.timgp;
            } else {
                return timgd;
            }
        };

        this.qa_test_calc = function(type) {
            //type = data or image
            var li1, li2, li3, j;
            li1 = li2 = li3 = 0;
            var trsh = 64;
            this.do_the_test_calc = false;

            if (type == 'data') {
                for (j = 0; j < this.timgp.length; j += 4) {
                    if (this.timgp[j + 0] * 1 > trsh && this.timgp[j + 2] * 1 > trsh && this.timgp[j + 1] * 1 < trsh) li1++;
                    if (this.timgp[j + 0] * 1 > trsh && this.timgp[j + 2] * 1 < trsh && this.timgp[j + 1] * 1 < trsh) li2++;
                    if (this.timgp[j + 0] * 1 < trsh && this.timgp[j + 2] * 1 > trsh && this.timgp[j + 1] * 1 < trsh) li3++;
                }
                var result_in = Math.round(li1 / (li1 + li3) * 1000);
                var result_out = Math.round(li2 / (li1 + li3) * 1000);
                var result_er = 1000 - result_in + result_out;
                return result_er / 10 + ',' + result_in / 10 + ',' + result_out / 10 + ',' + li1 + ',' + li2 + ',' + li3;
            }
            if (type == 'image') {
                this.timga = this.err_image.data;
                for (j = 0; j < this.timga.length; j += 4) {
                    li1 = li2 = li3 = 0;
                    if (this.timga[j + 0] * 1 > trsh && this.timga[j + 2] * 1 > trsh && this.timga[j + 1] * 1 < trsh) li1++;
                    if (this.timga[j + 0] * 1 > trsh && this.timga[j + 2] * 1 < trsh && this.timga[j + 1] * 1 < trsh) li2++;
                    if (this.timga[j + 0] * 1 < trsh && this.timga[j + 2] * 1 > trsh && this.timga[j + 1] * 1 < trsh) li3++;
                    if (li2 == 1 || li3 == 1) {
                        this.timga[j + 0] = 255;
                        this.timga[j + 1] = 0;
                        this.timga[j + 2] = 0;
                    } else {
                        this.timga[j + 0] = 255;
                        this.timga[j + 1] = 255;
                        this.timga[j + 2] = 255;
                        this.timga[j + 3] = 255;
                    }
                }
                return this.err_image;
            }
        };

        this.qa_redraw_canvas_main = function(tx, ty) {

            this.context.globalAlpha = 1;
            this.context.fillStyle = '#ffffff';
            this.context.fillRect(-30, -30, this.canvas.width + 60, this.canvas.height + 60);
            this.context.drawImage(this.gen_img, tx + 1, ty + 1);
            tx -= 1;
            ty -= 1;
            this.context.globalAlpha = 0.75;
            this.context.lineWidth = 3;
            var col = '#385D8A';
            //edit or answer
            if (this.qconfig != '' && this.qmode == 'edit') Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qconfig.split(','), 'h', this);
            if (this.qanswer != '' && this.qmode == 'answer') Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qanswer.split(','), 'h', this);

            //niko
            if (this.access_sel > -1) {
                if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;
                if (this.qtest.length > 0) {
                    var pp = this.qtest.split(',');
                    this.context.strokeStyle = '#FFBD69';
                    this.context.lineWidth = 3;
                    this.context.strokeRect(tx + parseInt(pp[(this.access_sel * 2 + 0)].trim(), 16) - 3.5, ty + parseInt(pp[(this.access_sel * 2 + 1)].trim(), 16) - 3.5, 9, 9);
                }
            }

            if (this.qmode == 'script') {
                if (this.qconfig != '' && this.qanswer != '' && typeof (this.err_image) != 'undefined' && this.global_show_error) {
                    //combine images
                    var img_final = this.context.getImageData(1, 1, this.canvas.width - 2, this.canvas.height - 2);
                    var img_final_data = img_final.data;
                    var err_final_data = this.err_image.data;
                    var li2, li3;
                    li2 = li3 = 0;
                    var trsh = 64;

                    for (var j = 0; j < err_final_data.length; j += 4) {
                        li2 = li3 = 0;
                        if (err_final_data[j + 0] * 1 > trsh && err_final_data[j + 2] * 1 < trsh && err_final_data[j + 1] * 1 < trsh) li2++;
                        if (err_final_data[j + 0] * 1 < trsh && err_final_data[j + 2] * 1 > trsh && err_final_data[j + 1] * 1 < trsh) li3++;
                        if (li2 == 1 || li3 == 1) {
                            img_final_data[j + 0] = 240;
                            img_final_data[j + 1] = img_final_data[j + 1] * 0.8;
                            img_final_data[j + 2] = img_final_data[j + 2] * 0.8;
                        }
                    }
                    this.context.putImageData(img_final, 1, 1);
                    col = '#ee6666';
                    this.context.globalAlpha = 0.75;
                    Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qconfig.split(','), 'e', this);
                    Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qanswer.split(','), 'e', this);
                }
                if (this.qconfig != '' && this.qanswer == '' && this.global_show_error) {
                    col = '#ee6666';
                    this.context.globalAlpha = 0.40;
                    Qsharedf.polyDrawH(this.context, '', col, tx, ty, this.qconfig.split(','), 'e', this);
                    this.context.globalAlpha = 0.75;
                    Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qconfig.split(','), 'e', this);
                }
                if (this.qconfig != '' && this.global_corect_answer) {
                    col = '#0aff0a';
                    this.context.globalAlpha = 0.25;
                    Qsharedf.polyDrawH(this.context, '', col, tx, ty, this.qconfig.split(','), 'e', this);
                    this.context.globalAlpha = 0.75;
                    Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qconfig.split(','), 'e', this);
                }
                if (this.qanswer != '' && this.global_your_answer) {
                    col = '#385D8A';
                    this.context.globalAlpha = 0.25;
                    Qsharedf.polyDrawH(this.context, '', col, tx, ty, this.qanswer.split(','), 'e', this);
                    this.context.globalAlpha = 0.75;
                    Qsharedf.polyDrawH(this.context, col, '', tx, ty, this.qanswer.split(','), 'e', this);
                }
            }
            //draw temp polygon
            if (((this.qconfig == '' && this.qmode == 'edit') || (this.qanswer == '' && this.qmode == 'answer')) && this.poly_temp != '') {
                var poly_temp_ext = this.poly_temp;
                poly_temp_ext += Math.round(this.x).toString(16) + ',' + Math.round(this.oy).toString(16);
                Qsharedf.polyDrawH(this.context, col, '', tx, ty, poly_temp_ext.split(','), 'd', this);
            }
            this.context.globalAlpha = 1;
        };

        this.qa_redraw_canvas = function() {
            if (!(this.gen_img_loaded && this.menu_img_loaded) && this.imageerrordisplay < 501) this.imageerrordisplay++;
            if (!(this.gen_img_loaded && this.menu_img_loaded) && this.imageerrordisplay == 500) {
                this.context.textAlign = "left";
                this.context.fillStyle = '#C00000';
                this.context.font = "13px Arial";
                this.context.fillText(Jsxls.lang_string['errorimagesarea'], 15, 15);
            }

            if (this.gen_img_loaded && this.menu_img_loaded && (this.dragging || this.redraw_once || this.mov_id != -1 || this.mouse_moved || this.ShiftChange)) {

                //buttons
                if (this.buttonBox.length == 0) this.qa_menuBuild();

                //testing the answer
                if (this.do_the_test && typeof (this.qanswer) != 'undefined' && this.qanswer != this.last_answer) {
                    this.last_answer = this.qanswer;
                    this.timgp = this.qa_test('data');
                }

                if (this.qconfig != '' && this.global_show_error) {
                    if (typeof (this.err_image_final) == 'undefined') {
                        this.err_image = this.qa_test('image');
                    }
                }

                this.redraw_once = false;
                this.mouse_moved = false;
                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.qa_redraw_canvas_main(0, this.yoffset);
                this.context.lineWidth = 1;
                this.context.strokeStyle = this.currentColours[1];

                //cross red or gray
                if (this.qmode == 'edit' || this.qmode == 'answer') {
                    if (this.global_delpoint_avail) {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][0] = 'toolbar/ico_cross_on.png';
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7] = '+';
                    } else {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][0] = 'toolbar/ico_cross_off.png';
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7] = '-';
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][5] = 0;
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][6] = 0;
                    }
                }

                //clear all active?
                if (this.qmode == 'edit' || this.qmode == 'answer') {
                    if ((this.qmode == 'edit' && this.qconfig != '') || (this.qmode == 'answer' && this.qanswer != '')) {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][7] = '';
                    } else {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][7] = '-';
                    }
                }

                Qsharedf.menuRebuild(this.context, true, this);

                //frames
                this.context.strokeStyle = '#c0c0c0';//'#7f9db9';
                this.context.strokeRect(0.5, 0.5, this.canvas.width - 1, 25);

                if (this.global_clearpnl) Qsharedf.build_msgbox((this.canvas.width / 2 - 130), (this.canvas.height / 2 - 40), 260, 80, Jsxls.lang_string['popUp_msg'], Jsxls.lang_string['popUp_yes'], Jsxls.lang_string['popUp_no'], '', this);

                //tooltip
                if (this.buttonOver != -1) Qsharedf.tooltip_draw(this.context, this.buttonBox[this.buttonOver], this);


                if (this.global_zoom && !this.isShift && !this.global_clearpnl && this.oy > 0) {
                    //mask
                    this.context.save();
                    this.context.beginPath();

                    this.imgdata = Images.map['toolbar/loupe.png'];
                    var loupe_x, zoom_x;
                    loupe_x = zoom_x = this.x;
                    var loupe_y = this.oy;
                    var zoom_y = this.oy;
                    var loupe_width = this.imgdata.width; //87

                    //reposition magnifying glas near the top edge
                    var tx = this.canvas.width - loupe_width;
                    var ty = this.yoffset + loupe_width;

                    if (this.oy < ty) {
                        loupe_y = ty;
                        zoom_y = this.oy * 2 - ty;
                    }

                    //reposition magnifying glas near the right edge
                    if (this.x > (tx)) {
                        loupe_x = tx;
                        zoom_x = this.x * 2 - tx;
                    }

                    this.draw_limit = [];
                    this.context.arc(loupe_x + Math.round(loupe_width / 2), loupe_y - Math.round(loupe_width / 2), Math.round(loupe_width / 2) - 2, 0, Math.PI * 2, false);
                    this.context.clip();
                    this.context.scale(2, 2);
                    this.qa_redraw_canvas_main((1 - zoom_x) / 2 + Math.round(loupe_width / 4), (1 - zoom_y) / 2 - Math.round(loupe_width / 4));
                    this.context.restore();
                    this.draw_limit = new Array(0, 27, this.canvas.width - 2, this.canvas.height - 2);

                    //cursor
                    this.context.drawImage(this.menu_img, this.imgdata.left, this.imgdata.top, this.imgdata.width, this.imgdata.height, loupe_x, loupe_y - this.imgdata.height, this.imgdata.width, this.imgdata.height);
                }
                // border
                this.context.strokeStyle = '#909090';  //#7f9db9';
                this.context.strokeRect(0.5, 0.5, this.canvas.width - 1, this.canvas.height - 1);

                //testing the answer
                if (this.do_the_test_calc) {
                    this.test_result = this.qa_test_calc('data');
                    this.qa_ReturnInfo();
                }
                if (this.qmode == 'edit' && this.last_config != this.qconfig) {
                    this.last_config = this.qconfig;
                    this.qa_ReturnInfo();
                }
            }
        };

        this.qa_mouseDragMoveOutside = function() {
            if (this.isMouseOutsiceCanvas && this.poly_temp.length > 2) {
                if (this.qmode == 'edit' && this.qconfig == '' && this.poly_temp.split(',').length > 3) this.qconfig = this.poly_temp + Math.round(this.poly_temp_points[0]).toString(16) + ',' + Math.round(this.poly_temp_points[1]).toString(16);
                if (this.qmode == 'answer' && this.qanswer == '' && this.poly_temp.split(',').length > 3) this.qanswer = this.poly_temp + Math.round(this.poly_temp_points[0]).toString(16) + ',' + Math.round(this.poly_temp_points[1]).toString(16);
                this.global_delpoint_avail = true;
                this.poly_temp = '';
                this.poly_temp_points = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                //this.do_the_test = true;
                this.redraw_once = true;
            }
            this.isMouseOutsiceCanvas = true;
        };

        this.qa_mouseDragMove = function(e) {
            this.isMouseOutsiceCanvas = false;
            this.ev = e || window.event;
            if (this.ev.target.id != this.canvas.id) return true;
            Qsharedf.get_char_key(this);

            if (this.ev.type == 'keydown') {
                this.isShift = this.ev.shiftKey ? true : false;
                this.isCtrl = this.ev.ctrlKey ? true : false;
                this.ShiftChange = true;
            }
            if (this.ev.type == 'keyup') {
                this.isShift = this.ev.shiftKey ? true : false;
                this.isCtrl = this.ev.ctrlKey ? true : false;
                this.ShiftChange = true;
            }
            if (this.ev.type == 'mousemove') {
                this.canv_rect = this.canvas.getBoundingClientRect();
                this.loc_lft = this.canv_rect.left;
                this.loc_top = this.canv_rect.top;
                this.x = this.ev.clientX - this.loc_lft;
                this.y = this.ev.clientY - this.loc_top;
                this.oy = this.y - this.yoffset;
            }

            //tab select
            if (this.key_code == 9) { //tab
                if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;
                if (this.qtest.length > 0) {
                    var pp = this.qtest.split(',');
                    if (!this.isShift) {
                        this.access_sel++;
                        if ((pp.length / 2 - 2) < this.access_sel) this.access_sel = 0;
                    }
                    if (this.isShift) {
                        this.access_sel--;
                        if (0 > this.access_sel) this.access_sel = pp.length / 2 - 2;
                    }
                }
                this.key_code = 0;
            }

            var ttx, tty;
            if (this.key_code >= 37 && this.key_code <= 40 && this.access_sel > -1) {
                if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;
                if (this.qtest.length > 0) {
                    pp = this.qtest.split(',');
                    ttx = parseInt(pp[(this.access_sel * 2 + 0)].trim(), 16);
                    tty = parseInt(pp[(this.access_sel * 2 + 1)].trim(), 16);
                    if (this.key_code == 39) ttx++; //arror right
                    if (this.key_code == 37) ttx--; //arrow left
                    if (this.key_code == 38) tty--; //arrow up
                    if (this.key_code == 40) tty++; //arrow down
                    pp[(this.access_sel * 2 + 0)] = Math.round(ttx).toString(16);
                    pp[(this.access_sel * 2 + 1)] = Math.round(tty).toString(16);
                    if (this.qmode == 'answer') this.qanswer = pp.join(',');
                    if (this.qmode == 'edit') this.qconfig = pp.join(',');

                    this.qa_ReturnInfo();
                    this.redraw_once = true;
                    this.qa_redraw_canvas;
                }
                this.key_code = 0;
            }

            if (this.dragging) { //this.dragging
                //new position of dragged element
                if (this.handler_sqr > -1) {
                    this.qtest = '';
                    if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                    if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;

                    pp = this.qtest.split(',');
                    pp[(this.handler_sqr * 2 - 2)] = Math.round(this.x).toString(16);
                    pp[(this.handler_sqr * 2 - 1)] = Math.round(this.oy).toString(16);
                    //move first handler if moving last in polygon
                    if ((this.handler_sqr * 2 - 2) == pp.length) {
                        pp[0] = pp[this.handler_sqr * 2 - 2];
                        pp[1] = pp[this.handler_sqr * 2 - 1];
                    }
                    if (this.handler_sqr == 1) {
                        pp[pp.length - 2] = pp[0];
                        pp[pp.length - 1] = pp[1];
                    }

                    this.qtest = pp.join(',');

                    if (this.qmode == 'answer' && this.qanswer != '') this.qanswer = this.qtest;
                    if (this.qmode == 'edit' && this.qconfig != '') this.qconfig = this.qtest;

                    this.redraw_once = true;
                    this.qa_redraw_canvas;
                }
            } else { //change of cursor
                this.drag_box_id = -1;
                if (Qsharedf.testWithin(this.x, this.y, 0, 0, this.canvas.width, this.canvas.height)) {
                    var over_object = false;

                    //this.test for buttons
                    var buttonTest = -1;
                    for (var i = 0; i < this.buttonBox.length; i++) {
                        this.buttonBox[i][5] = this.buttonBox[i][6];
                        if (this.buttonBox[i][0] == 'toolbar/ico_drop.png') this.buttonBox[i][5] = this.buttonBox[i - 1][5];

                        if (this.buttonBox[i][0].indexOf('vert_') == -1 && Qsharedf.testWithin(this.x, this.y, this.buttonBox[i][1], this.buttonBox[i][2], this.buttonBox[i][3], this.buttonBox[i][4]) == true) {
                            over_object = true;
                            buttonTest = i;
                            this.buttonBox[i][5] = 1;

                            //double button
                            var j = i;
                            if (this.buttonBox[i][0] == 'toolbar/ico_drop.png') j = i - 1;
                            if (i < this.buttonBox.length - 1 && this.buttonBox[i + 1][0] == 'toolbar/ico_drop.png') j = i + 1;
                            this.buttonBox[j][5] = 1;
                        }
                    }

                    if (this.buttonOver != buttonTest) {
                        this.buttonOver = buttonTest;
                        this.redraw_once = true;
                        this.qa_redraw_canvas;
                    }

                    //this.test for handler points
                    if ((this.qmode == 'edit' && this.qconfig != '') || (this.qmode == 'answer' && this.qanswer != '')) {
                        this.qtest = '';
                        if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                        if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;

                        pp = this.qtest.split(',');
                        this.handler_dot = -1;
                        this.handler_sqr = -1;
                        for (var n = 1; n < pp.length / 2; n++) {
                            ttx = (parseInt(pp[n * 2].trim(), 16) - parseInt(pp[n * 2 - 2].trim(), 16)) / 2 + parseInt(pp[n * 2 - 2].trim(), 16);
                            tty = (parseInt(pp[n * 2 + 1].trim(), 16) - parseInt(pp[n * 2 - 1].trim(), 16)) / 2 + parseInt(pp[n * 2 - 1].trim(), 16);
                            if (Qsharedf.testWithin(this.x, this.oy, ttx - 3.5, tty - 3.5, 7, 7)) this.handler_dot = n;
                            if (Qsharedf.testWithin(this.x, this.oy, parseInt(pp[n * 2 - 2].trim(), 16) - 3.5, parseInt(pp[n * 2 - 1].trim(), 16) - 3.5, 7, 7)) this.handler_sqr = n;
                        }
                    }

                    var cur = 'default';
                    if (this.oy > 0) cur = 'crosshair';
                    if (this.qmode != 'script' && (this.global_delpoint || this.isCtrl)) cur = 'url(/js/images/cur_cross.cur) 6 5, default'; //this works only in css3 browsers otherwise whole cursor is ignored
                    if (over_object) cur = 'pointer';
                    if (this.buttonOver > -1 && this.buttonBox[this.buttonOver][0] == 'toolbar/ico_help.png') cur = 'help';
                    if (this.global_clearpnl) cur = 'default';
                    e.target.style.cursor = cur;

                }
            }
            if (this.oldx != this.x || this.oldy != this.oy) this.mouse_moved = true;
            this.oldx = this.x;
            this.oldy = this.oy;

            //this.freehand draw
            if (this.oy > 0 && this.poly_temp_points[7] != 0 && this.freehand) {
                this.angle1 = this.angle2 = this.distn = this.dx = this.dy = -1;
                if (this.poly_temp_points[3] != 0 && this.poly_temp_points[5] != 0)
                    this.angle1 = Math.atan2(this.poly_temp_points[5] - this.poly_temp_points[3], this.poly_temp_points[4] - this.poly_temp_points[2]);
                if (this.poly_temp_points[5] != 0)
                    this.angle2 = Math.atan2(this.oy - this.poly_temp_points[5], this.x - this.poly_temp_points[4]);

                if (this.poly_temp_points[5] == 0) {
                    this.dx = this.x - this.poly_temp_points[6];
                    this.dy = this.oy - this.poly_temp_points[7];
                } else {
                    this.dx = this.x - this.poly_temp_points[4];
                    this.dy = this.oy - this.poly_temp_points[5];
                }

                this.distn = Math.sqrt(this.dx * this.dx + this.dy * this.dy);

                var add_point = false;

                //if one just started freedrawing
                if (this.poly_temp_points[3] == 0 && this.distn > 10) {
                    //because this is this.freehand and no point has been added - add starting one first
                    this.poly_temp += Math.round(this.poly_temp_points[6]).toString(16) + ',' + Math.round(this.poly_temp_points[7]).toString(16) + ',';

                    if (this.poly_temp_points[5] == 0) {
                        this.poly_temp_points[0] = this.poly_temp_points[6];
                        this.poly_temp_points[1] = this.poly_temp_points[7];
                    }
                    this.poly_temp_points[4] = this.poly_temp_points[6];
                    this.poly_temp_points[5] = this.poly_temp_points[7];

                    //and then dirct to add the actual one
                    add_point = true;
                }

                //checking the angle (dependend on the distance)
                if (this.poly_temp_points[3] != 0 && this.poly_temp_points[5] != 0 && this.distn > 10 && Math.abs(this.angle2 - this.angle1) > (1 / this.distn * 3)) add_point = true;

                if (add_point) {
                    this.poly_temp += Math.round(this.x).toString(16) + ',' + Math.round(this.oy).toString(16) + ',';
                    this.poly_temp_points[2] = this.poly_temp_points[4];
                    this.poly_temp_points[3] = this.poly_temp_points[5];
                    this.poly_temp_points[4] = this.x;
                    this.poly_temp_points[5] = this.oy;
                }
            }
            //this.freehand draw end

            //cancel propagation if BackSpace
            if (this.ev.type == 'keydown' && this.ev.keyCode <= 46) {
                if (this.ev.stopPropagation) this.ev.stopPropagation();
                if (this.ev.cancelBubble != null) this.ev.cancelBubble = true;
                if (this.ev.preventDefault) this.ev.preventDefault();
                if (this.ev.returnValue) this.ev.returnValue = false;
            }
            return false;
        };

        this.qa_mouseDragDown = function(e) {
            this.x = e.clientX - this.canv_rect.left;
            this.y = e.clientY - this.canv_rect.top;
            this.oy = this.y - this.yoffset;
            this.access_sel = -1;

            if (Qsharedf.testWithin(this.x, this.y, 0, 0, this.canvas.width, this.canvas.height)) {
                this.dragging = true;
            }
            if (this.handler_dot > -1 && !this.global_delpoint) {
                this.qtest = '';
                if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;

                var pp1 = this.qtest.split(',');
                var pp2 = pp1.slice(0, this.handler_dot * 2);
                pp2.push(Math.round(this.x).toString(16));
                pp2.push(Math.round(this.oy).toString(16));
                this.qtest = pp2.join(',');
                this.qtest += ',' + pp1.slice(this.handler_dot * 2, pp1.length).join(',');
                if (this.qmode == 'answer' && this.qanswer != '') this.qanswer = this.qtest;
                if (this.qmode == 'edit' && this.qconfig != '') this.qconfig = this.qtest;

                this.handler_sqr = this.handler_dot + 1;
                this.handler_dot = -1;
                this.redraw_once = true;
                this.qa_redraw_canvas;
            }
            //this.freehand
            if (((this.qmode == 'answer' && this.qanswer == '') || (this.qmode == 'edit' && this.qconfig == '')) && this.oy > 0) {
                this.poly_temp_points[6] = this.x;
                this.poly_temp_points[7] = this.oy;
                this.freehand = true;
            }
        };

        this.qa_mouseDragUp = function() {
            this.dragging = false;
            Qsharedf.button_test(this);

            if (this.buttonOver > -1 && this.buttonBox[this.buttonOver][0] == 'toolbar/ico_help.png') {
                if (this.qmode == 'answer') {
                    window.open('../help/student/index.php?id=44');
                } else if (this.qmode == 'edit') {
                    window.open('../../help/staff/index.php?id=305');
                } else {
                    window.open('../help/staff/index.php?id=305');
                }
            }

            if (this.qmode == 'edit' || this.qmode == 'answer') {
                this.global_zoom = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_zoom.png']][6] == 2) this.global_zoom = true;
                this.global_delpoint = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][6] == 2 && this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7] == '+') this.global_delpoint = true;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][5] == 2) {
                    if ((this.qmode == 'edit' && this.qconfig != '') || (this.qmode == 'answer' && this.qanswer != '')) this.global_clearpnl = true;
                    this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][6] = 0;
                }
            }

            if (this.qmode == 'script') {
                this.global_your_answer = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_area.png']][7] == '+' && this.buttonBox[this.buttonBoxNames['toolbar/ico_area.png']][6] == 2) this.global_your_answer = true;
                this.global_corect_answer = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_tick.png']][7] == '+' && this.buttonBox[this.buttonBoxNames['toolbar/ico_tick.png']][6] == 2) this.global_corect_answer = true;
                this.global_show_error = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_warn.png']][7] == '+' && this.buttonBox[this.buttonBoxNames['toolbar/ico_warn.png']][6] == 2) this.global_show_error = true;

                this.global_zoom = false;
            }

            //this.test panel buttons
            if (this.panel_buttons.length > 0) {
                for (var n = 0; n < this.panel_buttons.length; n++) {
                    if (Qsharedf.testWithin(this.x, this.y, this.panel_buttons[n][1], this.panel_buttons[n][2], this.panel_buttons[n][3], this.panel_buttons[n][4])) this.panel_button_selected = this.panel_buttons[n][0];
                }
            }

            //polygon & this.freehand
            //distance of up from down as for "click"
            this.dx = this.x - this.poly_temp_points[6];
            this.dy = this.oy - this.poly_temp_points[7];
            this.distn = Math.sqrt(this.dx * this.dx + this.dy * this.dy);

            if (this.oy > 0) {
                //condition for the finish
                if ((this.poly_temp.length > 2 && (Math.abs(this.poly_temp_points[0] - this.x) < 7 && Math.abs(this.poly_temp_points[1] - this.oy) < 7)) || (Math.abs(this.poly_temp_points[8] - this.x) < 3 && Math.abs(this.poly_temp_points[9] - this.oy) < 3)) {
                    if (this.qmode == 'edit' && this.qconfig == '') this.qconfig = this.poly_temp + Math.round(this.poly_temp_points[0]).toString(16) + ',' + Math.round(this.poly_temp_points[1]).toString(16);
                    if (this.qmode == 'answer' && this.qanswer == '') this.qanswer = this.poly_temp + Math.round(this.poly_temp_points[0]).toString(16) + ',' + Math.round(this.poly_temp_points[1]).toString(16);
                    this.global_delpoint_avail = true;
                    this.poly_temp = '';
                    this.poly_temp_points = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                    //this.do_the_test = true;
                } else {
                    //??
                    if (!this.freehand || this.distn < 5) this.poly_temp += Math.round(this.x).toString(16) + ',' + Math.round(this.oy).toString(16) + ',';
                    //remember the starting point
                    if (this.poly_temp_points[1] == 0) {
                        this.poly_temp_points[0] = this.x;
                        this.poly_temp_points[1] = this.oy;
                    }
                    //remember the second last and the last point
                    this.poly_temp_points[2] = this.poly_temp_points[4];
                    this.poly_temp_points[3] = this.poly_temp_points[5];
                    this.poly_temp_points[4] = this.poly_temp_points[8] = this.x;
                    this.poly_temp_points[5] = this.poly_temp_points[9] = this.oy;
                    this.poly_temp_points[6] = 0;
                    this.poly_temp_points[7] = 0;
                }
            }
            this.freehand = false;

            if (this.panel_button_selected != '') {
                this.panel_buttons = new Array();
                this.global_clearpnl = false;
                this.global_delpoint = false;
                if (this.panel_button_selected == 'Y') {
                    this.poly_temp = '';
                    this.poly_temp_points = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                    if (this.qmode == 'edit') this.qconfig = '';
                    if (this.qmode == 'answer') this.qanswer = '';
                    this.global_delpoint_avail = false;
                }
                this.panel_button_selected = '';
            }

            if (this.handler_sqr != -1 && (this.global_delpoint || this.isCtrl)) {
                this.qtest = '';
                if (this.qmode == 'answer' && this.qanswer != '') this.qtest = this.qanswer;
                if (this.qmode == 'edit' && this.qconfig != '') this.qtest = this.qconfig;

                var pp1 = this.qtest.split(',');
                if (this.handler_sqr > 1 && this.handler_sqr < pp1.length / 2) {
                    this.qtest = pp1.slice(0, this.handler_sqr * 2 - 2).join(',');
                    this.qtest += ',' + pp1.slice(this.handler_sqr * 2, pp1.length).join(',');
                } else {
                    this.qtest = pp1.slice(2, pp1.length - 2).join(',');
                    this.qtest += ',' + pp1.slice(2, 4).join(',');
                }
                if (this.qmode == 'answer' && this.qanswer != '') this.qanswer = this.qtest;
                if (this.qmode == 'edit' && this.qconfig != '') this.qconfig = this.qtest;

                //clear whole array
                pp1 = this.qtest.split(',');
                if (pp1.length <= 4) {
                    if (this.qmode == 'answer' && this.qanswer != '') this.qanswer = '';
                    if (this.qmode == 'edit' && this.qconfig != '') this.qconfig = '';
                    this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][6] = 0;
                    this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][5] = 0;
                    this.global_delpoint = false;
                    this.poly_temp = '';
                    this.poly_temp_points = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                    this.global_delpoint_avail = false;
                    //e.target.style.cursor = 'crosshair';
                }
                this.handler_sqr = -1;
            }
            this.redraw_once = true;
            this.do_the_test = true;
            this.qa_redraw_canvas;
            //this.qa_ReturnInfo();
        };

        this.qa_ReturnInfo = function() {
            var questions_result = '';
            if (this.qmode == 'answer') {
                questions_result = this.test_result + ';';
                if (this.qanswer != '') questions_result += this.qanswer + ', ';
                var target_field = document.getElementById('q' + this.q_Num);
            }
            if (this.qmode == 'edit') {
                questions_result = '0,0,0,0,0,0;';
                if (this.qconfig != '') questions_result += this.qconfig + ', ';
                target_field = document.getElementById(this.doorId);
            }
            if (questions_result != '' && target_field) target_field.value = questions_result;
        };

        this.qa_mouseDblClick = function() {
            this.global_dblclick = true;
        };
    }
});