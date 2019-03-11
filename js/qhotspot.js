function setUpHotspot(num, doorId, lang, image, config, answer, extra, colour, mode) {
	this.canvas = document.getElementById('canvas'+num);
  this.draw_limit = new Array(302,27-this.yOffset,this.canvas.width-2,this.canvas.height-2);
  
	if (this.canvas && this.canvas.getContext){
		this.canvas.onmouseup   = this.qh_mouseDragUp.bind(this);
		this.canvas.onmousedown = this.qh_mouseDragDown.bind(this);
		this.canvas.onmousemove = this.qh_mouseDragMove.bind(this);
		this.canvas.tabIndex 		= 1000; //force keyboard events
		if (document.addEventListener){ //FF+, IE10+, Ch+
      document.addEventListener("keydown",	qh_mouseDragMove.bind(this),false);
      document.addEventListener("keypress", qh_mouseDragMove.bind(this),false);			
      document.addEventListener("keyup",		qh_mouseDragMove.bind(this),false);
		} else { //FF-, IE10-, IE9-, Ch-
			document.onkeydown   = qh_mouseDragMove.bind(this);
			document.onkeyup     = qh_mouseDragMove.bind(this);
			document.onkeypress  = qh_mouseDragMove.bind(this);
		}
		this.intervalID = window.setInterval(this.qh_redraw_canvas.bind(this), 10);
	}
	
	if (this.canvas && !this.canvas.getContext){
		alert (lang_string['errorcanvas']);
	}

	if (this.canvas && this.canvas.getContext){
		this.context = this.canvas.getContext('2d');
		this.context.lineWidth = 1;
    this.q_Num = num;
		this.doorId = doorId;
		
		//gen_img
		this.gen_img = new Image();  
		function qh_gen_img_onload() {
			this.gen_img_loaded = true;
			this.redraw_once = true;
			this.qa_redraw_canvas;
		} 
		this.gen_img.onload = qh_gen_img_onload.bind(this);
		this.gen_img.src = ((mode == 'edit' || mode == 'correction')?'../':'')+'../getfile.php?type=media&filename='+image;
		//---------- mode 
		if (mode == 'review') mode='script';
		if (mode == 'edit' || mode == 'analysis' || mode == 'correction') this.yOffset = 0;
		if (mode == 'answer' || mode == 'script') this.yOffset = 25;
    this.qmode = mode;
		
		//---------- config,     
 		if (config == '') config = '~~';
		var existingLabelInfo = config.split('|');		

		for (i=0; i<existingLabelInfo.length; i++) {
			if (existingLabelInfo[i]!='') {
				var myLabelInfo = existingLabelInfo[i].split("~");  							// divides each bit of info about label		
        
        this.hotSpots.push(i);
        this.hotSpots[i]    = new Array ();
        this.hotSpots[i][0] = i;					                               //label index
        this.hotSpots[i][1] = myLabelInfo[0];		                         //label text
        var ind = 1;
        if (myLabelInfo[1] == "polygon" || myLabelInfo[1] == "rectangle" || myLabelInfo[1] == "ellipse") {
          ind++;
          this.hotSpots[i][2] = '#0070C0';                               // colour = blue (default)
        } else {
          this.hotSpots[i][2] = this.hexifycolour(myLabelInfo[ind]);	   //colour
        	if (myLabelInfo[ind] == '') this.hotSpots[i][2] = this.layerColours[0];
				}     
        //Hotspots ...
        this.hotSpots[i][3] = (myLabelInfo.length-ind-2)/3;              //number of HS
        for (j=0; j<this.hotSpots[i][3];j++) {
          this.hotSpots[i][(3+j*6+1)] = myLabelInfo[(ind+j*3+1)];        //type
          this.hotSpots[i][(3+j*6+2)] = myLabelInfo[(ind+j*3+2)];	       //coords
          this.hotSpots[i][(3+j*6+3)] = myLabelInfo[(ind+j*3+3)];        //id
        }  
			}
		}
    
		//---------- answer, 
 		if (this.qmode != 'correction')
                    for (i in this.hotSpots) {
			this.answers[i] = new Array(new Array(0,'false','false'));
		}
		
		if (answer != "" && answer != undefined && answer != "undefined" && answer != null && answer != "null" && answer != "u") {
      this.is_an_answer = true;
			if (this.qmode == 'answer' || this.qmode == 'script') {
				var answer_l1 = answer.split("|");
				for (i=0; i<answer_l1.length; i++) {
					this.answers[i][0] = answer_l1[i].split(",");
				}
			}
			if (this.qmode == 'analysis') {
				var answer_l1 = answer.split("|"); // all the hotspots
				for (i=0; i<answer_l1.length; i++) {
					if (typeof(this.answers[i])=='undefined') this.answers[i] = new Array ();
					var answer_l2 = answer_l1[i].split(";"); //all the users
					for (j=0; j<answer_l2.length; j++) {
						var tmp_ans = answer_l2[j].split(",");
						this.answers[i][j] = [tmp_ans[0],tmp_ans[1],tmp_ans[2],0];
					}				
				}
			}
			if (this.qmode == 'correction') {
				var answer_l1 = answer.split(";"); // all the hotspots
				for (i=0; i<answer_l1.length; i++) {
					var answer_l0 = answer_l1[i].split(",");
						var answer_id = answer_l0[0];
						answer_l0.splice(0,1);
						answer_l1[i] = answer_l0.join(',');
					var answer_l2 = answer_l1[i].split("|"); //all the users
					if (typeof(this.answers[i])=='undefined') this.answers[i] = new Array ();
					for (j=0; j<answer_l2.length; j++) {
						var tmp_ans = answer_l2[j].split(",");
						this.answers[i][j] = [-1,tmp_ans[0],tmp_ans[1],answer_id]; 
					}
				}
			}
		}
		if (answer == 'u') this.allUnaswered=true; 
		
		//---------- extra
		//TODO: this needs looking at - make parameters consistent between Image Hotspot, Labelling and Area.
    //format: $tmp_display_students_response . ',' . $tmp_display_correct_answer . ',' . $tmp_exclude
		this.exclusions = '00000000000000000000';
		if (this.qmode == 'script') {
			this.extra = extra;
			tmp_extra = extra.split(",");
			if (typeof(tmp_extra[0])!='undefined' && tmp_extra[0] == '0') this.display_students_response = false;
			if (typeof(tmp_extra[1])!='undefined' && tmp_extra[1] == '0') this.display_correct_answer = false;
			if (typeof(tmp_extra[2])!='undefined') this.exclusions = tmp_extra[2];
		}
		
		//---------- colour 
		this.currentColours[3] = colour;
    
    //menubar
 		this.menu_img = new Image();  
		function menu_img_onload(){
			this.menu_img_loaded = true;
      this.menu_ready++;
      this.redraw_once = true;
      this.qa_redraw_canvas;
		}
		this.menu_img.onload = menu_img_onload.bind(this);
		this.menu_img.src = ((this.qmode == 'edit' || this.qmode == 'correction')?'../':'')+'../js/images/combined.png';
	}
	if (this.qmode == 'correction') this.do_the_test = true; 
}

function setTextColour(colour) {
	var col = colour.split('');
	var red = parseInt(col[1]+col[2], 16);
	var gre = parseInt(col[3]+col[4], 16);
	var blu = parseInt(col[5]+col[6], 16);
	var intensity = red + gre + blu;
	if (intensity > 382) {
		return ('#000000');
	} else {
		return ('#FFFFFF');
	}
}

function qh_panelBoxBuild (but_name,pan_name) {
  var temp_but = this.buttonBox[this.buttonBoxNames[but_name]];
  this.imgdata = menuImages[pan_name];
  var tmp_but_num = this.buttonBoxNames[but_name];
  this.qh_panelBox.push('xxx'+temp_but);
  this.qh_panelBox[tmp_but_num] = new Array();
  this.qh_panelBox[tmp_but_num][0] = tmp_but_num;
  this.qh_panelBox[tmp_but_num][1] = but_name;
  this.qh_panelBox[tmp_but_num][2] = pan_name;
  this.qh_panelBox[tmp_but_num][3] = temp_but[1];
  this.qh_panelBox[tmp_but_num][4] = temp_but[2]+25;
  this.qh_panelBox[tmp_but_num][5] = this.imgdata.width;
  this.qh_panelBox[tmp_but_num][6] = this.imgdata.height;
}

