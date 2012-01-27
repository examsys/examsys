$(function () {
  $('.rankselect').change(rankCheck);
});

function refreshparent() {
  window.opener.location.reload();
}
function onoff(questionID, itemID) {
  objID = questionID + '_' + itemID;
  if (document.getElementById(objID).className == "act") {
    document.getElementById(objID).className = "inact";
    setting = '1';
  } else {
    document.getElementById(objID).className = "act";
    setting = '0';
  }
  objID = 'dismiss' + questionID;
  current_value = document.getElementById(objID).value;
  new_value = current_value.slice(0,itemID-1) + setting + current_value.slice(itemID,current_value.length);
  document.getElementById(objID).value = new_value;
}

// please keep these lines on when you copy the source
// made by: Nicolas - http://www.javascript-page.com
var clockID = 0;
function UpdateClock() {
  if(clockID) {
    clearTimeout(clockID);
    clockID  = 0;
  }
  var tDate = new Date();
  document.getElementById('theTime').value = "" + ((tDate.getHours() < 10) ? "0" : "") + tDate.getHours() +
    ((tDate.getMinutes()  < 10) ? ":0" : ":") + tDate.getMinutes() +
    ((tDate.getSeconds() < 10) ? ":0" : ":") + tDate.getSeconds();
    clockID = setTimeout("UpdateClock()", 1000);
}
function StartClock() {
  clockID = setTimeout("UpdateClock()", 500);
}
function KillClock() {
  if(clockID) {
    clearTimeout(clockID);
    clockID  = 0;
  }
}
function MRQ(questionid,part_id,options_total,selectable) {
  checked_total = 0;
  for (i=1; i<=options_total; i++) {
    currentid = "q" + questionid + "_" + i;
    if (document.getElementById(currentid).checked == 1) {
      checked_total++;
    }
  }
  if (checked_total > selectable) {
	alert(lang['msgselectable1'] + ' ' + selectable + ' ' + lang['msgselectable2']);
    document.getElementById("q" + questionid + "_" + part_id).checked = 0;
  }
}
function rankCheck() {
  var sel = $(this).val();    
  var classlist =  '.' + $(this).attr('class').replace(' ', '.');
  var count = 0;
  var loopSel = '';
  
  $(classlist).each(function () {
    loopSel = $(this).val();
    if(loopSel != '0' && loopSel != 'u' && loopSel == sel) count++;
  });
  if (count > 1) {
    alert(lang['msgselectable3'] + ' ' + sel  + lang['msgselectable4']);
    $(this).val('u');
  }
}

function multimatchingCheck(questionid,options_total,selectable) {
  checked_total = 0;
  for (i=0; i<options_total; i++) {
    if (document.getElementById(questionid).options[i].selected == 1) {
      checked_total++;
    }
  }
  tmp_count = 0;
  if (checked_total > selectable) {
    alert(lang['msgselectable1'] + ' ' + selectable + ' ' + lang['msgselectable2']);
	
    for (i=0; i<options_total; i++) {
      if (document.getElementById(questionid).options[i].selected == 1) {
        tmp_count++;
      }
      if (tmp_count > selectable) {
        document.getElementById(questionid).options[i].selected = 0;
      }
    }
  }
}
function openCalc(obj_control,obj_control1,obj_control2) {
  if (typeof(calc) == 'object' && calc.closed != true) {
    calc.focus();
  } else {
//    calc=window.open("../tools/calc98/jcalc98.htm","calculator","width=250,height=331,top=10,left="+(document.documentElement.clientWidth-280)+"scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no");
    calc=window.open("../tools/sCal-8-9/sCal-09.php?calc="+ obj_control +"&form=" + obj_control1 + "&field=" + obj_control2,"win_ch", "width=" + 320 + ",height=" + 500 + ",help=no,status=no,scrollbars=no,resizable=no,toolbar=no,location=no,scrollbars=no,directories=no,status=no,menubar=no,resizable=no,location=no,directories=no,status=no,menubar=no,top=10,left=" + (document.documentElement.clientWidth-350) + ",dependent=yes,alwaysRaised=yes", true);
    if (window.focus) {
      calc.focus();
    }
  }
}

function openCalc2() {
  if (typeof(calc) == 'object' && calc.closed != true) {
    calc.focus();
  } else {
    calc=window.open("../tools/calc98/jcalc98.htm","calculator","width=250,height=331,top=10,left="+(document.documentElement.clientWidth-280)+"scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no");
    if (window.focus) {
      calc.focus();
    }
  }
}

function openLink(url,name,width,height) {
  if (typeof (doc) == 'object' && doc.closed != true) {
    doc.focus();
  } else {
    doc = window.open(url, name, "width=" + width + ",height=" + height + ",top=10,left="+(document.documentElement.clientWidth-280)+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
    if (window.focus) {
      doc.focus();
    }
  }
}
function write_string(p_string) {
  document.write(p_string);
}
var DragMath = Array();
function saveMath() {
  for (var i=0; i<DragMath.length; i++) {
	var applet = document.getElementById('DragMath_' + DragMath[i]);
    var input = document.getElementById(DragMath[i]);
	input.value = applet.getMathExpression();
  }
}

