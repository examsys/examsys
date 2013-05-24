// JavaScript Document
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
//
// Hofstee plot
//
// @author Nikodem Miranowicz
// @version 1.0
// @copyright Copyright (c) 2013 The University of Nottingham
// @package
//

var graph_x = 200;
var graph_y = 50;
var graph_w = 500;
var graph_h = 500;
var dragging = false;
var redraw = true;
var boundaries = [];
var temp_boundaries = [];
var active_line = 0;
var a1,a2,b1,b2;
var tx1,tx2,ty1,ty2,xs,ys;
var d_x = 40;
var d_y = 200;
var x,y,i,j,delta;

//recalculating data
var graph_data = [];
var data,last_data = 0;
j=-1;
for (i=0;i<marks.length;i++) {
	data = 1*marks[i];
	if (data!=last_data) j++;
	graph_data[j] = [data,(i+1)*100/marks.length,i];
	last_data =  data;
	}	
	
temp_boundaries = [marks[0],marks[marks.length-1],100/marks.length,100];
boundaries = [marks[0],marks[marks.length-1],100/marks.length,100];
canvas = document.getElementById('canvas_graph');

if (canvas && canvas.getContext){
	canvas.onmouseup     = g_mouseDragUp;
	canvas.onmousedown = g_mouseDragDown;
	canvas.onmousemove = g_mouseDragMove;
	var intervalID = window.setInterval(g_redraw_canvas, 10);
}

if (canvas && !canvas.getContext){
	alert ('Canvas not supported');
}

if (canvas && canvas.getContext){
	context = canvas.getContext('2d');
}

function drawLine(cc,xx,yy,ww,hh) {
	context.strokeStyle = cc;
	context.beginPath();
	context.moveTo(xx-0.5,yy-0.5);
	context.lineTo(xx+ww-0.5,yy+hh-0.5);
	context.stroke();
}
	
