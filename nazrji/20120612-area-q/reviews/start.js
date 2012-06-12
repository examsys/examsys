function refreshparent() {
  window.opener.location.reload();
}
function dismissItem(itemID) {
  if (document.getElementById(itemID).className == "active") {
    document.getElementById(itemID).className = "inactive";
  } else {
    document.getElementById(itemID).className = "active";
  }
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
function confirmSubmit() {
  if (document.questions.button_pressed.value == 'finish') {
    var agree = confirm("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.");
    if (agree) {
      document.body.style.cursor = 'wait';
      return true;
    } else {
      return false;
    }
  } else {
    return true;
  }
}
function checkMRQ(questionid,part_id,options_total,selectable) {
  checked_total = 0;
  for (i=1; i<=options_total; i++) {
    currentid = "q" + questionid + "_" + i;
    if (document.getElementById(currentid).checked == 1) {
      checked_total++;
    }
  }
  if (checked_total > selectable) {
    alert("WARNING: Too many options selected. Only " + selectable + " items can be selected.");
    document.getElementById("q" + questionid + "_" + part_id).checked = 0;
  }
}
function rankCheck(questionid,part_id,options_total,selectable) {
  checked_total = 0;
  duplicate = 0;
  current_value = document.getElementById('q' + questionid + '_' + part_id).value;
  for (i=1; i<=options_total; i++) {
    currentid = "q" + questionid + "_" + i;
    if (document.getElementById(currentid).value < 9990) {
      checked_total++;
    }
    if (i != part_id && current_value < 9990) {
      if (document.getElementById(currentid).value == current_value) {
        duplicate = 1;
      }
    }
  }
  if (checked_total > selectable) {
    alert("WARNING: Too many options selected. Only " + selectable + " items can be selected.");
    document.getElementById('q' + questionid + '_' + part_id).value = 9990;
  } else if (duplicate == 1) {
    alert("WARNING: You have already selected " + current_value + ". Please select a different ranking.");
    document.getElementById('q' + questionid + '_' + part_id).value = 9990;    
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
    alert("WARNING: Too many options selected. Only " + selectable + " items can be selected.");
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
function openCalculator() {
  var browserName=navigator.appName;
  var browserVer=navigator.appVersion;
  ie7OK = browserVer.indexOf( 'MSIE 7.0' ) != -1;
  var leftFigure = document.documentElement.clientWidth - 280;
  //if (ie7OK) {
  //  window.showModelessDialog('../calc98/jcalc98.htm','','dialogTop:40;dialogLeft:'+leftFigure+';dialogHeight:324px;dialogWidth:250px;status:no;scroll:no;resizable:no;unadorned:no');
  //} else {
    if (typeof(calc) == 'object' && calc.closed != true) {
      calc.focus();
    } else {
      calc=window.open("../tools/calc98/jcalc98.php","calculator","width=250,height=391,top=10,left="+(document.documentElement.clientWidth-280)+"scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no");
      if (window.focus) {
        calc.focus();
      }
    }
  //}
}
function write_string(p_string) {
  document.write(p_string);
}
