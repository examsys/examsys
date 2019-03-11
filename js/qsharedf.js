var rq = new Array(); //array of questions/canvases

//main startup function
function setUpQuestion(num, canvasId, lang, image, config, answer, extra, colour, type, mode) {
  var jspathprefix = '../';
  if (typeof(mode) == 'undefined') {
    mode = 'answer';
  } else if (mode == '1') {
    mode = 'answer';
  } else if (mode == '2') {
    mode = 'edit';
    jspathprefix = '../../';
  } else if (mode == '3') {
    mode = 'script';
  } else if (mode == '4') {
    mode = 'analysis';
  } else if (mode == '5') {
    mode = 'correction';
  } else if (mode == 'edit') {
    jspathprefix = '../../';
  }
	
  if (type == 'labelling') {
		rq[num] = new rql(num);
		rq[num].setUpLabelling(num, canvasId, lang, image, config, answer, extra, colour, mode);
		}
  if (type == 'hotspot') {
		rq[num] = new rqh(num);
		rq[num].setUpHotspot(num, canvasId, lang, image, config, answer, extra, colour, mode)
	}
  if (type == 'area') {
		rq[num] = new rqa(num);
		rq[num].setUpArea(num, canvasId, lang, image, config, answer, extra, colour, mode)
		}
}

function get_char_key() {
	if (this.ev.type == 'keypress') { 
		this.char_code = (this.ev.charCode == 0?'':String.fromCharCode(this.ev.charCode));
	}
	if (this.ev.type == 'keydown') {
		this.isShift = this.ev.shiftKey ? true : false;
		this.isCtrl = this.ev.ctrlKey ? true : false;
		this.ShiftChange = true;
		if (this.ev.keyCode == 32) this.char_code = ' ';
	}
	if (this.ev.type == 'keyup') { 
		this.isShift = this.ev.shiftKey ? true : false;
		this.isCtrl = this.ev.ctrlKey ? true : false;
		this.ShiftChange = true;
		this.key_code = this.ev.keyCode;
	}		
}

//converts flashcolor into htmlcolor
function hexifycolour(thiscolor) {
  if (typeof(thiscolor)!='undefined') {
		if (thiscolor!='' && thiscolor.indexOf('0x') == -1 && thiscolor.indexOf('#') == -1)
			thiscolor = '#'+Number(thiscolor).toString(16);
		if (thiscolor.indexOf('0x')>-1) thiscolor = '#'+thiscolor.substr(2,6);
		if (thiscolor.length<7) {
			thiscolor = '000000'+thiscolor.substr(1,thiscolor.length-1);
			thiscolor = '#'+thiscolor.substr(thiscolor.length-6,6);
		}
		return thiscolor;
	}
}

//calculates the height fo the text block of given width
function textHeight(tt, tw) {
  var ty = 0;
  if (tt!='' && tt!=undefined) {
    var words = tt.split(' ');
    var line = '';
    for (var n = 0; n < words.length; n++) {
      var testLine = line + words[n] + ' ';
      var metrics = this.context.measureText(testLine);
      var testWidth = metrics.width;
      if (testWidth > tw) {
        line = words[n] + ' ';
        ty += this.fontSizes[this.fontSizePos];
      } else {
        line = testLine;
      }
    }
  }
  return (ty + this.fontSizes[this.fontSizePos]);
}   

//wrapps text with given width
//gives back an Array: text with \n's, height, width
function wrapText(tt,tw,elastic) {
	if (typeof(elastic) == 'undefined') elastic = true;
	function breakText(ctx) {
		var words = tt.split(' ');
		var broken = false;
		for (var n = 0; n < words.length; n++) {
			var metrics = ctx.measureText(words[n]);
			if (metrics.width > tw) {
				broken = true;
				for (var m = 1; m < words[n].length; m++) {
					metrics = ctx.measureText(words[n].substr(0,m));
					if (metrics.width < tw) var div_point = m; 
				}
				words[n] = words[n].substr(0,div_point)+' '+words[n].substr(div_point);
			}
			tt = words.join(' ');
		}
		return broken;
	}
	
	var ty = 0;
	if (tt!=undefined) {
		var to_brake = true;
		if (!elastic){
			while (to_brake) to_brake = breakText(this.context);
		}
		var words = tt.split(' ');
		var line = '';
		var lines = '';
		
		//verify width (tw) against words lengths
		if (elastic){
			for (var n = 0; n < words.length; n++) {
				var metrics = this.context.measureText(words[n]);
				if (metrics.width > tw) tw = metrics.width;
			}
		}
		for (var n = 0; n < words.length; n++) {
			var testLine = line;
			if (testLine!='') testLine += ' ';
			testLine += words[n];
			
			var metrics = this.context.measureText(testLine);
			var testWidth = metrics.width;
			if (testWidth > tw) {
				lines += line + '|';
				line = words[n];
				ty += this.fontSizes[this.fontSizePos];
			} else {
				line = testLine;
			}
		}
		lines += line;
		return Array(lines,ty + this.fontSizes[this.fontSizePos],tw);
	}
}

