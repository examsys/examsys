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
// html5 labelling question
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['html5images', 'qsharedf', 'jsxls'], function(Images, Qsharedf, Jsxls) {
    return function () {
        this.test;
        this.x, this.y, this.z, this.m;
        this.sub_x = this.sub_y = 0;
        this.i, this.j;
        this.menu_line = -1;
        this.scale_i = 1; //label image scale
        this.drag_box_id = -1; //index of box being dragged
        this.drag_box_combo = -1;
        this.drag_pho_id = -1;  //index of pholder box over
        this.active_box_id = -1; //index of box being active
        this.active_box_combo = -1;
        this.access_switch = -1;
        this.answer_access_id = -1; //index of box being accessed
        this.answer_access_combo = -1;
        this.pholder_access_id = -1; //index of box being accessed
        this.edit_box_id = -1; //index of box being edited
        this.edit_box_combo = -1;
        this.mov_id = -1; //index of box being animated (to position)
        this.mov_combo = -1;
        this.mov_x = 0;
        this.mov_y = 0;
        this.active_box_handler = -1;
        this.menu_ready = 1;
        this.edit_box_blink = 0;
        this.edit_box_pos = 0;
        this.key_code = 0;
        this.char_code = ''
        this.i_spacex = 5;
        this.i_spacey = 5;//11;
        this.nikotest = 1;
        this.allImagesLoaded = false;
        this.max_num_images = 0;
        this.pholderBox = new Array(); // label no. that's correct answer for each placeholder
        // distractor placeholders have answer of -1
        // sublevels of this keep all the placeholder data
        this.answerBox = new Array(); // sublevels of this keep all the label data
        this.imageBox = new Array(); // image pointers
        this.menuBox = new Array();
        this.shapeBox = new Array(); // sublevels of this keep all the lines/arrows/bobbles data
        this.buttonBox = new Array();  // sublevels of this keep all the buttons data
        this.ql_panelBox = new Array(); // sublevels of this keep the panels data
        this.buttonBoxNames = new Array(); // transcription of button names into its index in ButtonBox (?)
        this.buttonClicked = -1; // index of the button that was clicked
        this.buttonOver = -1; // index of the button the mouse is over
        this.panelOptionOver = -1; // index of the option on panel the mouse is over
        this.panelOver = -1 // index of the panel the mouse is over
        this.panelOverColour = '';
        this.colorReference = new Array();
        this.panelActiveParts = new Array(); // array of positions panel's active elements
        this.global_edit = false;
        this.global_erase = false;
        this.shape_x1 = this.shape_y1 = this.shape_x2 = this.shape_y2 = -1
        // temporary params of a new line/arrow/bobble
        this.global_add = '';
        this.global_move = false;
        this.active_shape = this.active_shape_move = this.active_shape_x = this.active_shape_y = -1;
        //defining panel's active parts
        //toolbar/pan_colours.png
        Qsharedf.def_colour_panel_parts(this);
        this.panelActiveParts.push('toolbar/pan_sizes.png');
        this.panelActiveParts['toolbar/pan_sizes.png'] = new Array();
        for (var i = 0; i < 7; i++)
            this.panelActiveParts['toolbar/pan_sizes.png'][i] = 3 + ',' + (i * 19 + 3);
        //'toolbar/pan_lines.png
        this.panelActiveParts.push('toolbar/pan_lines.png');
        this.panelActiveParts['toolbar/pan_lines.png'] = new Array();
        for (i = 0; i < 7; i++)
            this.panelActiveParts['toolbar/pan_lines.png'][i] = 3 + ',' + (i * 19 + 3);
        this.labelInstanceDepth = new Array();  // depth new instances are created on inside each labelGroup clip
        this.labelTxt = new Array(); // stores text on each label
        this.labelTypes = new Array(); // is label a "text" or "image" label?
        this.imageNames = new Array(); // names of images on each label
        this.imageDimensions = new Array(); // individual dimensions of draggable images
        this.labelCoords = new Array(); // coords for each label
        this.comboCoords = new Array(); // coords for comboboxes - used temporarily in setting them up
        this.distractorTxt = new Array(); // distractors in comboboxes
        this.depthSwapperLabel = new Array();// selected labels are swapped with this clip so label will be
        //on top of all others in same group
        this.qType = "label"; // draggable label ("label"), drop down menu ("menu")
        this.labelMulti = "single"; // are labels unique or repeated ("single" / "multiple")?
        this.yOffset; // coords of everything made in label_add.swf include toolbar
        // this need to be removed as image here
        // is loaded with this.y coord = 0
        this.isCtrl = this.isShift = false;
        this.ShiftChange = false;
        this.empty_answer = false;
        this.not_first_answer = false;
        this.q_Num;
        this.doorId;
        this.slow_speed = 7; //parameter of slowing down speed
        this.currentColours = Array('#FFFFFF', '#3F3F3F', '#000000', '#FF0000');
        // fill, line, text, unanswered colours
        this.lineThickness = 1;  // current thickness of borders around draggable labels
        //and manually drawn lines / arrows (in pixels)
        this.fontChoices = Array(9, 10, 11, 12, 14, 16, 18); // font size in drop down menu
        this.fontSizes = Array(11, 12, 14, 16, 18, 20, 22);  // font size equivalent in Flash (not standard sizes)
        this.fontSizePos = 1; // current font size for labels (index from array above);
        this.draw_limit = new Array(); //used to limit polygon, ellipse and sqare positions
        this.dragging = false;
        this.redraw_once = false;
        this.gen_img;
        this.menu_img;
        this.gen_img_loaded = false;
        this.menu_img_loaded = false;
        this.loc_lft = this.loc_top = 0;
        this.canvas;
        this.context;
        this.canv_rect;
        this.marks_per_correct = 1;
        this.marks_per_incorrect = 0;
        this.marking_method = 'Mark per Option';
        this.qmode;
        this.exclusions = '00000000000000000000';
        this.extra_std = 0;
        this.extra_feedback = '11111';
        this.display_ticks_crosses = true;
        this.display_correct_answer = true;
        this.hide_feedback_ifunanswered = true;
        this.char_labels;
        this.imglabelWidth;
        this.imglabelHeight;
        this.keypressed = false;
        this.all_images = new Array();
        this.imageerrordisplay = 0;
        this.tested_canvas_width = 0;
        this.tested_canvas_height = 0;

        this.ql_gen_img_onload = function() {
            this.gen_img_loaded = true;
            this.redraw_once = true;
            this.ql_redraw_canvas();
        }

        this.ql_ans_img_onload = function() {
            this.imagesLoaded++;
            if (this.imagesLoaded == this.max_num_images) {
                this.allImagesLoaded = true;
                this.redraw_once = true;
                this.ql_redraw_canvas();
            }
        }

        this.menu_img_onload = function() {
            this.menu_img_loaded = true;
            this.menu_ready++;
            this.redraw_once = true;
            this.qa_redraw_canvas;
            this.ql_ReturnInfo();
        }

        this.setUpLabelling = function(num, doorId, lang, image, config, answer, extra, colour, mode) {
            mode = Qsharedf.getmode(mode);
            this.canvas = document.getElementById('canvas' + num);
            this.canv_rect = this.canvas.getBoundingClientRect();

            if (this.canvas && this.canvas.getContext) {
                this.canvas.onmouseup = this.ql_mouseDragUp.bind(this);
                this.canvas.onmousedown = this.ql_mouseDragDown.bind(this);
                this.canvas.onmousemove = this.ql_mouseDragMove.bind(this);
                this.canvas.addEventListener("touchstart", this.ql_mouseDragDown.bind(this), false);
                this.canvas.addEventListener("touchmove", this.ql_mouseDragMove.bind(this), false);
                this.canvas.addEventListener("touchend", this.ql_mouseDragUp.bind(this), false);
                this.canvas.tabIndex = 1000; //force keyboard events
                if (document.addEventListener) {
                    document.addEventListener("keydown", this.ql_mouseDragMove.bind(this), false);
                    document.addEventListener("keypress", this.ql_mouseDragMove.bind(this), false);
                    document.addEventListener("keyup", this.ql_mouseDragMove.bind(this), false);
                } else {
                    document.onkeydown = this.ql_mouseDragMove.bind(this);
                    document.onkeypress = this.ql_mouseDragMove.bind(this);
                    document.onkeyup = this.ql_mouseDragMove.bind(this);
                }
                this.intervalID = window.setInterval(this.ql_redraw_canvas.bind(this), 10);
            }
            if (this.canvas && !this.canvas.getContext) {
                alert(Jsxls.lang_string['errorcanvas']);
            }
            if (this.canvas && this.canvas.getContext) {
                this.context = this.canvas.getContext('2d');
                this.context.lineWidth = 1;

                //---------- num,
                this.q_Num = num;
                //---------- doorId,
                this.doorId = doorId;

                this.gen_img = new Image();

                this.gen_img.onload = this.ql_gen_img_onload.bind(this);
                this.gen_img.src = ((mode == 'edit') ? '../' : '') + '../getfile.php?type=media&filename=' + image;
                //---------- mode
                this.yOffset = 25;
                if (mode == 'edit')
                    this.yOffset = 0;
                this.qmode = mode;

                this.extraImgs = new Array();
                this.exclusions = '00000000000000000000';
                if (extra != "" && extra != undefined && extra != "undefined" && extra != null && extra != "null") {
                    if (this.qmode == 'edit') {
                        this.extraImgs = extra.split(';');
                    } else if (this.qmode == 'script') {
                        var tmp_extra = extra.split(",");
                        if (typeof (tmp_extra[0]) != 'undefined')
                            this.extra_std = tmp_extra[0];
                        if (typeof (tmp_extra[1]) != 'undefined')
                            this.exclusions = tmp_extra[1];
                        if (typeof (tmp_extra[2]) != 'undefined')
                            this.extra_feedback = tmp_extra[2];
                        if (typeof (this.extra_feedback[0]) != 'undefined' && this.extra_feedback[0] == '0')
                            this.display_ticks_crosses = false;
                        if (typeof (this.extra_feedback[1]) != 'undefined' && this.extra_feedback[1] == '0')
                            this.display_correct_answer = false;
                        if (typeof (this.extra_feedback[4]) != 'undefined' && this.extra_feedback[4] == '0')
                            this.hide_feedback_ifunanswered = false;
                    } else {
                        var extra_l1 = extra.split('~');
                        this.marks_per_correct = extra_l1[0];
                        this.marks_per_incorrect = extra_l1[1];
                        this.marking_method = extra_l1[2];
                    }
                }

                //---------- config,
                if (config == '')
                    config = '#3f3f3f;1;#ffffff;10;#000000;100;19;0;0;single;label;$$$$;';
                var existingInfo = config.split(';');
                this.currentColours[1] = existingInfo[0];  // line colour
                this.lineThickness = Number(existingInfo[1]); // line thickness
                this.currentColours[0] = existingInfo[2];	  // fill colour
                for (i = 0; i < this.fontChoices.length; i++) {
                    if (this.fontChoices[i] == existingInfo[3]) {
                        this.fontSizePos = i; // text size
                        break;
                    }
                }

                this.currentColours[2] = existingInfo[4]; // text colour
                this.labelWidth = Number(existingInfo[5]); // text label width
                this.labelWidthEffect = this.labelWidth;
                this.labelHeight = Number(existingInfo[6]); // text label height
                this.labelHeightEffect = this.labelHeight;
                this.imglabelWidth = Number(existingInfo[7]); // image label width
                this.imglabelHeight = Number(existingInfo[8]); // image label height
                this.labelMulti = existingInfo[9]; // single/multiple
                this.qType = existingInfo[10]; // label/menu
                this.existingLabelInfo = new Array(); // one label?

                if (typeof (existingInfo[11]) != 'undefined') {
                    this.existingLabelInfo = existingInfo[11].split("|"); // divides each label
                } else {
                    this.existingLabelInfo = existingInfo[11];
                }

                //adding images loaded as extras
                for (i = 0; i < this.extraImgs.length; i++) {
                    if (this.extraImgs[i] != '') {
                        var tmp_dat = this.extraImgs[i].substr(0, this.extraImgs[i].length - 1).split(",");
                        var tmp_txt = '$$$$' + tmp_dat.join('~');
                        var tmp_x = Number(tmp_dat[1]);
                        var tmp_y = Number(tmp_dat[2]);
                        if (tmp_x > 205) {
                            var tmp_red = 100 / tmp_x;
                            tmp_x = 205;
                            tmp_y = tmp_red * tmp_y;
                        }
                        if (this.imglabelWidth <= tmp_x)
                            this.imglabelWidth = tmp_x;
                        if (this.imglabelHeight <= tmp_y)
                            this.imglabelHeight = tmp_y;
                        this.existingLabelInfo.push(tmp_txt);
                    }
                }

                //repairing/removing undefined labels
                for (i = 0; i < this.existingLabelInfo.length; i++)
                    if (typeof (this.existingLabelInfo[i]) == 'undefined' || this.existingLabelInfo[i] == '' || this.existingLabelInfo[i] == '$$$$') {
                        //this.existingLabelInfo[i] = '$$$$';
                        this.existingLabelInfo.splice(i, 1);
                    }

                //adding empty labels to 20
                for (i = this.existingLabelInfo.length; i < 20; i++)
                    this.existingLabelInfo.push('$$$$');

                //arrays of default positions of labels
                var apx = new Array();
                var apy = new Array();
                var tmpx = 5;
                var tmpy = 30;
                for (i = 0; i < 20; i++) {
                    apx.push(tmpx);
                    apy.push(tmpy);
                    tmpx += this.labelWidth + this.i_spacex + this.lineThickness - 1;
                    if ((tmpx + this.labelWidth) >= 220) {
                        tmpx = 5;
                        tmpy += this.labelHeight + this.i_spacey + this.lineThickness - 1;
                    }
                }
                //reading lines/arrows/bobbles
                for (i = 12; i < existingInfo.length; i++) {
                    if (existingInfo[i] != '') {
                        var shapeTemp = existingInfo[i].split("$");
                        for (j = 2; j < shapeTemp.length; j++)
                            shapeTemp[j]++; //shift for 1px border
                        this.shapeBox.push(shapeTemp);
                    }
                }

                //colours recalc
                for (i = 0; i < this.currentColours.length; i++)
                    this.currentColours[i] = Qsharedf.hexifycolour(this.currentColours[i]);

                this.imagesLoaded = 0;
                var blank_count = 0;
                var max_mli_index = 0;
                if (typeof (this.existingLabelInfo) != 'undefined') {
                    for (i = 0; i < this.existingLabelInfo.length; i++) {
                        var myLabelInfo = this.existingLabelInfo[i].split("$"); //divides each bit of info about label
                        var mli_index = (myLabelInfo[0] != '' ? Number(myLabelInfo[0]) : (max_mli_index + 1)); //index
                        if (max_mli_index < mli_index)
                            max_mli_index = mli_index;
                        var yes_to_add = true;
                        if (typeof (myLabelInfo[4]) == 'undefined')
                            yes_to_add = false;
                        if (this.qmode == 'analysis' && myLabelInfo[4] == '')
                            yes_to_add = false;
                        if (this.qmode == 'script' && myLabelInfo[4] == '')
                            yes_to_add = false;
                        if (mli_index > 20)
                            yes_to_add = false;

                        if (yes_to_add) {
                            var mli_combo = (myLabelInfo[1] != '' ? Number(myLabelInfo[1]) : 0); //combo indicator? >0
                            var mli_pos_xa = Math.round(Number(myLabelInfo[2])); //pos_x
                            var mli_pos_ya = Math.round(Number(myLabelInfo[3])); //pos_y
                            var mli_pos_xb = apx[mli_index - blank_count]; //pos_x
                            var mli_pos_yb = apy[mli_index - blank_count]; //pos_y
                            var mli_answr = myLabelInfo[4]; //answer

                            if (typeof (myLabelInfo[4]) == 'undefined' || myLabelInfo[4] == '')
                                blank_count++;

                            var myLabelType = "text"; // text or image label?
                            var mli_ext_pass = false;
                            var mli_formats = new Array('jpeg', 'jpg', 'png', 'gif');
                            for (var a = 0; a < mli_formats.length; a++)
                                if (mli_answr.toLowerCase().indexOf('.' + mli_formats[a]) != -1)
                                    mli_ext_pass = true;
                            if (typeof (mli_answr) != 'undefined' && mli_ext_pass)
                                myLabelType = "image";

                            //correction in labels text
                            if (myLabelType == "text") {
                                mli_answr = mli_answr.split('#034').join('"');
                                mli_answr = mli_answr.split('#039').join("'");
                                mli_answr = mli_answr.split('#172').join('?');
                                mli_answr = mli_answr.split('#059').join(';');
                                mli_answr = mli_answr.split('#126').join('~');
                                mli_answr = mli_answr.split('#124').join('|');
                            }

                            var tmp_pholder = new Array();
                            this.pho_index = this.pholderBox.length - 1;
                            //updating this.pholderBox array
                            tmp_pholder[0] = mli_index; //index
                            if (mli_pos_xa >= 220) {
                                tmp_pholder[5] = mli_pos_xa; //pos_x
                            } else {
                                tmp_pholder[5] = -500;
                            }
                            tmp_pholder[6] = mli_pos_ya - this.yOffset; //pos_y
                            tmp_pholder[1] = myLabelType; //type: text/image
                            if (myLabelType == 'image') {
                                var mli_answr_label = mli_answr.split("~");
                                tmp_pholder[2] = mli_answr_label[0]; //answer ie. 'beetle3.png' from 'beetle3.png~80~75'
                            } else {
                                tmp_pholder[2] = mli_answr; //answer ie. 'spider'
                            }
                            tmp_pholder[3] = ''; //corectness
                            tmp_pholder[4] = mli_combo; //combo
                            if (mli_combo == 0 || this.labelMulti == 'multiple')
                                this.pholderBox[i] = tmp_pholder;

                            var tmp_answer = new Array();
                            tmp_answer[0] = mli_index; //index
                            tmp_answer[1] = myLabelType; //type: text/image
                            tmp_answer[2] = mli_answr; //label
                            this.labelTxt.push(mli_answr);
                            tmp_answer[9] = tmp_answer[10] = ''; //empty for non-image
                            if (myLabelType == 'image') {
                                var existingImageInfo = myLabelInfo[4].split("~");
                                tmp_answer[2] = existingImageInfo[0]; //filename
                                if (this.all_images.indexOf(tmp_answer[2]) < 0) {
                                    this.all_images.push(tmp_answer[2], '');
                                    this.max_num_images++;
                                }
                                tmp_answer[9] = Number(existingImageInfo[1]); //image oryginal width
                                tmp_answer[10] = Number(existingImageInfo[2]); //image oryginal height
                            }
                            var flag_position = 'keep';
                            if (this.qmode == 'edit' && mli_pos_xa < 220)
                                flag_position = 'new';
                            if (this.qmode == 'analysis' && mli_pos_xa < 220)
                                flag_position = 'new';
                            if (this.qmode == 'answer' && this.qType != 'menu')
                                flag_position = 'new';
                            if (this.qmode == 'script' && this.qType != 'menu')
                                flag_position = 'new';

                            if (flag_position == 'new') {
                                tmp_answer[5] = mli_pos_xb; //pos_x - new
                                tmp_answer[6] = mli_pos_yb - this.yOffset; //pos_y - new
                                tmp_answer[7] = mli_pos_xb; //initial pos_x
                                tmp_answer[8] = mli_pos_yb - this.yOffset; //initial pos_y
                            }
                            if (flag_position == 'keep') {

                                tmp_answer[5] = mli_pos_xa; //pos_x from data
                                tmp_answer[6] = mli_pos_ya - this.yOffset; //pos_y from data
                                tmp_answer[7] = mli_pos_xa; //initial pos_x
                                tmp_answer[8] = mli_pos_ya - this.yOffset; //initial pos_y
                            }
                            if (flag_position == 'out') {
                                tmp_answer[5] = -500
                                tmp_answer[6] = mli_pos_yb - this.yOffset; //pos_y from data
                                tmp_answer[7] = -500
                                tmp_answer[8] = mli_pos_yb - this.yOffset; //initial pos_y
                            }
                            tmp_answer[3] = ''; //corectness
                            tmp_answer[4] = mli_combo; //combo

                            if (typeof (this.answerBox[mli_index]) == 'undefined')
                                this.answerBox[mli_index] = new Array();
                            this.answerBox[mli_index][mli_combo] = tmp_answer;

                        } else {
                            blank_count++;
                        }
                    }
                }

                //reducing list of pholders for menu
                var tmp_reduct = new Array();
                if (this.qType == 'menu') {
                    for (i = this.pholderBox.length - 1; i >= 0; i--) {
                        if (typeof (this.pholderBox[i]) == 'undefined' || typeof (tmp_reduct[this.pholderBox[i][2] + this.pholderBox[i][5] + this.pholderBox[i][6]]) != 'undefined')
                            this.pholderBox.splice(i, 1);
                        tmp_reduct[this.pholderBox[i][2] + this.pholderBox[i][5] + this.pholderBox[i][6]] = 1;
                    }
                }

                //calculating order number of the pholderBox for analysis as [7]
                var nr = 0;
                for (i = 0; i < this.pholderBox.length; i++)
                    if (typeof (this.pholderBox[i]) != 'undefined' && this.pholderBox[i][5] > -500)
                        this.pholderBox[i][7] = nr++;

                //scaling?
                var scale_x, scale_y;
                if (this.imglabelWidth > 200)
                    scale_x = 200 / this.imglabelWidth;
                if (this.imglabelHeight > 200)
                    scale_y = 200 / this.imglabelHeight;
                this.scale_i = scale_x;
                if (this.scale_i < scale_y)
                    this.scale_i = scale_y;

                //loading label images and drawing boxes
                this.context.fillStyle = this.currentColours[0];
                this.context.strokeStyle = this.currentColours[1];

                //removing empty elements
                var tmp_count = this.answerBox.length;
                for (i = tmp_count - 1; i >= 0; i--)
                    if (typeof (this.answerBox[i]) == 'undefined')
                        this.answerBox.splice(i, 1);

                for (i = 0; i < this.answerBox.length; i++) {
                    tmp_count = this.answerBox[i].length;
                    for (var j = tmp_count - 1; j >= 0; j--)
                        if (typeof (this.answerBox[i][j]) == 'undefined')
                            this.answerBox[i].splice(j, 1);
                }

                //duplicates in edit for multi
                for (i = 0; i < this.answerBox.length; i++) {
                    var tmp_add = true;
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        var tmp_arr = this.answerBox[i][j].slice(0);
                        if (tmp_arr[5] < 220)
                            tmp_add = false;
                    }
                    if (tmp_add) {
                        tmp_arr[4] = tmp_arr[5] = tmp_arr[6] = tmp_arr[7] = 0
                        var tmp_len = 0;
                        for (var k = 0; k < this.answerBox.length; k++)
                            for (l = 0; l < this.answerBox[k].length; l++)
                                if (this.answerBox[k][l][2] != '')
                                    tmp_len++
                        if (tmp_len < 40)
                            this.answerBox[i].push(tmp_arr);
                    }
                }

                //renumbering ids
                for (i = 0; i < this.answerBox.length; i++)
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        this.answerBox[i][j][0] = i;
                        this.answerBox[i][j][4] = j;
                    }

                //removing empty elements
                tmp_count = this.pholderBox.length;
                for (i = tmp_count - 1; i >= 0; i--)
                    if (typeof (this.pholderBox[i]) == 'undefined')
                        this.pholderBox.splice(i, 1);

                //renumbering ids
                for (i = 0; i < this.pholderBox.length; i++)
                    if (typeof (this.pholderBox[i]) != 'undefined')
                        this.pholderBox[i][0] = i;

                //loading images
                if (typeof (this.answerBox) != 'undefined')
                    for (i = 0; i < this.answerBox.length; i++) {
                        j = 0;
                        if (typeof (this.answerBox[i][j]) != 'undefined' && this.answerBox[i][j][1] == "image" && this.all_images[this.all_images.indexOf(this.answerBox[i][j][2]) + 1] == '') {
                            this.imageBox[this.answerBox[i][j][2]] = new Image();
                            this.imageBox[this.answerBox[i][j][2]].onload = this.ql_ans_img_onload.bind(this);
                            this.imageBox[this.answerBox[i][j][2]].src = ((this.qmode == 'edit') ? '../' : '') + '../getfile.php?type=media&filename=' + this.answerBox[i][j][2];
                            this.all_images[this.answerBox[i][j][2]] = i + ',' + j;
                        }
                    }

                if (this.max_num_images == 0) {
                    this.allImagesLoaded = true;
                    this.redraw_once = true;
                    this.ql_redraw_canvas();
                }

                //----- menuBox
                if (this.qType == 'menu') {
                    this.menuBox.push('');
                    for (i = 0; i < this.answerBox.length; i++) {
                        for (j = 0; j < this.answerBox[i].length; j++) {
                            if (typeof (this.answerBox[i][j]) != 'undefined' && this.answerBox[i][j][1] == 'text' && this.answerBox[i][j][2] != '' && this.menuBox.indexOf(this.answerBox[i][j][2]) == -1) {
                                this.menuBox.push(this.answerBox[i][j][2]);
                                if (this.qmode != 'edit' && this.qmode != 'script')
                                    this.answerBox[i][j][2] = '';
                            }
                        }
                    }
                }

                //---------- answer,
                if (this.qmode != 'edit' && this.qType == 'menu') {
                    for (i = 0; i < this.answerBox.length; i++)
                        if (typeof (this.answerBox[i]) != 'undefined')
                            for (j = 0; j < this.answerBox[i].length; j++)
                                this.answerBox[i][j][2] = '';
                }
                // sort out existing answer info
                if (answer != '' && answer != undefined && answer != "undefined" && answer != null && answer != "null") {
                    var answer_l1 = answer.split(";");
                    this.not_first_answer = true;
                    if (typeof (answer_l1[1]) != 'undefined' && answer_l1[1] == '')
                        this.empty_answer = true
                    var answer_l2 = answer_l1[1].split('$');
                    var ans_x, ans_y, ans_n, ans_b, new_j, new_i = 0;


                    for (var l = 0; l < answer_l2.length / 4; l++) {
                        if (answer_l2[l * 4] != '') {
                            ans_x = Number(answer_l2[l * 4 + 0]);
                            ans_y = Number(answer_l2[l * 4 + 1]);
                            ans_n = answer_l2[l * 4 + 2];

                            ans_n = ans_n.split('#034').join('"');
                            ans_n = ans_n.split('#039').join("'");
                            ans_n = ans_n.split('#172').join('?');
                            ans_n = ans_n.split('#059').join(';');
                            ans_n = ans_n.split('#126').join('~');
                            ans_n = ans_n.split('#124').join('|');
                            ans_b = answer_l2[l * 4 + 3];

                            if ((this.qmode == 'answer' || this.qmode == 'script') && this.qType != 'menu' && ans_n != '') {
                                new_i = -1;
                                for (i = 0; i < this.answerBox.length; i++) {
                                    if (typeof (this.answerBox[i][0]) != 'undefined' && this.answerBox[i][0][2] == ans_n && this.answerBox[i][0][3] == '' && new_i == -1) {
                                        new_i = i;
                                        new_j = 0;
                                        if (this.labelMulti == 'multiple') {
                                            new_j = Number(this.answerBox[i].length) - 1;
                                            if (this.answerBox[i][new_j][3] != '' || this.labelMulti == 'multiple') {
                                                this.answerBox[i].push(this.answerBox[i][new_j].slice(0));
                                                new_j++;
                                            }
                                        }
                                    }
                                }
                                if (new_i > -1) {
                                    $("#cb_" + new_i).val(3);
                                    this.answerBox[new_i][new_j][2] = ans_n;
                                    this.answerBox[new_i][new_j][3] = ans_b;
                                    this.answerBox[new_i][new_j][4] = new_j;
                                    this.answerBox[new_i][new_j][5] = ans_x;
                                    this.answerBox[new_i][new_j][6] = ans_y + 25 - this.yOffset;
                                    //ans_n = '$';
                                }
                            }

                            if (this.qmode != 'edit' && this.qType == 'menu') {
                                var test_subst = true;
                                for (i = 0; i < this.answerBox.length; i++) {
                                    if (typeof (this.answerBox[i]) != 'undefined') {
                                        for (j = 0; j < this.answerBox[i].length; j++) {
                                            if (test_subst && typeof (this.answerBox[i][j]) != 'undefined' && Number(this.answerBox[i][j][7]) == ans_x && Number(this.answerBox[i][j][8]) == ans_y) {
                                                this.answerBox[i][j][2] = ans_n;
                                                this.answerBox[i][j][3] = ans_b;
                                                this.answerBox[i][j][4] = 0;
                                                this.answerBox[i][j][5] = ans_x;
                                                this.answerBox[i][j][6] = ans_y + 25 - this.yOffset;
                                                test_subst = false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                //adding empty labels to 20
                for (var n = this.answerBox.length; n < 20; n++) {
                    this.answerBox[n] = new Array();
                    this.answerBox[n][0] = new Array(n, "text", "", "", 0, 5, 30, 5, 30, "", "");
                }

                //---------- colour
                this.currentColours[3] = colour;

                //menubar
                this.menu_img = new Image();

                this.menu_img.onload = this.menu_img_onload.bind(this);
                this.menu_img.src = ((this.qmode == 'edit') ? '../' : '') + '../js/images/combined.png';
            }
        }

        this.get_labelHeightEffect = function() {
            this.context.font = this.fontSizes[this.fontSizePos] + "px Arial";
            this.menu_ext = 0;
            if (this.qType == "menu")
                this.menu_ext = 18;
            this.labelWidthEffect = this.labelWidth;
            this.labelHeightEffect = this.labelHeight;
            for (var i = 0; i < this.answerBox.length; i++) {
                for (var j = 0; j < this.answerBox[i].length; j++) {
                    if (typeof (this.answerBox[i][j]) != 'undefined' && this.answerBox[i][j][1] == 'text') {
                        var wrapTemp = Qsharedf.wrapText(this.answerBox[i][j][2], this.labelWidthEffect - this.menu_ext, true, this);
                        if (typeof (wrapTemp) != 'undefined') {
                            if (wrapTemp[1] > this.labelHeightEffect)
                                this.labelHeightEffect = wrapTemp[1] + 4;
                            if (wrapTemp[2] > (this.labelWidthEffect - this.menu_ext))
                                this.labelWidthEffect = wrapTemp[2] + 8 + this.menu_ext;
                        }
                    }
                }
            }
            for (i = 0; i < this.pholderBox.length; i++) {
                if (typeof (this.pholderBox[i]) != 'undefined' && this.pholderBox[i][1] == 'text') {
                    wrapTemp = Qsharedf.wrapText(this.pholderBox[i][2], this.labelWidthEffect - this.menu_ext, true, this);
                    if (typeof (wrapTemp) != 'undefined') {
                        if (wrapTemp[1] > this.labelHeightEffect) {
                            this.labelHeightEffect = wrapTemp[1] + 4;
                        }
                        if (wrapTemp[2] > (this.labelWidthEffect - this.menu_ext))
                            this.labelWidthEffect = wrapTemp[2] + 8 + this.menu_ext;
                    }
                }
            }
        }

        this.combo_scope = function(answer_set) {
            var vc = '';
            for (var v = 0; v < answer_set.length; v++) {
                vc += ((answer_set[v][5] < 220) ? '-' : '+') + answer_set[v][10] + ',';
            }
            return vc;
        }

        this.ql_draw_box = function(i, j, temp_x, temp_y) {
            temp_x++;
            temp_y++;
            var this_box = this.answerBox[i][j];
            if (j == -1)
                this_box = this.answerBox[i][0];
            if (j == 99)
                this_box = this.pholderBox[i];
            //finding answers for this menubox
            if (this.qType == 'menu' && j == 99) {
                this.tmp_text = '';
                for (var a = 0; a < this.answerBox.length; a++) {
                    if (typeof (this.answerBox[a]) != 'undefined') {
                        for (b = 0; b < this.answerBox[a].length; b++) {
                            if (this.answerBox[a][b][5] == this_box[5] && this.answerBox[a][b][6] == this_box[6]) {
                                this.tmp_text = this.answerBox[a][b][2];
                            }
                        }
                    }
                }
                if (this.tmp_text == '' && this.not_first_answer && this.qmode != 'edit' && this.qmode != 'script')
                    this.context.fillStyle = this.currentColours[3]; //&& this.empty_answer
            }
            if (this_box[1] == 'image' && this.qType != 'menu')
                this.context.fillRect(temp_x, temp_y, this.imglabelWidth, this.imglabelHeight);
            if (this_box[1] == 'text')
                this.context.fillRect(temp_x, temp_y, this.labelWidthEffect, this.labelHeightEffect);

            this.context.shadowColor = '#fff';
            this.context.shadowBlur = 0;
            this.context.shadowOffsetX = 0;
            this.context.shadowOffsetY = 0;

            if (this_box[1] == 'image' && this.qType != 'menu') {
                if (typeof (this.imageBox[this_box[2]]) != 'undefined') {
                    var tmp_red = 1;
                    this.tmp_image = this.imageBox[this_box[2]];
                    this.tmp_text = this_box[2];
                    var tmp_width = this_box[9];
                    var tmp_height = this_box[10];
                    if (this.qmode == 'script' && this.display_correct_answer) {
                        if ((this.drag_pho_id == i && j == 99) || (this.drag_box_id == i && this.drag_box_combo == j && temp_x > 220)) {
                            for (a = 0; a < this.pholderBox.length; a++)
                                if (Math.abs(this.pholderBox[a][5] - temp_x) <= 1 && Math.abs(this.pholderBox[a][6] - temp_y) <= 1)
                                    this.tmp_text = this.pholderBox[a][2];
                            for (a = 0; a < this.answerBox.length; a++) {
                                for (var b = 0; b < this.answerBox[a].length; b++) {
                                    if (this.answerBox[a][b][2] == this.tmp_text) {
                                        this.tmp_image = this.imageBox[this.answerBox[a][b][2]];
                                        tmp_width = this.answerBox[a][b][9];
                                        tmp_height = this.answerBox[a][b][10];
                                    }
                                }
                            }
                        }
                    }

                    if (tmp_width > 205)
                        tmp_red = 205 / tmp_width;
                    this.context.drawImage(this.tmp_image, temp_x + (this.imglabelWidth - tmp_width * tmp_red) * 0.5, temp_y + (this.imglabelHeight - tmp_height * tmp_red) * 0.5, tmp_width * tmp_red, tmp_height * tmp_red);
                    this.context.strokeRect(temp_x + 0.5, temp_y + 0.5, this.imglabelWidth, this.imglabelHeight);
                    if (this.exclusions[this.pholderBox[i][7]] == '1') {
                        var tmp_style = this.context.strokeStyle;
                        Qsharedf.lineDraw(this.context, '#FF0000', temp_x + 10.5, temp_y + 10.5, this.imglabelWidth - 20, this.imglabelHeight - 20, null, this);
                        Qsharedf.lineDraw(this.context, '#FF0000', temp_x + 10.5, temp_y + 10.5 + this.imglabelHeight - 20, this.imglabelWidth - 20, -this.imglabelHeight + 20, null, this);
                        this.context.strokeStyle = tmp_style;
                    }
                }
            }
            if (this_box[1] == 'text') {
                this.context.textAlign = ((this.qType == 'menu') ? "left" : "center");
                this.context.font = this.fontSizes[this.fontSizePos] + "px Arial";
                this.context.fillStyle = this.currentColours[2];

                this.tmp_text = this_box[2];

                if (this.qmode == 'script' && this.display_correct_answer) {
                    this.context.fillStyle = '#000';
                    if (this.drag_pho_id == i && j == 99)
                        this.context.fillStyle = this.currentColours[2];
                    if ((this.drag_box_id == i && this.drag_box_combo == j && temp_x >= 220)) {
                        this.context.fillStyle = this.currentColours[2];
                        for (a = 0; a < this.pholderBox.length; a++)
                            if (Math.abs(this.pholderBox[a][5] - temp_x) <= 1 && Math.abs(this.pholderBox[a][6] - temp_y) <= 1) {
                                this.tmp_text = this.pholderBox[a][2];
                            }
                    }
                }

                tmp_width = this.labelWidthEffect;
                wrapped = Qsharedf.wrapText(this.tmp_text, tmp_width, true, this);
                if (this.qType == 'menu') {
                    this.tmp_text = '';
                    for (a = 0; a < this.answerBox.length; a++) {
                        if (typeof (this.answerBox[a]) != 'undefined') {
                            for (b = 0; b < this.answerBox[a].length; b++) {
                                if (this.answerBox[a][b][5] == this_box[5] && this.answerBox[a][b][6] == this_box[6]) {
                                    this.tmp_text = this.answerBox[a][b][2];
                                }
                            }
                        }
                    }
                    if (this.qmode == 'script' && this.display_correct_answer && !(this.hide_feedback_ifunanswered && this.tmp_text == '')) {
                        if ((this.drag_pho_id == i && j == 99) || (this.drag_box_id == i && this.drag_box_combo == j && temp_x >= 220)) {
                            for (a = 0; a < this.pholderBox.length; a++) {
                                if (Math.abs(this.pholderBox[a][5] - temp_x) <= 1 && Math.abs(this.pholderBox[a][6] - temp_y) <= 1) {
                                    this.tmp_text = this.pholderBox[a][2];
                                }
                            }
                        }
                    }
                    tmp_width = this.labelWidthEffect - 19;
                    var wrapped = Qsharedf.wrapText(this.tmp_text, tmp_width, true, this);
                    tmp_width = 5;
                }
                if (typeof (this.pholderBox[i]) != 'undefined' && typeof (this.pholderBox[i][7]) != 'undefined' && this.exclusions[this.pholderBox[i][7]] == '1') {
                    this.context.fillStyle = '#FF0000';
                    tmp_style = this.context.strokeStyle;
                    Qsharedf.lineDraw(this.context, '#FF0000', temp_x + 5.5, temp_y + this.fontSizes[this.fontSizePos] - 1.5, tmp_width - 10, 0, null, this);
                    this.context.strokeStyle = tmp_style;
                }
                this.context.fillStyle = this.currentColours[2];
                Qsharedf.fillWrappedText(this.context, wrapped[0], Math.round(temp_x + tmp_width * 0.5) + 0.5, Math.round(temp_y + this.fontSizes[this.fontSizePos]) + 0.5, this);
                this.context.fillStyle = this.currentColours[0];

                var tmp_halfpoint = Math.round(this.lineThickness / 2) - this.lineThickness / 2;
                this.context.strokeRect(temp_x + tmp_halfpoint, temp_y + tmp_halfpoint, this.labelWidthEffect, this.labelHeightEffect);

                if (this.qType == 'menu') {
                    var tmp_dim = Array(temp_x + this.labelWidthEffect - 1 + this.lineThickness / 2 - 18, temp_y, 18 - this.lineThickness / 2, this.labelHeightEffect);
                    //dropdown combo button
                    this.context.fillStyle = '#f7f7f7';
                    this.context.fillRect(Math.round(tmp_dim[0]) + tmp_halfpoint, Math.round(tmp_dim[1]) + tmp_halfpoint, Math.round(tmp_dim[2]), Math.round(tmp_dim[3]));
                    this.context.strokeRect(Math.round(tmp_dim[0]) + tmp_halfpoint, Math.round(tmp_dim[1]) + tmp_halfpoint, Math.round(tmp_dim[2]), Math.round(tmp_dim[3]));

                    //dropdown combo triangle sign
                    this.context.lineWidth = 1
                    this.context.strokeStyle = '#000';
                    for (a = 0; a < 4; a++)
                        this.context.strokeRect(tmp_dim[0] + 6 + a, tmp_dim[1] + Math.round(tmp_dim[3] / 2) + a - 1.5, 7 - 2 * a, 0);

                    this.context.lineWidth = this.lineThickness;
                    this.context.strokeStyle = this.currentColours[1];
                    this.context.fillStyle = this.currentColours[0];
                    //drop down list
                    if (i == this.active_box_id && this.qmode == 'answer') {
                        tmp_height = this.labelHeightEffect * this.menuBox.length;
                        var tmp_trans = this.lineThickness - 1;

                        var too_close = 0;
                        if ((temp_y + tmp_trans + this.labelHeightEffect + tmp_height) > this.canvas.height)
                            too_close = 2
                        if ((temp_y + tmp_trans + this.labelHeightEffect) < tmp_height)
                            too_close++;
                        if (too_close == 2)
                            tmp_trans = -tmp_height - this.labelHeightEffect;
                        if (too_close == 3)
                            tmp_trans = this.canvas.height - tmp_height - temp_y - this.labelHeightEffect - 2;

                        this.context.fillStyle = '#fff';
                        this.context.fillRect(temp_x + 0.5, temp_y + tmp_trans + this.labelHeightEffect + 0.5, this.labelWidthEffect, tmp_height);

                        for (a = 1; a < this.menuBox.length; a++) {
                            this.context.fillStyle = '#fff';
                            if (Math.round(a / 2) == (a / 2))
                                this.context.fillStyle = '#f8f8f8';
                            this.context.fillRect(temp_x + 1, temp_y + tmp_trans + this.labelHeightEffect + 1 + (a - 1) * this.labelHeightEffect, this.labelWidthEffect - 1, this.labelHeightEffect - 1);
                        }

                        //finding the one already selected
                        this.menu_line = 1;
                        for (a = this.menuBox.length - 1; a >= 0; a--)
                            if (this.menuBox[a] == this.answerBox[i][0][2])
                                this.menu_line = a + 1;

                        //finding the one with cursor over
                        if (this.x > temp_x && this.x < temp_x + this.labelWidthEffect && this.y > temp_y + tmp_trans + this.labelHeightEffect && this.y < temp_y + tmp_trans + this.labelHeightEffect + tmp_height) {
                            for (a = 1; a <= this.menuBox.length; a++)
                                if (this.y > temp_y + tmp_trans + this.labelHeightEffect + 3.5 + (a - 1) * this.labelHeightEffect)
                                    this.menu_line = a;
                        }

                        this.context.fillStyle = this.currentColours[0];
                        if (this.context.fillStyle == '#ffffff')
                            this.context.fillStyle = '#ddd'
                        this.context.fillRect(temp_x + 1, temp_y + tmp_trans + this.labelHeightEffect + 1 + (this.menu_line - 1) * this.labelHeightEffect, this.labelWidthEffect - 1, this.labelHeightEffect - 1);

                        var tmp_colour = this.currentColours[1];
                        this.context.strokeStyle = tmp_colour;
                        this.context.strokeRect(temp_x + 0.5, temp_y + tmp_trans + this.labelHeightEffect + 0.5, this.labelWidthEffect, tmp_height);

                        this.context.textAlign = "left"; //menu
                        this.context.fillStyle = this.currentColours[2];
                        this.context.font = this.fontSizes[this.fontSizePos] + "px Arial";
                        for (a = 0; a < this.menuBox.length; a++) {
                            wrapped = Qsharedf.wrapText(this.menuBox[a], this.labelWidthEffect, true, this);
                            var temp_padding = this.fontChoices[this.fontSizePos] / 2 - 1;
                            if (wrapped[0].indexOf('|') > -1)
                                temp_padding -= wrapped[1] / 4;
                            Qsharedf.fillWrappedText(this.context, wrapped[0], temp_x + 6, temp_y + tmp_trans + this.labelHeightEffect * (a + 1.5) + temp_padding, this);
                        }
                    }
                    this.context.lineWidth = this.lineThickness;
                    this.context.strokeStyle = this.currentColours[1];
                    this.context.fillStyle = this.currentColours[0];
                }
            }

            if (this.qmode == 'script' && this_box[3] != '' && temp_x >= 220) {
                var tmp_test = true;
                if (this.drag_box_id == i && this.drag_box_combo == j && this_box[3] == 'f')
                    tmp_test = false;
                if (this.drag_pho_id == i && j == 99 && this_box[3] == 'f')
                    tmp_test = false;
                if (!(this.display_correct_answer))
                    tmp_test = true;
                if (!(this.display_ticks_crosses))
                    tmp_test = false;
                if (j == -1)
                    tmp_test = false;

                if (tmp_test) {
                    this.imgdata = Images.map['toolbar/ico_tick_g.png'];
                    if (this_box[3] == 'f')
                        this.imgdata = Images.map['toolbar/ico_tick_r.png'];

                    var tmp_h = this.labelHeightEffect;
                    var tmp_w = this.labelWidthEffect;
                    if (this_box[1] == 'image') {
                        tmp_w = this.imglabelWidth;
                        tmp_h = this.imglabelHeight;
                    }
                    this.context.globalAlpha = 0.5;
                    this.context.fillStyle = '#ddd';
                    this.context.beginPath();
                    var tmp_pos = temp_x + tmp_w + 11;
                    if ((tmp_pos + 12) > this.canvas.width)
                        tmp_pos = temp_x - 12;
                    this.context.arc(tmp_pos, temp_y + tmp_h - 10, 10, 0, 2 * Math.PI, false);
                    this.context.fill();
                    //this.context.stroke();
                    this.context.globalAlpha = 1;
                    this.context.strokeStyle = this.currentColours[1];
                    this.context.fillStyle = this.currentColours[0];

                    this.context.drawImage(this.menu_img, this.imgdata.left, this.imgdata.top, this.imgdata.width, this.imgdata.height, tmp_pos - 10, temp_y + tmp_h - 19, this.imgdata.width, this.imgdata.height);
                }
            }

            if (this.qmode == 'analysis') {
                this.tmp_num = '';
                for (a = 0; a < this.pholderBox.length; a++) {
                    if (temp_x >= 220 && Math.abs(this.pholderBox[a][5] - temp_x) <= 1 && Math.abs(this.pholderBox[a][6] - temp_y) <= 1) {
                        if (typeof (this.pholderBox[a][7]) != 'undefined') {
                            this.tmp_num = this.pholderBox[a][7];
                            var temp_col = this.context.fillStyle;
                            this.context.fillStyle = this.currentColours[1];
                            this.context.fillRect(temp_x - 16, temp_y, 16, 15);
                            this.context.textAlign = "center";
                            this.context.fillStyle = '#fff';
                            this.context.font = "bold 13px Arial";
                            this.char_labels = this.tmp_num + 1;
                            this.context.fillText(String.fromCharCode(64 + this.char_labels), temp_x - 8, temp_y + 12);
                            this.context.fillStyle = temp_col;
                        }
                    }
                }
            }
            if (this.tested_canvas_height > -1 && this.tested_canvas_height < temp_y)
                this.tested_canvas_height = temp_y;
        }

        this.ql_redraw_box = function(i, j) {
            var this_box = this.answerBox[i][j];
            if (j == 99)
                this_box = this.pholderBox[i];

            if (typeof this_box != 'undefined' && (this.labelMulti == 'multiple' || this_box[4] == 0)) {
                var temp_x = this_box[5];
                var temp_y = this_box[6];

                if (this.qmode == 'script' && this.qType == "menu" && this.active_box == i) {
                    this_box = this.pholderBox[i];
                    temp_x = this_box[5] - 10;
                    temp_y = this_box[6] - 10;
                }
                //setting shadow
                if (((this.drag_box_id == i && this.drag_box_combo == j) || (this.mov_id == i && this.mov_combo == j)) && this.panelOptionOver == -1 && this.qmode != 'script') {
                    this.context.shadowColor = '#AAA';
                    this.context.shadowBlur = 8;
                    this.context.shadowOffsetX = 2;
                    this.context.shadowOffsetY = 2;
                }

                //slowing down (need to be after setting shadow not to leave shadow after animation)
                if (this.mov_id == i && this.mov_combo == j) {
                    temp_x = this.mov_x = this.mov_x - (this.mov_x - this_box[5]) / this.slow_speed;
                    temp_y = this.mov_y = this.mov_y - (this.mov_y - this_box[6]) / this.slow_speed;
                    //end of slowing down
                    if (Math.abs(this.mov_x - this_box[5]) < 1) {
                        temp_x = this_box[5];
                        temp_y = this_box[6];
                        this.mov_id = -1; //box in place -> clear mov_id -> no box to move anymore
                        this.mov_combo = -1;
                        this.drag_box_id = -1;
                        this.drag_pho_id = -1;
                        this.drag_box_combo = -1;
                        this.redraw_once = true;
                    }
                }
                this.ql_draw_box(i, j, temp_x, temp_y);
            }
        }

        this.ql_panelBoxBuild = function(but_name, pan_name) {
            var temp_but = this.buttonBox[this.buttonBoxNames[but_name]];
            var imgdata = Images.map[pan_name];
            var tmp_but_num = this.buttonBoxNames[but_name];
            this.ql_panelBox.push(tmp_but_num);
            this.ql_panelBox[tmp_but_num] = new Array();
            this.ql_panelBox[tmp_but_num][0] = tmp_but_num;
            this.ql_panelBox[tmp_but_num][1] = but_name;
            this.ql_panelBox[tmp_but_num][2] = pan_name;
            this.ql_panelBox[tmp_but_num][3] = temp_but[1];
            this.ql_panelBox[tmp_but_num][4] = temp_but[2] + 25;
            this.ql_panelBox[tmp_but_num][5] = imgdata.width;
            this.ql_panelBox[tmp_but_num][6] = imgdata.height;
        }

        this.ql_menuBuild = function() {
            var toolb1 = new Array('toolbar/ico_bucket.png', 'toolbar/ico_brush.png', 'toolbar/ico_letter.png', 'toolbar/ico_size.png', 'toolbar/ico_lines.png');
            var toolt1 = new Array('fillcolour', 'linecolour', 'textcolour', 'textsize', 'lines');
            var toolb2 = new Array('toolbar/ico_erase.png', 'toolbar/ico_resize.png', 'toolbar/ico_line.png', 'toolbar/ico_bobble.png', 'toolbar/ico_arrow.png')
            var toolt2 = new Array('erase', 'edit', 'line', 'bobble', 'arrow')
            var posx = Qsharedf.menuBuild_icons('toolbar/vert_1.png', 0, 0, 0, '', '', '', this);
            var spac = 4;
            posx = 4;
            var posy = 3;
            for (i = 0; i < toolb1.length; i++) {
                posx = Qsharedf.menuBuild_icons(toolb1[i], posx, posy, 0, '', '', Jsxls.lang_string[toolt1[i]], this) + spac;
                posx = Qsharedf.menuBuild_icons('toolbar/ico_drop.png', posx - 2, posy, 0, '', '', '', this) + spac;
            }

            posx = Qsharedf.menuBuild_icons('toolbar/vert_1.png', 220, 0, 0, '', '', '', this);
            spac = 5;
            posx = 224;
            for (i = 0; i < toolb2.length; i++) {
                posx = Qsharedf.menuBuild_icons(toolb2[i], posx, posy, 0, 'a', '', Jsxls.lang_string[toolt2[i]], this) + spac;
            }
            this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][5] = 2;
            this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][6] = 2;

            posx = Qsharedf.menuBuild_icons('toolbar/vert_2.png', posx, posy, 0, '', '', '', this) + spac;
            var temp_opt = 2;
            if (this.labelMulti == 'multiple')
                temp_opt = 0;
            posx = Qsharedf.menuBuild_icons('toolbar/ico_single.png', posx, posy, temp_opt, 'b', '', Jsxls.lang_string['single'], this) + spac;
            posx = Qsharedf.menuBuild_icons('toolbar/ico_multiple.png', posx, posy, 2 - temp_opt, 'b', '', Jsxls.lang_string['multiple'], this) + spac;
            posx = Qsharedf.menuBuild_icons('toolbar/vert_2.png', posx, posy, 0, '', '', '', this) + spac;
            temp_opt = 2;
            if (this.qType == 'menu')
                temp_opt = 0;
            posx = Qsharedf.menuBuild_icons('toolbar/ico_label.png', posx, posy, temp_opt, 'c', '', Jsxls.lang_string['label'], this) + spac;
            posx = Qsharedf.menuBuild_icons('toolbar/ico_menu.png', posx, posy, 2 - temp_opt, 'c', '', Jsxls.lang_string['menu'], this) + spac;
            posx = Qsharedf.menuBuild_icons('toolbar/ico_help.png', this.canvas.width - 23, posy, 0, '-', '', '', this) + spac;

            //setting the this.ql_panelBox array
            this.ql_panelBoxBuild('toolbar/ico_bucket.png', 'toolbar/pan_colours.png');
            this.ql_panelBoxBuild('toolbar/ico_brush.png', 'toolbar/pan_colours.png');
            this.ql_panelBoxBuild('toolbar/ico_letter.png', 'toolbar/pan_colours.png');
            this.ql_panelBoxBuild('toolbar/ico_size.png', 'toolbar/pan_sizes.png');
            this.ql_panelBoxBuild('toolbar/ico_lines.png', 'toolbar/pan_lines.png');
        }

        this.ql_redraw_canvas = function() {
            this.char_labels = 0;
            this.draw_limit = new Array(0, 27, this.canvas.width - 2, this.canvas.height - 2);

            //scaling up the label width by text

            function draw_shape(_self, tt, tx1, ty1, tx2, ty2) {
                //drawing the line, bobble or arrow...
                _self.context.beginPath();
                _self.context.moveTo(tx1, ty1);
                _self.context.lineTo(tx2, ty2);
                _self.context.stroke();

                if (tt == 'arrow') {
                    _self.context.lineWidth = 1;
                    var xx = tx2 - tx1;
                    var yy = ty2 - ty1;
                    var rr = Math.atan2(yy, xx);
                    var pp = 0.5;
                    tt = 4 + 1.3 * _self.lineThickness;
                    var x1 = 1 * tx2 + Math.cos(rr) * tt / 2;
                    var y1 = 1 * ty2 + Math.sin(rr) * tt / 2;
                    var x2 = Math.round(x1 + Math.cos(rr - Math.PI + pp) * tt);
                    var y2 = Math.round(y1 + Math.sin(rr - Math.PI + pp) * tt);
                    var x3 = Math.round(x1 + Math.cos(rr - Math.PI - pp) * tt);
                    var y3 = Math.round(y1 + Math.sin(rr - Math.PI - pp) * tt);
                    _self.context.beginPath();
                    _self.context.moveTo(x1, y1);
                    _self.context.lineTo(x2, y2);
                    _self.context.lineTo(x3, y3);
                    _self.context.lineTo(x1, y1);
                    _self.context.fill();
                    _self.context.stroke();
                    _self.context.lineWidth = _self.lineThickness;
                }

                if (tt == 'bobble') {
                    _self.context.beginPath();
                    _self.context.arc(tx2, ty2, 2 + 0.5 * _self.lineThickness, 0, 2 * Math.PI, false);
                    _self.context.fill();
                    _self.context.stroke();
                }
            }

            if (!(this.allImagesLoaded && this.gen_img_loaded && this.menu_img_loaded) && this.imageerrordisplay < 501)
                this.imageerrordisplay++;
            if (!(this.allImagesLoaded && this.gen_img_loaded && this.menu_img_loaded) && this.imageerrordisplay == 500) {
                this.context.textAlign = "left";
                this.context.fillStyle = '#C00000';
                this.context.font = "13px Arial";
                this.context.fillText(Jsxls.lang_string['errorimageslabelling'], 15, 15);
            }

            //main redrawing part
            if (this.allImagesLoaded && this.menu_img_loaded && this.gen_img_loaded && (this.dragging || this.redraw_once || this.mov_id != -1 || (this.global_add != '' && this.shape_x1 > -1) || this.global_move || this.global_erase)) {
                this.redraw_once = false;
                this.get_labelHeightEffect();
                //store this.lineThickness
                var hold_lineThickness = this.lineThickness;

                for (i = 0; i < this.shapeBox.length; i++) {
                    //recalculating shapes against limits
                    if (this.shapeBox[i][2] < this.draw_limit[0])
                        this.shapeBox[i][2] = this.draw_limit[0];
                    if (this.shapeBox[i][4] < this.draw_limit[0])
                        this.shapeBox[i][4] = this.draw_limit[0];
                    if (this.shapeBox[i][2] > this.draw_limit[2])
                        this.shapeBox[i][2] = this.draw_limit[2];
                    if (this.shapeBox[i][4] > this.draw_limit[2])
                        this.shapeBox[i][4] = this.draw_limit[2];
                    if (this.shapeBox[i][3] < this.draw_limit[1])
                        this.shapeBox[i][3] = this.draw_limit[1];
                    if (this.shapeBox[i][5] < this.draw_limit[1])
                        this.shapeBox[i][5] = this.draw_limit[1];
                    if (this.shapeBox[i][3] > this.draw_limit[3])
                        this.shapeBox[i][3] = this.draw_limit[3];
                    if (this.shapeBox[i][5] > this.draw_limit[3])
                        this.shapeBox[i][5] = this.draw_limit[3];
                }

                //testing
                if ((this.global_move || this.global_erase) && typeof this.x != 'undefined') {
                    this.lineThickness = 1.5 * hold_lineThickness + 2;
                    this.active_shape = -1;
                    this.context.lineWidth = this.lineThickness;
                    this.context.fillStyle = this.context.strokeStyle = '#ff0000';
                    for (i = 0; i < this.shapeBox.length; i++) {
                        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                        draw_shape(this, this.shapeBox[i][1], this.shapeBox[i][2], this.shapeBox[i][3] - this.yOffset, this.shapeBox[i][4], this.shapeBox[i][5] - this.yOffset);
                        var timgd = this.context.getImageData(this.x, this.y, 1, 1);
                        var timgp = timgd.data;
                        if (Qsharedf.hexifycolour('' + ((timgp[0] * 256 + timgp[1]) * 256 + 1 * timgp[2])).toUpperCase() == '#FF0000')
                            this.active_shape = i;
                    }
                }
                //testing - end

                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.context.drawImage(this.gen_img, 221, 26 - this.yOffset);

                //frames
                this.context.lineWidth = 1;
                this.context.strokeStyle = '#c0c0c0';//'#7f9db9';
                if (this.qmode == 'edit')
                    this.context.strokeRect(0.5, 0.5, this.canvas.width - 1, 25);
                this.context.strokeStyle = '#909090';  //'#7f9db9';
                this.context.strokeRect(220.5, 0.5, this.canvas.width - 220, this.canvas.height - 1);

                if (this.global_move && this.active_shape_move > -1) {
                    var tx = this.active_shape_x - this.x;
                    var ty = this.active_shape_y - this.y;
                    var shape_end = 0
                    if (Math.abs(this.shapeBox[this.active_shape_move][2] - this.active_shape_x) < 5 && Math.abs(this.shapeBox[this.active_shape_move][3] - this.active_shape_y) < 5)
                        shape_end = 1;
                    if (Math.abs(this.shapeBox[this.active_shape_move][4] - this.active_shape_x) < 5 && Math.abs(this.shapeBox[this.active_shape_move][5] - this.active_shape_y) < 5)
                        shape_end = 2;

                    //move whole
                    if (shape_end == 0 || shape_end == 1) {
                        this.shapeBox[this.active_shape_move][2] -= tx;
                        this.shapeBox[this.active_shape_move][3] -= ty;
                    }
                    if (shape_end == 0 || shape_end == 2) {
                        this.shapeBox[this.active_shape_move][4] -= tx;
                        this.shapeBox[this.active_shape_move][5] -= ty;
                    }
                    this.active_shape_x = this.x;
                    this.active_shape_y = this.y;
                }

                //draw background for active shape
                if ((this.global_move || this.global_erase) && this.active_shape > -1) {
                    this.context.lineWidth = this.lineThickness;
                    this.context.fillStyle = this.context.strokeStyle = '#ffaaaa';
                    this.context.lineCap = 'round';
                    draw_shape(this, this.shapeBox[this.active_shape][1], this.shapeBox[this.active_shape][2], this.shapeBox[this.active_shape][3] - this.yOffset, this.shapeBox[this.active_shape][4], this.shapeBox[this.active_shape][5] - this.yOffset);
                }

                //restore this.lineThickness
                this.lineThickness = hold_lineThickness;
                this.context.lineCap = 'butt';
                //draw line, arrow, bobble
                this.context.lineWidth = this.lineThickness;
                this.context.strokeStyle = this.currentColours[1];
                this.context.fillStyle = this.currentColours[1];
                for (i = 0; i < this.shapeBox.length; i++) {
                    draw_shape(this, this.shapeBox[i][1], this.shapeBox[i][2], this.shapeBox[i][3] - this.yOffset, this.shapeBox[i][4], this.shapeBox[i][5] - this.yOffset);
                }

                //draw handlers for active shape
                if (this.global_move && this.active_shape > -1) {
                    Qsharedf.edtDot(this.context, '#cc0000', this.shapeBox[this.active_shape][2], this.shapeBox[this.active_shape][3] - this.yOffset, 2 + 0.1 * this.lineThickness, this);
                    Qsharedf.edtDot(this.context, '#cc0000', this.shapeBox[this.active_shape][4], this.shapeBox[this.active_shape][5] - this.yOffset, 2 + 0.1 * this.lineThickness, this);

                    this.context.strokeStyle = this.currentColours[1];
                    this.context.fillStyle = this.currentColours[1];
                }

                if (this.shape_x1 > -1 && this.shape_x2 == -1)
                    draw_shape(this, this.global_add, this.shape_x1, this.shape_y1 - this.yOffset, this.x, this.y - this.yOffset);
                this.context.font = this.fontSizes[this.fontSizePos] + "px Arial";


                //draw labels placeholders
                var loc_width, loc_height;

                if (this.qmode != 'edit' && this.qType != 'menu') {
                    for (i = 0; i < this.pholderBox.length; i++) {
                        if (typeof (this.pholderBox[i]) != 'undefined') {
                            //selecting width and height
                            loc_width = this.imglabelWidth;
                            loc_height = this.imglabelHeight;

                            if (this.pholderBox[i][1] == 'text') {
                                loc_width = this.labelWidthEffect;
                                loc_height = this.labelHeightEffect;
                            }

                            //drawing background (unanswered)
                            this.context.fillStyle = this.currentColours[0];
                            if (this.pholderBox[i][3] == '' && this.not_first_answer && this.qmode != 'edit' && this.qmode != 'script') {
                                this.context.fillStyle = this.currentColours[3]; //&& this.empty_answer
                                this.context.fillRect(this.pholderBox[i][5] + 1.5, this.pholderBox[i][6] + 1.5, loc_width, loc_height);
                            }
                            //fill and strike background rectangle
                            if (this.qmode != 'script' && this.qmode != 'answer')
                                this.context.fillRect(this.pholderBox[i][5] + 1.5, this.pholderBox[i][6] + 1.5, loc_width, loc_height);
                            this.context.strokeRect(this.pholderBox[i][5] + 1.5, this.pholderBox[i][6] + 1.5, loc_width, loc_height);
                            if (this.tested_canvas_width > -1 && this.tested_canvas_width < (this.pholderBox[i][5] + loc_width))
                                this.tested_canvas_width = (this.pholderBox[i][5] + loc_width);

                        }
                    }
                }
                this.context.fillStyle = this.currentColours[0]; //resetting colour

                //moving shapes by arrow keys
                if (this.qmode == 'edit') {
                    if (this.active_shape > -1) {
                        if (this.key_code == 39) { //arror right
                            this.shapeBox[this.active_shape][2]++;
                            this.shapeBox[this.active_shape][4]++;
                        }
                        if (this.key_code == 37) { //arrow left
                            this.shapeBox[this.active_shape][2]--;
                            this.shapeBox[this.active_shape][4]--;
                        }
                        if (this.key_code == 38) { //arrow up
                            this.shapeBox[this.active_shape][3]--;
                            this.shapeBox[this.active_shape][5]--;
                        }
                        if (this.key_code == 40) { //arrow down
                            this.shapeBox[this.active_shape][3]++;
                            this.shapeBox[this.active_shape][5]++;
                        }
                    }
                }
                //tab select of labels
                if (this.qmode == 'edit' && this.edit_box_id == -1) {
                    if (this.key_code == 9) { //tab
                        var tmp_pos_array = [];
                        var tmp_sort_array = [];
                        var tmp_area = 0;
                        //creating sorted list of labels and selecting next available
                        for (i = this.answerBox.length - 1; i >= 0; i--)
                            for (var j = this.answerBox[i].length - 1; j >= 0; j--) {
                                tmp_area = 2;
                                if (this.answerBox[i][j][5] >= 220)
                                    tmp_area = 1;
                                if (this.answerBox[i][j][5] > 0 && (j == 0 || this.labelMulti == 'multiple'))
                                    tmp_pos_array[(tmp_area * 1000 + this.answerBox[i][j][6]) + '.' + (1000 + this.answerBox[i][j][5])] = i + ',' + j;
                            }

                        //sort tmp array of labels positions
                        for (key in tmp_pos_array)
                            tmp_sort_array.push(key);
                        tmp_sort_array.sort();

                        //locate position of the selected
                        var tmp_pos = '';
                        for (var key in tmp_pos_array)
                            if (tmp_pos_array[key] == this.active_box_id + ',' + this.active_box_combo)
                                tmp_pos = key;

                        //locate index and selecting next
                        var tmp_i = -1;
                        for (i = 0; i < tmp_sort_array.length; i++)
                            if (tmp_sort_array[i] == tmp_pos)
                                tmp_i = i;

                        if (!this.isShift) {
                            tmp_i++;
                            if (tmp_i == tmp_sort_array.length)
                                tmp_i = 0;
                        }
                        if (this.isShift) {
                            tmp_i--;
                            if (tmp_i < 0)
                                tmp_i = tmp_sort_array.length - 1;
                        }

                        tmp_pos = tmp_sort_array[tmp_i];

                        this.active_box_id = tmp_pos_array[tmp_pos].split(',')[0];
                        this.active_box_combo = tmp_pos_array[tmp_pos].split(',')[1];

                        if ((this.answerBox.length - 1) < this.active_box_id)
                            this.active_box_id = 0;
                    }

                    if (typeof (this.answerBox[this.active_box_id]) != 'undefined' && typeof (this.answerBox[this.active_box_id][this.active_box_combo]) != 'undefined') {
                        if (this.key_code == 39)
                            this.answerBox[this.active_box_id][this.active_box_combo][5]++; //arrow right
                        if (this.key_code == 37)
                            this.answerBox[this.active_box_id][this.active_box_combo][5]--; //arrow left
                        if (this.key_code == 38)
                            this.answerBox[this.active_box_id][this.active_box_combo][6]--; //arrow up
                        if (this.key_code == 40)
                            this.answerBox[this.active_box_id][this.active_box_combo][6]++; //arrow down

                    }
                    if (this.key_code >= 37 && this.key_code <= 40)
                        this.ql_ReturnInfo();
                }

                //edit box
                if (this.qmode == 'edit' && this.active_box_id > -1 && typeof (this.answerBox[this.active_box_id]) != 'undefined' && typeof (this.answerBox[this.active_box_id][this.active_box_combo]) != 'undefined' && this.answerBox[this.active_box_id][this.active_box_combo][1] != 'image') {

                    var tmp_edit_text = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][2];
                    if (this.labelMulti == 'multiple')
                        tmp_edit_text = this.answerBox[this.active_box_id][this.active_box_combo][2];
                    var text_len = tmp_edit_text.length;
                    if (this.key_code == 39)
                        this.edit_box_pos++; //arrow right
                    if (this.key_code == 37)
                        this.edit_box_pos--; //arrow left

                    if (this.key_code == 35)
                        this.edit_box_pos = text_len; //end
                    if (this.key_code == 36)
                        this.edit_box_pos = 0; //home
                    if (this.edit_box_pos < 0)
                        this.edit_box_pos = 0;
                    if (this.edit_box_pos > text_len)
                        this.edit_box_pos = text_len;
                    var temp_t = '';
                    if (this.char_code != '') {	//characters
                        temp_t = tmp_edit_text.substr(0, this.edit_box_pos) + this.char_code + tmp_edit_text.substr(this.edit_box_pos);
                        this.edit_box_pos++;
                    }
                    if (this.key_code == 46) { //del
                        temp_t = tmp_edit_text.substr(0, this.edit_box_pos) + tmp_edit_text.substr(this.edit_box_pos + 1);
                        if (temp_t == '')
                            temp_t = '$';
                    }
                    if (this.key_code == 8) { //backspace
                        temp_t = tmp_edit_text.substr(0, this.edit_box_pos - 1) + tmp_edit_text.substr(this.edit_box_pos);
                        this.edit_box_pos--;
                        if (temp_t == '')
                            temp_t = '$';
                    }
                    if (temp_t != '') {
                        for (a = 0; a < this.answerBox[this.active_box_id].length; a++) {
                            if (temp_t == '$')
                                temp_t = '';
                            this.answerBox[this.active_box_id][a][2] = temp_t;
                            this.pholderBox[this.answerBox[this.active_box_id][a][0]][2] = temp_t;
                        }
                        this.ql_ReturnInfo();
                    }
                }
                if (this.qmode == 'edit') {
                    this.char_code = '';
                    this.key_code = 0;
                }

                //rebuilding the panel of available labels
                var tmpx = 5;
                var tmpy = 30 - this.yOffset;
                var tmpw, tmph, tmphn = 0, tmpwn = 0;
                //1D array i,j,width,height of those labels in the label panel
                var tmpp = Array();
                var tmpl = Array();

                for (i = 0; i < this.answerBox.length; i++) {
                    tmpl[i] = true;
                    if (typeof (this.answerBox[i]) != 'undefined') {
                        for (j = 0; j < this.answerBox[i].length; j++) {
                            if (typeof (this.answerBox[i][j]) != 'undefined' && this.answerBox[i][j][5] < 220 && tmpl[i] && (this.labelMulti == 'multiple' || j == 0)) {
                                tmpl[i] = false;
                                tmpw = this.labelWidthEffect;
                                tmph = this.labelHeightEffect;
                                if (this.answerBox[i][j][1] == 'image') {
                                    tmpw = this.imglabelWidth;
                                    tmph = this.imglabelHeight;
                                }
                                tmpp.push(Array(i, j, tmpw, tmph));
                            }
                        }
                    }
                }

                for (a = 0; a < tmpp.length; a++) {
                    i = tmpp[a][0];
                    //j = tmpp[a][1];
                    var sum_label = '';
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        tmpw = tmpp[a][2];
                        tmph = tmpp[a][3];
                        var ax = tmpx;
                        if (this.answerBox[i][j][2] == '' && (this.qmode == 'answer' || this.qmode == 'script' || this.qmode == 'analysis'))
                            ax = -500;

                        if (this.answerBox[i][j][7] < 220) {
                            this.answerBox[i][j][7] = ax;
                            this.answerBox[i][j][8] = tmpy;
                        }
                        if (i != this.drag_box_id || j != this.drag_box_combo || this.answerBox[i][j][5] == 0) {
                            if (this.answerBox[i][j][5] < 220) {
                                this.answerBox[i][j][5] = ax;
                                this.answerBox[i][j][6] = tmpy;
                            }
                        }
                        sum_label += this.answerBox[i][j][2];
                    }
                    if (!(sum_label == '' && (this.qmode == 'answer' || this.qmode == 'script'))) {
                        tmpx += this.i_spacex + tmpw + this.lineThickness - 1;
                        if (tmphn < tmph)
                            tmphn = tmph;
                        tmpwn = 0;
                        if (a < (tmpp.length - 1))
                            tmpwn = tmpp[a + 1][2];
                        if ((tmpx + tmpwn) >= 220) {
                            tmpx = 5;
                            tmpy += tmphn + this.i_spacey + this.lineThickness - 1;
                            tmphn = 0;
                        }
                    }
                }

                if (this.qType == "menu" && (this.qmode == 'answer' || this.qmode == 'script')) {
                    //menus
                    for (i = this.answerBox.length - 1; i >= 0; i--) {
                        if (typeof (this.pholderBox[i]) != 'undefined' && this.pholderBox[i][1] == 'text' && this.pholderBox[i][5] >= 220 && i != this.active_box_id) {
                            this.ql_draw_box(i, 99, this.pholderBox[i][5], this.pholderBox[i][6]);
                        }
                    }
                    //draw the active menu list on top of others
                    if (this.active_box_id != -1)
                        this.ql_draw_box(this.active_box_id, 99, this.pholderBox[this.active_box_id][5], this.pholderBox[this.active_box_id][6])
                }

                //images and labels
                for (i = this.answerBox.length - 1; i >= 0; i--) {
                    for (j = this.answerBox[i].length - 1; j >= 0; j--) {
                        if (typeof (this.answerBox[i][j]) != 'undefined' && !(this.drag_box_id == i && this.drag_box_combo == j) && !(this.mov_id == i && this.mov_combo == j) && !(this.qType == "menu" && this.qmode == 'answer' && this.answerBox[i][j][1] != 'image')) {
                            if (!(this.qType == "menu" && this.qmode == 'script' && this.answerBox[i][j][5] < 220)) {
                                if ((this.qmode == 'script' || this.qmode == 'analysis') && this.answerBox[i][j][5] < 220)
                                    this.context.globalAlpha = 0.5;
                                this.ql_redraw_box(i, j);
                                this.context.globalAlpha = 1;
                            }
                        }
                    }
                }
                if (this.tested_canvas_height > -1) {
                    if (this.canvas.height < this.tested_canvas_height)
                        this.canvas.height = Math.floor(this.tested_canvas_height);
                    if (this.canvas.width < this.tested_canvas_width)
                        this.canvas.width = Math.floor(this.tested_canvas_width) + 5;
                    this.tested_canvas_height = -1;
                    this.tested_canvas_width = -1;
                    this.redraw_once = true;
                }
                this.context.fillStyle = this.currentColours[0]; //resetting colour

                //redraw active label to have it on top
                if (this.active_box_id > -1 && !(this.qType == "menu" && this.qmode == 'answer' && this.answerBox[this.active_box_id][this.active_box_combo][1] != 'image'))
                    this.ql_redraw_box(this.active_box_id, this.active_box_combo);

                //redraw empty pholderbox in script and analysis
                if (this.drag_pho_id > -1 && this.display_correct_answer && (this.qmode == 'script' || this.qmode == 'analysis')) {
                    //finding answers for this pholder
                    this.tmp_text = '';
                    for (var a = 0; a < this.answerBox.length; a++) {
                        if (typeof (this.answerBox[a]) != 'undefined') {
                            for (var b = 0; b < this.answerBox[a].length; b++) {
                                if (this.answerBox[a][b][5] == this.pholderBox[this.drag_pho_id][5] && this.answerBox[a][b][6] == this.pholderBox[this.drag_pho_id][6]) {
                                    this.tmp_text = this.answerBox[a][b][2];
                                }
                            }
                        }
                    }
                    if (!(this.hide_feedback_ifunanswered && this.tmp_text == ''))
                        this.ql_redraw_box(this.drag_pho_id, 99);
                }

                //redraw dragged shape to have it on top
                var drag_mix = this.drag_box_id + ':' + this.drag_box_combo;
                var active_mix = this.active_box_id + ':' + this.active_box_combo;
                var mov_mix = this.mov_id + ':' + this.mov_combo;
                if (this.drag_box_id > -1 && drag_mix != active_mix && !(this.qType == "menu" && (this.qmode == 'answer' || this.qmode == 'script') && this.answerBox[this.drag_box_id][this.drag_box_combo][1] != 'image')) {
                    if (this.qmode == 'script' && this.qType != "menu" && this.answerBox[this.drag_box_id][this.drag_box_combo][5] < 220)
                        this.context.globalAlpha = 0.5;
                    this.ql_redraw_box(this.drag_box_id, this.drag_box_combo);
                    this.context.globalAlpha = 1;
                }

                //locate correct pholderBox for answerBox for script
                if (this.qmode == 'script' && this.drag_pho_id == -1) {
                    if (this.drag_box_id > -1) {
                        var tmp_test = -1;
                        for (i = 0; i < this.pholderBox.length; i++) {
                            if (this.answerBox[this.drag_box_id][this.drag_box_combo][5] == this.pholderBox[i][5] &&
                                this.answerBox[this.drag_box_id][this.drag_box_combo][6] == this.pholderBox[i][6])
                                tmp_test = i;
                        }
                        this.drag_pho_id = tmp_test;
                    }
                    //redraw pholder for script
                    if (this.drag_pho_id > -1 && this.display_correct_answer) {
                        tmp_test = -1;
                        for (i = 0; i < this.answerBox.length; i++) {
                            if (this.answerBox[i][0][1] == this.pholderBox[this.drag_pho_id][1] && this.answerBox[i][0][2] == this.pholderBox[this.drag_pho_id][2])
                                tmp_test = i;
                        }
                        if (tmp_test != -1) {
                            this.ql_draw_box(tmp_test, -1, this.pholderBox[this.drag_pho_id][5], this.pholderBox[this.drag_pho_id][6]);//&& this.qType != "menu"
                        }
                    }
                }

                //redraw animated shape to have it on top
                if (this.mov_id > -1 && mov_mix != drag_mix && mov_mix != active_mix && !(this.qType == "menu" && this.qmode == 'answer' && this.answerBox[this.mov_id][this.mov_combo][1] != 'image')) {
                    this.ql_redraw_box(this.mov_id, this.mov_combo);
                }

                if (this.qmode == 'answer') {
                    this.context.lineWidth = 3;
                    if (this.answer_access_id > -1) {
                        loc_width = this.imglabelWidth;
                        loc_height = this.imglabelHeight;
                        if (this.answerBox[this.answer_access_id][this.answer_access_combo][1] == 'text') {
                            loc_width = this.labelWidthEffect;
                            loc_height = this.labelHeightEffect;
                        }

                        //draw handlers for active label
                        this.context.strokeStyle = '#FFBD69';
                        this.context.strokeRect(
                            this.answerBox[this.answer_access_id][this.answer_access_combo][5] - this.context.lineWidth / 2 + 1.5,
                            this.answerBox[this.answer_access_id][this.answer_access_combo][6] - this.context.lineWidth / 2 + 1.5,
                            loc_width + this.context.lineWidth,
                            loc_height + this.context.lineWidth);
                        this.context.strokeStyle = this.currentColours[1];
                    }
                    if (this.pholder_access_id > -1) {
                        loc_width = this.imglabelWidth;
                        loc_height = this.imglabelHeight;
                        if (this.pholderBox[this.pholder_access_id][1] == 'text') {
                            loc_width = this.labelWidthEffect;
                            loc_height = this.labelHeightEffect;
                        }

                        //draw handlers for active label
                        this.context.strokeStyle = '#FFBD69';
                        this.context.strokeRect(
                            this.pholderBox[this.pholder_access_id][5] - this.context.lineWidth / 2 + 1.5,
                            this.pholderBox[this.pholder_access_id][6] - this.context.lineWidth / 2 + 1.5,
                            loc_width + this.context.lineWidth,
                            loc_height + this.context.lineWidth);
                        this.context.strokeStyle = this.currentColours[1];
                    }
                    this.context.lineWidth = this.lineThickness;
                }

                if (this.qmode == 'edit' && this.active_box_id > -1 && this.active_box_id != this.mov_id) {
                    loc_width = this.imglabelWidth;
                    loc_height = this.imglabelHeight;
                    if (this.answerBox[this.active_box_id][this.active_box_combo][1] == 'text') {
                        loc_width = this.labelWidthEffect;
                        loc_height = this.labelHeightEffect;
                    }

                    //draw handlers for active label
                    this.context.strokeStyle = '#cc0000';
                    this.context.strokeRect(
                        this.answerBox[this.active_box_id][this.active_box_combo][5] - this.lineThickness / 2 + 1.5,
                        this.answerBox[this.active_box_id][this.active_box_combo][6] - this.lineThickness / 2 + 1.5,
                        loc_width + this.lineThickness,
                        loc_height + this.lineThickness);

                    Qsharedf.edtDot(
                        this.context, '#cc0000',
                        this.answerBox[this.active_box_id][this.active_box_combo][5] - this.lineThickness / 2 + 1.5,
                        this.answerBox[this.active_box_id][this.active_box_combo][6] - this.lineThickness / 2 + 1.5,
                        2.5 + 0.1 * this.lineThickness, this);
                    Qsharedf.edtDot(
                        this.context, '#cc0000',
                        this.answerBox[this.active_box_id][this.active_box_combo][5] - this.lineThickness / 2 + 1.5,
                        this.answerBox[this.active_box_id][this.active_box_combo][6] + loc_height + this.lineThickness / 2 + 1.5,
                        2.5 + 0.1 * this.lineThickness, this);
                    Qsharedf.edtDot(
                        this.context, '#cc0000',
                        this.answerBox[this.active_box_id][this.active_box_combo][5] + loc_width + this.lineThickness / 2 + 1.5,
                        this.answerBox[this.active_box_id][this.active_box_combo][6] - this.lineThickness / 2 + 1.5,
                        2.5 + 0.1 * this.lineThickness, this);
                    Qsharedf.edtDot(
                        this.context, '#cc0000',
                        this.answerBox[this.active_box_id][this.active_box_combo][5] + loc_width + this.lineThickness / 2 + 1.5,
                        this.answerBox[this.active_box_id][this.active_box_combo][6] + loc_height + this.lineThickness / 2 + 1.5,
                        2.5 + 0.1 * this.lineThickness, this);
                    this.context.strokeStyle = this.currentColours[1];
                }

                //cursor blink
                if (this.qmode == 'edit' && this.mov_id == -1 && this.edit_box_id > -1 && typeof (this.answerBox[this.active_box_id]) != 'undefined' && this.answerBox[this.active_box_id][this.active_box_combo][1] != 'image') {
                    this.edit_box_blink++;
                    if (this.edit_box_blink > 60)
                        this.edit_box_blink = 0;
                    if (this.edit_box_blink > 30) {
                        var text_all = Qsharedf.wrapText(tmp_edit_text, this.labelWidthEffect, true, this)[0];

                        var text_temp = '';
                        if (this.edit_box_pos > 0)
                            text_temp = text_all.substr(0, this.edit_box_pos);
                        var wrap_temp = text_temp.split('|');
                        var text_part_line = wrap_temp.length - 1;

                        var text_part = wrap_temp[text_part_line]
                        var text_full = text_all.split('|')[text_part_line];

                        var metrics_part = this.context.measureText(text_part);
                        var metrics_full = this.context.measureText(text_full);

                        this.context.strokeStyle = '#000000';
                        this.context.lineWidth = 1;
                        this.context.beginPath();
                        var temp_x = Math.round(this.answerBox[this.active_box_id][this.active_box_combo][5] + metrics_part.width) + 0.5;
                        temp_x += ((this.qType == "menu") ? 3 : (this.labelWidthEffect - metrics_full.width) / 2);
                        var temp_y = Math.round(this.fontSizes[this.fontSizePos] * text_part_line + this.answerBox[this.active_box_id][this.active_box_combo][6] + 4) - 0.5;
                        this.context.moveTo(temp_x, temp_y);
                        this.context.lineTo(temp_x, temp_y + this.fontSizes[this.fontSizePos]);
                        this.context.stroke();
                        this.context.strokeStyle = this.currentColours[1];
                    }
                }

                //buttons
                if (this.qmode == 'edit') {
                    if (this.buttonBox.length == 0)
                        this.ql_menuBuild();
                    if (this.qType == "menu") {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_multiple.png']][0] = 'toolbar/ico_multiple_off.png';
                    } else {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_multiple.png']][0] = 'toolbar/ico_multiple.png';
                    }
                    if (this.labelMulti == "multiple") {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_menu.png']][0] = 'toolbar/ico_menu_off.png';
                    } else {
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_menu.png']][0] = 'toolbar/ico_menu.png';
                    }
                    Qsharedf.menuRebuild(this.context, true, this);

                    this.context.fillStyle = this.currentColours[0];
                    this.context.fillRect(this.buttonBox[this.buttonBoxNames['toolbar/ico_bucket.png']][1] + 2, this.buttonBox[this.buttonBoxNames['toolbar/ico_bucket.png']][2] + 14, 16, 3);
                    this.context.fillStyle = this.currentColours[1];
                    this.context.fillRect(this.buttonBox[this.buttonBoxNames['toolbar/ico_brush.png']][1] + 2, this.buttonBox[this.buttonBoxNames['toolbar/ico_brush.png']][2] + 14, 16, 3);
                    this.context.fillStyle = this.currentColours[2];
                    this.context.fillRect(this.buttonBox[this.buttonBoxNames['toolbar/ico_letter.png']][1] + 2, this.buttonBox[this.buttonBoxNames['toolbar/ico_letter.png']][2] + 14, 16, 3);

                    this.panelOverColour = '';
                    var m = 0;
                    //draw colourtable
                    for (var n = 0; n < this.colorReference.length; n++)
                        if (this.currentColours[0] == this.colorReference[n])
                            m = n;
                    Qsharedf.menuRebuild_panel(this.panelActiveParts, this.ql_panelBox, 'toolbar/ico_bucket.png', 'toolbar/pan_colours.png', 0, m, this);
                    //draw linetable
                    for (n = 0; n < this.colorReference.length; n++)
                        if (this.currentColours[1] == this.colorReference[n])
                            m = n;
                    Qsharedf.menuRebuild_panel(this.panelActiveParts, this.ql_panelBox, 'toolbar/ico_brush.png', 'toolbar/pan_colours.png', 0, m, this);
                    //draw fontcolourtable
                    for (n = 0; n < this.colorReference.length; n++)
                        if (this.currentColours[2] == this.colorReference[n])
                            m = n;
                    Qsharedf.menuRebuild_panel(this.panelActiveParts, this.ql_panelBox, 'toolbar/ico_letter.png', 'toolbar/pan_colours.png', 0, m, this);
                    //draw sizetable
                    Qsharedf.menuRebuild_panel(this.panelActiveParts, this.ql_panelBox, 'toolbar/ico_size.png', 'toolbar/pan_sizes.png', 1, this.fontSizePos, this);

                    //display char size number on menu button
                    var tp = this.panelActiveParts['toolbar/pan_sizes.png'][this.fontSizePos].split(',');
                    var imgdata = Images.map['toolbar/pan_sizes.png'];
                    var temp_but = this.buttonBox[this.buttonBoxNames['toolbar/ico_size.png']];
                    this.context.drawImage(this.menu_img, imgdata.left + 1 * tp[0], imgdata.top + 1 * tp[1], 18, 18, (temp_but[1] * 1 - 1), temp_but[2], 18, 18);

                    //draw linetable
                    Qsharedf.menuRebuild_panel(this.panelActiveParts, this.ql_panelBox, 'toolbar/ico_lines.png', 'toolbar/pan_lines.png', 2, this.lineThickness - 1, this);
                }
                //tooltip
                this.draw_limit = new Array(0, 27, this.canvas.width - 2, this.canvas.height - 2);
                if (this.buttonOver != -1 && this.buttonClicked != 1 && this.buttonClicked != 3 && this.buttonClicked != 5 && this.buttonClicked != 7 && this.buttonClicked != 9)
                    Qsharedf.tooltip_draw(this.context, this.buttonBox[this.buttonOver], this);

                // border
                this.context.lineWidth = 1;
                this.context.strokeStyle = '#909090';  //'#7f9db9';
                this.context.strokeRect(0.5, 0.5, this.canvas.width - 1, this.canvas.height - 1); //border
            }
        }

        this.ql_mouseDragMove = function(e) {
            this.ev = e || window.event;
            if (this.ev.target.id != this.canvas.id)
                return true;
            Qsharedf.get_char_key(this);

            if (this.ev.type == 'keydown') {
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
            }
            if (this.ev.type == 'touchmove') {
                this.ev.preventDefault();
                this.canv_rect = this.canvas.getBoundingClientRect();
                this.loc_lft = this.canv_rect.left;
                this.loc_top = this.canv_rect.top;
                this.x = this.ev.targetTouches[0].pageX - this.loc_lft;
                this.y = this.ev.targetTouches[0].pageY - this.loc_top;
            }
            //dragging labels handlers
            if (typeof (this.active_box_handler) != 'undefined' && this.active_box_handler != -1) {
                var dim = new Array(this.answerBox[this.active_box_id][this.active_box_combo][5], this.answerBox[this.active_box_id][this.active_box_combo][6], this.answerBox[this.active_box_id][this.active_box_combo][5] + this.labelWidthEffect, this.answerBox[this.active_box_id][this.active_box_combo][6] + this.labelHeightEffect);

                if (this.active_box_handler == 1 || this.active_box_handler == 4) {
                    this.answerBox[this.active_box_id][this.active_box_combo][5] = this.x;
                    this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][5] = this.x;
                }
                if (this.active_box_handler == 1 || this.active_box_handler == 2) {
                    this.answerBox[this.active_box_id][this.active_box_combo][6] = this.y;
                    this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][6] = this.y;
                }
                if (this.active_box_handler == 1) {
                    dim[0] = this.x;
                    dim[1] = this.y;
                }
                if (this.active_box_handler == 2) {
                    dim[2] = this.x;
                    dim[1] = this.y;
                }
                if (this.active_box_handler == 3) {
                    dim[2] = this.x;
                    dim[3] = this.y;
                }
                if (this.active_box_handler == 4) {
                    dim[0] = this.x;
                    dim[3] = this.y;
                }
                this.labelWidth = dim[2] - dim[0];
                this.labelHeight = dim[3] - dim[1];
                if (this.labelWidthEffect < this.labelWidth)
                    this.labelWidthEffect = this.labelWidth;
                if (this.labelHeightEffect < this.labelHeight)
                    this.labelHeightEffect = this.labelHeight;
            }

            if (this.dragging && this.drag_box_id > -1) { //this.dragging
                //new position of dragged shape
                if ((this.qmode == 'answer' && (this.qType == 'label' || this.answerBox[this.drag_box_id][this.drag_box_combo][1] == 'image')) || this.global_move) {
                    this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.x - this.sub_x;
                    this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.y - this.sub_y;
                }

                //limits
                this.draw_limit = new Array(1, (26 - this.yOffset), this.canvas.width - this.labelWidthEffect - 2, this.canvas.height - this.labelHeightEffect - 2);
                if (this.qmode == 'edit')
                    this.draw_limit = new Array(0, 26, this.canvas.width - this.labelWidthEffect - 2, this.canvas.height - this.labelHeightEffect - 2);


                if (this.answerBox[this.drag_box_id][this.drag_box_combo][5] < this.draw_limit[0])
                    this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.draw_limit[0];
                if (this.answerBox[this.drag_box_id][this.drag_box_combo][6] < this.draw_limit[1])
                    this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.draw_limit[1];
                if (this.answerBox[this.drag_box_id][this.drag_box_combo][5] > this.draw_limit[2])
                    this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.draw_limit[2];
                if (this.answerBox[this.drag_box_id][this.drag_box_combo][6] > this.draw_limit[3])
                    this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.draw_limit[6];

                if (this.qmode == 'edit') {
                    this.pholderBox[this.answerBox[this.drag_box_id][this.drag_box_combo][0]][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][5];
                    this.pholderBox[this.answerBox[this.drag_box_id][this.drag_box_combo][0]][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][6];

                }
            } else { //change of cursor
                var drag_box_old = this.drag_box_id + ':' + this.drag_box_combo;
                var drag_pho_old = this.drag_pho_id;
                this.drag_box_id = -1;
                this.drag_pho_id = -1;
                this.drag_box_combo = -1;
                if (this.qmode != 'analysis' && Qsharedf.testWithin(this.x, this.y, 0, 0, this.canvas.width, this.canvas.height)) {
                    var over_object = false;
                    for (i = 0; i < this.answerBox.length; i++) {
                        for (j = 0; j < this.answerBox[i].length; j++) {
                            if (typeof (this.answerBox[i][j]) != 'undefined' && (this.labelMulti == 'multiple' || this.answerBox[i][j][4] == 0)) {
                                if (this.answerBox[i][j][1] == 'image') {
                                    if (Qsharedf.testWithin(this.x, this.y, this.answerBox[i][j][5], this.answerBox[i][j][6], this.imglabelWidth, this.imglabelHeight) == true) {
                                        over_object = true;
                                        if (this.drag_box_id == -1 || this.answerBox[i][j][9] != '') {
                                            this.drag_box_id = i;
                                            this.drag_box_combo = j;
                                        }
                                    }
                                }
                                if (this.answerBox[i][j][1] == 'text') {
                                    if (this.qType != 'menu') {
                                        if (Qsharedf.testWithin(this.x, this.y, this.answerBox[i][j][5], this.answerBox[i][j][6], this.labelWidthEffect, this.labelHeightEffect) == true) {
                                            over_object = true;
                                            if (this.drag_box_id == -1 || this.answerBox[i][j][9] != '') {
                                                this.drag_box_id = i;
                                                this.drag_box_combo = j;
                                            }
                                        }
                                    } else {
                                        if (typeof (this.pholderBox[i]) != 'undefined' && ((this.qmode == 'edit' && Qsharedf.testWithin(this.x, this.y, this.answerBox[i][j][5], this.answerBox[i][j][6], this.labelWidthEffect, this.labelHeightEffect) == true) || (this.qmode != 'edit' && this.pholderBox[i][5] >= 220 && Qsharedf.testWithin(this.x, this.y, this.pholderBox[i][5], this.pholderBox[i][6], this.labelWidthEffect, this.labelHeightEffect) == true))) {
                                            this.drag_box_id = i;
                                            this.drag_pho_id = i;
                                            //this.drag_box_combo = 0;
                                            this.drag_box_combo = j;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    for (i = 0; i < this.pholderBox.length; i++) {
                        var tmp_test = false;
                        if (this.pholderBox[i][1] == 'image' && Qsharedf.testWithin(this.x, this.y, this.pholderBox[i][5], this.pholderBox[i][6], this.imglabelWidth, this.imglabelHeight) == true)
                            tmp_test = true;
                        if (this.pholderBox[i][1] == 'text' && Qsharedf.testWithin(this.x, this.y, this.pholderBox[i][5], this.pholderBox[i][6], this.labelWidthEffect, this.labelHeightEffect) == true)
                            tmp_test = true;

                        if (tmp_test && this.drag_box_id == -1) {
                            over_object = true;
                            this.drag_pho_id = i;
                        }
                    }

                    if (drag_box_old != this.drag_box_id + ':' + this.drag_box_combo && this.qmode == 'script' || drag_pho_old != this.drag_pho_id)
                        this.redraw_once = true;

                    //test for buttons
                    var buttonTest = -1;
                    for (var i = 0; i < this.buttonBox.length; i++) {
                        this.buttonBox[i][5] = this.buttonBox[i][6];
                        if (this.buttonBox[i][0] == 'toolbar/ico_drop.png')
                            this.buttonBox[i][5] = this.buttonBox[i - 1][5];

                        if (this.buttonBox[i][0].indexOf('vert_') == -1 && Qsharedf.testWithin(this.x, this.y, this.buttonBox[i][1], this.buttonBox[i][2], this.buttonBox[i][3], this.buttonBox[i][4]) == true && this.buttonBox[i][0] != 'toolbar/ico_multiple_off.png' && this.buttonBox[i][0] != 'toolbar/ico_menu_off.png') {
                            over_object = true;
                            buttonTest = i;
                            this.buttonBox[i][5] = 1;

                            //double button
                            var j = i;
                            if (this.buttonBox[i][0] == 'toolbar/ico_drop.png')
                                j = i - 1;
                            if (i < this.buttonBox.length - 1 && this.buttonBox[i + 1][0] == 'toolbar/ico_drop.png')
                                j = i + 1;
                            this.buttonBox[j][5] = 1;
                        }
                    }

                    if (this.buttonOver != buttonTest) {
                        this.buttonOver = buttonTest;
                        this.redraw_once = true;
                        this.ql_redraw_canvas();
                    }

                    //test for panels
                    var panelOptionTest = -1;
                    this.panelOver = -1;

                    if (this.buttonClicked > -1 && typeof this.ql_panelBox[this.buttonClicked] != 'undefined') {
                        var tmp_but = -1, tmp_pan = -1;
                        if (Qsharedf.testWithin(this.x, this.y, this.ql_panelBox[this.buttonClicked][3], this.ql_panelBox[this.buttonClicked][4], this.ql_panelBox[this.buttonClicked][5], this.ql_panelBox[this.buttonClicked][6]) == true) {
                            tmp_but = this.buttonBox[this.buttonClicked];
                            if (typeof this.ql_panelBox[this.buttonClicked][2] != 'undefined')
                                tmp_pan = this.ql_panelBox[this.buttonClicked][2];
                            this.panelOver = this.buttonClicked;
                            over_object = true;
                            this.drag_box_id = -1;
                            this.drag_pho_id = -1;
                            this.drag_box_combo = -1;
                            var test_width = 19;
                            if (tmp_pan == 'toolbar/pan_sizes.png')
                                test_width = 22;
                            if (tmp_pan == 'toolbar/pan_lines.png')
                                test_width = 130;
                            for (i = 0; i < this.panelActiveParts[tmp_pan].length; i++) {
                                var tp = this.panelActiveParts[tmp_pan][i].split(',');
                                if (Qsharedf.testWithin(this.x, this.y, tmp_but[1] + 1 * tp[0] + 0.5, tmp_but[2] + 25 + 1 * tp[1] + 0.5, test_width, 20) == true)
                                    panelOptionTest = i;
                            }
                        }
                    }
                    if (this.panelOptionOver != panelOptionTest) {
                        this.panelOptionOver = panelOptionTest;
                        this.redraw_once = true;
                        this.ql_redraw_canvas();
                    }

                    var cur = 'default';
                    if (over_object)
                        cur = 'pointer';
                    if (this.global_move && this.active_shape > -1 && this.y > 28)
                        cur = 'move';
                    if (this.active_box_handler != -1)
                        cur = 'move';
                    //this works only in css3 browsers otherwise whole cursor is ignored
                    if (this.global_erase && this.active_shape > -1 && this.y > 28)
                        cur = 'url(/js/images/cur_erase.cur) 6 5, default';
                    if (this.global_erase && this.drag_box_id > -1 && this.y > 28)
                        cur = 'url(/js/images/cur_erase.cur) 6 5, default';

                    if (this.buttonOver > -1 && this.buttonBox[this.buttonOver][0] == 'toolbar/ico_help.png')
                        cur = 'help';
                    e.target.style.cursor = cur;
                }
            }
            //this.freehand draw end

            if (this.qmode == 'answer') {
                if ((this.char_code == ' ' || this.key_code == 13) && this.drag_box_id == -1) { //space
                    if (this.qType == 'menu') {
                        if (this.active_box_id == this.answer_access_id) {
                            this.active_box_id = -1;
                        } else {
                            this.active_box_id = this.answer_access_id;
                        }
                        this.active_box_combo = 0;
                        this.redraw_once = true;
                        this.ql_redraw_canvas();
                    } else {
                        this.access_switch++;
                        if (this.access_switch == 2) {
                            this.access_switch = 0;

                            //simulating drag and drop
                            this.drag_box_id = this.answer_access_id;
                            this.drag_box_combo = this.answer_access_combo;
                            this.mov_id = this.answer_access_id;
                            this.mov_combo = 0;
                            this.ql_mouseDragUp();
                        }
                    }
                }
                if (this.key_code == 38 && this.qType == 'menu') { //arrow up
                    if (this.menu_line < 0)
                        this.menu_line = 0;
                    if (this.menu_line > 1)
                        this.menu_line--;
                    this.active_box_id = this.answer_access_id;
                    this.active_box_combo = 0;
                    if (typeof (this.answerBox[this.active_box_id]) != 'undefined' && typeof (this.menuBox[this.menu_line - 1]) != 'undefined')
                        this.answerBox[this.active_box_id][0][2] = this.menuBox[this.menu_line - 1];
                    this.ql_mouseDragUp();
                    this.active_box_id = this.answer_access_id;
                    this.active_box_combo = 0;
                }
                if (this.key_code == 40 && this.qType == 'menu') { //arrow down
                    if (this.menu_line < 0)
                        this.menu_line = 0;
                    if (this.menu_line < this.menuBox.length)
                        this.menu_line++;
                    this.active_box_id = this.answer_access_id;
                    this.active_box_combo = 0;
                    if (typeof (this.answerBox[this.active_box_id]) != 'undefined' && typeof (this.menuBox[this.menu_line - 1]) != 'undefined')
                        this.answerBox[this.active_box_id][0][2] = this.menuBox[this.menu_line - 1];
                    this.ql_mouseDragUp();
                    this.active_box_id = this.answer_access_id;
                    this.active_box_combo = 0;
                }

                //accessibility tab select of labels
                var key;
                if (this.key_code == 9) { //tab
                    if (this.access_switch == -1)
                        this.access_switch++;

                    if (this.access_switch == 0) {
                        //creating sorted list of labels and selecting next available
                        var tmp_pos_array = [];
                        var tmp_sort_array = [];
                        for (i = this.answerBox.length - 1; i >= 0; i--)
                            for (j = this.answerBox[i].length - 1; j >= 0; j--)
                                if (this.answerBox[i][j][5] > 0)
                                    tmp_pos_array[(1000 + this.answerBox[i][j][6]) + '.' + (1000 + this.answerBox[i][j][5])] = i + ',' + j;

                        //sort tmp array of labels positions
                        for (key in tmp_pos_array)
                            tmp_sort_array.push(key);
                        tmp_sort_array.sort();

                        //locate position of the selected
                        var tmp_pos = '';
                        for (key in tmp_pos_array)
                            if (tmp_pos_array[key] == this.answer_access_id + ',' + this.answer_access_combo)
                                tmp_pos = key;

                        //locate index and selecting next
                        var tmp_i = -1;
                        for (i = 0; i < tmp_sort_array.length; i++)
                            if (tmp_sort_array[i] == tmp_pos)
                                tmp_i = i;

                        if (!this.isShift) {
                            tmp_i++;
                            if (tmp_i == tmp_sort_array.length)
                                tmp_i = 0;
                        }
                        if (this.isShift) {
                            tmp_i--;
                            if (tmp_i < 0)
                                tmp_i = tmp_sort_array.length - 1;
                        }

                        tmp_pos = tmp_sort_array[tmp_i];

                        this.answer_access_id = tmp_pos_array[tmp_pos].split(',')[0];
                        this.answer_access_combo = tmp_pos_array[tmp_pos].split(',')[1];

                        if ((this.answerBox.length - 1) <= this.answer_access_id)
                            this.answer_access_id = 0;
                    }
                    if (this.access_switch == 1) {
                        //creating sorted list of labels and selecting next available
                        tmp_pos_array = [];
                        tmp_sort_array = [];
                        for (i = this.pholderBox.length - 1; i >= 0; i--)
                            if (this.pholderBox[i][5] > 0)
                                tmp_pos_array[(1000 + this.pholderBox[i][6]) + '.' + (1000 + this.pholderBox[i][5])] = i;

                        //sort tmp array of labels positions
                        for (key in tmp_pos_array)
                            tmp_sort_array.push(key);
                        tmp_sort_array.sort();

                        //locate position of the selected
                        tmp_pos = '';
                        for (key in tmp_pos_array)
                            if (tmp_pos_array[key] == this.pholder_access_id)
                                tmp_pos = key;

                        //locate index and selecting next
                        tmp_i = -1;
                        for (i = 0; i < tmp_sort_array.length; i++)
                            if (tmp_sort_array[i] == tmp_pos)
                                tmp_i = i;
                        if (!this.isShift) {
                            tmp_i++;
                            if (tmp_i == tmp_sort_array.length)
                                tmp_i = 0;
                        }
                        if (this.isShift) {
                            tmp_i--;
                            if (tmp_i < 0)
                                tmp_i = tmp_sort_array.length - 1;
                        }
                        if (tmp_i == tmp_sort_array.length)
                            tmp_i = 0;
                        tmp_pos = tmp_sort_array[tmp_i];

                        this.pholder_access_id = tmp_pos_array[tmp_pos];

                        if ((this.pholderBox.length - 1) <= this.pholder_access_id)
                            this.pholder_access_id = 0;
                    }
                }
                this.char_code = '';
                this.key_code = 0;
                this.redraw_once = true;
                this.ql_redraw_canvas();
            }

            //cancel propagation if BackSpace
            if (this.ev.type == 'keydown' && this.ev.keyCode <= 46) {
                if (this.ev.stopPropagation)
                    this.ev.stopPropagation();
                if (this.ev.cancelBubble != null)
                    this.ev.cancelBubble = true;
                if (this.ev.preventDefault)
                    this.ev.preventDefault();
                if (this.ev.returnValue)
                    this.ev.returnValue = false;
            }
            return false;
        }

        this.ql_mouseDragDown = function(e) {
            this.ev = e || window.event;
            if (this.ev.type == 'mousedown') {
                this.canv_rect = this.canvas.getBoundingClientRect();
                this.x = this.ev.clientX - this.canv_rect.left;
                this.y = this.ev.clientY - this.canv_rect.top;
            }
            if (this.ev.type == 'touchstart') {
                this.ev.preventDefault();
                this.canv_rect = this.canvas.getBoundingClientRect();
                this.x = this.ev.targetTouches[0].pageX - this.canv_rect.left;
                this.y = this.ev.targetTouches[0].pageY - this.canv_rect.top;

                for (i = 0; i < this.answerBox.length; i++) {
                    for (var j = 0; j < this.answerBox[i].length; j++) {
                        if (typeof (this.answerBox[i][j]) != 'undefined' && (this.labelMulti == 'multiple' || this.answerBox[i][j][4] == 0)) {
                            if (this.answerBox[i][j][1] == 'image') {
                                if (Qsharedf.testWithin(this.x, this.y, this.answerBox[i][j][5], this.answerBox[i][j][6], this.imglabelWidth, this.imglabelHeight, this) == true) {
                                    if (this.drag_box_id == -1 || this.answerBox[i][j][9] != '') {
                                        this.drag_box_id = i;
                                        this.drag_box_combo = j;
                                    }
                                }
                            }
                            if (this.answerBox[i][j][1] == 'text') {
                                if (this.qType != 'menu') {
                                    if (Qsharedf.testWithin(this.x, this.y, this.answerBox[i][j][5], this.answerBox[i][j][6], this.labelWidthEffect, this.labelHeightEffect) == true) {
                                        if (this.drag_box_id == -1 || this.answerBox[i][j][9] != '') {
                                            this.drag_box_id = i;
                                            this.drag_box_combo = j;
                                        }
                                    }
                                } else {
                                    if (typeof (this.pholderBox[i]) != 'undefined' && ((this.qmode == 'edit' && Qsharedf.testWithin(this.x, this.y, this.answerBox[i][j][5], this.answerBox[i][j][6], this.labelWidthEffect, this.labelHeightEffect) == true) || (this.qmode != 'edit' && this.pholderBox[i][5] >= 220 && Qsharedf.testWithin(this.x, this.y, this.pholderBox[i][5], this.pholderBox[i][6], this.labelWidthEffect, this.labelHeightEffect) == true))) {
                                        this.drag_box_id = i;
                                        this.drag_pho_id = i;
                                        this.drag_box_combo = j;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (Qsharedf.testWithin(this.x, this.y, 0, 0, this.canvas.width, this.canvas.height)) {
                if (this.drag_box_id > -1 && this.drag_box_combo < 99) {
                    this.sub_x = this.x - this.answerBox[this.drag_box_id][this.drag_box_combo][5];
                    this.sub_y = this.y - this.answerBox[this.drag_box_id][this.drag_box_combo][6];
                }
                if (this.panelOptionOver == -1)
                    this.dragging = true;
            }

            this.answer_access_id = -1;
            this.answer_access_combo = -1;
            this.pholder_access_id = -1;
            this.access_switch = 0;

            this.active_shape_move = this.active_shape;
            this.active_shape_x = this.x;
            this.active_shape_y = this.y;

            //test for label handlers
            if (this.active_box_id > -1 && typeof (this.answerBox[this.active_box_id]) != 'undefined' && typeof (this.answerBox[this.active_box_id][this.active_box_combo]) != 'undefined' && this.answerBox[this.active_box_id][this.active_box_combo][1] == 'text') {
                var tt = 2.5 + 0.1 * this.lineThickness;
                var tx1 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][5] - this.x) < tt);
                var tx2 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][5] + this.labelWidthEffect - this.x) < tt);
                var ty1 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][6] - this.y) < tt);
                var ty2 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][6] + this.labelHeightEffect - this.y) < tt);
                if (tx1 && ty1)
                    this.active_box_handler = 1;
                if (tx2 && ty1)
                    this.active_box_handler = 2;
                if (tx2 && ty2)
                    this.active_box_handler = 3;
                if (tx1 && ty2)
                    this.active_box_handler = 4;
            }

            //removing unnecesarry duplacates in multiple/labels
            if (Number(this.drag_box_id) > -1 && Number(this.drag_box_combo) < 99 && this.qmode == 'answer' && this.labelMulti == 'multiple' && this.qType == 'label') {
                //if there are more like this in the same position - remove those duplicates
                var del_list = [];
                for (i = 0; i < this.answerBox.length; i++) {
                    var ref_pos = []; //array of positions for reference
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        var this_pos = this.answerBox[i][j][5] + ',' + this.answerBox[i][j][6];
                        if (ref_pos.indexOf(this_pos) > -1 && this.mov_id == -1 && this.mov_combo == -1)
                            del_list.push(Array(i, j));
                        ref_pos.push(this_pos);
                    }
                }
                if (del_list.length > 0) {
                    for (i = del_list.length; i > 0; i--)
                        this.answerBox[del_list[i - 1][0]].splice(del_list[i - 1][1], 1);
                }
                //renumber answer ids
                for (i = 0; i < this.answerBox.length; i++)
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        this.answerBox[i][j][0] = i;
                        this.answerBox[i][j][4] = j;
                    }
                this.drag_box_id = -1;
                this.drag_box_combo = -1;
            }
        }

        this.ql_mouseDragUp = function() {
            //help link
            if (this.buttonOver > -1 && this.buttonBox[this.buttonOver][0] == 'toolbar/ico_help.png') {
                window.open('/help/staff/index.php?id=60');
            }

            //test for buttons
            if (this.buttonBox.length > 0) {
                this.buttonClicked = -1;
                //release buttons without set
                for (i = 0; i < this.buttonBox.length; i++) {
                    if (this.buttonBox[i][7] == '')
                        this.buttonBox[i][5] = this.buttonBox[i][6] = 0;
                }

                if (this.buttonOver != -1) {
                    //testing button sets
                    var butSet = this.buttonBox[this.buttonOver][7];
                    for (i = 0; i < this.buttonBox.length; i++) {
                        if (butSet == this.buttonBox[i][7])
                            this.buttonBox[i][5] = this.buttonBox[i][6] = 0;
                    }

                    //double button?
                    var j = i = this.buttonOver;
                    if (this.buttonBox[i][0] == 'toolbar/ico_drop.png')
                        i = j - 1;
                    if (i < this.buttonBox.length - 1 && this.buttonBox[i + 1][0] == 'toolbar/ico_drop.png')
                        j = i + 1;
                    this.buttonOver = i;
                    this.buttonBox[j][5] = 2;
                    this.buttonBox[this.buttonOver][5] = this.buttonBox[this.buttonOver][6] = 2;
                    this.buttonClicked = this.buttonOver;
                }

                //drawing the line, bobble or arrow
                if (this.global_add != '') {
                    if (this.shape_x1 == -1) {
                        this.shape_x1 = this.x;
                        this.shape_y1 = this.y;
                    } else {
                        this.shape_x2 = this.x;
                        this.shape_y2 = this.y;

                        this.shapeBox.push(new Array(this.shapeBox.length, this.global_add, this.shape_x1, this.shape_y1, this.shape_x2, this.shape_y2));
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_' + this.global_add + '.png']][5] = 0;
                        this.buttonBox[this.buttonBoxNames['toolbar/ico_' + this.global_add + '.png']][6] = 0;
                        this.global_add = '';
                        this.shape_x1 = this.shape_y1 = this.shape_x2 = this.shape_y2 = -1;
                    }
                }
                //button effects
                this.global_erase = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][6] == 2)
                    this.global_erase = true;
                this.global_move = false;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][6] == 2)
                    this.global_move = true;

                var old_labelMulti = this.labelMulti;
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_single.png']][6] == 2)
                    this.labelMulti = 'single';
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_multiple.png']][6] == 2 && this.buttonBox[this.buttonBoxNames['toolbar/ico_multiple.png']][0] == 'toolbar/ico_multiple.png')
                    this.labelMulti = 'multiple';
                if (this.labelMulti != old_labelMulti) {
                    var n = 0, m = 0, o = 0;
                    var tmp_unique_positions = [];
                    var tmp_unique_labels = [];
                    var tmp_clone_answerBox = this.answerBox.slice(0);
                    this.answerBox.length = 0;
                    if (this.labelMulti == 'single') {
                        j = 0;
                        for (i = 0; i < tmp_clone_answerBox.length; i++) {
                            if (typeof (tmp_clone_answerBox[i][j]) != 'undefined') {
                                if (n < 20 && tmp_clone_answerBox[i][j][2] != '') {
                                    var tmp_position = tmp_clone_answerBox[i][j][5] + ',' + tmp_clone_answerBox[i][j][6];
                                    if (typeof (tmp_unique_positions[tmp_position]) == 'undefined') {
                                        tmp_unique_positions[tmp_position] = n;
                                        if (typeof (this.answerBox[n]) == 'undefined')
                                            this.answerBox[n] = new Array();
                                        this.answerBox[n][0] = tmp_clone_answerBox[i][j];
                                        this.answerBox[n][0][0] = n;
                                        this.answerBox[n][0][4] = 0;
                                        this.pholderBox[n][2] = this.answerBox[n][0][2];
                                        this.pholderBox[n][5] = this.answerBox[n][0][5];
                                        this.pholderBox[n][6] = this.answerBox[n][0][6];
                                        if (this.pholderBox[n][5] < 220)
                                            this.pholderBox[n][5] = -500;
                                        n++;
                                    }
                                }
                            }
                        }
                    }

                    if (this.labelMulti == 'multiple') {
                        j = 0;
                        for (i = 0; i < tmp_clone_answerBox.length; i++) {
                            if (typeof (tmp_clone_answerBox[i]) != 'undefined') {
                                for (j = 0; j < tmp_clone_answerBox[i].length; j++) {
                                    if (typeof (tmp_clone_answerBox[i][j]) != 'undefined') {
                                        var tmp_label = tmp_clone_answerBox[i][j][2];
                                        if (tmp_label != '' && typeof (tmp_unique_labels[tmp_label]) == 'undefined')
                                            tmp_unique_labels[tmp_label] = o++;
                                        if (tmp_label != '' && typeof (tmp_unique_labels[tmp_label]) != 'undefined') {
                                            n = tmp_unique_labels [tmp_label];
                                            if (typeof (this.answerBox[n]) == 'undefined')
                                                this.answerBox[n] = new Array();
                                            this.answerBox[n].push(tmp_clone_answerBox[i][j]);
                                            m = this.answerBox[n].length - 1;
                                            this.answerBox[n][m][0] = n;
                                            this.answerBox[n][m][4] = m;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    for (n = this.answerBox.length; n < 20; n++) {
                        this.answerBox[n] = new Array();
                        this.answerBox[n][0] = new Array(n, "text", "", "", 0, 5, 30, 5, 30, "", "");
                    }
                }

                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_label.png']][6] == 2)
                    this.qType = 'label';
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_menu.png']][6] == 2 && this.buttonBox[this.buttonBoxNames['toolbar/ico_menu.png']][0] == 'toolbar/ico_menu.png') {
                    this.qType = 'menu';
                    this.labelHeightEffect = this.labelHeight = 19;
                }

                //state of drawing buttons
                this.global_add = '';
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_line.png']][6] == 2)
                    this.global_add = 'line';
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_bobble.png']][6] == 2)
                    this.global_add = 'bobble';
                if (this.buttonBox[this.buttonBoxNames['toolbar/ico_arrow.png']][6] == 2)
                    this.global_add = 'arrow';
            }

            //this.panelOver
            if (this.panelOver == 1 && this.panelOverColour != '')
                this.currentColours[0] = this.panelOverColour;
            if (this.panelOver == 3 && this.panelOverColour != '')
                this.currentColours[1] = this.panelOverColour;
            if (this.panelOver == 5 && this.panelOverColour != '')
                this.currentColours[2] = this.panelOverColour;
            if (this.panelOver == 7)
                this.fontSizePos = this.panelOptionOver;
            if (this.panelOver == 9)
                this.lineThickness = this.panelOptionOver + 1;

            //dropdown labels
            if (this.menu_line > -1 && this.active_box_id > -1 && this.active_box_combo > -1) {
                this.answerBox[this.active_box_id][this.active_box_combo][2] = this.menuBox[this.menu_line - 1];
                //is it correctly dropped label
                if (this.answerBox[this.active_box_id][this.active_box_combo][2] == this.pholderBox[this.active_box_id][2]) {
                    this.answerBox[this.active_box_id][this.active_box_combo][3] = 't' + this.active_box_id;
                } else {
                    this.answerBox[this.active_box_id][this.active_box_combo][3] = 'f' + this.active_box_id;
                }
                this.active_box_id = -1;
            } else { //open new dropdown only if it is NOT while answering other
                if (this.qmode != 'script') {
                    if (this.drag_box_id == this.active_box_id && this.drag_box_combo == this.active_box_combo) {
                        this.edit_box_id = this.drag_box_id;
                        this.edit_box_combo = this.drag_box_combo;
                    }
                    if (this.edit_box_id == this.active_box_id && this.edit_box_combo == this.active_box_combo && !(this.drag_box_id == this.active_box_id && this.drag_box_combo == this.active_box_combo)) {
                        this.edit_box_id = -1;
                        this.edit_box_combo = -1;
                    }
                    if (this.active_box_id != this.drag_box_id) {
                        this.active_box_id = this.drag_box_id;
                    } else {
                        this.active_box_id = -1;
                    }
                    this.active_box_combo = this.drag_box_combo;
                }
            }
            this.dragging = false;
            this.active_box_handler = -1;

            //text cursor positioning on mouseclick
            if (this.qmode == 'edit' && this.mov_id == -1 && this.active_box_id > -1 && this.answerBox[this.active_box_id][this.active_box_combo][1] != 'image') {
                var temp_x = this.answerBox[this.active_box_id][this.active_box_combo][5];
                var temp_y = this.answerBox[this.active_box_id][this.active_box_combo][6];
                if (Qsharedf.testWithin(this.x, this.y, temp_x, temp_y, this.labelWidthEffect, this.labelHeightEffect)) {
                    var text_all = Qsharedf.wrapText(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][2], this.labelWidthEffect, true, this);
                    var text_lines = text_all[0].split('|').length;
                    var click_line = Math.floor(text_lines * ((this.y - temp_y) / this.labelHeightEffect));
                    this.context.font = this.fontSizes[this.fontSizePos] + "px Arial";
                    var text_full = text_all[0].split('|')[click_line];
                    var text_padd = ((this.qType == "menu") ? 3 : (this.labelWidthEffect - this.context.measureText(text_full).width) / 2);
                    var text_line_pos = 0;
                    for (var a = 1; a <= text_full.length; a++) {
                        var temp_width = this.context.measureText(text_full.substr(0, a)).width;
                        var temp_lett = temp_width / a / 2;
                        if ((temp_x + text_padd + temp_width - temp_lett) <= this.x)
                            text_line_pos = a;
                    }
                    var text_arr = text_all[0].split('|');
                    text_arr = text_all[0].split('|');
                    text_arr.splice(click_line, text_lines - click_line);
                    this.edit_box_pos = text_arr.join('|').length + text_line_pos;
                    if (click_line > 0)
                        this.edit_box_pos++;
                }
            }

            //erase shape by selecting erase button
            if (this.global_erase && this.active_shape > -1) {
                this.shapeBox.splice(this.active_shape, 1);
            }
            this.active_shape_move = this.active_shape = -1;

            //'erase' label by selecting erase button
            if (this.global_erase && this.drag_box_id > -1 && this.qmode == 'edit') {
                this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7] = 0;
                this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8] = 0;
                this.mov_id = this.drag_box_id;
                this.mov_combo = this.drag_box_combo;
            }

            //'erase' label by dragging it to label panel on the left
            if (this.drag_box_id > -1 && this.drag_box_combo < 99 && this.qmode == 'edit' && this.answerBox[this.drag_box_id][this.drag_box_combo][5] < 220) {
                this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7] = 0;
                this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8] = 0;
                this.mov_x = this.x - this.sub_x;
                this.mov_y = this.y - this.sub_y;
                this.mov_id = this.drag_box_id;
                this.mov_combo = this.drag_box_combo;
            }

            //testing dragged labels (sigle or multiple) over pholder to get dest_box
            var dest_box = -1; //when it's >-1 than it's over some destination holder
            if (this.drag_box_id > -1 && this.drag_box_combo < 99 && this.qmode == 'answer') {
                //testing against the position of placeholders
                for (i = 0; i < this.pholderBox.length; i++) {
                    if (typeof (this.pholderBox[i]) != 'undefined') {
                        var loc_width = this.imglabelWidth, loc_height = this.imglabelHeight;
                        if (this.pholderBox[i][1] == 'text') {
                            loc_width = this.labelWidthEffect;
                            loc_height = this.labelHeightEffect;
                        }
                        //answer box center within pholderBox
                        if (Qsharedf.testWithin(this.answerBox[this.drag_box_id][this.drag_box_combo][5] + loc_width / 2, this.answerBox[this.drag_box_id][this.drag_box_combo][6] + loc_height / 2, this.pholderBox[i][5], this.pholderBox[i][6], loc_width, loc_height) == true)
                            dest_box = i;
                    }
                }
                if (this.qType == "menu")
                    dest_box = this.drag_box_id;
            }

            if (this.pholder_access_id > -1)
                dest_box = this.pholder_access_id;

            //verify if the label being dragged is not the same as already there
            var duplicate = false;
            if (this.drag_box_id > -1 && this.drag_box_combo < 99 && this.qmode == 'answer' && this.labelMulti == 'multiple' && this.qType == 'label' && dest_box > -1) {
                var next_combo_nr = this.answerBox[this.drag_box_id].length; //nr of last combo for this drag_box_id

                for (i = 0; i < this.answerBox.length; i++) {
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        if (this.answerBox[i][j][5] == this.pholderBox[dest_box][5] && this.answerBox[i][j][6] == this.pholderBox[dest_box][6] && this.answerBox[i][j][2] == this.answerBox[this.drag_box_id][this.drag_box_combo][2])
                            duplicate = true;
                    }
                }
                if (duplicate) {
                    dest_box = -1;
                    next_combo_nr = -1;
                }
                //if new - create new instance of the dragged label with new next_combo_nr
                if (this.pholder_access_id > -1 || (this.x >= 220 && this.answerBox[this.drag_box_id][this.drag_box_combo][7] < 220 && dest_box > -1)) {
                    var that_box = this.answerBox[this.drag_box_id][this.drag_box_combo].slice(0);
                    that_box[4] = next_combo_nr;
                    //reset copy
                    that_box[5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7];
                    that_box[6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8];
                    this.answerBox[this.drag_box_id][next_combo_nr] = that_box;
                }
            }

            //if new  - create new instance of the dragged label with new next_combo_nr
            if (this.qmode == 'edit' && this.labelMulti == 'multiple' && this.qType == 'label') {
                var label_num_test = [];
                for (i = 0; i < this.answerBox.length; i++)
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        if (this.answerBox[i][j][2] != '') {
                            if (typeof (label_num_test[this.answerBox[i][j][5] + '_' + this.answerBox[i][j][6] + '_' + this.answerBox[i][j][2]]) == 'undefined') {
                                label_num_test[this.answerBox[i][j][5] + '_' + this.answerBox[i][j][6] + '_' + this.answerBox[i][j][2]] = 1;
                            } else {
                                this.answerBox[i][j][1] = 'erase';
                            }
                        }
                    }
                for (i = this.answerBox.length - 1; i >= 0; i--)
                    for (j = this.answerBox[i].length - 1; j >= 0; j--)
                        if (this.answerBox[i][j][1] == 'erase')
                            this.answerBox[i].splice(j, 1);

                for (i = 0; i < this.answerBox.length; i++) {
                    var tmp_add = true;
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        var tmp_arr = this.answerBox[i][j].slice(0);
                        if (tmp_arr[5] < 220)
                            tmp_add = false;
                    }
                    if (tmp_add) {
                        tmp_arr[4] = tmp_arr[5] = tmp_arr[6] = tmp_arr[7] = 0
                        var tmp_len = 0;
                        for (var k = 0; k < this.answerBox.length; k++)
                            for (var l = 0; l < this.answerBox[k].length; l++)
                                if (this.answerBox[k][l][2] != '')
                                    tmp_len++
                        if (tmp_len < 40)
                            this.answerBox[i].push(tmp_arr);
                    }
                }
            }

            //renumbering ids
            for (i = 0; i < this.answerBox.length; i++)
                for (j = 0; j < this.answerBox[i].length; j++) {
                    this.answerBox[i][j][0] = i;
                    this.answerBox[i][j][4] = j;
                }

            if (this.drag_box_id > -1 && this.drag_box_combo < 99 && this.qmode == 'answer') {
                this.mov_id = this.drag_box_id;
                this.mov_combo = this.drag_box_combo;
                this.mov_x = this.x - this.sub_x;
                this.mov_y = this.y - this.sub_y;

                if (this.pholder_access_id > -1) { //fix for accessibility
                    this.mov_x = this.answerBox[this.answer_access_id][this.answer_access_combo][5];
                    this.mov_y = this.answerBox[this.answer_access_id][this.answer_access_combo][6];
                    this.answer_access_id = -1;
                    this.answer_access_combo = -1;
                    this.pholder_access_id = -1;
                }

                if (dest_box > -1 && this.answerBox[this.drag_box_id][this.drag_box_combo][1] == this.pholderBox[dest_box][1]) {
                    //removing any shape previously put into that position
                    for (i = 0; i < this.answerBox.length; i++) {
                        for (j = 0; j < this.answerBox[i].length; j++) {
                            if (typeof (this.answerBox[i][j]) != 'undefined' && typeof (this.pholderBox[dest_box]) != 'undefined' && this.answerBox[i][j][5] == this.pholderBox[dest_box][5] && this.answerBox[i][j][6] == this.pholderBox[dest_box][6] && i != this.drag_box_id) {
                                this.mov_id = i;
                                this.mov_combo = j;
                                this.mov_x = this.answerBox[i][j][5];
                                this.mov_y = this.answerBox[i][j][6];
                                this.answerBox[i][j][5] = this.answerBox[i][j][7];
                                this.answerBox[i][j][6] = this.answerBox[i][j][8];
                                this.answerBox[i][j][3] = '';
                            }
                        }
                    }
                    //is it correctly dropped label?

                    if (this.answerBox[this.drag_box_id][this.drag_box_combo][2] == this.pholderBox[dest_box][2]) {
                        this.answerBox[this.drag_box_id][this.drag_box_combo][3] = 't' + dest_box;
                    } else {
                        this.answerBox[this.drag_box_id][this.drag_box_combo][3] = 'f' + dest_box;
                    }

                    this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.pholderBox[dest_box][5];
                    this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.pholderBox[dest_box][6];
                } else {
                    //label dropped outside and target is sent back
                    this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7];
                    this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8];
                    this.answerBox[this.drag_box_id][this.drag_box_combo][3] = '';
                }
            }

            this.redraw_once = true;
            this.ql_redraw_canvas();
            this.ql_ReturnInfo();
        }

        this.fix_names = function(name) {
            name = name.split('"').join('#034');
            name = name.split("'").join('#039');
            name = name.split('?').join('#172');
            name = name.split(';').join('#059');
            name = name.split('~').join('#126');
            return name;
        }

        this.ql_ReturnInfo = function() {
            var questions_correct = 0;
            var questions_incorrect = 0;
            var questions_total = 0;
            var questions_result = '';
            var answer_result = '';
            var temp_answ = new Array();
            var j;

            if (this.qmode == 'answer') {
                for (i = 0; i < this.pholderBox.length; i++) {
                    if (typeof (this.pholderBox[i]) != 'undefined' && this.pholderBox[i][2] != "" && this.pholderBox[i][5] >= 220) {
                        temp_answ[this.pholderBox[i][2]] = this.pholderBox[i][5] + ',' + this.pholderBox[i][6];
                        questions_total++;
                    }
                }
                var tmp_info = new Array();
                for (i = 0; i < this.answerBox.length; i++) {
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        if (typeof (this.answerBox[i][j]) != 'undefined' && this.answerBox[i][j][2] != '') {
                            if (this.answerBox[i][j][3].substr(0, 1) == 't' || this.answerBox[i][j][3].substr(0, 1) == 'f') {
                                var tmp_pho = this.answerBox[i][j][3].substr(1);
                                var tmp_ans = '$' + this.fix_names(this.answerBox[i][j][2]) + '$' + this.answerBox[i][j][3].substr(0, 1) + '$';
                                if (tmp_pho == '') {
                                    if (this.answerBox[i][j][5] >= 220)
                                        tmp_info[this.answerBox[i][j][5] + '$' + (this.answerBox[i][j][6] - 25 + this.yOffset)] = tmp_ans;
                                } else {
                                    if (this.pholderBox[tmp_pho][5] >= 220)
                                        tmp_info[this.pholderBox[tmp_pho][5] + '$' + (this.pholderBox[tmp_pho][6] - 25 + this.yOffset)] = tmp_ans;
                                }
                            }
                        }
                    }
                }
                for (var key in tmp_info) {
                    answer_result += key + tmp_info[key];
                    var tmp_tst = tmp_info[key].split('$');
                    if (tmp_tst[2] == 't')
                        questions_correct++;
                    if (tmp_tst[2] == 'f')
                        questions_incorrect++;
                }

                var marks_max = this.marks_per_correct * questions_total;
                var marks_total = this.marks_per_correct * questions_correct + this.marks_per_incorrect * questions_incorrect;
                if (this.marking_method != 'Mark per Option') {
                    marks_total = this.marks_per_incorrect;
                    marks_max = this.marks_per_correct;
                    if (questions_correct == questions_total)
                        marks_total = this.marks_per_correct;
                }
                questions_result = marks_total + '$' + marks_max + ';' + answer_result;
                var target_field = document.getElementById('q' + this.q_Num);
            }

            if (this.qmode == 'edit') {
                questions_result += parseInt(Qsharedf.hexifycolour(this.currentColours[1]).substr(1), 16) + ';';
                questions_result += this.lineThickness + ';';
                questions_result += parseInt(Qsharedf.hexifycolour(this.currentColours[0]).substr(1), 16) + ';';
                questions_result += this.fontChoices[this.fontSizePos] + ';';
                questions_result += parseInt(Qsharedf.hexifycolour(this.currentColours[2]).substr(1), 16) + ';';
                questions_result += this.labelWidth + ';';
                questions_result += this.labelHeight + ';';
                questions_result += this.imglabelWidth + ';';
                questions_result += this.imglabelHeight + ';';
                questions_result += this.labelMulti + ';';
                questions_result += this.qType + ';';

                var label_num_test = [];
                for (i = 0; i < this.answerBox.length; i++)
                    for (j = 0; j < this.answerBox[i].length; j++) {
                        if (this.answerBox[i][j][2] != '') {
                            if (typeof (label_num_test[this.answerBox[i][j][5] + '_' + this.answerBox[i][j][6] + '_' + this.answerBox[i][j][2]]) == 'undefined') {
                                label_num_test[this.answerBox[i][j][5] + '_' + this.answerBox[i][j][6] + '_' + this.answerBox[i][j][2]] = 1;
                            } else {
                                this.answerBox[i][j][1] = 'erase';
                            }
                        }
                    }
                for (i = this.answerBox.length - 1; i >= 0; i--)
                    for (j = this.answerBox[i].length - 1; j >= 0; j--)
                        if (this.answerBox[i][j][1] == 'erase')
                            this.answerBox[i].splice(j, 1);

                for (i = 0; i < this.answerBox.length; i++) {
                    if (this.labelMulti == 'single') {
                        if (this.answerBox[i][0][2] != '' && !(this.answerBox[i][0][1] == 'image' && this.qType == 'menu')) {
                            questions_result += i;
                            questions_result += '$' + this.answerBox[i][0][4];
                            questions_result += '$' + this.answerBox[i][0][5];
                            questions_result += '$' + this.answerBox[i][0][6];
                            questions_result += '$' + this.fix_names(this.answerBox[i][0][2]);
                            if (this.answerBox[i][0][1] == 'image') {
                                questions_result += '~' + this.answerBox[i][0][9];
                                questions_result += '~' + this.answerBox[i][0][10];
                            }
                            questions_result += '|';
                        }
                    } else {
                        for (j = 0; j < this.answerBox[i].length; j++) {
                            if (this.answerBox[i][j][2] != '' && !(this.answerBox[i][j][1] == 'image' && this.qType == 'menu')) {
                                questions_result += i;
                                questions_result += '$' + j;
                                questions_result += '$' + this.answerBox[i][j][5];
                                questions_result += '$' + this.answerBox[i][j][6];
                                questions_result += '$' + this.fix_names(this.answerBox[i][j][2]);
                                if (this.answerBox[i][j][1] == 'image') {
                                    questions_result += '~' + this.answerBox[i][j][9];
                                    questions_result += '~' + this.answerBox[i][j][10];
                                }
                                questions_result += '|';
                            }
                        }
                    }
                }
                questions_result += ';';

                for (i = 0; i < this.shapeBox.length; i++) {
                    for (j = 0; j < this.shapeBox[i].length; j++) {
                        var value = this.shapeBox[i][j];
                        if (j > 1)
                            value--; //shift back for 1px border
                        questions_result += value + '$';
                    }
                    questions_result += ';';
                }
                target_field = document.getElementById('points' + this.q_Num);
            }

            if (questions_result != '' && target_field)
                target_field.value = questions_result;
        }
    }
});