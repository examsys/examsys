<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Class total report
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/class_totals.inc';
  
  ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>TouchStone: Class Totals<?php echo " $cfg_install_type"; ?></title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
a.user {color:black}
a.user:hover {color:white; background-color:#000080}
.h {background-color:#F1F5FB; color:black}
.breadcrumb {margin-top:2px; margin-left:10px; font-size:90%}
.breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
</style>

<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
  var ie  = document.all
  var ns6 = document.getElementById&&!document.all
  var isMenu  = false ;
  var menuSelObj = null ;
  var overpopupmenu = false;
  function mouseSelect(e) {
    var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
    if (isMenu) {
      if (overpopupmenu == false) {
        isMenu = false ;
        overpopupmenu = false;
        document.getElementById('menudiv').style.display = 'none';
        return true ;
      }
      return true ;
    }
    return false;
  }
  // POP UP MENU
  function ItemSelMenu(tmpStarted, tmpUserID, tmpUsername, tmpDisplayName, tmpLogType, tmpReassign, tmpLogLate, tmpPercent, e) {
    if (!e) var e = window.event;
	var currentX = e.clientX;
	var currentY = e.clientY;
    var scrOfX = getScrollX();
	var scrOfY = getScrollY();

    document.getElementById('started').value = tmpStarted;
    document.getElementById('userID').value = tmpUserID;
    document.getElementById('username').value = tmpUsername;
    document.getElementById('display_name').value = tmpDisplayName;
    document.getElementById('log_type').value = tmpLogType;
    document.getElementById('reassign').value = tmpReassign;
    document.getElementById('loglate').value = tmpLogLate;
    document.getElementById('percent').value = tmpPercent;

    document.getElementById('menudiv').style.left = currentX+scrOfX + 'px';
    document.getElementById('menudiv').style.top = currentY+scrOfY + 'px';

    document.getElementById('menudiv').style.display = "";
    document.getElementById('item1b').style.backgroundColor='#FFFFFF';
    document.getElementById('item2b').style.backgroundColor='#FFFFFF';
    document.getElementById('item3b').style.backgroundColor='#FFFFFF';
    document.getElementById('item4b').style.backgroundColor='#FFFFFF';
    document.getElementById('item5b').style.backgroundColor='#FFFFFF';

    if (tmpReassign == 'y') {
      document.getElementById('item5b').style.color='#000000';
    } else {
      document.getElementById('item5b').style.color='#C0C0C0';
    }

    if (tmpLogLate == 'y') {
      document.getElementById('item6b').style.color='#000000';
    } else {
      document.getElementById('item6b').style.color='#C0C0C0';
    }

    isMenu = true;
    return false ;
  }

  function getScrollXY() {
    var scrOfX = 0, scrOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrOfY = window.pageYOffset;
      scrOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrOfY = document.body.scrollTop;
      scrOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrOfY = document.documentElement.scrollTop;
      scrOfX = document.documentElement.scrollLeft;
    }
    parent.frames['menu'].document.PapersMenu.scrOfY.value = scrOfY;
  }

  function menuRowOn(rowID) {
    // Left menu column
    document.getElementById('item'+rowID+'a').style.backgroundColor='#FFE7A2';
    document.getElementById('item'+rowID+'a').style.borderTop='1px solid #FFBD69';
    document.getElementById('item'+rowID+'a').style.borderBottom='1px solid #FFBD69';
    document.getElementById('item'+rowID+'a').style.borderLeft='1px solid #FFBD69';

    // Right menu column
    document.getElementById('item'+rowID+'b').style.backgroundColor='#FFE7A2';
    document.getElementById('item'+rowID+'b').style.borderTop='1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderBottom='1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderRight='1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderLeft='1px solid #FFE7A2';
  }

  function menuRowOff(rowID) {
    // Left menu column
    document.getElementById('item'+rowID+'a').style.backgroundColor='#F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderTop='1px solid #F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderBottom='1px solid #F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderLeft='1px solid #F1F5FB';

    // Right menu column
    document.getElementById('item'+rowID+'b').style.backgroundColor='#FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderTop='1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderBottom='1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderRight='1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderLeft='1px solid #FFFFFF';
  }

  function confirmSubmit() {
    var agree = confirm("Are you sure you want to email everyone on this list their marks?");
    if (agree)
      return true;
    else
      return false;
  }

  function viewNote2(userID) {
    window.open("display_note.php?paperID=<?php echo $paperID; ?>&userID="+userID+"","note","width=400,height=300,left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  }

  function popupEmailTemplate() {
    var winwidth = 785;
    var winheight = 550;
    templatewin = window.open("emailtemplate.php","templatewin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    templatewin.moveTo(screen.width/2-350,screen.height/2-275);
  }

  function viewScript() {
    document.getElementById('menudiv').style.display = 'none';
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    window.open("../paper/finish.php?paperID=<?php echo $paperID; ?>&previous=" + document.getElementById('started').value + "&userid=" + document.getElementById('userID').value + "&surname=" + document.getElementById('display_name').value + "&log_type=" +document.getElementById('log_type').value+ "&percent=" +document.getElementById('percent').value+ "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  }

  function viewFeedback() {
    document.getElementById('menudiv').style.display = 'none';
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    window.open("../mapping/user_feedback.php?paperID=<?php echo $paperID; ?>&userID=" + document.getElementById('userID').value + "&started=" + document.getElementById('started').value + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  }

  function viewProfile() {
    document.getElementById('menudiv').style.display = 'none';
    window.top.location = '../users/details.php?paperID=<?php echo $paperID; ?>&userID=' + document.getElementById('userID').value;
  }

  function newStudentNote() {
    document.getElementById('menudiv').style.display = 'none';
    note = window.open("../users/new_student_note.php?userID=" + document.getElementById('userID').value + "&paperID=<?php echo $paperID; ?>&display_name=" + document.getElementById('display_name').value + "&calling=class_totals","note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      note.focus();
    }
  }

  function reassignScript() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('reassign').value == 'n') {
      alert("Only temporary accounts may be reassigned.");
      return false;
    } else {
      reassign = window.open("check_reassign_script.php?userID=" + document.getElementById('userID').value + "&paperID=<?php echo $paperID; ?>&started=" + document.getElementById('started').value + "&log_type=" + document.getElementById('log_type').value + "","reassign","width=600,height=500,left="+(screen.width/2-300)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        reassign.focus();
      }
    }
  }
  
  function reassignLogLate() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('loglate').value == 'n') {
      alert("This student does not have any late answer submissions.");
      return false;
    } else {
      loglate = window.open("check_reassign_log_late.php?userID=" + document.getElementById('userID').value + "&paperID=<?php echo $paperID; ?>&started=" + document.getElementById('started').value + "&log_type=" + document.getElementById('log_type').value + "","reassign","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        reassign.focus();
      }
    }
  }

  function viewNote(userID, e) {
    if (!e) var e = window.event;
	  var currentX = e.clientX;
	  var currentY = e.clientY;
    var scrOfX = getScrollX();
	  var scrOfY = getScrollY();
	
	  var XMLHttpRequestObject = false; 

    if (window.XMLHttpRequest) {
      XMLHttpRequestObject = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
      XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
    }

    if (XMLHttpRequestObject) {
      dataSource = "getNote.php?paperID=<?php echo $paperID; ?>&userID=" + userID;
      XMLHttpRequestObject.open("GET", dataSource); 

      XMLHttpRequestObject.onreadystatechange = function() { 
        if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) { 
          document.getElementById('noteMsg').innerHTML = XMLHttpRequestObject.responseText;
          document.getElementById('noteDiv').style.display="block";
          document.getElementById('noteDiv').style.left = currentX+scrOfX+16 + 'px';
          document.getElementById('noteDiv').style.top = currentY+scrOfY-16 + 'px';
          delete XMLHttpRequestObject;
          XMLHttpRequestObject = null;
        }
      } 
      XMLHttpRequestObject.send(null); 
    }
  }
  
  function getScrollX() {
    var scrollOfX = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrollOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrollOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrollOfX = document.documentElement.scrollLeft;
    }
	return scrollOfX;
  }
  
  function getScrollY() {
    var scrollOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrollOfY = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrollOfY = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrollOfY = document.documentElement.scrollTop;
    }
	return scrollOfY;
  }

  document.onmousedown = mouseSelect;