function fillWrappedText(ctx,tt,tx,ty) {
	var words = tt.split('|');
	for (var n = 0; n < words.length; n++) {
		this.context.fillText(words[n], tx, ty);
		ty += this.fontSizes[this.fontSizePos];
	}
}


function findPos(obj) {
  var loc_lft = 0; 
  var loc_top = 0;  
  if (obj.offsetParent) {
	do {
		loc_lft += obj.offsetLeft;
		loc_top += obj.offsetTop;
	} while (obj = obj.offsetParent);
	return {left:loc_lft,top:loc_top};
	}
}

//tests if given point is within given rectangle
function testWithin(ax,ay,bx,by,cx,cy) {
	var testres = false;
	if ((ax > bx) && (ax < (bx + cx)) && (ay > by) && (ay < (by + cy))) testres = true;
	
	var showtest = false;
	if (showtest) {
						if (typeof(tw) == 'undefined') tw=true;
						this.context.strokeStyle='#AAA';
						if (tw) {
							tw = false;
							if (testres) {
								this.context.strokeStyle='#0F0';
							} else {
								this.context.strokeStyle='#F00';
							}
						}
						this.context.strokeRect(bx,by,cx,cy);
						twr = [bx,by,cx,cy,this.context.strokeStyle];
	}
	
	return testres;
}

//draws a dot
function edtDot(ctx,cc,xx,yy,rr) {
  this.context.strokeStyle = cc;
  this.context.fillStyle = cc;
  this.context.beginPath();
  this.context.arc(xx,yy, rr, 0 , 2 * Math.PI, false);
  this.context.stroke();
  this.context.fill();
}

//draws a line colour, start, length/width
function lineDraw(ctx,cc,xx,yy,ww,hh,ee) {
  this.context.strokeStyle = cc;
  this.context.beginPath();
  this.context.moveTo(xx,yy);
  this.context.lineTo(xx+ww,yy+hh);
  this.context.stroke();
}

//draws an ellipse line/fill colour, start, length/width, is_filled
function ellipseDraw(ctx,cc,cb,xx,yy,ww,hh,ee) {
  if (cc!='') this.context.strokeStyle = cc;
  if (cb!='') this.context.fillStyle = cb;
  if (ee) this.context.fillStyle = this.context.strokeStyle = cc;
  
  //recalculating against limits
  if (ww<0) {xx=xx+ww;ww=-ww};
  if (hh<0) {yy=yy+hh;hh=-hh};
  var wx,hy; //calulated left and bottom side
  if (xx<this.draw_limit[0]) {
    wx=xx+ww; 
    xx=this.draw_limit[0]; 
    ww=wx-xx;
    if (ww<0) ww=0;
    }
  if (xx>this.draw_limit[2])  xx=this.draw_limit[2];
  if (yy<this.draw_limit[1]) {
    hy=yy+hh; 
    yy=this.draw_limit[1]; 
    hh=hy-yy;
    if (hh<0) hh=0;
    }
  if (yy>this.draw_limit[3]) yy=this.draw_limit[3];

  if ((xx+ww)>this.draw_limit[2])  ww=this.draw_limit[2]-xx;
  if ((yy+hh)>this.draw_limit[3]) hh=this.draw_limit[3]-yy;
  
  var kappa = .5522848;
  ox = (ww / 2) * kappa,
  oy = (hh / 2) * kappa,
  xe = xx + ww,
  ye = yy + hh,
  xm = xx + ww / 2,
  ym = yy + hh / 2;

  this.context.beginPath();
  this.context.moveTo(xx, ym);
  this.context.bezierCurveTo(xx, ym - oy, xm - ox, yy, xm, yy);
  this.context.bezierCurveTo(xm + ox, yy, xe, ym - oy, xe, ym);
  this.context.bezierCurveTo(xe, ym + oy, xm + ox, ye, xm, ye);
  this.context.bezierCurveTo(xm - ox, ye, xx, ym + oy, xx, ym);
  this.context.closePath();
  this.context.stroke();
  if (cb!='') this.context.fill();
  
  if (ee) {
    this.context.globalAlpha = 1;
    this.edtDot(ctx,cb,xx,yy,3);
    this.edtDot(ctx,cb,xx+ww,yy,3);
    this.edtDot(ctx,cb,xx,yy+hh,3);
    this.edtDot(ctx,cb,xx+ww,yy+hh,3);
  }
}