function g_redraw_canvas() {
	function act(line_nr) {
		if (line_nr==active_line) {
			context.shadowColor = '#F88';
			context.shadowBlur = 5;
		} else {
			context.shadowColor = 'white';
			context.shadowBlur = 0;
		}
	}
	
	if (dragging || redraw) {
		context.clearRect(0,0,canvas.width,canvas.height);
		context.shadowColor = 'white';
		context.shadowBlur = 0;
		 
		//drawing a graph  
		context.lineWidth = 1;
		drawLine('#000',graph_x,canvas.height-graph_y,0,-graph_h);
		drawLine('#000',graph_x,canvas.height-graph_y,graph_w,0);
		for (i=1;i<=10;i++) {
			drawLine('#DDD',graph_x,canvas.height-graph_y-i*50,graph_w,0);
		}
		var ty = canvas.height-graph_y;
		var ty_old = ty;
		context.beginPath();
		context.strokeStyle = '#000';
		context.moveTo(graph_x,ty-0.5);
		for (i=0;i<graph_data.length;i++) {
			ty=canvas.height-graph_y-5*graph_data[i][1];
			if (ty != ty_old) context.lineTo(graph_x+graph_data[i][0]*5,ty-0.5);
			ty_old = ty;
		}
		context.stroke();
		delta=0.1; if (document.getElementById('checkbox').checked) delta = 1;
		if (delta==1) {
			for (i=0;i<4;i++) {
				boundaries[i] = Math.round(boundaries[i]);
			}
		}
		//position of boudary lines
		var x1 = graph_x+graph_w/100*boundaries[0];
		var x2 = graph_x+graph_w/100*boundaries[1];
		var y3 = canvas.height-graph_y-graph_h/100*boundaries[2];
		var y4 = canvas.height-graph_y-graph_h/100*boundaries[3];
										 
		//standing labels
		context.font="bold 13px Arial";
		context.textAlign="center";
		context.fillText(lang_correct,graph_x+graph_w/2,canvas.height-graph_y+35);
		context.save();
		context.rotate(-Math.PI/2);
		context.fillText(lang_cohort,-graph_h/2-graph_y,graph_x-45);
		context.restore();
		
		//graph ticks and labels
		context.font="11px Arial";
		context.textAlign="right";
		for (i=0;i<=10;i++) {
			drawLine('#000',graph_x,canvas.height-graph_y-50*i,-5,0);
			context.fillText(i*10+'%',graph_x-10,canvas.height-graph_y-50*i+3);
		}
		context.textAlign="center";
		for (i=0;i<=10;i++) {
			drawLine('#000',graph_x+50*i,canvas.height-graph_y,0,5);
			context.fillText(i*10+'%',graph_x+50*i+5,canvas.height-graph_y+15);
		}
		
		//moving labels
		context.font="13px Arial";
		context.fillStyle = '#000';
		context.textAlign="center";
		var divert = 0;
		if (Math.abs(x1-x2)<50) divert = (50-Math.abs(x1-x2))/2;
		if (divert > 15) divert = 15;
		context.fillText(Math.round(boundaries[0]*10)/10+'%',x1,canvas.height-graph_y-graph_h-5);
		context.fillText(Math.round(boundaries[1]*10)/10+'%',x2,canvas.height-graph_y-graph_h-5-divert);
		context.textAlign="right";
		divert = 0;
		if (Math.abs(y3-y4)<25) divert = -4*(25-Math.abs(y3-y4));
		if (divert < -40) divert = -40;
		context.fillText(Math.round(boundaries[2]*10)/10+'%',graph_x+graph_w+40,y3+5);
		context.fillText(Math.round(boundaries[3]*10)/10+'%',graph_x+graph_w+40-divert,y4+5);      
		
		//drawing cyan line
		drawLine('#00C0C0',x1,y4,x2-x1,y3-y4);
		
		//searching for intersection
		//a and b for the first line
		a1=0;b1=x2;
		if (x2!=x1) {
			a1 = (y3-y4)/(x2-x1);
			b1 = y4 - a1*x1;
		}
		tx2 = graph_x;
		ty2 = canvas.height-graph_y;
		xs = ys = '';
		for (i=0;i<graph_data.length;i++) {
			tx1 = graph_x+graph_data[i][0]*5;
			ty1 = canvas.height-graph_y-5*graph_data[i][1];
			
			//a and b for the second line
			a2=0;b2=tx1;
			if (tx2!=tx1) {
				a2 = (ty2-ty1)/(tx2-tx1);
				b2 = ty1 - a2*tx1;
			}
					
			if (a1 != a2) {
				cx = (b2 - b1)/(a1 - a2);
				if (tx2==tx1) cx=tx1;
				cy = a1*cx + b1;
			}	
			
			if ((cx>=tx2) && (cx<=tx1) && ((cy>=ty2 && cy<=ty1) || (cy>=ty1 && cy<=ty2))) {
				if (cx>=x1 && cx<=x2 && cy>=y4 && cy<=y3) {
					xs = Math.round((cx-graph_x)/5*10)/10;
					ys = Math.round(-(cy-canvas.height+graph_y)/5*10)/10;
					for (j=1;j<ys;j++) drawLine('#00C0C0',cx,canvas.height-graph_y-5*j,0,3);      
					for (j=0;j<xs;j++) drawLine('#00C0C0',graph_x+5*j,cy,3,0);      
					xs+='%';
					ys+='%';
					if (xs == undefined) xs = '';
					if (ys == undefined) ys = '';
				}
			}
			tx2 = tx1;
			ty2 = ty1;
		}
		
		//displaying data 
		context.strokeStyle = '#ddd';
		context.strokeRect(d_x-20+0.5,d_y-25+0.5,100,150); 
		
		//outside labels
		context.font="13px Arial";
		context.textAlign="left";
		context.fillText('x1 = '+Math.round(boundaries[0]*10)/10+'%',d_x,d_y+00);      
		context.fillText('x2 = '+Math.round(boundaries[1]*10)/10+'%',d_x,d_y+20);      
		context.fillText('y1 = '+Math.round(boundaries[2]*10)/10+'%',d_x,d_y+40);      
		context.fillText('y2 = '+Math.round(boundaries[3]*10)/10+'%',d_x,d_y+60);      
		context.fillText('xs = '+xs,d_x,d_y+90);
		context.fillText('ys = '+ys,d_x,d_y+110);      
		
		//textboxes outside canvas
		document.getElementById('x1').value = Math.round(boundaries[0]*10)/10+'%';
		document.getElementById('x2').value = Math.round(boundaries[1]*10)/10+'%';
		document.getElementById('y1').value = Math.round(boundaries[2]*10)/10+'%';
		document.getElementById('y2').value = Math.round(boundaries[3]*10)/10+'%';
		document.getElementById('xs').value = xs;
		document.getElementById('ys').value = ys;

		//drawing boundaries
		var gap = 0;
		act(1);
		drawLine('#c00000',Math.round(x1),canvas.height-graph_y-gap,0,-graph_h+2*gap);      
		act(2);
		drawLine('#c00000',Math.round(x2),canvas.height-graph_y-gap,0,-graph_h+2*gap);      
		act(3);
		drawLine('#c00000',graph_x+gap,Math.round(y3),graph_w-2*gap,0);
		act(4);
		drawLine('#c00000',graph_x+gap,Math.round(y4),graph_w-2*gap,0);

	}
	redraw = false;
}