function qh_menuBuild() {
	if (this.qmode == 'edit' || this.qmode == 'analysis' || this.qmode == 'correction') {
		//this.imgdata = menuImages['toolbar/vert_0.png'];
		//this.context.drawImage(this.menu_img,this.imgdata.left+0.5,this.imgdata.top,this.imgdata.width-1,this.imgdata.height,0,0,this.canvas.width,this.imgdata.height);
	}	
	var posy = 3;
	if (this.qmode == 'edit') {
		var spac = 3;	
		posx = this.menuBuild_icons('toolbar/vert_1.png',0,0,0,'','','')+spac;
		posx = this.menuBuild_icons('toolbar/ico_plus.png',250,posy,0,'','',lang_string['newLayer'])+spac;
		posx = this.menuBuild_icons('toolbar/ico_minus.png',posx,posy,0,'','',lang_string['deleteLayer'])+spac;

		posx = this.menuBuild_icons('toolbar/vert_1.png',300,0,0,'','','')+spac;
		posx = this.menuBuild_icons('toolbar/ico_resize.png',posx,posy,0,'a','',lang_string['edit'])+spac;
		posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
		posx = this.menuBuild_icons('toolbar/ico_ellipse.png',posx,posy,0,'a','',lang_string['ellipse'])+spac;
		posx = this.menuBuild_icons('toolbar/ico_rectangle.png',posx,posy,0,'a','',lang_string['rectangle'])+spac;
		posx = this.menuBuild_icons('toolbar/ico_polygon.png',posx,posy,0,'a','',lang_string['polygon'])+spac;
		posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
		posx = this.menuBuild_icons('toolbar/ico_erase.png',posx,posy,0,'a','',lang_string['erase'])+spac;
		posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
		posx = this.menuBuild_icons('toolbar/ico_check_on.png',3,4,0,'-',lang_string['view'],'')+spac;
		posx = this.menuBuild_icons('toolbar/ico_help.png',this.canvas.width-23,posy,0,'-','','')+spac;    

		posx = this.menuBuild_icons('toolbar/ico_palette.png',274,24+12,2,'','',lang_string['colour']);
		this.qh_panelBoxBuild('toolbar/ico_palette.png','toolbar/pan_colours.png');
	}
	if (this.qmode == 'analysis' || this.qmode == 'correction') {
		var spac = 30;	
		posx = this.menuBuild_icons('toolbar/vert_1.png',0,0,0,'','','')+spac;
		posx = this.menuBuild_icons('toolbar/ico_check_on.png',posx,posy,0,'-',lang_string['hotspots'],'')+spac;
		posx = this.menuBuild_icons('toolbar/ico_check_on.png',posx,posy,0,'-',lang_string['correctAnswers'],'')+spac;
		posx = this.menuBuild_icons('toolbar/ico_check_on.png',posx,posy,0,'-',lang_string['incorrectAnswers'],'')+spac;
	}
	if (this.qmode == 'script') {
		posx = this.menuBuild_icons('toolbar/ico_check_on.png',10,10,0,'-',lang_string['view'],'');		
	}

}

function test_handler(xx,yy,ww,hh,vv) {
  var nr = -1;
  var size = 5;
  if (vv.length>0) {
		//square handlers
    for (var n=0;n<vv.length/2;n++) {
      if (Math.abs(this.x-(parseInt(vv[n*2].trim(), 16)+xx+0.5))<=size && Math.abs(this.y-(parseInt(vv[n*2+1].trim(), 16)+0.5+yy))<=size) nr=n;
		}
    //round handlers
		vv.push(vv[0]);
		vv.push(vv[1]);
    for (var n=1;n<vv.length/2;n++) {
			var temp_x = (parseInt(vv[n*2].trim(), 16) - parseInt(vv[(n-1)*2].trim(), 16))/2 + parseInt(vv[(n-1)*2].trim(), 16);
			var temp_y = (parseInt(vv[n*2+1].trim(), 16) - parseInt(vv[(n-1)*2+1].trim(), 16))/2 + parseInt(vv[(n-1)*2+1].trim(), 16);
			if (Math.abs(this.x-(temp_x+xx+0.5))<=size && Math.abs(this.y-(temp_y+0.5+yy))<=size && nr == -1) nr=-10-n;
    }
  } else {
    if ((Math.abs(this.x-xx)<=size) && (Math.abs(this.y-yy)<=size)) nr=1;
    if ((Math.abs(this.x-xx-ww)<=size) && (Math.abs(this.y-yy)<=size)) nr=2;
    if ((Math.abs(this.x-xx)<=size) && (Math.abs(this.y-yy-hh)<=size)) nr=3;
    if ((Math.abs(this.x-xx-ww)<=size) && (Math.abs(this.y-yy-hh)<=size)) nr=4;
  }
  return nr;
}

function qh_test(type) {
  this.do_the_test = false;
  this.hotspot_over = '';
  this.handle_over = -1;
  this.context.globalAlpha = 1;
	this.context.lineWidth = 1;
	for (i in this.hotSpots) {
    var fields = this.hotSpots[i][3];
		this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
    //drawing all the fileds for that label
    for (j=0; j<fields;j++) {
      var f_type = this.hotSpots[i][(3+j*6+1)];
      this.HsCo = this.hotSpots[i][(3+j*6+2)].split(',');
			//var col1 = this.hotSpots[i][2];
      //var col2 = this.hotSpots[i][2];
      var col1 = this.layerColours[i];
      var col2 = this.layerColours[i];

			if (type == 'cursor') this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
			this.draw_limit = new Array(302,27-this.yOffset,this.canvas.width-2,this.canvas.height-2);

      if (f_type == 'ellipse') {
        this.ellipseDraw(this.context,col1,col2,parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),this.global_edit); 
      }
			
      if (f_type == 'rectangle') {
        this.rectDraw(this.context,col1,col2,parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),this.global_edit); 
      }
			
      if (f_type == 'polygon') {
        tmp_mode = 'a';if (this.global_edit) tmp_mode = 't';
        this.polyDrawH(this.context,col1,col2,300,25-this.yOffset,this.HsCo,tmp_mode);
      }
			
      //testing the cursor position against above drawn labels
      if (type == 'cursor') {
        var timgd = this.context.getImageData(this.x,this.y,1,1);
        var timgp = timgd.data;
				
        if (this.hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2])).toUpperCase() == col1.toUpperCase()) {
          this.hotspot_over = i+'@'+j+'#'+Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+'$';
        }
        
				if (this.hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2])).toUpperCase() == col2.toUpperCase()) {
          if (f_type == 'ellipse' || f_type == 'rectangle') {
						this.handle_over = this.test_handler(parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),'');
					}
					
          if (f_type == 'polygon') {
            this.handle_over = this.test_handler(300,25-this.yOffset,0,0,this.HsCo); 
          }
          this.hotspot_over =  i+'@'+j+'#'+Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+'$'+this.handle_over;
        }
      }
    }
    //testing the answer against above drawn labels
    if (type == 'answers') {
			if (this.qmode == 'correction') {
				for (j=0;j<this.answers.length;j++) {        
						this.answers[j][i][0] = '0';
						if (typeof this.answers[j][i][1]!='undefined' && this.answers[j][i][1]!='' && this.answers[j][i][1]!='false') {
							tx = (1*this.answers[j][i][1]+300+0.5);
							ty = (1*this.answers[j][i][2]+25-this.yOffset);
							timgd = this.context.getImageData(tx,ty,1,1);
							timgp = timgd.data;
							if (this.hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2])).toUpperCase() != '#000000' && timgp[3]>192) { 
								this.answers[j][i][0] = '1'; //192 is 3/4 of 256 to take intoaccount antialiasing
							}			
						}				
				}
			}else{
				for (j=0;j<this.answers[i].length;j++) {        
					this.answers[i][j][0] = '0';
					if (typeof this.answers[i][j][1]!='undefined' && this.answers[i][j][1]!='' && this.answers[i][j][1]!='false') {
						tx = (1*this.answers[i][j][1]+300+0.5);
						ty = (1*this.answers[i][j][2]+25-this.yOffset);
						timgd = this.context.getImageData(tx,ty,1,1);
						timgp = timgd.data;
						if (this.hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2])).toUpperCase() != '#000000' && timgp[3]>192) { 
							this.answers[i][j][0] = '1'; //192 is 3/4 of 256 to take intoaccount antialiasing
						}			
					}
				}
			}
		}	
  }
	//return;
	this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
  if (type == 'answers' && (this.qmode == 'answer' || this.qmode == 'edit' || this.qmode == 'correction')) this.qh_ReturnInfo();
  if (type == 'cursor') return this.hotspot_over;
}

