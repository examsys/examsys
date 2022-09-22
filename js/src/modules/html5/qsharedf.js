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
// html5 shared functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['html5images', 'jsxls'], function(Images, Jsxls) {
    return {
        getmode: function(mode) {
            if (typeof (mode) == 'undefined') {
                mode = 'answer';
            } else if (mode == '1') {
                mode = 'answer';
            } else if (mode == '2') {
                mode = 'edit';
            } else if (mode == '3') {
                mode = 'script';
            } else if (mode == '4') {
                mode = 'analysis';
            } else if (mode == '5') {
                mode = 'correction';
            }
            return mode;
        },

        get_char_key: function(ref) {
            if (ref.ev.type == 'keypress') {
                ref.char_code = (ref.ev.charCode == 0 ? '' : String.fromCharCode(ref.ev.charCode));
            }
            if (ref.ev.type == 'keydown') {
                ref.isShift = ref.ev.shiftKey ? true : false;
                ref.isCtrl = ref.ev.ctrlKey ? true : false;
                ref.ShiftChange = true;
                if (ref.ev.keyCode == 32) ref.char_code = ' ';
            }
            if (ref.ev.type == 'keyup') {
                ref.isShift = ref.ev.shiftKey ? true : false;
                ref.isCtrl = ref.ev.ctrlKey ? true : false;
                ref.ShiftChange = true;
                ref.key_code = ref.ev.keyCode;
            }
        },

        //converts flashcolor into htmlcolor
        hexifycolour: function(thiscolor) {
            if (typeof (thiscolor) != 'undefined') {
                if (thiscolor != '' && thiscolor.indexOf('0x') == -1 && thiscolor.indexOf('#') == -1)
                    thiscolor = '#' + Number(thiscolor).toString(16);
                if (thiscolor.indexOf('0x') > -1) thiscolor = '#' + thiscolor.substr(2, 6);
                if (thiscolor.length < 7) {
                    thiscolor = '000000' + thiscolor.substr(1, thiscolor.length - 1);
                    thiscolor = '#' + thiscolor.substr(thiscolor.length - 6, 6);
                }
                return thiscolor;
            }
        },

        //calculates the height fo the text block of given width
        textHeight: function(tt, tw, ref) {
            var ty = 0;
            if (tt != '' && tt != undefined) {
                var words = tt.split(' ');
                var line = '';
                for (var n = 0; n < words.length; n++) {
                    var testLine = line + words[n] + ' ';
                    var metrics = ref.context.measureText(testLine);
                    var testWidth = metrics.width;
                    if (testWidth > tw) {
                        line = words[n] + ' ';
                        ty += ref.fontSizes[ref.fontSizePos];
                    } else {
                        line = testLine;
                    }
                }
            }
            return (ty + ref.fontSizes[ref.fontSizePos]);
        },

        breakText: function(ctx, tt, tw) {
            var words = tt.split(' ');
            var broken = false;
            for (var n = 0; n < words.length; n++) {
                var metrics = ctx.measureText(words[n]);
                if (metrics.width > tw) {
                    broken = true;
                    for (var m = 1; m < words[n].length; m++) {
                        metrics = ctx.measureText(words[n].substr(0, m));
                        if (metrics.width < tw) var div_point = m;
                    }
                    words[n] = words[n].substr(0, div_point) + ' ' + words[n].substr(div_point);
                }
                tt = words.join(' ');
            }
            return broken;
        },

        //wrapps text with given width
        //gives back an Array: text with \n's, height, width
        wrapText: function(tt, tw, elastic, ref) {
            if (typeof (elastic) == 'undefined') elastic = true;

            var ty = 0;
            if (tt != undefined) {
                var to_brake = true;
                if (!elastic) {
                    while (to_brake) to_brake = this.breakText(ref.context, tt, tw);
                }
                var words = tt.split(' ');
                var line = '';
                var lines = '';

                //verify width (tw) against words lengths
                if (elastic) {
                    for (var n = 0; n < words.length; n++) {
                        var metrics = ref.context.measureText(words[n]);
                        if (metrics.width > tw) tw = metrics.width;
                    }
                }
                for (n = 0; n < words.length; n++) {
                    var testLine = line;
                    if (testLine != '') testLine += ' ';
                    testLine += words[n];

                    metrics = ref.context.measureText(testLine);
                    var testWidth = metrics.width;
                    if (testWidth > tw) {
                        lines += line + '|';
                        line = words[n];
                        ty += ref.fontSizes[ref.fontSizePos];
                    } else {
                        line = testLine;
                    }
                }
                lines += line;
                return Array(lines, ty + ref.fontSizes[ref.fontSizePos], tw);
            }
        },

        fillWrappedText: function(ctx, tt, tx, ty, ref) {
            var words = tt.split('|');
            for (var n = 0; n < words.length; n++) {
                ref.context.fillText(words[n], tx, ty);
                ty += ref.fontSizes[ref.fontSizePos];
            }
        },

        findPos: function(obj) {
            var loc_lft = 0;
            var loc_top = 0;
            if (obj.offsetParent) {
                do {
                    loc_lft += obj.offsetLeft;
                    loc_top += obj.offsetTop;
                } while (obj == obj.offsetParent);
                return {left: loc_lft, top: loc_top};
            }
        },

        //tests if given point is within given rectangle
        testWithin: function (ax, ay, bx, by, cx, cy) {
            var testres = false;
            if ((ax > bx) && (ax < (bx + cx)) && (ay > by) && (ay < (by + cy))) {
              testres = true;
            }
            return testres;
        },

        //draws a dot
        edtDot: function(ctx, cc, xx, yy, rr, ref) {
            ref.context.strokeStyle = cc;
            ref.context.fillStyle = cc;
            ref.context.beginPath();
            ref.context.arc(xx, yy, rr, 0, 2 * Math.PI, false);
            ref.context.stroke();
            ref.context.fill();
        },

        //draws a line colour, start, length/width
        lineDraw: function(ctx, cc, xx, yy, ww, hh, ee, ref) {
            ref.context.strokeStyle = cc;
            ref.context.beginPath();
            ref.context.moveTo(xx, yy);
            ref.context.lineTo(xx + ww, yy + hh);
            ref.context.stroke();
        },

        //draws an ellipse line/fill colour, start, length/width, is_filled
        ellipseDraw: function(ctx, cc, cb, xx, yy, ww, hh, ee, ref) {
            if (cc != '') ref.context.strokeStyle = cc;
            if (cb != '') ref.context.fillStyle = cb;
            if (ee) ref.context.fillStyle = ref.context.strokeStyle = cc;

            //recalculating against limits
            if (ww < 0) {
                xx = xx + ww;
                ww = -ww
            }

            if (hh < 0) {
                yy = yy + hh;
                hh = -hh
            }

            var wx, hy; //calulated left and bottom side
            if (xx < ref.draw_limit[0]) {
                wx = xx + ww;
                xx = ref.draw_limit[0];
                ww = wx - xx;
                if (ww < 0) ww = 0;
            }
            if (xx > ref.draw_limit[2]) xx = ref.draw_limit[2];
            if (yy < ref.draw_limit[1]) {
                hy = yy + hh;
                yy = ref.draw_limit[1];
                hh = hy - yy;
                if (hh < 0) hh = 0;
            }
            if (yy > ref.draw_limit[3]) yy = ref.draw_limit[3];

            if ((xx + ww) > ref.draw_limit[2]) ww = ref.draw_limit[2] - xx;
            if ((yy + hh) > ref.draw_limit[3]) hh = ref.draw_limit[3] - yy;

            var kappa = .5522848;
            var ox = (ww / 2) * kappa,
                oy = (hh / 2) * kappa,
                xe = xx + ww,
                ye = yy + hh,
                xm = xx + ww / 2,
                ym = yy + hh / 2;

            ref.context.beginPath();
            ref.context.moveTo(xx, ym);
            ref.context.bezierCurveTo(xx, ym - oy, xm - ox, yy, xm, yy);
            ref.context.bezierCurveTo(xm + ox, yy, xe, ym - oy, xe, ym);
            ref.context.bezierCurveTo(xe, ym + oy, xm + ox, ye, xm, ye);
            ref.context.bezierCurveTo(xm - ox, ye, xx, ym + oy, xx, ym);
            ref.context.closePath();
            ref.context.stroke();
            if (cb != '') ref.context.fill();

            if (ee) {
                ref.context.globalAlpha = 1;
                this.edtDot(ctx, cb, xx, yy, 3, ref);
                this.edtDot(ctx, cb, xx + ww, yy, 3, ref);
                this.edtDot(ctx, cb, xx, yy + hh, 3, ref);
                this.edtDot(ctx, cb, xx + ww, yy + hh, 3, ref);
            }
        },

        //draws rectangle line/fill colour, start, length/width, is_filled
        rectDraw: function(ctx, cc, cb, xx, yy, ww, hh, ee, ref) {

            if (cc != '') ref.context.strokeStyle = cc;
            if (cb != '') ref.context.fillStyle = cb;
            if (ee) ref.context.fillStyle = ref.context.strokeStyle = cc;

            //recalculating against limits
            if (ww < 0) {
                xx = xx + ww;
                ww = -ww
            }

            if (hh < 0) {
                yy = yy + hh;
                hh = -hh
            }


            var wx, hy; //calulated left and bottom side
            if (xx < ref.draw_limit[0]) {
                wx = xx + ww;
                xx = ref.draw_limit[0];
                ww = wx - xx;
                if (ww < 0) ww = 0;
            }
            if (xx > ref.draw_limit[2]) xx = ref.draw_limit[2];
            if (yy < ref.draw_limit[1]) {
                hy = yy + hh;
                yy = ref.draw_limit[1];
                hh = hy - yy;
                if (hh < 0) hh = 0;
            }
            if (yy > ref.draw_limit[3]) yy = ref.draw_limit[3];

            if ((xx + ww) > ref.draw_limit[2]) ww = ref.draw_limit[2] - xx;
            if ((yy + hh) > ref.draw_limit[3]) hh = ref.draw_limit[3] - yy;

            ref.context.strokeRect(xx, yy, ww, hh);
            if (cb != '') ref.context.fillRect(xx, yy, ww, hh);
            if (ee) {
                ref.context.globalAlpha = 1;
                this.edtDot(ctx, cb, xx, yy, 3, ref);
                this.edtDot(ctx, cb, xx + ww, yy, 3, ref);
                this.edtDot(ctx, cb, xx, yy + hh, 3, ref);
                this.edtDot(ctx, cb, xx + ww, yy + hh, 3, ref);
            }
        },

        //draws polygon in different modes
        polyDrawH: function(ctx, cc, cb, xx, yy, pp, mode, ref) {
            /*
            ref.context - ref.canvas ref.context
            cc - stroke colour
            cb - fill colour
            xx - relative x
            yy - relative y
            pp - array of points in hex

            mode:
            t - test the area only
            a - test with activ elements
            h - show with black/white handlers
            e - show coloured without handlers
            f - show coloured with handlers
            d - show with green dot at the start and without handlers
            */
            if (cc != '') ref.context.strokeStyle = cc;
            if (cb != '') ref.context.fillStyle = cb;
            if (mode == 'e' || mode == 'r' || mode == 'f' || mode == 't') ref.context.fillStyle = ref.context.strokeStyle = cc;

            var tpe = new Array(); //array of line equations for polygons
            var tpi = new Array(); //array of line interconnections
            var qq = new Array(); //corrected
            var templw = ref.context.lineWidth;
            var d1 = 3.5;
            var d2 = 7;
            var int_count = 0;
            ref.context.lineJoin = "round";
            ref.context.lineCap = "round";
            //yy=yy-0.5;
            ref.context.beginPath();
            var tx0, ty0, tx1, ty1, tx2, ty2, tx3, ty3, ta, tb;
            tx2 = parseInt(pp[0].trim(), 16) + xx + 0.5;
            ty2 = parseInt(pp[1].trim(), 16) + yy + 0.5;
            if (ref.draw_limit.length > 0 && tx2 < ref.draw_limit[0]) tx2 = ref.draw_limit[0];
            if (ref.draw_limit.length > 0 && tx2 > ref.draw_limit[2]) tx2 = ref.draw_limit[2];
            qq.push(tx2);
            if (ref.draw_limit.length > 0 && ty2 < ref.draw_limit[1]) ty2 = ref.draw_limit[1];
            if (ref.draw_limit.length > 0 && ty2 > ref.draw_limit[3]) ty2 = ref.draw_limit[3];
            qq.push(ty2);

            tx0 = tx2;
            ty0 = ty2;
            ref.context.moveTo(tx0, ty0);

            var css = ref.context.strokeStyle;
            var cfs = ref.context.fillStyle;
            for (var n = 1; n < pp.length / 2; n++) {

                ref.context.strokeStyle = css;
                ref.context.fillStyle = cfs;
                tpe[n] = new Array();
                tx1 = tx2;
                ty1 = ty2;
                tx2 = parseInt(pp[n * 2].trim(), 16) + 0.5 + xx
                ty2 = parseInt(pp[n * 2 + 1].trim(), 16) + 0.5 + yy;
                if (Math.abs(tx2 - tx1) > 3 || Math.abs(ty2 - ty1) > 3) {
                    //test points against limits
                    if (ref.draw_limit.length > 0 && tx2 < ref.draw_limit[0]) tx2 = ref.draw_limit[0];
                    if (ref.draw_limit.length > 0 && tx2 > ref.draw_limit[2]) tx2 = ref.draw_limit[2];
                    qq.push(tx2);
                    if (ref.draw_limit.length > 0 && ty2 < ref.draw_limit[1]) ty2 = ref.draw_limit[1];
                    if (ref.draw_limit.length > 0 && ty2 > ref.draw_limit[3]) ty2 = ref.draw_limit[3];
                    qq.push(ty2);

                    //calculate and record line coords and equation
                    ta = 0;
                    tb = tx2;
                    if (tx2 != tx1) {
                        ta = (ty2 - ty1) / (tx2 - tx1);
                        tb = ty1 - ta * tx1;
                    }
                    tpe[n][0] = tx1;
                    tpe[n][1] = ty1;
                    tpe[n][2] = tx2;
                    tpe[n][3] = ty2;

                    tpe[n][4] = ta;
                    tpe[n][5] = tb;
                    tpe[n][6] = ''; // x of intersection(s)
                    tpe[n][7] = ''; // y of intersection(s)

                    ref.context.lineTo(tx2, ty2);
                    //test lines intersections
                    if (n > 1) {
                        for (var m = 1; m < n; m++) {
                            if (tpe[m][4] != ta) {
                                tx3 = (tb - tpe[m][5]) / (tpe[m][4] - ta);
                                if (tx1 == tx2) tx3 = tx1;
                                ty3 = tpe[m][4] * tx3 + tpe[m][5];
                                tpe[m][6] += ',' + tx3;
                                tpe[m][7] += ',' + ty3;

                                //distances between points
                                var dx = tx3 - tpe[m][0];
                                var dy = ty3 - tpe[m][1];
                                var distn1 = Math.sqrt(dx * dx + dy * dy);
                                dx = tx3 - tpe[m][2];
                                dy = ty3 - tpe[m][3];
                                var distn2 = Math.sqrt(dx * dx + dy * dy);
                                dx = tx3 - tpe[n][0];
                                dy = ty3 - tpe[n][1];
                                var distn3 = Math.sqrt(dx * dx + dy * dy);
                                dx = tx3 - tpe[n][2];
                                dy = ty3 - tpe[n][3];
                                var distn4 = Math.sqrt(dx * dx + dy * dy);

                                //order of point coordinats
                                var pos1 = Math.abs(tpe[m][2] - tpe[m][0]) - Math.abs(tpe[m][2] - tx3) - Math.abs(tpe[m][0] - tx3);
                                var pos2 = Math.abs(tpe[m][3] - tpe[m][1]) - Math.abs(tpe[m][3] - ty3) - Math.abs(tpe[m][1] - ty3);
                                var pos3 = Math.abs(tpe[n][2] - tpe[n][0]) - Math.abs(tpe[n][2] - tx3) - Math.abs(tpe[n][0] - tx3);
                                var pos4 = Math.abs(tpe[n][3] - tpe[n][1]) - Math.abs(tpe[n][3] - ty3) - Math.abs(tpe[n][1] - ty3);

                                if (pos1 == 0 && pos2 == 0 && pos3 == 0 && pos4 == 0 && distn1 > 1 && distn2 > 1 && distn3 > 1 && distn4 > 1) {
                                    tpi[++int_count] = new Array();
                                    tpi[int_count][0] = tx3;
                                    tpi[int_count][1] = ty3;
                                }
                            }
                        }
                    }
                }
            }

            if (mode != 'd') ref.context.lineTo(tx0, ty0);
            if (mode != 't') ref.context.stroke();
            if (cb != '') ref.context.fill();

            //green dot for area
            if (mode == 'd') {
                ref.context.lineWidth = 1;
                ref.context.globalAlpha = 0.75;
                this.ellipseDraw(ctx, '#00ff00', '#00ff00', tx0 - d1, ty0 - d1, d2, d2, false, ref);
                ref.context.globalAlpha = 1;
            }

            //duplicate first two to the end if not already there
            if (qq[0] != qq[qq.length - 2] && mode != 'd') {
                qq.push(qq[0]);
                qq.push(qq[1]);
            }

            //draw handlers
            if (mode == 'h' || mode == 'f') {
                if (mode == 'h') {
                    var lcc = '#000000';
                    var lcb = '#ffffff';
                }
                if (mode == 'f') lcc = lcb = cc;
                ref.context.globalAlpha = 1;
                ref.context.lineWidth = 1;
                for (n = 1; n < qq.length / 2; n++) {
                    var ttx = (qq[n * 2] - qq[n * 2 - 2]) / 2 + qq[n * 2 - 2];
                    var tty = (qq[n * 2 + 1] - qq[n * 2 - 1]) / 2 + qq[n * 2 - 1];
                    //edge dots
                    this.ellipseDraw(ctx, lcc, lcb, ttx - d1, tty - d1, d2, d2, false, ref);
                    //nod sqares
                    this.rectDraw(ctx, lcb, lcc, qq[n * 2] - d1, qq[n * 2 + 1] - d1, d2, d2, false, ref)
                }
            }
            ref.context.lineWidth = templw;

            //mark intersections
            if (mode != 'a' && mode != 't') {
                for (m = 1; m < tpi.length; m++) {
                    ref.context.strokeStyle = '#ff0000';
                    ref.context.beginPath();
                    ref.context.arc(tpi[m][0], tpi[m][1], 3, 0, Math.PI * 2, true);
                    ref.context.closePath();
                    ref.context.stroke();
                    ref.any_overlaping = true;
                }
            }
            ref.context.strokeStyle = css;
            ref.context.fillStyle = cfs;
        },

        //adds this menu icon to the ref.buttonBox array of menuicons' parameters
        menuBuild_icons: function(name, posx, posy, state, set, text, tooltip, ref) {
            var iposy = posy;
            var iposx = posx;
            var imgdata = Images.map[name];
            var iwidth = imgdata.width + 2;
            var iheight = imgdata.height + 1;
            if (name == 'toolbar/ico_drop.png') {
                iwidth = 12;
                iposx += -4;
            }
            if (name.indexOf('vert_') > -1) {
                iposy = -1;
                iposx = posx - 1;
            }
            ref.context.font = "13px Arial";
            var textWidth = ref.context.measureText(text).width;

            ref.buttonBox.push(name);
            ref.buttonBoxNames[name] = ref.buttonBox.length - 1;
            ref.buttonBox[ref.buttonBox.length - 1] = new Array();
            ref.buttonBox[ref.buttonBox.length - 1][0] = name;
            ref.buttonBox[ref.buttonBox.length - 1][1] = iposx;
            ref.buttonBox[ref.buttonBox.length - 1][2] = iposy;
            var bpad = 2;
            if (text == '') bpad = 0;
            ref.buttonBox[ref.buttonBox.length - 1][3] = iwidth + textWidth + bpad * 2;
            ref.buttonBox[ref.buttonBox.length - 1][4] = iheight;
            ref.buttonBox[ref.buttonBox.length - 1][5] = state; //over state
            ref.buttonBox[ref.buttonBox.length - 1][6] = state; //away state
            ref.buttonBox[ref.buttonBox.length - 1][7] = set;
            ref.buttonBox[ref.buttonBox.length - 1][8] = text;
            ref.buttonBox[ref.buttonBox.length - 1][9] = tooltip;
            return posx = iposx + iwidth + textWidth + bpad * 2;
        },

        //recreates menu based on ref.buttonBox array data
        menuRebuild: function(ctx, bar, ref) {
            var tmp_lw = ref.context.lineWidth;
            var tmp_ss = ref.context.strokeStyle;
            var tmp_fs = ref.context.fillStyle;

            ref.context.lineWidth = 1;
            ref.context.strokeStyle = '#000088';
            ref.context.fillStyle = '#FFFFFF';

            if (bar) {
                ref.context.fillRect(0, 0, ref.canvas.width, 25);
            }
            for (var n = 0; n < ref.buttonBox.length; n++) {
                var state = ref.buttonBox[n][5];
                var imgdata = Images.map[ref.buttonBox[n][0]];
                //imgdatab = map['toolbar/but_back'+state+'.png'];
                var iwidth = imgdata.width + 2;
                if (ref.buttonBox[n][0] == 'toolbar/ico_drop.png') iwidth = 12;
                //button background
                if (state != 0 && ref.buttonBox[n][7] != '-') {
                    ref.context.fillStyle = '#ffd389';
                    if (state == 1) ref.context.fillStyle = '#ffeab7';
                    ref.context.fillRect(ref.buttonBox[n][1], ref.buttonBox[n][2], ref.buttonBox[n][3] + 1, ref.buttonBox[n][4] + 1);
                }
                var bpad = 1;
                if (ref.buttonBox[n][8] == '') bpad = 0;
                ref.context.drawImage(ref.menu_img, imgdata.left, imgdata.top, imgdata.width, imgdata.height, ref.buttonBox[n][1] + 1 + bpad, ref.buttonBox[n][2] + 1, iwidth - 2, imgdata.height);
                if (ref.buttonBox[n][8] != '') {
                    ref.context.textAlign = "left";
                    ref.context.fillStyle = '#000000';
                    ref.context.font = "13px Arial";
                    ref.context.fillText(ref.buttonBox[n][8], ref.buttonBox[n][1] + 20 + bpad, ref.buttonBox[n][2] + 15);
                }
            }
            ref.context.lineWidth = tmp_lw;
            ref.context.strokeStyle = tmp_ss;
            ref.context.fillStyle = tmp_fs;
        },

        def_colour_panel_parts: function(ref) {
            //defining panel's active parts
            ref.panelActiveParts.push('toolbar/pan_colours.png');
            ref.panelActiveParts['toolbar/pan_colours.png'] = new Array();
            var lw = 12;
            var lh = 18;
            var i;
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][0 + i] = (i * lh + 1) + ',' + (7 + lw * 1);
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][10 + i] = (i * lh + 1) + ',' + (15 + lw * 2);
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][20 + i] = (i * lh + 1) + ',' + (15 + lw * 3);
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][30 + i] = (i * lh + 1) + ',' + (15 + lw * 4);
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][40 + i] = (i * lh + 1) + ',' + (15 + lw * 5);
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][50 + i] = (i * lh + 1) + ',' + (15 + lw * 6);
            for (i = 0; i < 10; i++) ref.panelActiveParts['toolbar/pan_colours.png'][60 + i] = (i * lh + 1) + ',' + (37 + lw * 7);
        },

        //recreates line 2, letter 1 or colour 0 (signed by panel_code) panel with selection highlighted
        menuRebuild_panel: function(panelActiveParts, panelBox, but_name, pan_name, panel_code, selection, ref) {
            var temp_but = ref.buttonBox[ref.buttonBoxNames[but_name]];
            var imgdata = Images.map[pan_name];
            ref.context.lineWidth = 1;
            ref.context.strokeStyle = '#000088';
            ref.context.fillStyle = '#FFFFFF';

            if (temp_but[6] == 2) {
                ref.context.fillRect(temp_but[1] + 0.5, temp_but[2] + 25.5, imgdata.width, imgdata.height);
                var tx = 12;
                var ty = 12;
                var px = 3.5;
                var py = 28.5;

                if (panel_code == 1) {
                    tx = 21;
                    ty = 20;
                    px = 0;
                    py = 25;
                }
                if (panel_code == 2) {
                    tx = 129;
                    ty = 20;
                    px = 0;
                    py = 25;
                }

                //drawing the image of the panel for colour panel
                if (panel_code == 0) {
                    ref.context.drawImage(ref.menu_img, imgdata.left, imgdata.top, imgdata.width, imgdata.height, temp_but[1], temp_but[2] + 25, imgdata.width, imgdata.height);

                    var tmp_but_num = ref.buttonBoxNames[but_name];
                    panelBox[tmp_but_num][3] = temp_but[1];
                    panelBox[tmp_but_num][4] = temp_but[2] + 25;

                    ref.context.textAlign = "left";
                    ref.context.fillStyle = '#00156E';
                    ref.context.font = "11px Arial";
                    ref.context.fillText(Jsxls.lang_string['themecolours'], temp_but[1] + 5, temp_but[2] + 25 + 16);
                    ref.context.fillText(Jsxls.lang_string['standardcolours'], temp_but[1] + 5, temp_but[2] + 25 + 117);

                    //building up the ref.colorReference
                    if (ref.colorReference.length == 0 && pan_name == 'toolbar/pan_colours.png') {
                        for (var n = 0; n < panelActiveParts[pan_name].length; n++) {
                            var tpc = panelActiveParts[pan_name][n].split(',');
                            var timgd = ref.context.getImageData(temp_but[1] + 1 * tpc[0] + 9, temp_but[2] + 25 + 1 * tpc[1] + 9, 1, 1);
                            var timgp = timgd.data;
                            ref.colorReference[n] = this.hexifycolour('' + ((timgp[0] * 256 + timgp[1]) * 256 + 1 * timgp[2]));
                        }
                    }
                }
                //solid option
                if (selection > -1 && panel_code > 0) {
                    var tp = panelActiveParts[pan_name][selection].split(',');
                    ref.context.fillStyle = '#ffd389';
                    ref.context.fillRect(temp_but[1] + 1 * tp[0] + 0.5, temp_but[2] + 25 + 1 * tp[1] + 0.5, tx, ty);
                }
                //soft option
                if (ref.panelOptionOver > -1 && panel_code > 0) {
                    tpc = panelActiveParts[pan_name][ref.panelOptionOver].split(',');
                    ref.context.fillStyle = '#ffeab7';
                    ref.context.fillRect(temp_but[1] + 1 * tpc[0] + 0.5, temp_but[2] + 25 + 1 * tpc[1] + 0.5, tx, ty);
                }

                //drawing the image of the panel for lines and sizes
                if (panel_code > 0) ref.context.drawImage(ref.menu_img, imgdata.left, imgdata.top, imgdata.width, imgdata.height, temp_but[1], temp_but[2] + 25, imgdata.width, imgdata.height);

                //solid option
                if (selection > -1 && panel_code == 0) {
                    tp = panelActiveParts[pan_name][selection].split(',');
                    if (panel_code == 0) ref.context.drawImage(ref.menu_img, imgdata.left + tp[0] * 1 + 4.5, imgdata.top + tp[1] * 1 + 4.5, 12, 11, temp_but[1] + tp[0] * 1 + 4.5, temp_but[2] + tp[1] * 1 + 4.5 + 25, 11, 11);
                    ref.context.strokeStyle = '#ffe294';
                    ref.context.strokeRect(temp_but[1] + 1 * tp[0] + px + 1, temp_but[2] + 1 * tp[1] + py + 1, tx - 1, ty - 1);
                    ref.context.strokeStyle = '#ee4810';
                    ref.context.strokeRect(temp_but[1] + 1 * tp[0] + px, temp_but[2] + 1 * tp[1] + py, tx + 1, ty + 1);
                }

                //soft option
                if (ref.panelOptionOver > -1 && panel_code == 0) {
                    tpc = panelActiveParts[pan_name][ref.panelOptionOver].split(',');
                    if (panel_code == 0 && typeof (tpc) != 'undefined') ref.context.drawImage(ref.menu_img, imgdata.left + tpc[0] * 1 + 5.5, imgdata.top + tpc[1] * 1 + 5.5, 10, 10, temp_but[1] + tpc[0] * 1 + 4.5, temp_but[2] + tpc[1] * 1 + 4.5 + 25, 11, 11);
                    ref.context.strokeStyle = '#ffe294';
                    ref.context.strokeRect(temp_but[1] + 1 * tpc[0] + px + 1, temp_but[2] + 1 * tpc[1] + py + 1, tx - 1, ty - 1);
                    ref.context.strokeStyle = '#f29436';
                    ref.context.strokeRect(temp_but[1] + 1 * tpc[0] + px, temp_but[2] + 1 * tpc[1] + py, tx + 1, ty + 1);

                    //testing the colour
                    timgd = ref.context.getImageData(temp_but[1] + 1 * tpc[0] + 9, temp_but[2] + 25 + 1 * tpc[1] + 9, 1, 1);
                    timgp = timgd.data;
                    ref.panelOverColour = this.hexifycolour('' + ((timgp[0] * 256 + timgp[1]) * 256 + 1 * timgp[2]));
                }
            }
        },

        //tests mouse actions against buttons from ref.buttonBox
        button_test: function(ref) {
            ref.buttonClicked = -1;
            if (ref.buttonOver != -1) {
                //double button?
                var m, n;
                m = n = ref.buttonOver;
                if (ref.buttonBox[n][0] == 'toolbar/ico_drop.png') n = m - 1;
                if (n < ref.buttonBox.length - 1 && ref.buttonBox[n + 1][0] == 'toolbar/ico_drop.png') m = n + 1;
                ref.buttonClicked = ref.buttonOver = n;

                //testing button sets
                var butSet = ref.buttonBox[ref.buttonOver][7];
                for (n = 0; n < ref.buttonBox.length; n++) {
                    if (butSet == ref.buttonBox[n][7] && butSet != '' && butSet != '+') ref.buttonBox[n][5] = ref.buttonBox[n][6] = 0;
                }

                //press button in set
                if (butSet != '' && butSet != '+') ref.buttonBox[ref.buttonOver][5] = ref.buttonBox[ref.buttonOver][6] = 2;

                //switch buttons without sets
                if (butSet == '' || butSet == '+') {
                    if (ref.buttonBox[ref.buttonClicked][6] == 2) {
                        ref.buttonBox[ref.buttonClicked][5] = ref.buttonBox[ref.buttonClicked][6] = 0;
                    } else {
                        ref.buttonBox[ref.buttonClicked][5] = 2;
                        ref.buttonBox[ref.buttonClicked][6] = 0;
                        if (ref.buttonBox[ref.buttonClicked][7] == '+') ref.buttonBox[ref.buttonClicked][6] = 2;
                    }
                }
            }
        },

        //builds messagebox with (x,y width and height) and 4x texts
        build_msgbox: function(mx, my, mw, mh, txt1, txt2, txt3, txt4, ref) {
            //setting shadow
            ref.context.shadowColor = '#555';
            ref.context.shadowBlur = 4;
            ref.context.shadowOffsetX = 2;
            ref.context.shadowOffsetY = 2;

            this.rectDraw(ref.context, '#aaaaaa', '#ffffff', mx, my, mw, mh, false, ref);
            //resetting the shadow
            ref.context.shadowColor = 'white';
            ref.context.shadowBlur = 0;
            ref.context.shadowOffsetX = 0;
            ref.context.shadowOffsetY = 0;

            //msg text
            ref.context.fillStyle = '#000000';
            ref.context.textAlign = "center";
            var txt0 = txt1.split('|');
            var posy = my + 25;
            for (var n = 0; n < txt0.length; n++) {
                ref.context.font = "12px Arial";
                var wrapped = this.wrapText(txt0[n], mw - 20, true, ref);
                this.fillWrappedText(ref.context, wrapped[0], mx + mw / 2, posy, ref);
                posy += wrapped[1] + 5;
            }

            //buttons
            var imgdata = Images.map['toolbar/button.png'];
            //y
            if (txt2 != '') {
                ref.context.drawImage(ref.menu_img, imgdata.left + 1, imgdata.top, imgdata.width - 2, imgdata.height, mx + mw / 2 - imgdata.width / 2 - 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
                ref.panel_buttons[1] = new Array('Y', mx + mw / 2 - imgdata.width / 2 - 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
                ref.context.fillText(txt2, mx + mw / 2 - 40, my + mh - 20);
            }
            //n
            if (txt3 != '') {
                ref.context.drawImage(ref.menu_img, imgdata.left + 1, imgdata.top, imgdata.width - 2, imgdata.height, mx + mw / 2 - imgdata.width / 2 + 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
                ref.panel_buttons[0] = new Array('N', mx + mw / 2 - imgdata.width / 2 + 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
                ref.context.fillText(txt3, mx + mw / 2 + 40, my + mh - 20);
            }
            //n
            if (txt4 != '') {
                var bw = 120;
                ref.context.drawImage(ref.menu_img, imgdata.left + 1, imgdata.top, 10, imgdata.height, mx + mw / 2 - bw / 2 - 10, my + mh - 12 - imgdata.height, 10, imgdata.height);
                ref.context.drawImage(ref.menu_img, imgdata.left + imgdata.width - 12, imgdata.top, 10, imgdata.height, mx + mw / 2 + bw / 2, my + mh - 12 - imgdata.height, 10, imgdata.height);
                ref.context.drawImage(ref.menu_img, imgdata.left + 10, imgdata.top, 10, imgdata.height, mx + mw / 2 - bw / 2, my + mh - 12 - imgdata.height, bw, imgdata.height);
                ref.panel_buttons[0] = new Array('C', mx + mw / 2 - bw / 2 - 10, my + mh - 12 - imgdata.height, bw + 20, imgdata.height);
                ref.context.fillText(txt4, mx + mw / 2, my + mh - 20);
            }
        },

        //builds tooltip
        tooltip_draw: function(ctx, but, ref) {
            //tooltip
            if (typeof but != 'undefined' && but[5] == 1 && but[9] != '') {
                ref.context.font = "12px Arial";
                var metrics = ref.context.measureText(but[9]);

                //setting the shadow
                ref.context.shadowColor = '#888';
                ref.context.shadowBlur = 6;
                ref.context.shadowOffsetX = 1;
                ref.context.shadowOffsetY = 1;

                this.rectDraw(ctx, '#FFF', '#FFF', but[1] + 10.5, but[2] + 30.5, metrics.width + 5, 16, false, ref);
                //resetting the shadow
                ref.context.shadowColor = '#fff';
                ref.context.shadowBlur = 0;
                ref.context.shadowOffsetX = 0;
                ref.context.shadowOffsetY = 0;

                ref.context.fillStyle = '#000';
                ref.context.textAlign = "left";
                ref.context.fillText(but[9], but[1] + 13, but[2] + 42);
            }
        },
    }
});