function testWithin(ax,ay,bx,by,cx,cy) {
	var testres = false;
	if ((ax > bx) && (ax < (bx + cx)) && (ay > by) && (ay < (by + cy))) testres = true;
	return testres;
}

function g_mouseDragUp(e) {
	dragging = false;
	if (testWithin(x,y,d_x-5,d_y-15+00,20,20)) {boundaries[0] = temp_boundaries[0];redraw = true;g_redraw_canvas;}
	if (testWithin(x,y,d_x-5,d_y-15+20,20,20)) {boundaries[1] = temp_boundaries[1];redraw = true;g_redraw_canvas;}
	if (testWithin(x,y,d_x-5,d_y-15+40,20,20)) {boundaries[2] = temp_boundaries[2];redraw = true;g_redraw_canvas;}
	if (testWithin(x,y,d_x-5,d_y-15+60,20,20)) {boundaries[3] = temp_boundaries[3];redraw = true;g_redraw_canvas;}
}

function g_mouseDragDown(e) {
	dragging = true;
}

function g_mouseDragMove(e) {
	rect = canvas.getBoundingClientRect();
	loc_lft = rect.left;
	loc_top = rect.top;
	var xm = e.clientX;
	var ym = e.clientY;
	x = xm - loc_lft;
	y = ym - loc_top; 

	if (dragging) {
		if (active_line==1 || active_line==2) boundaries[active_line-1] = (x-graph_x)/graph_w*100;
		if (active_line==3 || active_line==4) boundaries[active_line-1] = 100-(y-graph_y)/graph_h*100;
		if (boundaries[active_line-1]>100) boundaries[active_line-1] = 100;
		if (boundaries[active_line-1]<0) boundaries[active_line-1] = 0;
		
		//swap active lines if dragging over
		if (boundaries[1]<boundaries[0]) {
			boundaries[1] = [boundaries[0], boundaries[0] = boundaries[1]][0];
			active_line = 3 - active_line;
		} 
		if (boundaries[3]<boundaries[2]) {
			boundaries[3] = [boundaries[2], boundaries[2] = boundaries[3]][0];
			active_line = 7 - active_line;
		}       
		if (active_line>-1) g_redraw_canvas;
	} else {
		var old_active_line = active_line;
		active_line = 0;
		var treshold = 3
		if (Math.abs(graph_x+graph_w/100*boundaries[0]-x)<treshold) active_line = 1;
		if (Math.abs(graph_x+graph_w/100*boundaries[1]-x)<treshold) active_line = 2;
		if (Math.abs(canvas.height-graph_y-graph_h/100*boundaries[2]-y)<treshold) active_line = 3;
		if (Math.abs(canvas.height-graph_y-graph_h/100*boundaries[3]-y)<treshold) active_line = 4;
		
		var cur = 'default';
		if (active_line>0) cur = 'col-resize';
		if (active_line>2) cur = 'row-resize';
		e.target.style.cursor = cur;
		
		if (active_line!=old_active_line) {
			redraw = true;    
			g_redraw_canvas;
		}
	}
}

function tfchange(event,keys) {
	target = ((event.target.name[0]=='x')?0:2)+1*event.target.name[1]-1;
	var ev0 = boundaries[target];
	var ev = Number(event.target.value.replace('%',''));
	if (isNaN(ev)) ev = ev0;
	
	if (keys) {
		if (event.keyCode==37 || event.keyCode==40) ev-=delta;
		if (event.keyCode==38 || event.keyCode==39) ev+=delta;
	}
	
	if (ev<0) ev = 0;
	if (ev>100) ev = 100;
	boundaries[target] = ev;
	if (boundaries[1]<boundaries[0]) boundaries[1] = [boundaries[0], boundaries[0] = boundaries[1]][0];
	if (boundaries[3]<boundaries[2]) boundaries[3] = [boundaries[2], boundaries[2] = boundaries[3]][0];
	
	redraw = true;
	g_redraw_canvas;	
}

$(".tf").keypress(function(event) {
	if (event.keyCode<=40 && event.keyCode>=37) tfchange(event,true);
});
	
$(".tf").blur(function(event) {
	tfchange(event,false);
});
	