function redraw_hotspot(i,j) {
  var f_type = this.hotSpots[i][(3+j*6+1)];
  this.HsCo = this.hotSpots[i][(3+j*6+2)].split(',');
  var col = 4; 
  if (this.activeLabel == i) col=0; 
  var a1 = 0.25, a2 = 0.25;
  if (this.activeLabel == i) {a1 = 1; a2 = 0.5;}              
  var cc=this.hsColours[col+1];
  var cb=this.hsColours[col];
	if (this.qmode == 'script') cb = cc = this.hotSpots[i][2];
  var local_edit = false;
	if ((this.global_edit || this.global_erase) && this.test_result.indexOf(i+'@'+j+'#')>-1) {
    if (cb!='') cb='#CC0000';
    if (cc!='') cc='#CC0000';
    if (this.global_edit) local_edit = true;
  }
  if (f_type == 'ellipse') {
    this.context.globalAlpha = a1;
    this.ellipseDraw(this.context,cc,'',parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),local_edit); 
    this.context.globalAlpha = a2;
    this.ellipseDraw(this.context,'',cb,parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),local_edit); 
  }
  if (f_type == 'rectangle') {
    this.context.globalAlpha = a1;
    this.rectDraw(this.context,cc,'',parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),local_edit); 
    this.context.globalAlpha = a2;
    this.rectDraw(this.context,'',cb,parseInt(this.HsCo[0], 16)+300.5,parseInt(this.HsCo[1], 16)+0.5+25-this.yOffset,parseInt(this.HsCo[2], 16)-parseInt(this.HsCo[0], 16),parseInt(this.HsCo[3], 16)-parseInt(this.HsCo[1], 16),local_edit); 
  }
  if (f_type == 'polygon') {
    tmp_mode = 'e';if (local_edit) tmp_mode = 'h';
    this.context.globalAlpha = a1;
    this.polyDrawH(this.context,cc,'',300,25-this.yOffset,this.HsCo,tmp_mode); 
    this.context.globalAlpha = a2;
    this.polyDrawH(this.context,'',cb,300,25-this.yOffset,this.HsCo,tmp_mode); 
  }

}