//draws rectangle line/fill colour, start, length/width, is_filled
function rectDraw(ctx,cc,cb,xx,yy,ww,hh,ee) {
	
  if (cc!='') this.context.strokeStyle = cc;
  if (cb!='') this.context.fillStyle = cb;
  if (ee) this.context.fillStyle = this.context.strokeStyle = cc;
  
  //recalculating against limits
  if (ww<0) {xx=xx+ww;ww=-ww};
  if (hh<0) {yy=yy+hh;hh=-hh};

  var wx,hy; //calulated left and bottom side
  if (xx<this.draw_limit[0]) {
    wx=xx+ww; 
    xx=this.draw_limit[0]; 
    ww=wx-xx;
    if (ww<0) ww=0;
    }
  if (xx>this.draw_limit[2])  xx=this.draw_limit[2];
  if (yy<this.draw_limit[1]) {
    hy=yy+hh; 
    yy=this.draw_limit[1]; 
    hh=hy-yy;
    if (hh<0) hh=0;
    }
  if (yy>this.draw_limit[3]) yy=this.draw_limit[3];

  if ((xx+ww)>this.draw_limit[2])  ww=this.draw_limit[2]-xx;
  if ((yy+hh)>this.draw_limit[3]) hh=this.draw_limit[3]-yy;
  
  this.context.strokeRect(xx,yy,ww,hh); 
  if (cb!='') this.context.fillRect(xx,yy,ww,hh);
  if (ee) {
    this.context.globalAlpha = 1;
    this.edtDot(ctx,cb,xx,yy,3);
    this.edtDot(ctx,cb,xx+ww,yy,3);
    this.edtDot(ctx,cb,xx,yy+hh,3);
    this.edtDot(ctx,cb,xx+ww,yy+hh,3);
  }
}