</script>
</head>

<body>
<div id="noteDiv" style="position:absolute; background-color:#FDFDCB; top:0px; left:0px; width:350px; z-index:10000; display:none">
<div style="background-color:#F8F7B6; text-align:right; padding:2px"><img onclick="document.getElementById('noteDiv').style.display='none'" src="../artwork/close_note.png" width="16" height="16" alt="Close" border="0" style="cursor:pointer" /></div>
<div id="noteMsg"></div>
</div>

<div id="menudiv" style="filter: progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=3); position:absolute; display:none; top:0px; left:0px;z-index:10000;" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table width="160" cellspacing="2" cellpadding="0" border="0" style="border:1px solid #6593CF; font-size:90%; background-color:white">
  <tr><td>
    <table width="160" cellspacing="0" cellpadding="1" border="0" style="font-size:100%; background-color:white">
      <tr>
        <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><img src="/touchstone/artwork/summative_16.gif" width="16" height="16" alt="" border="0" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();">Exam Script</td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><img src="/touchstone/artwork/ok_comment.png" width="16" height="16" alt="" border="0" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();">Feedback</td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right"><img src="/touchstone/artwork/popup_divider.png" width="100%" height="3" border="0" alt="-" /></td>
      </tr>
      <tr>
        <td id="item3a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();"><img src="/touchstone/artwork/small_user_icon.gif" width="16" height="16" alt="" border="0" /></td><td id="item3b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();">Student Profile</td>
      </tr>
      <tr>
        <td id="item4a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="newStudentNote();"><img src="/touchstone/artwork/notes_icon.gif" width="14" height="14" alt="" border="0" /></td><td id="item4b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="newStudentNote();">New Note...</td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right"><img src="/touchstone/artwork/popup_divider.png" width="100%" height="3" border="0" alt="-" /></td>
      </tr>
      <tr>
        <td id="item5a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('5');" onmouseout="menuRowOff('5');" onclick="reassignScript();">&nbsp;</td><td id="item5b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('5');" onmouseout="menuRowOff('5');" onclick="reassignScript();">Re-assign to User...</td>
      </tr>
      <tr>
        <td id="item6a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('6');" onmouseout="menuRowOff('6');" onclick="reassignLogLate();">&nbsp;</td><td id="item6b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('6');" onmouseout="menuRowOff('6');" onclick="reassignLogLate();">Late Submissions</td>
      </tr>
    </table>
  </td></tr>