function qh_redraw_canvas() {
	if (!(this.gen_img_loaded && this.menu_img_loaded) && this.imageerrordisplay<501) this.imageerrordisplay ++;
	if (!(this.gen_img_loaded && this.menu_img_loaded) && this.imageerrordisplay==500) {
		this.context.textAlign="left";
		this.context.fillStyle='#C00000';
		this.context.font="13px Arial";
		this.context.fillText(lang_string['errorimageshotspot'],15,15);
	}
	
	if (this.gen_img_loaded && this.menu_img_loaded && (this.dragging || this.redraw_once || this.mov_id!=-1 || this.start_polygon || (this.qmode == 'edit' && this.activeLabelText>-1))) {
		this.redraw_once = false;
		this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
    //menu buttons
    if (this.buttonBox.length == 0 && (this.qmode == 'edit' || this.qmode == 'analysis' || this.qmode == 'script' || this.qmode == 'correction')) this.qh_menuBuild(); 

    //test against label fields  
    if (this.do_the_test && this.qmode!='script') {
			this.qh_test('answers');
		}
		
		//return;
    if ((this.global_edit || this.global_erase) && !this.dragging) {
      this.test_result = this.qh_test('cursor');
    }
		
    this.context.drawImage(this.gen_img,300,25-this.yOffset);
    this.context.lineWidth = this.lineThickness;
		this.context.strokeStyle=this.currentColours[1];
			
    //label bars
    var pan_y = 25-this.yOffset;
    var pan_h = 46;
    for (i in this.hotSpots) {
      this.context.textAlign="left";
   		this.context.font="12px Arial";
			this.palico = 0;
			if (this.qmode == 'edit') this.palico = 20;
      var wrapped = this.wrapText(this.hotSpots[i][1],245-this.palico, false);
      pan_h = 25 + wrapped[1];

      //reset pos_y for bpalette button
      if (this.activeLabel == i && this.qmode == 'edit') this.buttonBox[this.buttonBoxNames['toolbar/ico_palette.png']][2] = pan_y+11;

      //add this.hotSpotsPanel data
      this.hotSpotsPanel[i] = new Array(0,pan_y,300,pan_h,45.5,pan_y+11.5,245-this.palico,pan_h-19);

      //background & border
      this.imgdata = menuImages['toolbar/back_h1.png'];
      this.context.strokeStyle= '#c0c0c0';//'#b3c7d9';			
      if (this.activeLabel == i && this.hotSpots.length>1) {
				this.imgdata = menuImages['toolbar/back_h2.png'];
				this.context.strokeStyle= '#c0c0c0';//'#fed55f';
			}
      
			this.context.drawImage(this.menu_img,this.imgdata.left+1,this.imgdata.top,this.imgdata.width-2,this.imgdata.height,0.5,pan_y+0.5,300,pan_h);

      this.context.strokeRect(0.5,pan_y+0.5,300,pan_h); 
      //color bar
			if (this.hotSpots[i][2]!=undefined) this.context.fillStyle= this.hotSpots[i][2];
  		this.context.fillRect(3,pan_y+3,300-5,4);
			var pos_x = 15;
			if (this.qmode == 'script' && this.display_students_response && !this.allUnaswered) {
				this.imgdata = menuImages['toolbar/ico_tick_g.png'];
				if (this.answers[i][0][0] == '0') this.imgdata = menuImages['toolbar/ico_tick_r.png'];
				this.context.drawImage(this.menu_img,this.imgdata.left,this.imgdata.top,this.imgdata.width,this.imgdata.height,3,pan_y+12,this.imgdata.width,this.imgdata.height);
				pos_x = 23;
			}
      //symbol
      this.context.fillStyle='#000000';
   		this.context.font="bold 18px Arial";
      this.context.fillText(String.fromCharCode(65+1*i), pos_x, pan_y+27);
      //text background
      this.context.fillStyle=this.currentColours[0];
      if ((this.is_an_answer && typeof(this.answers[i])!='undefined' && this.answers[i][0][1] == 'false') || this.allUnaswered == true) this.context.fillStyle=this.currentColours[3];
   		this.context.fillRect(39.5,pan_y+11.5,252-this.palico,pan_h-17);
      //label text
      this.context.fillStyle='#000000';
			if (this.exclusions[i] == '1') {
				this.context.fillStyle='#FF0000';
	      this.lineDraw(this.context,'#FF0000',43.5,pan_y+22.5,243-this.palico,0);
			}
   		this.context.font="12px Arial";
			this.fillWrappedText(this.context,wrapped[0],45.5,pan_y+25.5);
      //text frame
      this.context.fillStyle='#C0C0C0';
   		this.context.strokeRect(39.5,pan_y+11.5,252-this.palico,pan_h-17);
      //colour pallete button
			if (this.qmode == 'edit') {
				this.imgdata = menuImages['toolbar/ico_palette.png'];
				this.context.drawImage(this.menu_img,this.imgdata.left,this.imgdata.top,this.imgdata.width,this.imgdata.height,275,pan_y+12,this.imgdata.width,this.imgdata.height);
			}
      if (this.activeLabelText == i) {
				this.activeLabel_y = pan_y;
				this.activeLabel_h = pan_h;
				}
      pan_y += pan_h;
		}
		if (this.qmode == 'script' && typeof(this.buttonBox[0])!='undefined') this.buttonBox[0][2] = pan_y+10;
		
    //frames
    this.context.strokeStyle='#c0c0c0';//'#7f9db9'; 
    if (this.qmode == 'edit') this.context.strokeRect(0.5,0.5,this.canvas.width-1,25); 

    this.context.strokeStyle='#909090';//#7f9db9
    this.context.strokeRect(300.5,0.5,this.canvas.width-300,this.canvas.height-1); 
		this.draw_limit = Array(302,27-this.yOffset,this.canvas.width-2,this.canvas.height-2);

		
		//moving shapes by arrow keys
		this.arrow_move = 0;
		if (this.qmode == 'edit' && this.hotspot_over!='') {
			if (this.key_code == 39) this.arrow_move = 3; //arror right
			if (this.key_code == 37) this.arrow_move = 1; //arrow left
			if (this.key_code == 38) this.arrow_move = -3; //arrow up
			if (this.key_code == 40) this.arrow_move = -1; //arrow down
		this.key_code = 0;
		}				
    //active fields
		//drawing hotspots
    if (this.qmode == 'edit' || this.qmode == 'script' || this.qmode == 'analysis' || this.qmode == 'correction') {
      //reposition of field's handlers
      if ((this.arrow_move != 0 || this.label_elem_drag!='') && this.testWithin(this.x,this.y,this.draw_limit[0],this.draw_limit[1],this.draw_limit[2],this.draw_limit[3])) {
        //setting the parameters of the 
        var led = this.label_elem_drag.split(/[@#$]/);
				if (this.arrow_move != 0) led = this.hotspot_over.split(/[@#$]/);
        var led3 = '';if (led[2]!='') led3=led[2].split(',');
        this.HsCo = this.hotSpots[led[0]][(3+led[1]*6+2)].split(',');
        var f_type = this.hotSpots[led[0]][(3+led[1]*6+1)];
				var tmp_arrow_move_x = 0;
				var tmp_arrow_move_y = 0;
				if (this.arrow_move > 0) tmp_arrow_move_x = this.arrow_move-2;
				if (this.arrow_move < 0) tmp_arrow_move_y = this.arrow_move+2;

				var temp_x = this.x-300;
				var temp_y = this.y-25+this.yOffset;

        if (f_type == 'polygon' && led[3]<-10) { //circular handlers - breaking the edge
					var brk = -led[3]-10;
					this.HsCo.splice(brk*2, 0, ''+Math.round(temp_x).toString(16), ''+Math.round(temp_y).toString(16));
					led[3] = brk; 
					
          this.hotSpots[led[0]][(3+led[1]*6+2)] = this.HsCo.join(',');
					this.hotspot_over = led[0]+'@'+led[1]+'#'+Math.round(temp_x).toString(16)+','+Math.round(temp_y).toString(16)+'$'+led[3];
				}

        if (led[3]!=-1) {
          //move point
          if ((f_type == 'ellipse') || (f_type == 'rectangle')) {
            switch (led[3])
            {
            case '1':
							if (this.arrow_move != 0) {
								temp_x = parseInt(this.HsCo[0],16) + tmp_arrow_move_x;
								temp_y = parseInt(this.HsCo[1],16) + tmp_arrow_move_y;
							}
              this.HsCo[0] = Math.round(temp_x).toString(16);
              this.HsCo[1] = Math.round(temp_y).toString(16);
              break;
            case '2':
							if (this.arrow_move != 0) {
								temp_x = parseInt(this.HsCo[2],16) + tmp_arrow_move_x;
								temp_y = parseInt(this.HsCo[1],16) + tmp_arrow_move_y;
							}
              this.HsCo[2] = Math.round(temp_x).toString(16);
              this.HsCo[1] = Math.round(temp_y).toString(16);
              break;
            case '3':
							if (this.arrow_move != 0) {
								temp_x = parseInt(this.HsCo[0],16) + tmp_arrow_move_x;
								temp_y = parseInt(this.HsCo[3],16) + tmp_arrow_move_y;
							}
              this.HsCo[0] = Math.round(temp_x).toString(16);
              this.HsCo[3] = Math.round(temp_y).toString(16);
              break;
            case '4':
							if (this.arrow_move != 0) {
								temp_x = parseInt(this.HsCo[2],16) + tmp_arrow_move_x;
								temp_y = parseInt(this.HsCo[3],16) + tmp_arrow_move_y;
							}
              this.HsCo[2] = Math.round(temp_x).toString(16);
              this.HsCo[3] = Math.round(temp_y).toString(16);
              break;
            }
          }
          if (f_type == 'polygon') {
						if (this.arrow_move != 0) {
							temp_x = parseInt(this.HsCo[led[3]*2+0],16) + tmp_arrow_move_x;
							temp_y = parseInt(this.HsCo[led[3]*2+1],16) + tmp_arrow_move_y;
						}
						this.HsCo[led[3]*2+0] = Math.round(temp_x).toString(16);
            this.HsCo[led[3]*2+1] = Math.round(temp_y).toString(16);
          } 
        } else {
          //move the whole
          if (this.label_elem_clicked_pos == '') {
            var x0 = parseInt(led3[0], 16)+300;
            var y0 = parseInt(led3[1], 16)+25-this.yOffset;
            this.label_elem_clicked_pos = x0+','+y0;
          }
          var pos0 = this.label_elem_clicked_pos.split(',');
					if (this.label_elem_drag!='') { //for mouse dragging
						for (n=0;n<this.HsCo.length/2;n++) {
							this.HsCo[n*2+0] = Math.round(parseInt(this.HsCo[n*2+0], 16)+this.x-pos0[0]).toString(16);
							this.HsCo[n*2+1] = Math.round(parseInt(this.HsCo[n*2+1], 16)+this.y-pos0[1]).toString(16);
						}
					}
          if (this.arrow_move != 0) { //for arrow keys manipulation
						for (n=0;n<this.HsCo.length/2;n++) {
							this.HsCo[n*2+0] = Math.round(parseInt(this.HsCo[n*2+0], 16)+tmp_arrow_move_x).toString(16);
          		this.HsCo[n*2+1] = Math.round(parseInt(this.HsCo[n*2+1], 16)+tmp_arrow_move_y).toString(16);
						}
						this.qh_ReturnInfo();
					}		
          if (this.label_elem_clicked_pos != '') this.label_elem_clicked_pos = this.x+','+this.y;
        }
        this.hotSpots[led[0]][(3+led[1]*6+2)] = this.HsCo.join(',');
      }
			if ((this.qmode!='analysis' && this.qmode != 'correction') || this.global_hotspots) {
				for (i in this.hotSpots) {
					if (((this.activeLabel == i) || this.show_all_hotspots) && this.display_correct_answer) {
						var fields = (this.hotSpots[i].length-4)/6;
						for (j=0; j<fields;j++) {
							//display all not active
							if ((!this.global_edit && !this.global_erase) || this.test_result.indexOf(i+'@'+j+'#') == -1) this.redraw_hotspot(i,j);
						}
					}
				}
			}
      
      //draw temp polygon
      if (this.start_polygon && this.poly_temp!='') {
        var poly_temp_ext = this.poly_temp;
        poly_temp_ext += Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16);
        this.context.lineWidth = 2;
        this.context.globalAlpha = 1;
        this.polyDrawH(this.context,this.hsColours[1],'',300,25-this.yOffset,poly_temp_ext.split(','),'d');
        this.context.globalAlpha = 0.25;
        this.polyDrawH(this.context,'',this.hsColours[1],300,25-this.yOffset,poly_temp_ext.split(','),'d');
        this.context.lineWidth = 1;
        }
      this.context.globalAlpha = 1;      
      
      //display the active
      if (this.test_result!='' && (this.global_edit || this.global_erase)) {
        var led = this.test_result.split(/[@#$]/);
        var led3 = '';if (led[2]!='') led3=led[2].split(',');
        if ((this.activeLabel == led[0]) || this.show_all_hotspots) this.redraw_hotspot(led[0],led[1]);        
      }
      this.context.globalAlpha = 1;
    }

		if (this.qmode == 'analysis') {
			if (this.global_correct) {
				this.context.fillStyle='#00ff00';
				i = this.activeLabel;
				{        
					for (j=0;j<this.answers[i].length;j++) {
						if (1*this.answers[i][j][0] == 1) this.context.fillRect(Math.round(1*this.answers[i][j][1]+300+0)-0.5,Math.round(1*this.answers[i][j][2]+25-this.yOffset)-0.5,2,2);
					}
				}
			}
			if (this.global_incorrect) {
				this.context.fillStyle='#ff0000';
				i = this.activeLabel;
				{        
					for (j=0;j<this.answers[i].length;j++) {        
						if (1*this.answers[i][j][0] == 0) this.context.fillRect(Math.round(1*this.answers[i][j][1]+300+0)-0.5,Math.	round(1*this.answers[i][j][2]+25-this.yOffset)-0.5,2,2);
					}
				}				
			}
		}
		
		if (this.qmode == 'correction') {
			if (this.global_correct) {
				this.context.fillStyle='#00ff00';
				i = this.activeLabel;
				{        
					for (j=0;j<this.answers.length;j++) {
						if (1*this.answers[j][i][0] == 1) this.context.fillRect(Math.round(1*this.answers[j][i][1]+300+0)-0.5,Math.round(1*this.answers[j][i][2]+25-this.yOffset)-0.5,2,2);
					}
				}
			}
			if (this.global_incorrect) {
				this.context.fillStyle='#ff0000';
				i = this.activeLabel;
				{        
					for (j=0;j<this.answers.length;j++) {        
						if (1*this.answers[j][i][0] == 0) this.context.fillRect(Math.round(1*this.answers[j][i][1]+300+0)-0.5,Math.	round(1*this.answers[j][i][2]+25-this.yOffset)-0.5,2,2);
					}
				}				
			}
		}
	
    //drop answer baloons
    if (this.qmode == 'answer' || this.qmode == 'script') {
      for (i=0;i<this.answers.length;i++) {
        if ((this.activeLabel == i || this.show_all_hotspots) && this.answers[i][0][1]!='false' && this.answers[i][0][2]!='false' && this.answers[i][0][1]!='' && this.answers[i][0][2]!='') {
          var flipv = fliph = 0;
					if (this.canvas.width-300-1*this.answers[i][0][1]<40) fliph = 1;
					if (this.answers[i][0][2]<25) flipv = 1;
					this.imgdata = menuImages['toolbar/smoke_b.png'];
          //changing blue slightly(0,42,255) before pasting baloon background
          var imgd = this.context.getImageData((1*this.answers[i][0][1]+300),(1*this.answers[i][0][2]-this.yOffset),this.imgdata.width,this.imgdata.height);
          var imgp = imgd.data;
          for (j=0; j<imgp.length; j+=4) {
            if (imgp[j+2] == 255 && imgp[j+1] == 42) imgp[j+1]=41;
            }
          this.context.putImageData(imgd, (1*this.answers[i][0][1]+300),(1*this.answers[i][0][2]-this.yOffset));
					
					//flipping
					this.context.save();
					//position the smoke
					var calc_x = calc_x0 = Math.round(1*this.answers[i][0][1]+300-4); //-4px correction for smoke icon shift and border
					var calc_y = calc_y0 = Math.round(1*this.answers[i][0][2]-this.yOffset+2); //+2px correction for smoke icon shift and border
 					if (fliph == 1) {
						this.context.scale(-1,1);
						calc_x = -calc_x-10;
						}
					if (flipv == 1) {
						this.context.scale(1,-1);
						calc_y = -calc_y-48;
					}
          //pasting the baloon background
					this.context.drawImage(this.menu_img,this.imgdata.left+1,this.imgdata.top+1,this.imgdata.width-2,this.imgdata.height-2,calc_x,calc_y+1,this.imgdata.width-2,this.imgdata.height-2);
          this.context.restore(); //restore from before flippping
                  
          //recoloring the baloon background
          imgd = this.context.getImageData(calc_x0-fliph*30,calc_y0+flipv*18,this.imgdata.width,this.imgdata.height);
          imgp = imgd.data;
					var calc_c = Array(parseInt(this.hotSpots[i][2].substr(1,2), 16),parseInt(this.hotSpots[i][2].substr(3,2), 16),parseInt(this.hotSpots[i][2].substr(5,2), 16));
          for (j=0; j<imgp.length; j+=4) {
            if (imgp[j+2] == 255 && imgp[j+1] == 42) {						
						imgp[j+0]=calc_c[0];
            imgp[j+1]=calc_c[1];
            imgp[j+2]=calc_c[2];
            }
          }
          this.context.putImageData(imgd,calc_x0-fliph*30,calc_y0+flipv*18);

          //pasting the baloon
					this.context.save();
					if (fliph == 1) this.context.scale(-1,1);
					if (flipv == 1) this.context.scale(1,-1);
          this.imgdata = menuImages['toolbar/smoke.png'];
         	this.context.drawImage(this.menu_img,this.imgdata.left,this.imgdata.top,this.imgdata.width-1,this.imgdata.height,calc_x-1,calc_y,this.imgdata.width,this.imgdata.height);
          this.context.restore(); //restore from before flippping

          //symbol
					this.context.fillStyle=setTextColour(this.hotSpots[i][2]);
			    this.context.textAlign="left";
          this.context.font="bold 18px Arial";
          this.context.fillText(String.fromCharCode(65+i),(1*this.answers[i][0][1]+320-3.5-45*fliph),(1*this.answers[i][0][2]-this.yOffset+21+22*flipv));
					
        }
      }
    }	
			
		//buttons
		if (this.qmode == 'edit' || this.qmode == 'analysis' || this.qmode == 'correction') this.menuRebuild(this.context);
		if (this.qmode == 'script' && this.hotSpots.length>1) this.menuRebuild(this.context,false);
		
		//msgbox overlaping
		this.draw_limit = Array(0,27-this.yOffset,this.canvas.width-2,this.canvas.height-2);
		if (this.any_overlaping && this.overlapping_show) this.build_msgbox(30,50,260,130,lang_string['warning']+"|"+lang_string['errormessage1']+"|"+lang_string['errormessage2'],'','',lang_string['msgClose']);

		m = 0;
		//draw colour table
		if (this.buttonClicked>-1 && this.buttonBox[this.buttonClicked][0] == 'toolbar/ico_palette.png') {
			this.panelOverColour = '';
			m = 0;
			for (n=0;n<this.colorReference.length;n++) if (this.hotSpots[this.activeLabel][2] == this.colorReference[n]) m = n;
				this.menuRebuild_panel(this.panelActiveParts,this.qh_panelBox,'toolbar/ico_palette.png','toolbar/pan_colours.png',0,m);
		}
		
		//tooltip
		if (this.buttonOver!=-1) this.tooltip_draw(this.context,this.buttonBox[this.buttonOver]);

		// border
		this.context.strokeStyle='#909090';  //#7f9db9
		this.context.strokeRect(0.5,0.5,this.canvas.width-1,this.canvas.height-1);
		
		//cursor blink
		if (this.qmode == 'edit' && this.activeLabelText>-1) {
			//edit box
			var label_txt = this.hotSpots[this.activeLabelText][1];
			var text_len = label_txt.length;
			if (this.key_code == '39') this.edit_box_pos++; 				//arror right
			if (this.key_code == '37') this.edit_box_pos--; 				//arrow left
			if (this.key_code == '35') this.edit_box_pos=text_len;	//end
			if (this.key_code == '36') this.edit_box_pos=0; 				//home	
			if (this.edit_box_pos<0) this.edit_box_pos=0;
			if (this.edit_box_pos>text_len) this.edit_box_pos=text_len;
			if (this.char_code!='') {	//characters
				this.hotSpots[this.activeLabelText][1] = label_txt.substr(0,this.edit_box_pos)+this.char_code+label_txt.substr(this.edit_box_pos);
				this.edit_box_pos++;
				this.qh_ReturnInfo()
			}
			if (this.key_code == '46') { //del
				this.hotSpots[this.activeLabelText][1] = label_txt.substr(0,this.edit_box_pos)+label_txt.substr(this.edit_box_pos+1);
				this.qh_ReturnInfo()
			}
			if (this.key_code == '8') { //backspace
				this.hotSpots[this.activeLabelText][1] = label_txt.substr(0,this.edit_box_pos-1)+label_txt.substr(this.edit_box_pos);
				this.edit_box_pos--;
				this.qh_ReturnInfo()
			}	
			this.char_code = '';
			this.key_code = 0;
				 
			this.edit_box_blink++;
			if (this.edit_box_blink>60) this.edit_box_blink=0;
			if (this.edit_box_blink>30) {
				this.context.font="12px Arial";
				var text_all = this.wrapText(this.hotSpots[this.activeLabelText][1],250-this.palico,false)[0];
				var text_temp = '';
				if (this.edit_box_pos>0) text_temp = text_all.substr(0,this.edit_box_pos);
				var wrap_temp = text_temp.split('|');
				var text_part_line = wrap_temp.length-1;
				
				var text_part = wrap_temp[text_part_line];
				var text_full = text_all.split('|')[text_part_line];
				var metrics_part = this.context.measureText(text_part);
				var metrics_full = this.context.measureText(text_full);
				
				this.context.strokeStyle='#000';					
				this.context.beginPath();

				var temp_x = Math.round(45+metrics_part.width)+0.5;
				var temp_y = Math.round(this.fontSizes[this.fontSizePos]*text_part_line+this.activeLabel_y+16)+0.5;
				this.context.moveTo(temp_x,temp_y);
				this.context.lineTo(temp_x,temp_y+this.fontSizes[this.fontSizePos]);
				this.context.stroke();
				this.context.strokeStyle=this.currentColours[1];
			}
		}
		this.canvas.style.cursor = this.cur;
	}else if(this.qmode == 'analysis' && this.canvas.style.cursor == 'wait'){
		this.redraw_once = true; //give one cycle to change the cursor
	}else if(this.qmode == 'analysis' && this.canvas.style.cursor != this.cur){
		this.canvas.style.cursor = this.cur; //reset the cursor if cycle is missed
	}
}

function qh_mouseDragMove(e){
	this.ev = e || window.event;
	if (this.ev.target.id != this.canvas.id) return true;
	this.get_char_key();
	if (this.ev.type == 'mousemove') {
		this.canv_rect = this.canvas.getBoundingClientRect();
		this.loc_lft = this.canv_rect.left;
		this.loc_top = this.canv_rect.top;
		this.x = this.ev.clientX - this.loc_lft;
		this.y = this.ev.clientY - this.loc_top;
	}	
	if (this.dragging){ //this.dragging
		//new position of dragged element
		/*
		if (this.drag_box_id>-1 && this.testWithin(this.x - this.sub_x,this.y - this.sub_y,300,25,this.canvas.width,this.canvas.height)) {
			this.hotSpots[this.drag_box_id][5] = this.x - this.sub_x;
			this.hotSpots[this.drag_box_id][6] = this.y - this.sub_y;
		}
		*/
    if (this.hotspot_over != '') this.label_elem_drag = this.hotspot_over;
	} else { //change of cursor
    //this.drag_box_id = -1;
		if (this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height) && this.canvas.style.cursor != 'wait'){
			var over_object = false;     
      
      //test for buttons
      var buttonTest = -1;
      for (var i=0;i<this.buttonBox.length;i++) {
        this.buttonBox[i][5] = this.buttonBox[i][6];
        if (this.buttonBox[i][0] == 'toolbar/ico_drop.png') this.buttonBox[i][5] = this.buttonBox[i-1][5];
				if (this.buttonBox[i][0].indexOf('vert_') == -1 && this.testWithin(this.x,this.y,this.buttonBox[i][1],this.buttonBox[i][2],this.buttonBox[i][3],this.buttonBox[i][4]) == true) {
          over_object = true;
          buttonTest = i;
          this.buttonBox[i][5] = 1;
          
          //double button
          var j=i;
          if (this.buttonBox[i][0] == 'toolbar/ico_drop.png') j=i-1;
          if (i<this.buttonBox.length-1 && this.buttonBox[i+1][0] == 'toolbar/ico_drop.png') j=i+1;
          this.buttonBox[j][5] = 1;
        }
      }
      if (this.buttonOver != buttonTest) {
        this.buttonOver = buttonTest;
        this.redraw_once = true;
        this.qh_redraw_canvas;
      }
      this.hotSpotsPanelOver=-1;
			this.hotSpotsPanelTextOver=-1;
      for (var i=0;i<this.hotSpotsPanel.length;i++) {
				//test for buttonBoxPanels
        if (this.testWithin(this.x,this.y,this.hotSpotsPanel[i][0],this.hotSpotsPanel[i][1],this.hotSpotsPanel[i][2],this.hotSpotsPanel[i][3]) == true) {
          over_object = true;
          this.hotSpotsPanelOver=i;
        }
				//test for PanelText
        if (this.testWithin(this.x,this.y,this.hotSpotsPanel[i][0]+40,this.hotSpotsPanel[i][1]+12,this.hotSpotsPanel[i][2]-69,this.hotSpotsPanel[i][3]-17) == true) {
          this.hotSpotsPanelTextOver=i;
        }
			}
                  
      this.cur = 'default';
      if (this.global_edit) this.cur = 'not-allowed';
			if (this.global_edit && this.test_result!='') this.cur = 'move';
			if (this.global_edit && this.test_result!='' && this.test_result.indexOf('$')<this.test_result.length-1) this.cur = 'default';
			if (this.global_erase && this.test_result!='') this.cur = 'url(/js/images/cur_erase.cur) 6 5, default';//this works only in css3 browsers otherwise whole cursor is ignored
			if (over_object) this.cur = 'pointer';
			if (this.handle_over != -1) this.cur = 'move';

      if (this.buttonOver>-1 && this.buttonBox[this.buttonOver][0] == 'toolbar/ico_help.png') this.cur = 'help';
      if (this.y>25 && (this.start_rectangle || this.start_ellipse  || this.start_polygon)) this.cur = 'crosshair';
      if (this.testWithin(this.x,this.y,0,0,300,this.canvas.height)) this.cur = 'default';
			if (this.testWithin(this.x,this.y,300,0,this.canvas.width,this.canvas.height) && this.qmode == 'answer') this.cur = 'crosshair';

      e.target.style.cursor = this.cur;
		}
	}
  //test cursor against labels
  if (this.global_edit || this.global_erase) {
    this.redraw_once = true;
    this.qh_redraw_canvas;
  }
  
  //test for panels
  var panelOptionTest = -1;
  this.panelOver = -1;
  if (this.buttonClicked>-1 && typeof this.qh_panelBox[this.buttonClicked] != 'undefined') {

  var tmp_but=-1,tmp_pan=-1;
  if (this.testWithin(this.x,this.y,this.qh_panelBox[this.buttonClicked][3],this.qh_panelBox[this.buttonClicked][4],this.qh_panelBox[this.buttonClicked][5],this.qh_panelBox[this.buttonClicked][6]) == true) {
    tmp_but = this.buttonBox[this.buttonClicked];
    if (typeof this.qh_panelBox[this.buttonClicked][2]!='undefined') tmp_pan = this.qh_panelBox[this.buttonClicked][2];
    this.panelOver=this.buttonClicked;
    over_object = true;
    //this.drag_box_id = -1;
    for (i=0;i<this.panelActiveParts[tmp_pan].length;i++) {
      var tp = this.panelActiveParts[tmp_pan][i].split(',');
      this.tw=true;
      if (this.testWithin(this.x,this.y,tmp_but[1]+1*tp[0]+0.5,tmp_but[2]+25+1*tp[1]+0.5,18,20) == true) panelOptionTest=i;
      }
    }
  }
  if (this.panelOptionOver != panelOptionTest) {
    this.panelOptionOver = panelOptionTest;
    this.redraw_once = true;
    this.qh_redraw_canvas;
  }
			
  //this.freehand draw  
  if (this.start_polygon && this.y>28 && this.poly_temp_points[7]!=0 && this.freehand) {
    this.angle1 = this.angle2 = this.distn = this.dx = this.dy = -1;
    if (this.poly_temp_points[3]!=0 && this.poly_temp_points[5]!=0) 
      this.angle1 = Math.atan2(this.poly_temp_points[5]-this.poly_temp_points[3],this.poly_temp_points[4]-this.poly_temp_points[2]);
    if (this.poly_temp_points[5]!=0) 
      this.angle2 = Math.atan2(this.y-this.poly_temp_points[5],this.x-this.poly_temp_points[4]);
    
    if (this.poly_temp_points[5] == 0) {
      this.dx = this.x - this.poly_temp_points[6];
      this.dy = this.y - this.poly_temp_points[7];
    } else {
      this.dx = this.x - this.poly_temp_points[4];
      this.dy = this.y - this.poly_temp_points[5];
    }
    
    this.distn = Math.sqrt(this.dx*this.dx+this.dy*this.dy);
		
    var add_point = false;
		
    //if one just started freedrawing
    if (this.poly_temp_points[3] == 0 && this.distn > 10) {
      //because this is this.freehand and no point has been added - add starting one first
      this.poly_temp += Math.round(this.poly_temp_points[6]-300).toString(16)+','+Math.round(this.poly_temp_points[7]-25+this.yOffset).toString(16)+',';
			
			if (this.poly_temp_points[5] == 0){
				this.poly_temp_points[0] = this.poly_temp_points[6];
				this.poly_temp_points[1] = this.poly_temp_points[7];
			}      
			this.poly_temp_points[4] = this.poly_temp_points[6];
			this.poly_temp_points[5] = this.poly_temp_points[7]; 

      //and then dirct to add the actual one
      add_point = true;
    }
          
    //checking the angle (dependend on the distance)
    if (this.poly_temp_points[3]!=0 && this.poly_temp_points[5]!=0 && this.distn > 10 && Math.abs(this.angle2-this.angle1)>(1/this.distn*3)) add_point = true;
        
    if (add_point) {
      this.poly_temp += Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+',';
      this.poly_temp_points[2] = this.poly_temp_points[4];
      this.poly_temp_points[3] = this.poly_temp_points[5];
      this.poly_temp_points[4] = this.x;
      this.poly_temp_points[5] = this.y;
    }
  }
  //this.freehand draw end  

	//cancel propagation if BackSpace
	if (this.ev.type == 'keydown' && this.ev.keyCode<=46) {
		if (this.ev.stopPropagation) this.ev.stopPropagation();
		if (this.ev.cancelBubble!=null) this.ev.cancelBubble = true;
		if (this.ev.preventDefault) this.ev.preventDefault();
		if (this.ev.returnValue) this.ev.returnValue = false;
	}
	return false;
}

function qh_mouseDragDown(e){
	this.x = e.clientX - this.canv_rect.left;
	this.y = e.clientY - this.canv_rect.top;
	if (this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height)){
		/*
		if (this.drag_box_id>-1) {
			this.sub_x = this.x - this.hotSpots[this.drag_box_id][5];
			this.sub_y = this.y - this.hotSpots[this.drag_box_id][6];
		}
		*/
		if (this.panelOptionOver == -1 && this.qmode!='analysis') this.dragging = true;	
	}
	if (this.qmode!='script' && this.testWithin(this.x,this.y,300,25,this.canvas.width,this.canvas.height)) {
		if (this.start_rectangle && this.activeLabel>-1) {    
			j = this.hotSpots[this.activeLabel][3]++;
			this.hotSpots[this.activeLabel][(3+j*6+1)] = 'rectangle';
			this.hotSpots[this.activeLabel][(3+j*6+2)] = Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+','+Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16); 
			this.hotSpots[this.activeLabel][(3+j*6+3)] = j; //id
			this.hotspot_over += this.activeLabel+'@'+j+'#'+Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+'$4';
		}

		if (this.start_ellipse && this.activeLabel>-1) {    
			j = this.hotSpots[this.activeLabel][3]++;
			this.hotSpots[this.activeLabel][(3+j*6+1)] = 'ellipse';
			this.hotSpots[this.activeLabel][(3+j*6+2)] = Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+','+Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16); 
			this.hotSpots[this.activeLabel][(3+j*6+3)] = j; //id
			this.hotspot_over += this.activeLabel+'@'+j+'#'+Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+'$4';
		}
		if (this.qmode=='edit' && this.panelOverColour!='' && this.panelOverColour!=undefined) this.hotSpots[this.activeLabel][2] = this.panelOverColour;

		//this.freehand
		if (this.start_polygon && this.y>28) {
			this.poly_temp_points[6] = this.x;
			this.poly_temp_points[7] = this.y;
			this.freehand = true;
		}
	}
}

function qh_mouseDragUp(){
	this.dragging = false;
	if (this.buttonOver>-1 && this.buttonBox[this.buttonOver][0] == 'toolbar/ico_help.png') window.open('/help/staff/index.php?id=17');
	
  //test for this.hotSpotsPanel
  if (this.hotSpotsPanelOver>-1) {
    this.activeLabel=this.hotSpotsPanelOver;
    this.panelOverColour = this.hotSpots[this.activeLabel][2];
  }
	
	//text cursor positioning on mouseclick
	if (this.qmode == 'edit' && this.activeLabel>-1) {
		if (this.testWithin(this.x,this.y,this.hotSpotsPanel[this.activeLabel][4],this.hotSpotsPanel[this.activeLabel][5],this.hotSpotsPanel[this.activeLabel][6],this.hotSpotsPanel[this.activeLabel][7])) {
			var text_all = this.wrapText(this.hotSpots[this.activeLabel][1],this.hotSpotsPanel[this.activeLabel][6]);
			var text_lines = text_all[0].split('|').length;
			var click_line = Math.floor(text_lines*((this.y-this.hotSpotsPanel[this.activeLabel][5])/this.hotSpotsPanel[this.activeLabel][7]));
   		this.context.font="12px Arial";
			var text_full = text_all[0].split('|')[click_line];
			var text_line_pos = 0;
			for (a=1;a<=text_full.length;a++) {
				var temp_width = this.context.measureText(text_full.substr(0,a)).width;
				temp_lett = temp_width/a/2;
				if ((this.hotSpotsPanel[this.activeLabel][4]+temp_width-temp_lett)<=this.x) text_line_pos = a;
			}
			var text_arr = text_all[0].split('|');
			var text_arr = text_all[0].split('|');
			text_arr.splice(click_line,text_lines-click_line);
			this.edit_box_pos = text_arr.join('|').length+text_line_pos;
			if (click_line>0) this.edit_box_pos++;
		}
	}
	
	this.activeLabelText=-1;
  if (this.hotSpotsPanelTextOver>-1 && this.qmode == 'edit') {
		this.activeLabelText=this.hotSpotsPanelTextOver;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_ellipse.png']][5] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_rectangle.png']][5] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][5] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_polygon.png']][5] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][5] = 1;
		
		this.buttonBox[this.buttonBoxNames['toolbar/ico_ellipse.png']][6] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_rectangle.png']][6] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][6] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_polygon.png']][6] = 0;
		this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][6] = 1;
		
		this.start_ellipse = false;
		this.start_rectangle = false;
		this.global_erase = false;
		this.start_polygon = false;
		this.global_edit = true;
		
		this.poly_temp = [];
	}
    	
  //test for image area
  if (this.qmode == 'answer' && this.testWithin(this.x,this.y,300,0,this.canvas.width-300,this.canvas.height)) {
    this.answers[this.activeLabel][0][1]=this.x-300-1; //1px for border
    this.answers[this.activeLabel][0][2]=this.y-1; //1px for border
		this.activeLabel++;
		if (this.hotSpots.length<=this.activeLabel) this.activeLabel=0;
  }
  
  this.button_test();
  
  //polygon & this.freehand
  //distance of mouse_up and mose_down
  this.dx = this.x - this.poly_temp_points[6];
  this.dy = this.y - this.poly_temp_points[7];
  this.distn = Math.sqrt(this.dx*this.dx+this.dy*this.dy); 
  if (this.qmode == 'edit' && this.start_polygon && this.y>28) {
    //condition for the finish    
    if (((Math.abs(this.poly_temp_points[0]-this.x)<3 && Math.abs(this.poly_temp_points[1]-this.y)<3)) || (Math.abs(this.poly_temp_points[8]-this.x)<3 && Math.abs(this.poly_temp_points[9]-this.y)<3)) {      
      //add new polygon area
      if (this.poly_temp.length>2) {
        j = this.hotSpots[this.activeLabel][3]++;
        this.hotSpots[this.activeLabel][(3+j*6+1)] = 'polygon';
        this.hotSpots[this.activeLabel][(3+j*6+3)] = j; //id
				if (this.poly_temp.substr(-1) == ',') this.poly_temp = this.poly_temp.substr(0,this.poly_temp.length-1);
        this.hotSpots[this.activeLabel][(3+j*6+2)] = this.poly_temp;				
        this.poly_temp = '';
        this.poly_temp_points = new Array(0,0,0,0,0,0,0,0,0,0);
        global_delpoint_avail = true
        this.overlapping_show = true;
      }
    } else {
      if (!this.freehand || this.distn<5) {
        this.poly_temp += Math.round(this.x-300).toString(16)+','+Math.round(this.y-25+this.yOffset).toString(16)+',';
      }
      //remember the starting point      
      if (this.poly_temp_points[1] == 0) {
        this.poly_temp_points[0] = this.x;
        this.poly_temp_points[1] = this.y;
      }
      //remember the second last and the last point
      this.poly_temp_points[2] = this.poly_temp_points[4];
      this.poly_temp_points[3] = this.poly_temp_points[5];
      this.poly_temp_points[4] = this.poly_temp_points[8] = this.x;
      this.poly_temp_points[5] = this.poly_temp_points[9] = this.y;
      this.poly_temp_points[6] = 0;
      this.poly_temp_points[7] = 0;
    }
  }
  this.freehand = false;
  
	if (this.qmode == 'edit') {
		this.global_edit = false;
		this.global_erase = false;
		
		this.start_rectangle = false;
		this.start_ellipse = false;
		this.start_polygon = false;  
		if (this.buttonBox.length!=0) {
			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][6] == 2) this.global_edit = true;
			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_ellipse.png']][6] == 2) this.start_ellipse = true;
			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_rectangle.png']][6] == 2) this.start_rectangle = true;
			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][6] == 2) this.global_erase = true;
			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_polygon.png']][6] == 2) this.start_polygon = true;

			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_minus.png']][5] == 2 && this.hotSpots.length>1 && this.activeLabel>-1) {    
				this.hotSpots.splice(this.activeLabel,1);
				this.activeLabel = 0;
			}
			if (this.buttonBox[this.buttonBoxNames['toolbar/ico_plus.png']][5] == 2 && this.hotSpots.length < 10) {
				i=this.hotSpots.length;
				this.hotSpots.push(i);
				this.hotSpots[i] = new Array ();
				this.hotSpots[i][0] = i; //label index
				this.hotSpots[i][1] = ''; //label text
				this.hotSpots[i][2] = this.layerColours[i];
				this.hotSpots[i][3] = 0;
				//this.answers[i][0] = '';
				this.answers[i] = new Array(new Array(0,'false','false'));
			}
			
			//hold on colour button
			if (this.buttonClicked == this.buttonBoxNames['toolbar/ico_palette.png'])
				this.buttonBox[this.buttonClicked][5] = this.buttonBox[this.buttonClicked][6] = 2;
		}
	}
	
	if (this.qmode == 'edit' || this.qmode == 'script') {	
		if (this.buttonBox.length!=0) {		
			//switch toolbar/ico_check_on.png
			if (this.buttonClicked == this.buttonBoxNames['toolbar/ico_check_on.png']) {
				if (this.buttonBox[this.buttonBoxNames['toolbar/ico_check_on.png']][0] == 'toolbar/ico_check_on.png') {
					this.buttonBox[this.buttonBoxNames['toolbar/ico_check_on.png']][0] = 'toolbar/ico_check_off.png';
					this.show_all_hotspots =false;
				} else {
					this.buttonBox[this.buttonBoxNames['toolbar/ico_check_on.png']][0] = 'toolbar/ico_check_on.png';
					this.show_all_hotspots = true;
				}  
			}
		}
	}
	
	if (this.qmode == 'analysis' || this.qmode == 'correction') {
		if (this.buttonBox.length!=0) {
			//switch toolbar/ico_check_on.png
			if (this.buttonClicked!=-1) {
				if (this.buttonBox[this.buttonClicked][0] == 'toolbar/ico_check_on.png') {
					this.buttonBox[this.buttonClicked][0] = 'toolbar/ico_check_off.png';
				} else {
					this.buttonBox[this.buttonClicked][0] = 'toolbar/ico_check_on.png';
				}  
			}
			this.global_hotspots = false;
			this.global_correct = false;
			this.global_incorrect = false;
			if (this.buttonBox[1][0] == 'toolbar/ico_check_on.png') this.global_hotspots = true;
			if (this.buttonBox[2][0] == 'toolbar/ico_check_on.png') this.global_correct = true;
			if (this.buttonBox[3][0] == 'toolbar/ico_check_on.png') this.global_incorrect = true;					
		}
	}
  this.label_elem_drag = '';
  this.label_elem_clicked_pos = '';
  
  //erase hotspot
  if (this.global_erase && this.test_result!='') {
    var led = this.test_result.split(/[@#$]/);
    var led3 = '';if (led[2]!='') led3=led[2].split(',');
    this.hotSpots[led[0]].splice((led[1])*6+4,6);
    this.hotSpots[led[0]][3]--;
  }
	if (this.qmode == 'answer' || this.qmode == 'edit') this.do_the_test = true;
	
	if (this.qmode == 'analysis') {
		this.canvas.style.cursor = 'wait';
	}else{
		this.redraw_once = true;
		this.qh_redraw_canvas;
	}
}


function qh_ReturnInfo() {
  var questions_result = '';
	if (this.qmode == 'answer') {
		var no_answer_count = 0;
		for (i=0;i<this.answers.length;i++) {
			if (this.answers[i][0][1]!='false') {
				questions_result+=this.answers[i][0][0]+','+Math.round(this.answers[i][0][1])+','+Math.round(this.answers[i][0][2]);
			} else {
				questions_result+=this.answers[i][0][0]+','+this.answers[i][0][1]+','+this.answers[i][0][2];
				no_answer_count++;
			}
			if (i<this.answers.length-1) questions_result+='|';
		}
		if (no_answer_count == this.answers.length) questions_result='';
		var target_field = document.getElementById('q'+this.q_Num);
	}

	if (this.qmode == 'correction') {
		for (i=0;i<this.answers.length;i++) {
			if (typeof(this.answers[i])!='undefined') {
				questions_result += this.answers[i][0][3]+',';
				for (j=0;j<this.answers[i].length;j++) {
					questions_result+=this.answers[i][j][0]+',';
					if (this.answers[i][j][1]!='false') {
						questions_result += Math.round(this.answers[i][j][1])+','+Math.round(this.answers[i][j][2]);
					} else {
						questions_result += this.answers[i][j][1]+','+this.answers[i][j][2];
					}
					if (j<this.answers[i].length-1) questions_result+='|';
				}
			}
			if (i<this.answers.length-1) questions_result+=';';
		}
		var target_field = document.getElementById('option_correct'+this.q_Num);
	}

	if (this.qmode == 'edit') {
		for (i=0;i<this.hotSpots.length;i++) {
			questions_result+=this.hotSpots[i][1]+'~';
			questions_result+=parseInt(hexifycolour(this.hotSpots[i][2]).substr(1), 16)+'~';
			for (j=0;j<Number(this.hotSpots[i][3]);j++) {
				questions_result+=this.hotSpots[i][4+6*j]+'~';
				questions_result+=this.hotSpots[i][5+6*j]+'~';
				questions_result+=this.hotSpots[i][6+6*j]+'~';
			}
			questions_result+='|';
		}
		questions_result = questions_result.substring(0,questions_result.length-1);
		var target_field = document.getElementById('points'+this.q_Num);
	}
	if (questions_result!='' && target_field) target_field.value = questions_result;
}

function rqh(num) {	
	this.setUpHotspot = setUpHotspot;
	this.qh_panelBoxBuild = qh_panelBoxBuild;
	this.qh_menuBuild = qh_menuBuild;
	this.test_handler = test_handler; 
	this.qh_test = qh_test;
	this.redraw_hotspot = redraw_hotspot;
	this.qh_redraw_canvas = qh_redraw_canvas;
	this.qh_mouseDragMove = qh_mouseDragMove;
	this.qh_mouseDragDown = qh_mouseDragDown;
	this.qh_ReturnInfo = qh_ReturnInfo;
	this.qh_mouseDragUp = qh_mouseDragUp;
	this.def_colour_panel_parts = def_colour_panel_parts;
	this.get_char_key =	get_char_key;
	
	this.hexifycolour=hexifycolour;
	this.textHeight=textHeight;
	this.wrapText=wrapText;
	this.fillWrappedText = fillWrappedText;
	this.findPos=findPos;
	this.testWithin=testWithin;
	this.edtDot=edtDot;
	this.lineDraw=lineDraw;
	this.ellipseDraw=ellipseDraw;
	this.rectDraw=rectDraw;
	this.polyDrawH=polyDrawH;
	this.menuBuild_icons= menuBuild_icons;
	this.menuRebuild=menuRebuild;
	this.menuRebuild_panel=menuRebuild_panel;
	this.button_test=button_test;
	this.build_msgbox=build_msgbox;
	this.tooltip_draw=tooltip_draw;
	
	this.test; 
	this.test_result = '';
	this.x,this.y,this.sub_x,this.sub_y;

	//this.drag_box_id=-1;                         //index of box beeing dragged
  this.menu_ready = 1;
	this.edit_box_blink = 0;
	this.edit_box_pos = 0;
	this.edit_box_pos_old = 0;
	this.key_code = 0;
	this.char_code = ''
	
  this.do_the_test = false;

  this.tw = false;
  this.twr = new Array(0,0,0,0,'#000');
	this.answers = new Array(); 			          	// sublevels of this keep all the answer data
	this.hotSpots = new Array(); 			            // sublevels of this keep all the label data
  this.hotSpotsPanel = new Array();
  this.buttonBox = new Array();                 // sublevels of this keep all the buttons data
  this.qh_panelBox = new Array();               // sublevels of this keep the panels data
  this.buttonBoxNames = new Array();      			// transcription of button names into its index in ButtonBox (?)
  this.panelActiveParts = new Array();       		// array of positions panel's active elements
  this.buttonClicked = -1;                      // index of the button that was clicked
  this.buttonOver =-1;                          // index of the button the mouse is over
  this.panelOptionOver =-1;                     // index of the option on panel the mouse is over
  this.panelOver =-1                            // index of the panel the mouse is over
  this.activeLabel = 0;
	this.activeLabelText = -1;
	this.activeLabel_y;
	this.activeLabel_h;
  this.handle_over = -1;
  this.hotSpotsPanelOver = -1;
	this.hotSpotsPanelTextOver = -1;
  this.allUnaswered = false;
  this.global_edit = false;
  this.global_erase = false;
  this.show_all_hotspots = true;
  this.hotspot_over = '';
  this.label_elem_drag = '';
  this.label_elem_clicked_pos = '';
  this.start_rectangle = this.start_ellipse = this.start_polygon = false;
  this.draw_limit = new Array(); 													//used to limit polygon, ellipse and sqare positions
  this.any_overlaping = false;
  this.overlapping_show = true;
	this.global_hotspots = true;
	this.global_correct = true;
	this.global_incorrect = true;


  this.layerColours   =  new Array('#FF0000', '#FFFF00', '#00B050', '#0070C0', '#7030A0', '#C00000', '#FFC000', '#92D050', '#00B0F0', '#002060', '#FFFFFF', '#F2F2F2', '#D8D8D8', '#BFBFBF', '#A5A5A5', '#7F7F7F', '#000000', '#7F7F7F', '#595959', '#3F3F3F', '#262626', '#0C0C0C', '#EEECE1', '#DDD9C3', '#C4BD97', '#938953', '#494429', '#1D1B10', '#1F497D', '#C6D9F0', '#8DB3E2', '#548DD4', '#17365D', '#0F243E', '#4F81BD', '#DBE5F1', '#B8CCE4', '#95B3D7', '#366092', '#244061', '#C0504D', '#F2DCDB', '#E5B9B7', '#D99694', '#953734', '#632423', '#9BBB59', '#EBF1DD', '#D7E3BC', '#C3D69B', '#76923C', '#4F6128', '#8064A2', '#E5E0EC', '#CCC1D9', '#B2A2C7', '#5F497A', '#3F3151', '#4BACC6', '#DBEEF3', '#B7DDE8', '#92CDDC', '#31859B', '#205867', '#F79646', '#FDEADA', '#FBD5B5', '#FAC08F', '#E36C09', '#974806');
  
  this.hsColours = new Array('#6FEB6F', '#00CC33', '#FF0000', '#CC0000', '#FFFFFF', '#CCCCCC'); 
																													// green fill/stroke, red fill/stroke, 
																													// grey fill/stroke              

																													//vars for polygon
  this.handler_dot = this.handler_sqr = this.handler_clk = -1;
  this.poly_temp = '';
  this.freehand = false;
  this.angle1, this.angle2, this.distn, this.dx, this.dy;
  this.poly_temp_points = new Array(0,0,0,0,0,0,0,0,0,0); //first point, second last point, last point, 
																													//last down, last up ... mouse points
	this.def_colour_panel_parts();
	this.yOffset ; 						                 							//coords of everything made in here include toolbar 
  this.is_an_answer = false;
  this.q_Num;
	this.doorId;

	this.currentColours = Array('#FFFFFF','#3F3F3F','#000000','#FF0000');  // fill, line, text colours
	this.colorReference = new Array();
	this.lineThickness  = 1; 									              // current thickness of borders around draggable 
																													// labels and manually drawn lines / arrows (in pixels) 
	this.fontSizes  = Array(11, 12, 14, 16, 18, 20, 22); 	  // font size equivalent in Flash (not standard sizes)
	this.fontSizePos    = 1; 									              // current font size for labels (index from array above);
	this.dragging = false;
	this.redraw_once = false;
	this.gen_img, this.menu_img;
	this.gen_img_loaded = false;
	this.menu_img_loaded = false;
	this.loc_lft = this.loc_top = 0;
  this.mov_id = -1;
  this.canvas;
	this.context;
  this.canv_rect;
  this.marks_per_correct = 1;
  this.marks_per_incorrect = 0;
  this.marking_method = 'Mark per Option';
  this.qmode;
	this.extra;
	this.exclusions = '00000000000000000000';
	this.display_students_response = true;
	this.display_correct_answer = true;
  this.imgdata,this.imgdatab,this.imgdatac;
	this.keypressed = false;
	this.imageerrordisplay = 0;
}