//draws polygon in different modes
function polyDrawH(ctx,cc,cb,xx,yy,pp,mode) {
  /*
  this.context - this.canvas this.context
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
  if (cc!='') this.context.strokeStyle = cc;
  if (cb!='') this.context.fillStyle = cb;
  if (mode == 'e' || mode == 'r' || mode == 'f' || mode == 't') this.context.fillStyle = this.context.strokeStyle = cc;
    
  var tpe = new Array(); //array of line equations for polygons
  var tpi = new Array(); //array of line interconnections
  var qq = new Array(); //corrected
  var templw = this.context.lineWidth;
  var d1 = 3.5;
  var d2 = 7;
  var int_count = 0;
  this.context.lineJoin = "round";
	this.context.lineCap = "round";
	//yy=yy-0.5;
  this.context.beginPath();
  var tx0,ty0,tx1,ty1,tx2,ty2,tx3,ty3,ta,tb;
  tx2 = parseInt(pp[0].trim(), 16)+xx+0.5;
  ty2 = parseInt(pp[1].trim(), 16)+yy+0.5;
  if (this.draw_limit.length>0 && tx2<this.draw_limit[0]) tx2=this.draw_limit[0];
  if (this.draw_limit.length>0 && tx2>this.draw_limit[2]) tx2=this.draw_limit[2];
  qq.push(tx2);
  if (this.draw_limit.length>0 && ty2<this.draw_limit[1]) ty2=this.draw_limit[1];
  if (this.draw_limit.length>0 && ty2>this.draw_limit[3]) ty2=this.draw_limit[3];
  qq.push(ty2);
  
  tx0 = tx2;
  ty0 = ty2;
  this.context.moveTo(tx0,ty0);
  
  var css = this.context.strokeStyle;
  var cfs = this.context.fillStyle;
  for (var n=1;n<pp.length/2;n++) {
    
    this.context.strokeStyle = css;
    this.context.fillStyle = cfs;
    tpe[n] = new Array();
    tx1 = tx2;
    ty1 = ty2;
    tx2 = parseInt(pp[n*2].trim(), 16)+0.5+xx
    ty2 = parseInt(pp[n*2+1].trim(), 16)+0.5+yy;
    if (Math.abs(tx2-tx1)>3 || Math.abs(ty2-ty1)>3) 
    {
      //test points against limits
      if (this.draw_limit.length>0 && tx2<this.draw_limit[0]) tx2=this.draw_limit[0];
      if (this.draw_limit.length>0 && tx2>this.draw_limit[2]) tx2=this.draw_limit[2];
      qq.push(tx2);
      if (this.draw_limit.length>0 && ty2<this.draw_limit[1]) ty2=this.draw_limit[1];
      if (this.draw_limit.length>0 && ty2>this.draw_limit[3]) ty2=this.draw_limit[3];
      qq.push(ty2);
      
      //calculate and record line coords and equation      
      ta=0; tb=tx2;
      if (tx2!=tx1) {
        ta = (ty2-ty1)/(tx2-tx1);
        tb = ty1 - ta*tx1;
      }
      tpe[n][0] = tx1;
      tpe[n][1] = ty1;
      tpe[n][2] = tx2;
      tpe[n][3] = ty2;

      tpe[n][4] = ta;
      tpe[n][5] = tb;
      tpe[n][6] = ''; // x of intersection(s)
      tpe[n][7] = ''; // y of intersection(s)
      
      this.context.lineTo(tx2,ty2);
      //test lines intersections
      if (n>1) {
        for (var m=1;m<n;m++) {
          if (tpe[m][4] != ta) {
            tx3 = (tb - tpe[m][5])/(tpe[m][4] - ta);
            if (tx1 == tx2) tx3=tx1;            
            ty3 = tpe[m][4]*tx3 + tpe[m][5];
            tpe[m][6] += ','+tx3;
            tpe[m][7] += ','+ty3;
            
            //distances between points
            dx = tx3-tpe[m][0];dy = ty3-tpe[m][1];            
            distn1 = Math.sqrt(dx*dx+dy*dy);
            dx = tx3-tpe[m][2];dy = ty3-tpe[m][3];            
            distn2 = Math.sqrt(dx*dx+dy*dy);
            dx = tx3-tpe[n][0];dy = ty3-tpe[n][1];            
            distn3 = Math.sqrt(dx*dx+dy*dy);
            dx = tx3-tpe[n][2];dy = ty3-tpe[n][3];            
            distn4 = Math.sqrt(dx*dx+dy*dy);
            
            //order of point coordinats
            pos1 = Math.abs(tpe[m][2]-tpe[m][0])-Math.abs(tpe[m][2]-tx3)-Math.abs(tpe[m][0]-tx3);
            pos2 = Math.abs(tpe[m][3]-tpe[m][1])-Math.abs(tpe[m][3]-ty3)-Math.abs(tpe[m][1]-ty3);
            pos3 = Math.abs(tpe[n][2]-tpe[n][0])-Math.abs(tpe[n][2]-tx3)-Math.abs(tpe[n][0]-tx3);
            pos4 = Math.abs(tpe[n][3]-tpe[n][1])-Math.abs(tpe[n][3]-ty3)-Math.abs(tpe[n][1]-ty3);
                        
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
  
  if (mode!='d') this.context.lineTo(tx0,ty0);
  if (mode!='t') this.context.stroke();
  if (cb!='') this.context.fill();
  
  //green dot for area  
  if (mode == 'd') {
    this.context.lineWidth = 1;
    this.context.globalAlpha = 0.75;
    this.ellipseDraw(ctx,'#00ff00','#00ff00',tx0-d1,ty0-d1,d2,d2,false);
    this.context.globalAlpha = 1;
  }

  //duplicate first two to the end if not already there
  if (qq[0]!=qq[qq.length-2] && mode!='d') {
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
    this.context.globalAlpha = 1;
    this.context.lineWidth = 1;
    for (var n=1;n<qq.length/2;n++) {
      var ttx = (qq[n*2]-qq[n*2-2])/2+qq[n*2-2];
      var tty = (qq[n*2+1]-qq[n*2-1])/2+qq[n*2-1];
      //edge dots    
      this.ellipseDraw(ctx,lcc,lcb,ttx-d1,tty-d1,d2,d2,false);
      //nod sqares
      this.rectDraw(ctx,lcb,lcc,qq[n*2]-d1,qq[n*2+1]-d1,d2,d2,false)
    }
  }
  this.context.lineWidth = templw;

  //mark intersections
  if (mode != 'a' && mode != 't') {
		for (var m =1; m < tpi.length; m++) {
			this.context.strokeStyle = '#ff0000';    
			this.context.beginPath();
			this.context.arc(tpi[m][0],tpi[m][1],3, 0, Math.PI*2, true); 
			this.context.closePath();
			this.context.stroke();
			any_overlaping = true;
		}
	}
  this.context.strokeStyle = css;
  this.context.fillStyle = cfs;
}

//adds this menu icon to the this.buttonBox array of menuicons' parameters
function menuBuild_icons(name,posx,posy,state,set,text,tooltip) {
  var iposy = posy;
  var iposx = posx;
  imgdata = menuImages[name];
  var iwidth = imgdata.width+2;
  var iheight = imgdata.height+1;
  if (name == 'toolbar/ico_drop.png') {
    iwidth = 12;
    iposx += -4;
    }
  if (name.indexOf('vert_')>-1) {
    iposy = -1; 
    iposx = posx-1; 
  }
  this.context.font="13px Arial";
  var textWidth = this.context.measureText(text).width;
    
  this.buttonBox.push(name);
  this.buttonBoxNames[name]=this.buttonBox.length-1;
  this.buttonBox[this.buttonBox.length-1] = new Array ();
  this.buttonBox[this.buttonBox.length-1][0] = name;
  this.buttonBox[this.buttonBox.length-1][1] = iposx;
  this.buttonBox[this.buttonBox.length-1][2] = iposy;
	bpad = 2; if (text == '') bpad = 0;
  this.buttonBox[this.buttonBox.length-1][3] = iwidth+textWidth+bpad*2;
  this.buttonBox[this.buttonBox.length-1][4] = iheight;
  this.buttonBox[this.buttonBox.length-1][5] = state; //over state
  this.buttonBox[this.buttonBox.length-1][6] = state; //away state
  this.buttonBox[this.buttonBox.length-1][7] = set;
  this.buttonBox[this.buttonBox.length-1][8] = text;
  this.buttonBox[this.buttonBox.length-1][9] = tooltip;
  return posx = iposx + iwidth + textWidth+bpad*2;
}

//recreates menu based on this.buttonBox array data
function menuRebuild(ctx,bar) {
  var tmp_lw = this.context.lineWidth;
  var tmp_ss = this.context.strokeStyle;
  var tmp_fs = this.context.fillStyle;

  this.context.lineWidth = 1;
  this.context.strokeStyle='#000088';
  this.context.fillStyle = '#FFFFFF';

  //toolbar background
	if (typeof(bar) == 'undefined') bar = true;
	if (bar) {
		//imgdata = menuImages['toolbar/vert_0.png'];
		//this.context.drawImage(this.menu_img,imgdata.left+0.5,imgdata.top,imgdata.width-1,imgdata.height,0,0,this.canvas.width,imgdata.height);
		this.context.fillRect(0,0,this.canvas.width,25);
	}
  for (var n=0;n<this.buttonBox.length;n++) {
    var state = this.buttonBox[n][5];
    imgdata = menuImages[this.buttonBox[n][0]];
    //imgdatab = menuImages['toolbar/but_back'+state+'.png'];
    var iwidth = imgdata.width+2;
    if (this.buttonBox[n][0] == 'toolbar/ico_drop.png') iwidth = 12;
    //button background
    if (state!=0 && this.buttonBox[n][7]!='-') {
			this.context.fillStyle = '#ffd389';
			if (state == 1) this.context.fillStyle = '#ffeab7';
      this.context.fillRect(this.buttonBox[n][1],this.buttonBox[n][2],this.buttonBox[n][3]+1,this.buttonBox[n][4]+1);
			//this.context.drawImage(this.menu_img,imgdatab.left+0.5,imgdatab.top,imgdatab.width-1,imgdatab.height,this.buttonBox[n][1],this.buttonBox[n][2],this.buttonBox[n][3],this.buttonBox[n][4]);
      //this.context.strokeStyle = '#000000';
      //this.context.strokeRect(this.buttonBox[n][1]+0.5,this.buttonBox[n][2]+0.5,this.buttonBox[n][3],this.buttonBox[n][4]);
    }
		bpad = 1; if (this.buttonBox[n][8] == '') bpad = 0;
    this.context.drawImage(this.menu_img,imgdata.left,imgdata.top,imgdata.width,imgdata.height,this.buttonBox[n][1]+1+bpad,this.buttonBox[n][2]+1,iwidth-2,imgdata.height);
    if (this.buttonBox[n][8]!='') {
      this.context.textAlign="left";
      this.context.fillStyle='#000000';
      this.context.font="13px Arial";
      this.context.fillText(this.buttonBox[n][8],this.buttonBox[n][1]+20+bpad,this.buttonBox[n][2]+15);
    }
  }  
  this.context.lineWidth = tmp_lw;
  this.context.strokeStyle = tmp_ss;
  this.context.fillStyle = tmp_fs;
}

function def_colour_panel_parts(){
  //defining panel's active parts
	this.panelActiveParts.push('toolbar/pan_colours.png');
  this.panelActiveParts['toolbar/pan_colours.png'] = new Array();
	var lw = 12;
	var lh = 18;
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][00+i] = (i*lh+1)+','+(7+lw*1);
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][10+i] = (i*lh+1)+','+(15+lw*2);
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][20+i] = (i*lh+1)+','+(15+lw*3);
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][30+i] = (i*lh+1)+','+(15+lw*4);
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][40+i] = (i*lh+1)+','+(15+lw*5);
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][50+i] = (i*lh+1)+','+(15+lw*6);
  for (i=0;i<10;i++) this.panelActiveParts['toolbar/pan_colours.png'][60+i] = (i*lh+1)+','+(37+lw*7);
}

//recreates line 2, letter 1 or colour 0 (signed by panel_code) panel with selection highlighted
function menuRebuild_panel (panelActiveParts,panelBox,but_name,pan_name,panel_code,selection) {
  var temp_but = this.buttonBox[this.buttonBoxNames[but_name]];
  var imgdata = menuImages[pan_name];
  this.context.lineWidth = 1;
  this.context.strokeStyle='#000088';
  this.context.fillStyle = '#FFFFFF';

  if (temp_but[6] == 2) {
		this.context.fillRect(temp_but[1]+0.5,temp_but[2]+25.5,imgdata.width,imgdata.height);
		var tx=12;
		var ty=12;
		var px=3.5;
		var py=28.5;
		
    if (panel_code == 1) {
			tx=21;
			ty=20;
			px=0;
			py=25;
		}
    if (panel_code == 2) {
			tx=129;
			ty=20;
			px=0;
			py=25;
		}  
    
    //drawing the image of the panel for colour panel
    if (panel_code == 0) {
      this.context.drawImage(this.menu_img,imgdata.left,imgdata.top,imgdata.width,imgdata.height,temp_but[1],temp_but[2]+25,imgdata.width,imgdata.height);
      
			var tmp_but_num = this.buttonBoxNames[but_name];
			panelBox[tmp_but_num][3] = temp_but[1];
			panelBox[tmp_but_num][4] = temp_but[2]+25;
	  
      this.context.textAlign="left";
      this.context.fillStyle='#00156E';
      this.context.font="11px Arial";
      this.context.fillText(lang_string['themecolours'],temp_but[1]+5,temp_but[2]+25+16);
      this.context.fillText(lang_string['standardcolours'],temp_but[1]+5,temp_but[2]+25+117);
      
      //building up the this.colorReference
      if (this.colorReference.length == 0 && pan_name == 'toolbar/pan_colours.png') {
        for (n=0;n<panelActiveParts[pan_name].length;n++) {
          var tpc = panelActiveParts[pan_name][n].split(',');
          var timgd = this.context.getImageData(temp_but[1]+1*tpc[0]+9,temp_but[2]+25+1*tpc[1]+9,1,1);
          var timgp = timgd.data;
          this.colorReference[n] = hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2]));
        }
      }
    }
		//solid option
		if (selection>-1 && panel_code>0) {
      var tp = panelActiveParts[pan_name][selection].split(',');
      //var imgdatab = menuImages['toolbar/but_back2.png'];
      //this.context.drawImage(this.menu_img,imgdatab.left+0.5,imgdatab.top,imgdatab.width-1,imgdatab.height,temp_but[1]+1*tp[0]+0.5,temp_but[2]+25+1*tp[1]+0.5,tx,ty);
  		//this.context.strokeRect(temp_but[1]+1*tp[0]+0.5,temp_but[2]+25+1*tp[1]+0.5,tx,ty);
			this.context.fillStyle = '#ffd389';
  		this.context.fillRect(temp_but[1]+1*tp[0]+0.5,temp_but[2]+25+1*tp[1]+0.5,tx,ty);
		}
		//soft option
    if (this.panelOptionOver>-1 && panel_code>0) {
      var tpc = panelActiveParts[pan_name][this.panelOptionOver].split(',');
      //var imgdatac = menuImages['toolbar/but_back1.png'];
      //this.context.drawImage(this.menu_img,imgdatac.left+0.5,imgdatac.top,imgdatac.width-1,imgdatac.height,temp_but[1]+1*tpc[0]+0.5,temp_but[2]+25+1*tpc[1]+0.5,tx,ty);
  		//this.context.strokeRect(temp_but[1]+1*tpc[0]+0.5,temp_but[2]+25+1*tpc[1]+0.5,tx,ty);
			this.context.fillStyle = '#ffeab7';
  		this.context.fillRect(temp_but[1]+1*tpc[0]+0.5,temp_but[2]+25+1*tpc[1]+0.5,tx,ty);
      }

		//drawing the image of the panel for lines and sizes
		if (panel_code>0) this.context.drawImage(this.menu_img,imgdata.left,imgdata.top,imgdata.width,imgdata.height,temp_but[1],temp_but[2]+25,imgdata.width,imgdata.height);

		//solid option
		if (selection>-1 && panel_code == 0) {
			var tp = panelActiveParts[pan_name][selection].split(',');
			if (panel_code == 0) this.context.drawImage(this.menu_img,imgdata.left+ tp[0]*1+4.5,imgdata.top+tp[1]*1+4.5,12,11,temp_but[1]+ tp[0]*1+4.5,temp_but[2]+tp[1]*1+4.5+25,11,11);
			this.context.strokeStyle='#ffe294';
			this.context.strokeRect(temp_but[1]+1*tp[0]+px+1,temp_but[2]+1*tp[1]+py+1,tx-1,ty-1);
			this.context.strokeStyle='#ee4810';
			this.context.strokeRect(temp_but[1]+1*tp[0]+px,temp_but[2]+1*tp[1]+py,tx+1,ty+1);
		 }

		//soft option
		if (this.panelOptionOver>-1 && panel_code == 0) {
			var tpc = panelActiveParts[pan_name][this.panelOptionOver].split(',');
			if (panel_code == 0 && typeof(tpc)!='undefined') this.context.drawImage(this.menu_img,imgdata.left+ tpc[0]*1+5.5,imgdata.top+tpc[1]*1+5.5,10,10,temp_but[1]+ tpc[0]*1+4.5,temp_but[2]+tpc[1]*1+4.5+25,11,11);
    	this.context.strokeStyle='#ffe294';
			this.context.strokeRect(temp_but[1]+1*tpc[0]+px+1,temp_but[2]+1*tpc[1]+py+1,tx-1,ty-1);
    	this.context.strokeStyle='#f29436';
			this.context.strokeRect(temp_but[1]+1*tpc[0]+px,temp_but[2]+1*tpc[1]+py,tx+1,ty+1);
		 
			//testing the colour 
			var timgd = this.context.getImageData(temp_but[1]+1*tpc[0]+9,temp_but[2]+25+1*tpc[1]+9,1,1);
			var timgp = timgd.data;
			this.panelOverColour = hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2]));
		}
  }
}

//tests mouse actions against buttons from this.buttonBox
function button_test() {
  this.buttonClicked = -1; 
  if (this.buttonOver != -1) {
    //double button?
    var m=n=this.buttonOver;
    if (this.buttonBox[n][0] == 'toolbar/ico_drop.png') n=m-1;
    if (n<this.buttonBox.length-1 && this.buttonBox[n+1][0] == 'toolbar/ico_drop.png') m=n+1;
    this.buttonClicked = this.buttonOver = n;
    
    //testing button sets
    var butSet = this.buttonBox[this.buttonOver][7];
    for (n=0;n<this.buttonBox.length;n++) {
      if (butSet == this.buttonBox[n][7] && butSet!='' && butSet!='+') this.buttonBox[n][5] = this.buttonBox[n][6] = 0;
    }
    
    //press button in set
    if (butSet != '' && butSet != '+') this.buttonBox[this.buttonOver][5] = this.buttonBox[this.buttonOver][6] = 2;
    
    //switch buttons without sets
    if (butSet == '' || butSet == '+') {
      if (this.buttonBox[this.buttonClicked][6] == 2) {
        this.buttonBox[this.buttonClicked][5]=this.buttonBox[this.buttonClicked][6]=0;
      } else {
        this.buttonBox[this.buttonClicked][5]=2;
        this.buttonBox[this.buttonClicked][6]=0;        
        if (this.buttonBox[this.buttonClicked][7] == '+') this.buttonBox[this.buttonClicked][6]=2;
      }
    }
  }
}

//builds messagebox with (x,y width and height) and 4x texts
function build_msgbox(mx,my,mw,mh,txt1,txt2,txt3,txt4) {
    //setting shadow
    this.context.shadowColor = '#555';
    this.context.shadowBlur = 4;
    this.context.shadowOffsetX = 2;
    this.context.shadowOffsetY = 2;
    
    this.rectDraw(this.context,'#aaaaaa','#ffffff',mx,my,mw,mh,false);
    //resetting the shadow
    this.context.shadowColor = 'white';
    this.context.shadowBlur = 0;
    this.context.shadowOffsetX = 0;
    this.context.shadowOffsetY = 0;
    
    //msg text
    this.context.fillStyle='#000000';
    this.context.textAlign="center";
    txt0 = txt1.split('|');
    posy = my+25;
    for (n=0;n<txt0.length;n++) {
   		this.context.font="12px Arial";
      var wrapped = this.wrapText(txt0[n], mw-20);
			this.fillWrappedText(this.context,wrapped[0],mx+mw/2, posy);
			posy += wrapped[1]+5;
			}
    
    //buttons 
    imgdata = menuImages['toolbar/button.png'];
    //y
    if (txt2!='') {
      this.context.drawImage(this.menu_img,imgdata.left+1,imgdata.top,imgdata.width-2,imgdata.height,mx+mw/2-imgdata.width/2-40,my+mh-12-imgdata.height,imgdata.width,imgdata.height);    
      this.panel_buttons[1]=new Array('Y',mx+mw/2-imgdata.width/2-40,my+mh-12-imgdata.height,imgdata.width,imgdata.height);
      this.context.fillText(txt2,mx+mw/2-40,my+mh-20);
    }
    //n    
    if (txt3!='') {
      this.context.drawImage(this.menu_img,imgdata.left+1,imgdata.top,imgdata.width-2,imgdata.height,mx+mw/2-imgdata.width/2+40,my+mh-12-imgdata.height,imgdata.width,imgdata.height);
      this.panel_buttons[0]=new Array('N',mx+mw/2-imgdata.width/2+40,my+mh-12-imgdata.height,imgdata.width,imgdata.height);
      this.context.fillText(txt3,mx+mw/2+40,my+mh-20);
    }
    //n    
    if (txt4!='') {
      bw = 120;
      this.context.drawImage(this.menu_img,imgdata.left+1,imgdata.top,10,imgdata.height,mx+mw/2-bw/2-10,my+mh-12-imgdata.height,10,imgdata.height);
      this.context.drawImage(this.menu_img,imgdata.left+imgdata.width-12,imgdata.top,10,imgdata.height,mx+mw/2+bw/2,my+mh-12-imgdata.height,10,imgdata.height);
      this.context.drawImage(this.menu_img,imgdata.left+10,imgdata.top,10,imgdata.height,mx+mw/2-bw/2,my+mh-12-imgdata.height,bw,imgdata.height);
      panel_buttons[0]=new Array('C',mx+mw/2-bw/2-10,my+mh-12-imgdata.height,bw+20,imgdata.height);
      this.context.fillText(txt4,mx+mw/2,my+mh-20);
    }
}

//builds tooltip
function tooltip_draw(ctx,but) {
  //tooltip
  if (typeof but !='undefined' && but[5] == 1 && but[9]!='') {
    this.context.font="12px Arial";
    var metrics = this.context.measureText(but[9]);
    
    //setting the shadow
    this.context.shadowColor = '#888';
    this.context.shadowBlur = 6;
    this.context.shadowOffsetX = 1;
    this.context.shadowOffsetY = 1;

    this.rectDraw(ctx,'#FFF','#FFF',but[1]+10.5,but[2]+30.5,metrics.width+5,16);
    //resetting the shadow
    this.context.shadowColor = '#fff';
    this.context.shadowBlur = 0;
    this.context.shadowOffsetX = 0;
    this.context.shadowOffsetY = 0;
    
    this.context.fillStyle='#000';
    this.context.textAlign="left";
    this.context.fillText(but[9],but[1]+13,but[2]+42);
  }
}