</table>
</div>
<?php
  for ($i=1; $i<=100; $i++) $distribution[$i] = 0;

  $notes = array();
  // Query any student notes for the current paper
  $result = $mysqli->prepare("SELECT userID FROM student_notes WHERE paper_id=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($row = $result->fetch()) {
    $notes[$tmp_userID] = 'y';
  }
  $result->close();

  $special_needs = array();
  // Query any student special needs for the current paper
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($special_userID);
  while ($row = $result->fetch()) {
    $special_needs[$special_userID] = 'y';
  }
  $result->close();

  $log_late = array();
  // Check log_late for any records
  $result = $mysqli->prepare("SELECT DISTINCT userID, title, surname, first_names FROM log_late, users WHERE log_late.userID=users.id AND q_paper=? AND started>? ORDER BY surname, initials");
  $result->bind_param('is', $paperID, $startdate);
  $result->execute();
  $result->bind_result($userID, $title, $surname, $first_names);
  while ($row = $result->fetch()) {
    $log_late[$userID] = $title . ' ' .  $surname . ', ' . $first_names;
  }
  $result->close();
  
  if ($marking == '0') {
    $marking_label = '%';
  } else {
    $marking_label = 'Adjusted %';
  }
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  if ($paper_type == 2) {
    echo "<tr><td class=\"h\" colspan=\"10\">";
  } else {
    echo "<tr><td class=\"h\" colspan=\"9\">";
  }
  if(isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = 'Class Totals (' . $_GET['repmodule'] . ' students only)';
  } else {
    $report_title = 'Class Totals';
  }
  
  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }
  echo '<div class="breadcrumb"><a href="../index.php">Home</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';
  
  echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">$report_title</span></td><td class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";

  // Name
  echo '<tr><td class="h" style="width:16px">&nbsp;</td><td class="h"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;';
  if ($sortby == 'name' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=name&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Name</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'name' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=name&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Name</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=name&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Name</a>&nbsp;</td>";
  }

  // Student ID
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'student_id' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=student_id&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Student ID</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'student_id' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=student_id&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Student ID</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=student_id&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Student ID</a>&nbsp;</td>";
  }

  // Course
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'student_grade' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=student_grade&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Course</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'student_grade' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=student_grade&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Course</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=student_grade&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Course</a>&nbsp;</td>";
  }

  // Mark
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'mark' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=mark&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Mark</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'mark' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=mark&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Mark</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=mark&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Mark</a>&nbsp;</td>";
  }

  // Percent
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'percent' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=percent&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">$marking_label</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'percent' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=percent&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">$marking_label</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=percent&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">$marking_label</a>&nbsp;</td>";
  }

  // Result
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'result' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=result&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Classification</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'result' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=result&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Classification</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=result&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Classification</a>&nbsp;</td>";
  }

  // Start time/date
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'started' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=started&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Start Time/Date</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'started' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=started&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Start Time/Date</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=started&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Start Time/Date</a>&nbsp;</td>";
  }

  // Duration
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'duration' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=duration&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Duration</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'duration' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=duration&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Duration</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=duration&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Duration</a>&nbsp;</td>";
  }

  // IP Address
  echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($sortby == 'ipaddress' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=ipaddress&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">IP Address</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } elseif ($sortby == 'ipaddress' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=ipaddress&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">IP Address</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=ipaddress&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">IP Address</a>&nbsp;</td>";
  }

  if ($paper_type == 2) {
    // Room (for summative exams)
    echo "<td class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
    if ($sortby == 'room' and $ordering == 'asc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=room&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">Room</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
    } elseif ($sortby == 'room' and $ordering == 'desc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=room&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Room</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td>";
    } else {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repdegree=" . $_GET['repdegree'] . "&module=" . $_GET['module'] . "&startdate=$startdate&enddate=$enddate&sortby=room&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">Room</a>&nbsp;</td>";
    }
  }

  echo '</tr>';
  echo '<tr style="height:4px"><td valign="top" colspan="11"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>';

  // Check for any temporary accounts and if so display warning banner
  $temp_user_no = 0;
  for ($i=0; $i<$user_no; $i++) {
    if (strpos($user_results[$i]['username'], 'user') === 0) {
      $temp_user_no++;
    }
  }
  if ($temp_user_no > 0) {
    echo "<tr><td style=\"height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/temp_account_warning.png\" style=\"padding-top:2px\" width=\"28\" height=\"28\" alt=\"Locked\" /></td><td colspan=\"10\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\">&nbsp;&nbsp;<strong>Temporary Accounts Warning</strong>&nbsp;&nbsp;&nbsp;Please reassign to the proper student accounts. <a href=\"#\" style=\"color:black\" onclick=\"launchHelp(185); return false;\">Click for more details.</a></td></tr>\n";
  }

  if (count($log_late) > 0) {
    echo "<tr><td style=\"width:40px; height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/late_warning_icon.png\" width=\"28\" height=\"28\" style=\"position:relative; left:0px; top:2px;\" alt=\"Warning\" />&nbsp;&nbsp;</td><td colspan=\"10\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><strong>Late Submissions</strong>&nbsp;&nbsp;&nbsp;Users have data saved after the end of the assessment (<a style=\"color:black\" href=\"#\" onclick=\"launchHelp(221); return false;\">Click for more details</a>): ";
    $html = '';
    foreach ($log_late as $student_userID => $student_name) {
      if ($html == '') {
        $html = $student_name;
      } else {
        $html .= ', ' . $student_name;
      }
    }
    echo "$html.</td></tr>\n";
  }

  $xmean_total = 0;
  $scatter_file = fopen("../temp/" . $_SERVER['PHP_AUTH_USER'] . "_scatter.dat", "w");              // Scatter plot data
  $absent_no = 0;
  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['visible'] == 1) {
      if (strpos($user_results[$i]['username'], 'user') !== 0) {
        $reassign = 'n';
      } else {
        $reassign = 'y';
      }
      if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
        $bg_color = '#FFC0C0';
        $line_color = '#EBEADB';
        echo "<tr style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\"><td>&nbsp;</td>";
        echo "<td style=\"padding:1px\">&nbsp;<a class=\"user\" href=\"../users/details.php?username=" . $user_results[$i]['username'] . "\">" . $user_results[$i]['title'] . "&nbsp;" . $user_results[$i]['surname'] . ",&nbsp;<span style=\"color:#808080\">" . $user_results[$i]['first_names'] . "</span></a>";
        if ($user_results[$i]['student_id'] == '') {
          echo "<td style=\"padding:1px; color:#808080\">&nbsp;&lt;Unknown&gt;</td>";
        } else {
          echo "<td style=\"padding:1px\">&nbsp;" . $user_results[$i]['student_id'] . "</td>";
        }
        echo "<td style=\"padding:1px\">&nbsp;" . $user_results[$i]['student_grade'] . "</td><td colspan=\"3\">&nbsp;</td><td style=\"padding:1px\">&nbsp;<strong>No Attendance</strong></td><td colspan=\"3\">&nbsp;</td></tr>\n";
        $absent_no++;
      } else {
        if (isset($log_late[$user_results[$i]['tmp_userID']])) {
          $late_submissions = 'y';
        } else {
          $late_submissions = 'n';
        }
        echo '<tr';
        if ($user_results[$i]['questions'] < $question_no) {
          fwrite($scatter_file,"0\n");
          fwrite($scatter_file,"0\n");
          $line_color = 'red';
          echo ' style="padding:1px"';
        } else {
          $line_color = '#EEEEEE';
          echo ' style="padding:1px"';
          $total_time += $user_results[$i]['duration'];
          $temp_location = $user_results[$i]['adj_percent'];
          if (isset($distribution[$temp_location])) {
            $distribution[$temp_location]++;
          } else {
            $distribution[$temp_location] = 1;
          }
          fwrite($scatter_file,$temp_location . "\n");
          fwrite($scatter_file,$user_results[$i]['duration'] . "\n");
        }
        if ($user_results[$i]['questions'] < $question_no) {
          echo "><td style=\"border-bottom:solid $line_color 1px\"><img src=\"../artwork/incomplete_paper_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning: not all screens completed\" border=\"0\" onclick=\"ItemSelMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['username'] . "','" . $user_results[$i]['title'] . " " . str_replace("'","&#8217;",$user_results[$i]['surname']) . ", " . $user_results[$i]['initials'] . " (" . $user_results[$i]['student_id'] . ")', '" . $user_results[$i]['paper_type'] . "', '$reassign', '$late_submissions', '" . $user_results[$i]['adj_percent'] . "', event);\" /></td>";
        } else {
          echo "><td style=\"border-bottom:solid $line_color 1px\">";
          if ($user_results[$i]['paper_type'] == 0) {
            echo '<img src="../artwork/formative_16.gif" width="16" height="16" alt="Display exam script for ' . $user_results[$i]['title'] . ' ' . $user_results[$i]['surname'] . '" border="0"';
          } elseif ($user_results[$i]['paper_type'] == '1') {
            echo '<img src="../artwork/progress_16.gif" width="16" height="16" alt="Display exam script for ' . $user_results[$i]['title'] . ' ' . $user_results[$i]['surname'] . '" border="0"';
          } elseif ($user_results[$i]['paper_type'] == '2') {
            echo '<img src="../artwork/summative_16.gif" width="16" height="16" alt="Display exam script for ' . $user_results[$i]['title'] . ' ' . $user_results[$i]['surname'] . '" border="0"';
          } elseif ($user_results[$i]['paper_type'] == '3') {
            echo '<img src="../artwork/survey_16.gif" width="16" height="16" alt="Display survey for ' . $user_results[$i]['title'] . ' ' . $user_results[$i]['surname'] . '" border="0"';
          } elseif ($user_results[$i]['paper_type'] == '5') {
            echo '<img src="../artwork/offline_16.gif" width="16" height="16" alt="Display survey for ' . $user_results[$i]['title'] . ' ' . $user_results[$i]['surname'] . '" border="0"';
          }
          echo " onclick=\"ItemSelMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['username'] . "','" . $user_results[$i]['title'] . " " . str_replace("'","&#8217;",$user_results[$i]['surname']) . "," . $user_results[$i]['initials'] . " (" . $user_results[$i]['student_id'] . ")', '" . $user_results[$i]['paper_type'] . "', '$reassign', '$late_submissions', '" . $user_results[$i]['adj_percent'] . "', event);\" /></td>";
        }
        if ($_GET['sortby'] == 'name') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        if (strpos($user_results[$i]['username'], 'user') === 0) {
          $bg_color = '#FFFF80';
          echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;<span style=\"cursor:hand\" onclick=\"ItemSelMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['username'] . "','" . $user_results[$i]['title'] . " " . str_replace("'","&#8217;",$user_results[$i]['surname']) . ", " . $user_results[$i]['initials'] . " (" . $user_results[$i]['student_id'] . ")', '" . $user_results[$i]['paper_type'] . "', '$reassign', '$late_submissions', '" . $user_results[$i]['adj_percent'] . "', event);\">" . str_replace('User','Temporary Account No. ',$user_results[$i]['surname']) . "</span>";
        } else {
          echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;<span style=\"cursor:hand\" onclick=\"ItemSelMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['username'] . "','" . $user_results[$i]['title'] . " " . str_replace("'","&#8217;",$user_results[$i]['surname']) . ", " . $user_results[$i]['initials'] . " (" . $user_results[$i]['student_id'] . ")', '" . $user_results[$i]['paper_type'] . "', '$reassign', '$late_submissions', '" . $user_results[$i]['adj_percent'] . "', event);\">" . $user_results[$i]['title'] . "&nbsp;" . $user_results[$i]['surname'] . ",&nbsp;<span style=\"color:#808080\">" . $user_results[$i]['first_names'] . "</span></span>";
        }
        if (isset($special_needs[$user_results[$i]['tmp_userID']]) and $special_needs[$user_results[$i]['tmp_userID']] == 'y') {
          echo '&nbsp;<img src="../artwork/accessibility_16.png" width="16" height="16" alt="Special Needs" border="0" />';
        }
        $student_id = $user_results[$i]['username'];
        if ($user_results[$i]['attempt'] > 1) {
          echo '&nbsp;<img src="../artwork/resit.png" width="16" height="16" alt="Resit" border="0" />';
        }
        if (isset($notes[$user_results[$i]['tmp_userID']]) and $notes[$user_results[$i]['tmp_userID']] == 'y') {
          echo '&nbsp;<a href="" onclick="viewNote(\'' . $user_results[$i]['tmp_userID'] . '\', event); return false;"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Notes" border="0" /></a>';
        }
        echo "</td>";
        if ($_GET['sortby'] == 'student_id') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        if ($user_results[$i]['student_id'] == '') {
          echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color; color:#808080\">&nbsp;&lt;Unknown&gt;</td>";
        } else {
          echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;" . $user_results[$i]['student_id'] . "</td>";
        }
        if ($_GET['sortby'] == 'student_grade') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;" . $user_results[$i]['student_grade'] . "</td>";
        if ($_GET['sortby'] == 'mark') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        if ($user_results[$i]['adj_percent'] < $pass_mark) {
          echo "<td align=\"right\" style=\"border-bottom:solid $line_color 1px; background-color:$bg_color; color:red\">";
          if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="Marking not complete" />&nbsp;';
          echo $user_results[$i]['mark'] . "</td>";
          echo "<td align=\"right\" style=\"border-bottom:solid $line_color 1px; background-color:$bg_color; color:red\">" . $user_results[$i]['adj_percent'] . "%</td><td style=\"border-bottom: solid $line_color 1px; color:red\">&nbsp;Fail</td>";
        } else {
          if ($user_results[$i]['adj_percent'] >= $distinction_mark) {
            echo "<td align=\"right\" style=\"border-bottom:solid $line_color 1px; background-color:$bg_color; color:#008000\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="Marking not complete" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td align=\"right\" style=\"border-bottom:solid $line_color 1px; background-color:$bg_color; color:#008000\">" . $user_results[$i]['adj_percent'] . "%</td><td style=\"border-bottom: solid $line_color 1px; color:#008000\">&nbsp;Distinction</td>";
          } else {
            echo "<td align=\"right\" style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="Marking not complete" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td align=\"right\" style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">" . $user_results[$i]['adj_percent'] . "%</td><td style=\"border-bottom: solid $line_color 1px\">&nbsp;Pass</td>";
          }
        }
        if ($_GET['sortby'] == 'started') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;" . $user_results[$i]['display_started'] . "</td>";
        if ($_GET['sortby'] == 'duration') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;" . formatsec($user_results[$i]['duration']) . "</td>";
        if ($_GET['sortby'] == 'ipaddress') {
          $bg_color = '#F7F7F7';
        } else {
          $bg_color = 'white';
        }
        echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;" . $user_results[$i]['ipaddress'] . "</td>";
        if ($paper_type == 2) {
          if ($_GET['sortby'] == 'room') {
            $bg_color = '#F7F7F7';
          } else {
            $bg_color = 'white';
          }
          echo "<td style=\"border-bottom:solid $line_color 1px; background-color:$bg_color\">&nbsp;" . $user_results[$i]['room'] . "</td>";
        }
        echo "</tr>\n";

        if ($completed_no > 0) {
          $user_results[$i]['xmean'] = (($user_results[$i]['mark'] - ($total_mark / $completed_no)) * ($user_results[$i]['mark'] - ($total_mark / $completed_no)));
          $user_results[$i]['xmean_percent'] = (($user_results[$i]['adj_percent'] - ($total_mark / $completed_no)) * ($user_results[$i]['adj_percent'] - ($total_mark / $completed_no)));
        } else {
          $user_results[$i]['xmean'] = 0;
          $user_results[$i]['xmean_percent'] = 0;
        }

        if ($user_results[$i]['questions'] >= $question_no) {
          $xmean_total += $user_results[$i]['xmean'];
        }
      }
    }
  }
  fclose($scatter_file);
  
  
  $distribution_file = fopen("../temp/" . $_SERVER['PHP_AUTH_USER'] . "_distribution.dat", "w");         // Distribution data
  fwrite($distribution_file,serialize($distribution) . "\n");
  fclose($distribution_file);

  if ($user_no > 0) {
    //Check for any paper notes
    echo "<tr><td colspan=\"11\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"11\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"11\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>Paper Notes</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    $result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'%d/%m/%Y %H:%i'), note_workstation FROM paper_notes WHERE paper_id=?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($note, $note_date, $note_workstation);
    echo "<tr><td colspan=\"11\">";
    while ($row = $result->fetch()) {
      $lab_name = '';
      $result2 = $mysqli->prepare("SELECT name FROM labs, ip_addresses WHERE labs.id=ip_addresses.lab AND address=?");
      $result2->bind_param('s', $note_workstation);
      $result2->execute();
      $result2->bind_result($lab_name);
      $result2->fetch();
      $result2->close();
      echo "<div style=\"margin-left:20px; border: 1px solid #FFFF40; padding:10px; background-color:#FFFF80; float:left; width:200px; height:100px\"><strong>$note_date</strong><br />$note<br /><br /><span style=\"font-size:80%\">$note_workstation";
      if ($lab_name != '') echo " ($lab_name)";
      echo "</span></div>\n";
    }
    echo "</td></tr>";
    $result->close();
  
  
  
    echo "<tr><td colspan=\"11\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"11\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"11\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>Distribution Chart</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";

    echo "<tr><td>&nbsp;</td><td colspan=\"10\"><img src=\"draw_distribution_chart.php?adjust=" . substr($marking,0,1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></td></tr>\n";

    echo "<tr><td colspan=\"11\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"11\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>Scatter Plot</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td colspan=\"10\"><img src=\"draw_scatter_plot.php?adjust=" . substr($marking,0,1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></td></tr>\n";

    // Display summary -------------------------------------------------------------------------------------
    echo "<tr><td colspan=\"11\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"11\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>Summary</td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";

    echo "<tr><td>&nbsp;</td><td colspan=\"10\">\n";
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td align=\"right\">Paper:</td><td colspan=\"2\">$paper</td></tr>\n";
    echo "<tr><td align=\"right\">Cohort Size";
    if ($_GET['percent'] < 100) {
      if ($_GET['direction'] = 'asc') {
        echo ' (top ' . $_GET['percent'] . '%)';
      } else {
        echo ' (bottom ' . $_GET['percent'] . '%)';
      }
    }
    echo ":</td><td align=\"right\">$cohort_size</td>";
    if ($completed_no < $cohort_size) {
      echo '<td>(' . ($cohort_size - $completed_no). ' candidates did not complete all screens)</td>';
    } else {
      echo '<td>';
      if ($absent_no == 1) {
        echo "<span style=\"color:#C00000\">($absent_no candidate absent)</span>";
      } elseif ($absent_no > 1) {
        echo "<span style=\"color:#C00000\">($absent_no candidates absent)</span>";
      }
      echo '</td>';
    }
    echo "</tr>\n";
    echo "<tr><td align=\"right\"># Failures:</td><td align=\"right\">$failures</td><td>(" . round(($failures / $cohort_size) * 100) . "% of cohort)</td></tr>\n";
    if (isset($ss_hon)) {
      echo "<tr><td align=\"right\"># Distinction:</td><td align=\"right\">$honours</td><td>(" . round(($honours / $cohort_size) * 100) . "% of cohort)</td></tr>\n";
    }
    echo "<tr><td align=\"right\">Total available Marks:</td><td align=\"right\">";
    if ($total_marks < $orig_total_marks) echo "<span style=\"color:red; text-decoration:line-through\">$orig_total_marks</span>&nbsp;&nbsp;";
    echo "$total_marks</td></tr>\n";
    echo "<tr><td align=\"right\">Pass Mark:</td><td align=\"right\">$pass_mark%</td><td>&nbsp;</td></tr>\n";
    if ($marking == '1') {
      echo "<tr><td align=\"right\">Random Mark:</td><td align=\"right\">" . number_format($total_random_mark, 2, '.', ',') . "</td></tr>\n";
      if ($completed_no > 0) {
        if ($total_marks > 0) {
          echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\">$mean_mark</td><td>($mean_percent%)</td></tr>\n";
        } else {
          echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\" style=\"color:#808080\">n/a</td><td>&nbsp;</td></tr>\n";
        }
      } else {
        echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\" style=\"color:#808080\">No completions</td><td>&nbsp;</td></tr>\n";
      }
    } elseif ($marking == '0') {
      if ($completed_no > 0) {
        echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\">$mean_mark</td><td>($mean_percent%)</td></tr>\n";
      } else {
        echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\" style=\"color:#808080\">No completions</td><td>&nbsp;</td></tr>\n";
      }
    } else {
      echo "<tr><td align=\"right\">SS:</td><td align=\"right\">" . round($ss_pass,2) . "%</td></tr>\n";
      if ($ss_hon > 0) echo "<tr><td align=\"right\">SS Distinction:</td><td align=\"right\">" . round($ss_hon,2) . "%</td></tr>\n";
      if ($completed_no > 0) {
        echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\">$mean_mark</td><td>($mean_percent%)</td></tr>\n";
      } else {
        echo "<tr><td align=\"right\">Mean Mark:</td><td align=\"right\" style=\"color:#808080\">No completions</td><td>&nbsp;</td></tr>\n";
      }
    }
    $mid_point = round($cohort_size / 2) - 1;
    echo "<tr><td align=\"right\">Median Mark:</td><td align=\"right\">$median_mark</td><td>($median_percent%)</td></tr>\n";
    if ($completed_no == 0) {
      echo "<tr><td align=\"right\">StDev Mark:</td><td align=\"right\" style=\"color:#808080\">n/a</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td align=\"right\">StDev Mark:</td><td align=\"right\">" . number_format($stddev_mark, 2, '.', ',') . "</td><td>(" . round($stddev_percent,1) . "%)</td></tr>\n";
    }
    echo "<tr><td align=\"right\">Max Mark:</td><td align=\"right\">$max_mark</td><td>(" . number_format($max_percent) . "%)</td></tr>\n";
    if ($min_mark == 9999) $min_mark = 0;
    echo "<tr><td align=\"right\">Min Mark:</td><td align=\"right\">$min_mark</td><td>(" . number_format($min_percent) . "%)</td></tr>\n";
    echo "<tr><td align=\"right\">Range:</td><td align=\"right\">" . ($max_mark - $min_mark) . "</td><td>(" . ($max_percent - $min_percent) . "%)</td></tr>\n";

    echo "<tr><td align=\"right\">Top 10%:</td><td align=\"right\">$top_10%</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Top 15%:</td><td align=\"right\">$top_15%</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Top 20%:</td><td align=\"right\">$top_20%</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Top 25%:</td><td align=\"right\">$top_25%</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Bottom 10%:</td><td align=\"right\">$bottom_10%</td><td>&nbsp;</td></tr>\n";

    if ($completed_no <= 1) {
      echo "<tr><td align=\"right\">Average Time:</td><td style=\"color:#808080\" align=\"right\">n/a</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td align=\"right\">Average Time:</td><td align=\"right\">" . formatsec(round($total_time / $completed_no,0)) . "</td><td>&nbsp;</td></tr>\n";
    }
    if (count($excluded) > 0) {
      echo "<tr><td align=\"right\">Excluded Questions:</td><td colspan=\"2\">$display_excluded</td></tr>\n";
    }
    if ($display_experimental != '') {
      echo "<tr><td align=\"right\">Experimental Questions:</td><td colspan=\"2\">$display_experimental</td></tr>\n";
    }
    if(count($warnings['deleted_qns']) > 0) {
      echo "<tr><td align=\"right\" valign=\"top\">Warnings:</td><td colspan=\"2\"><img src=\"../artwork/incomplete_paper_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning: Answers found for questions that no longer appear on the paper\" border=\"0\" />&nbsp;Answers found for questions that no longer appear on the paper (IDs:";
      for($i = 0; $i < count($warnings['deleted_qns']); $i++) {
      	echo $warnings['deleted_qns'][$i];
      	if($i < count($warnings['deleted_qns']) - 1) {
      		echo ", ";
      	}
      }
      echo ")</td></tr>\n";
   	}
    echo "</table>\n</td></tr>\n";
    echo "<tr><td colspan=\"10\" height=\"9\">&nbsp;</td></tr>\n";

    // Email Class -----------------------------------------------------------------------------------------
    if (isset($_POST['emailclass']) and $_POST['emailclass'] == 'yes') {
      // Save the latest template to disk.
      $file = fopen("../email_templates/$userID.txt", "w");
      fwrite($file,$_POST['from'] . "\n");
      fwrite($file,$_POST['ccaddress'] . "\n");
      fwrite($file,$_POST['bccaddress'] . "\n");
      fwrite($file,$_POST['subject'] . "\n");
      fwrite($file,$_POST['emailtemplate'] . "\n");
      fclose($file);

      for ($i=0; $i<$user_no; $i++) {
        switch ($i) {
          case 25:
          case 50:
          case 75:
          case 100:
          case 125:
          case 150:
          case 175:
          case 200:
          case 225:
          case 250:
          case 275:
          case 300:
          case 325:
          case 350:
          case 375:
          case 400:
          case 425:
          case 450:
          case 475:
          case 500:
          case 525:
          case 550:
          case 575:
          case 600:
            echo "<tr><td>&nbsp;</td><td colspan=\"8\" height=\"9\">$i sent</td></tr>\n";
            flush();
            ob_flush();
        }

        // Perform replacement.
        $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">\n<html><head>\n<title>$paper</title>\n<style>\nbody {font-family: Arial,sans-serif; background-color: white; color:black}</style>\n</head>\n<body>";
        $message .= stripslashes($_POST['emailtemplate']);
        $message = str_replace("{student-title}",$user_results[$i]['title'],$message);
        $message = str_replace("{student-last-name}",$user_results[$i]['surname'],$message);
        $message = str_replace("{student-mark}",$user_results[$i]['mark'],$message);
        $message = str_replace("{student-percent}",$user_results[$i]['adj_percent'],$message);
        $message = str_replace("{total-paper-mark}",$total_marks,$message);
        $message = str_replace("{student-time}",formatsec($user_results[$i]['duration']),$message);
        $message = str_replace("{class-mean-mark}",$mean_mark,$message);
        $message = str_replace("{class-mean-percent}",$mean_percent,$message);
        if ($completed_no-1 == 0) {
          $message = str_replace("{class-stdev}",0,$message);
        } else {
          $message = str_replace("{class-stdev}",number_format($stddev_mark, 2, '.', ','),$message);
        }
        $message = str_replace("{class-max-mark}",$max_mark,$message);
        $message = str_replace("{class-min-mark}",$min_mark,$message);
        $message = str_replace("{class-mean-time}",formatsec(round($total_time / $completed_no,0)),$message);
        $message = str_replace("{random-mark}",number_format($total_random_mark, 1, '.', ','),$message);
        $message = str_replace("{paper-title}",$paper,$message);

        $to = $user_results[$i]['username'] . '@nottingham.ac.uk';

        $subject = stripslashes($_POST['subject']);
        $subject = str_replace("{total-paper-mark}",$total_marks,$subject);
        $subject = str_replace("{class-mean-mark}",round($total_mark / $completed_no, 1),$subject);
        $subject = str_replace("{class-mean-percent}",$mean_percent,$subject);
        $subject = str_replace("{class-max-mark}",$max_mark,$subject);
        $subject = str_replace("{class-min-mark}",$min_mark,$subject);
        $subject = str_replace("{class-mean-time}",formatsec(round($total_time / $completed_no,0)),$subject);
        $subject = str_replace("{random-mark}",number_format($total_random_mark, 1, '.', ','),$subject);
        $subject = str_replace("{paper-title}",$paper,$subject);

        $headers = "From: " . $_POST['from'] . "\n";
        $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
        if ($_POST['ccaddress'] != '') {
          $headers .= "cc: " . $_POST['ccaddress'] . "\n";
        }
        if ($_POST['bccaddress'] != '') {
          $headers .= "bcc: " . $_POST['bccaddress'] . "\n";
        }
        $message .= "</body>\n</html>\n";
        mail ($to, $subject, $message, $headers) or print "<tr><td colspan=\"10\">Could not send mail to <strong>$to</strong>.</td></tr>";
      }
      echo '<tr><td colspan="10">Emails sent.</td></tr>';
    } else {
      if ($paper_type < 2) {
        echo "<tr><td>&nbsp;</td><td colspan=\"8\">\n";
        echo "<form name=\"theform\" method=\"post\">\n";
        echo "<input type=\"button\" value=\"Email Class Marks\" onclick=\"popupEmailTemplate();\" />\n";
        echo '<input type="hidden" name="emailclass" value="" />';
        echo '<input type="hidden" name="from" value="" />';
        echo '<input type="hidden" name="emailtemplate" value="" />';
        echo '<input type="hidden" name="ccaddress" value="" />';
        echo '<input type="hidden" name="bccaddress" value="" />';
        echo '<input type="hidden" name="subject" value="" />';
        echo "</form>\n</td></tr>\n";
      }
    }
  } else {
    echo "</table>\n<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;This paper has not been attempted by anyone.</td></tr></table>\n<div>\n</body>\n</html>";
    exit;
  }
  echo "</table>\n";
  $mysqli->close();
?>
<input type="hidden" id="started" value="" /><input type="hidden" id="userID" value="" /><input type="hidden" id="username" value="" /><input type="hidden" id="display_name" value="" /><input type="hidden" id="log_type" value="" /><input type="hidden" id="reassign" value="" /><input type="hidden" id="loglate" value="" /><input type="hidden" id="percent" value="" />
</body>
</html>
