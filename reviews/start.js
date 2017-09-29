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
    
    var agree = confirm(lang['javacheck2']);
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

function MRQ(questionid, part_id, options_total, selectable) {
	var abstainExist = document.getElementById("q" + questionid + "_abstain");
	if (abstainExist != null) {
		$("#q" + questionid + "_abstain").prop("checked", false);
	}
	
  checked_total = 0;
  for (i=1; i<=options_total; i++) {
    currentid = "q" + questionid + "_" + i;
    if ($('#' + currentid).prop("checked")) {
      checked_total++;
    }
  }
  if (checked_total > selectable) {
		alert(lang['msgselectable1'] + ' ' + selectable + ' ' + lang['msgselectable2']);
		$("#q" + questionid + "_" + part_id).prop("checked", false);
  }
}

function MRQabstain(questionid, options_total) {
  for (i=1; i<=options_total; i++) {
		$("#q" + questionid + "_" + i).prop("checked", false);
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
    alert(lang['msgselectable1'] + selectable + lang['msgselectable2']);
    document.getElementById('q' + questionid + '_' + part_id).value = 9990;
  } else if (duplicate == 1) {
    alert(lang['msgselectable3'] + current_value + lang['msgselectable4']);
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
    alert(lang['msgselectable1'] + selectable + lang['msgselectable2']);
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
function write_string(p_string) {
  document.write(p_string);
}
