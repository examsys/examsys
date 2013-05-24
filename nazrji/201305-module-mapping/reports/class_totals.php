<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Class total report
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

set_time_limit(0);

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/class_totals.inc';
require_once '../classes/folderutils.class.php';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli);

if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$paper            = $propertyObj->get_paper_title();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;

$user_results = compile_report($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $paperID, $startdate, $enddate, $mysqli);
$user_no = count($user_results);

ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />

<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/staff_help.js"></script>
<script language="JavaScript">
  var ie  = document.all;
  var ns6 = document.getElementById&&!document.all;
  var isMenu  = false;
  var menuSelObj = null;
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
  }
  // POP UP MENU
  function popMenu(tmpStarted, tmpUserID, tmpLogType, tmpReassign, tmpLogLate, tmpPercent, e) {
    if (!e) var e = window.event;
    var currentX = e.clientX;
    var currentY = e.clientY;
    var scrOfX = $('body,html').scrollLeft();
    var scrOfY = $('body,html').scrollTop();

    document.getElementById('started').value = tmpStarted;
    document.getElementById('userID').value = tmpUserID;
    document.getElementById('log_type').value = tmpLogType;
    document.getElementById('reassign').value = tmpReassign;
    document.getElementById('loglate').value = tmpLogLate;
    document.getElementById('percent').value = tmpPercent;

    top_pos = currentY+scrOfY;
    if (top_pos > ($(window).height() + scrOfY - 140)) {
      top_pos = $(window).height() + scrOfY - 140;
    }
    document.getElementById('menudiv').style.left = currentX+scrOfX + 'px';
    document.getElementById('menudiv').style.top = top_pos + 'px';

    document.getElementById('menudiv').style.display = "block";
    document.getElementById('item1b').style.backgroundColor = '#FFFFFF';
    document.getElementById('item2b').style.backgroundColor = '#FFFFFF';
    document.getElementById('item3b').style.backgroundColor = '#FFFFFF';
    document.getElementById('item4b').style.backgroundColor = '#FFFFFF';
    document.getElementById('item5b').style.backgroundColor = '#FFFFFF';

    if (tmpStarted == '') {
      document.getElementById('item1b').style.color = '#C0C0C0';
      document.getElementById('item2b').style.color = '#C0C0C0';
    } else {
      document.getElementById('item1b').style.color = '#000000';
      document.getElementById('item2b').style.color = '#000000';
    }

    if (tmpReassign == 'y') {
      document.getElementById('item3b').style.color = '#C0C0C0';
      document.getElementById('item5b').style.color = '#000000';
    } else {
      document.getElementById('item3b').style.color = '#000000';
      document.getElementById('item5b').style.color = '#C0C0C0';
    }

    if (tmpLogLate == 'y') {
      document.getElementById('item6b').style.color = '#000000';
      document.getElementById('log_late_icon').style.display = 'block';
    } else {
      document.getElementById('item6b').style.color = '#C0C0C0';
      document.getElementById('log_late_icon').style.display = 'none';
    }

    isMenu = true;
    return false ;
  }

  function menuRowOn(rowID) {
    // Left menu column
    document.getElementById('item'+rowID+'a').style.backgroundColor = '#FFE7A2';
    document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #FFBD69';
    document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #FFBD69';
    document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #FFBD69';

    // Right menu column
    document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFE7A2';
    document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFE7A2';
  }

  function menuRowOff(rowID) {
    // Left menu column
    document.getElementById('item'+rowID+'a').style.backgroundColor = '#F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #F1F5FB';

    // Right menu column
    document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFFFFF';
  }

  function confirmSubmit() {
    var agree = confirm("Are you sure you want to email everyone on this list their marks?");
    if (agree)
      return true;
    else
      return false;
  }

  function popupEmailTemplate() {
    var winwidth = 785;
    var winheight = 550;
    templatewin = window.open("emailtemplate.php","templatewin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    templatewin.moveTo(screen.width/2-350,screen.height/2-275);
  }

  function viewScript() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('started').value != '') {
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../paper/finish.php?id=<?php echo $propertyObj->get_crypt_name(); ?>&previous=" + document.getElementById('started').value + "&userid=" + document.getElementById('userID').value + "&log_type=" +document.getElementById('log_type').value+ "&percent=" +document.getElementById('percent').value+ "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }

  function viewFeedback() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('started').value != '') {
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../mapping/user_feedback.php?id=<?php echo $propertyObj->get_crypt_name(); ?>&userID=" + document.getElementById('userID').value + "&started=" + document.getElementById('started').value + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }

  function viewProfile() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('reassign').value == 'n') {
      window.top.location = '../users/details.php?paperID=<?php echo $paperID; ?>&userID=' + document.getElementById('userID').value;
    }
  }

  function newStudentNote() {
    document.getElementById('menudiv').style.display = 'none';
    note = window.open("../users/new_student_note.php?userID=" + document.getElementById('userID').value + "&paperID=<?php echo $paperID; ?>&calling=class_totals","note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      note.focus();
    }
  }

  function reassignScript() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('reassign').value == 'y') {
      reassign = window.open("check_reassign_script.php?userID=" + document.getElementById('userID').value + "&paperID=<?php echo $paperID; ?>&started=" + document.getElementById('started').value + "&log_type=" + document.getElementById('log_type').value + "","reassign","width=600,height=500,left="+(screen.width/2-300)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        reassign.focus();
      }
    }
  }

  function reassignLogLate() {
    document.getElementById('menudiv').style.display = 'none';
    if (document.getElementById('loglate').value == 'y') {
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
    var scrOfX = $('body,html').scrollLeft();
    var scrOfY = $('body,html').scrollTop();
	  
    dataSource = "getNote.php?paperID=<?php echo $paperID; ?>&userID=" + userID;
    
    $("#noteMsg").load(dataSource,function(responseTxt,statusTxt,xhr) {
      if (statusTxt=="success") {
        $("#noteDiv").css('display', 'block');
        $("#noteDiv").css('left', currentX + scrOfX + 16 + 'px');

        top_pos = currentY+scrOfY-16;
        if (top_pos > ($(window).height() + scrOfY - 130)) {
          top_pos = $(window).height() + scrOfY - 130;
        }
        $("#noteDiv").css('top', top_pos + 'px');
      }
    });
  }

  document.onmousedown = mouseSelect;
</script>
</head>


<body>
<div id="noteDiv" class="studentnote">
<div style="text-align:right; padding:2px"><img onclick="document.getElementById('noteDiv').style.display='none'" src="../artwork/close_note.png" width="16" height="16" alt="Close" style="cursor:pointer" /></div>
<div id="noteMsg"></div>
</div>

<?php
$popup_width = 180;
if ($language != 'en') {
  $popup_width = 300;
}
?>
<div id="menudiv" class="popupmenu" style="width:<?php echo $popup_width; ?>px" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table cellspacing="2" cellpadding="0" border="0" style="font-size:100%; background-color:white; width:100%">
  <tr><td>
    <table cellspacing="0" cellpadding="1" border="0" style="font-size:90%; background-color:white; width:100%">
      <tr>
        <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" border="0" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><?php echo $string['examscript']; ?></td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" border="0" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><?php echo $string['feedback']; ?></td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right"><img src="../artwork/popup_divider.png" width="100%" height="3" border="0" alt="-" /></td>
      </tr>
      <tr>
        <td id="item3a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();">
          <img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" border="0" />
          </td>
          <td id="item3b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();">
          <?php echo $string['studentprofile']; ?>
          </td>
      </tr>
      <tr>
        <td id="item4a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="newStudentNote();">
        <img src="../artwork/notes_icon.gif" width="14" height="14" alt="" border="0" />
        </td>
        <td id="item4b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="newStudentNote();">
        <?php echo $string['newnote']; ?>
        </td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right">
        <img src="../artwork/popup_divider.png" width="100%" height="3" border="0" alt="-" /></td>
      </tr>
      <tr>
        <td id="item5a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('5');" onmouseout="menuRowOff('5');" onclick="reassignScript();">
        <img src="../artwork/guest_account_16.png" width="16" height="16" alt="" border="0" />
        </td>
        <td id="item5b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('5');" onmouseout="menuRowOff('5');" onclick="reassignScript();">
        <?php echo $string['reassigntouser']; ?></td>
      </tr>
      <tr>
        <td id="item6a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('6');" onmouseout="menuRowOff('6');" onclick="reassignLogLate();">
        <img id="log_late_icon" style="display:none" src="../artwork/log_late_16.gif" width="16" height="16" alt="" border="0" /></td>
        <td id="item6b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('6');" onmouseout="menuRowOff('6');" onclick="reassignLogLate();">
        <?php echo $string['latesubmissions']; ?>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</div>
<?php
  for ($i=1; $i<=100; $i++) $distribution[$i] = 0;

  $notes = array();
  // Query any student notes for the current paper
  $result = $mysqli->prepare("SELECT userID FROM student_notes WHERE paper_id = ?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $notes[$tmp_userID] = 'y';
  }
  $result->close();


  // Query any student special needs for the current paper
  $special_needs = array();
  $users_in = array();
  foreach($user_results as $u) {
    $users_in[] = $u['tmp_userID'];
  }
  $users_in = implode(',',$users_in);
  if ($users_in != '') {
    $result = $mysqli->prepare("SELECT userID FROM special_needs where userID IN ($users_in)");
    $result->execute();
    $result->bind_result($special_userID);
    while ($result->fetch()) {
      $special_needs[$special_userID] = 'y';
    }
    $result->close();
  }

  if ($marking == '0') {
    $marking_label = $string['%'];
    $marking_key = 'percent';
  } else {
    $marking_label = $string['adjusted%'];
    $marking_key = 'adj_percent';
  }

  //output table heading
  $table_order = array(''=>'', $string['name']=>'name', $string['studentid']=>'student_id', $string['course']=>'student_grade', $string['mark']=>'mark', $marking_label=>$marking_key, $string['classification']=>'classification', $string['rank']=>'rank', $string['decile']=>'decile', $string['starttime']=>'started', $string['duration']=>'duration', $string['ipaddress']=>'ipaddress');
  if ($paper_type == '2') $table_order[$string['room']] = 'room';
  $metadata_cols = array();
  if (isset($user_results[0])){
    foreach ($user_results[0] as $key => $val) {
      if (strrpos($key,'meta_') !== false) {
        $key_display = ucfirst(str_replace('meta_','',$key));
        $table_order[$key_display] = $key;
        $metadata_cols[$key] = $key;
      }
    }
  }

  $cols = count($table_order);

  echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:80%\">\n";
  if ($paper_type == '2') {
    echo "<tr><th class=\"h\" colspan=\"" . ($cols - 1) . "\">";
  } else {
    echo "<tr><th class=\"h\" colspan=\"" . ($cols - 1) . "\">";
  }
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = $string['classtotals'] . '</strong> (' . module_utils::get_moduleid_from_id($_GET['repmodule'], $mysqli) . ' ' . $string['studentsonly'] . ')';
  } else {
    $report_title = $string['classtotals'] . '</strong>';
  }

  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';

  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif ( isset( $_GET['module'] ) and $_GET['module'] != '' ) {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';

  echo "<span style=\"margin-left:10px; font-size:200%; color:black\"><strong>$report_title</span></th><th class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";

  if (isset($_GET['folder'])) {
    $tmp_folder = '&folder=' . $_GET['folder'];
  } else {
    $tmp_folder = '';
  }

  if (isset($_GET['module'])) {
    $tmp_module = '&module=' . $_GET['module'];
  } else {
    $tmp_module = '';
  }

  // output table header
  $sortby = $_GET['sortby'];
  $ordering = $_GET['ordering'];
  if (isset($_GET['absent'])) {
    $absent = $_GET['absent'];
  } else {
    $absent = 0;
  }
  if (isset($user_results[0])) {
    echo "<tr style=\"font-size:110%\">\n";
    foreach ($table_order as $display => $key) {
      if ($key == '') {
        echo "<th>";
      } else {
        echo "<th class=\"vert_div\">&nbsp;";
      }
      if ($sortby == $key and $ordering == 'asc') {
        echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . $tmp_module . $tmp_folder . "&startdate=$startdate&enddate=$enddate&sortby=$key&ordering=desc&percent=" . $_GET['percent'] . "&absent=$absent&studentsonly=$studentsonly\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
      } elseif ($sortby == $key and $ordering == 'desc') {
        echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . $tmp_module . $tmp_folder . "&startdate=$startdate&enddate=$enddate&sortby=$key&ordering=asc&percent=" . $_GET['percent'] . "&absent=$absent&studentsonly=$studentsonly\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
      } else {
        echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . $tmp_module . $tmp_folder . "&startdate=$startdate&enddate=$enddate&sortby=$key&ordering=asc&percent=" . $_GET['percent'] . "&absent=$absent&studentsonly=$studentsonly\">$display</a>&nbsp;</th>";
      }
    }
    echo "</tr>\n";
  }

  if ($sortby == 'classification') {
    $sortby = 'mark';
  }

  echo '<tr><th colspan="' . ($cols) . '" class="bevel"></th></tr>';

  // Check for any temporary accounts and if so display warning banner
  $temp_user_no = 0;
  for ($i=0; $i<$user_no; $i++) {
    if (strpos($user_results[$i]['username'], 'user') === 0) {
      $temp_user_no++;
    }
  }
  if ($temp_user_no > 0) {
?>
    <tr>
      <td class="redwarn" colspan="<?php echo $cols ?>">
        <table class="warn-icon">
          <tr>
            <td><img src="../artwork/temp_account_warning.png" width="28" height="28" alt="Warning" /></td>
            <td><?php echo $string['temporaryaccountswarning'] ?></td>
          </tr>
        </table>
      </td>
    </tr>
<?php
  }

  if (count($log_late) > 0) {
?>
    <tr>
      <td class="redwarn" colspan="<?php echo $cols ?>">
        <table class="warn-icon">
          <tr>
            <td><img src="../artwork/late_warning_icon.png" width="28" height="28" alt="<?php echo strip_tags($string['latesubmissionsmsg']) ?>" /></td>
            <td>

<?php
    $html = $string['latesubmissionsmsg'] . " (<a style=\"color:black\" href=\"#\" onclick=\"launchHelp(221); return false;\">" . $string['moredetails'] . "</a>): ";
    foreach ($log_late as $student_userID => $student_name) {
      if ($html == '') {
        $html = $student_name;
      } else {
        $html .= ', ' . $student_name;
      }
    }
    echo "$html.</td></tr></table></td></tr>\n";
  }

  $percent_decimals = $configObject->get('percent_decimals');
  $absent_no = 0;
  $scatter_data = '';
  for ($i=0; $i<$user_no; $i++) {
    extract($user_results[$i]);
    $onclick = '';

    if ($user_results[$i]['visible'] == 1) {
      if (strpos($user_results[$i]['username'], 'user') !== 0) {
        $reassign = 'n';
      } else {
        $reassign = 'y';
      }

      if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
        $bg_color = '#FFC0C0';
        $late_submissions = '';
        ?>
        <tr class="nonattend" id="res<?php echo $i+1 ?>"><td>&nbsp;</td>
        <td class="padl" onclick="popMenu('', '<?php echo $tmp_userID; ?>', '<?php echo $paper_type; ?>', '<?php echo $reassign ?>', '<?php echo $late_submissions ?>', '<?php echo $adj_percent; ?>', event);<?php echo $onclick; ?>" />
        <?php echo "$title&nbsp;$surname ,&nbsp;"; ?><span class="grey"><?php echo $first_names ?></span>
        <?php
        if ($user_results[$i]['student_id'] == '') {
          echo "<td class=\"padl grey\">" . $string['unknown'] . "</td>";
        } else {
          echo "<td class=\"padl\">" . $user_results[$i]['student_id'] . "</td>";
        }
        echo "<td class=\"padl\">" . $user_results[$i]['student_grade'] . "</td><td colspan=\"" . (9 + count($metadata_cols)) . "\" style=\"text-align:center\">&lt;" . $string['noattendance'] . "&gt;</td></tr>\n";
        $absent_no++;
      } else {
        if (isset($log_late[$user_results[$i]['tmp_userID']])) {
          $late_submissions = 'y';
        } else {
          $late_submissions = 'n';
        }
        echo '<tr id="res' . ($i+1) . '"';
        if ($user_results[$i]['questions'] < $question_no) {
          $scatter_data .= "0\n0\n";
          $class = 'redln';
        } else {
          $class = 'greyln';
          $temp_location = $user_results[$i]['adj_percent'];
          if (isset($distribution[$temp_location])) {
            $distribution[$temp_location]++;
          } else {
            $distribution[$temp_location] = 1;
          }
          $scatter_data .= $temp_location . "\n" . $user_results[$i]['duration'] . "\n";
        }
        if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
          $role_css = 'staff';
        } else {
          $role_css = '';
        }
        if ($user_results[$i]['questions'] < $question_no) {
          echo "><td class=\"$class $role_css\"><img src=\"../artwork/incomplete_paper_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['notcompleted'] . "\" onclick=\"popMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['paper_type'] . "','$reassign', '$late_submissions','" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "',event);" . $onclick . "\" /></td>";
        } else {
          echo "><td class=\"$class $role_css\">";
          if (isset($log_late[$user_results[$i]['tmp_userID']])) {
            echo '<img src="../artwork/log_late_16.gif" width="16" height="16" alt="' . $string['displayexamscript'] . '"';
          } elseif ($user_results[$i]['paper_type'] == 0) {
            echo '<img src="../artwork/formative_16.gif" width="16" height="16" alt="' . $string['displayexamscript'] . '"';
          } elseif ($user_results[$i]['paper_type'] == '1') {
            echo '<img src="../artwork/progress_16.gif" width="16" height="16" alt="' . $string['displayexamscript'] . '"';
          } elseif ($user_results[$i]['paper_type'] == '2') {
            echo '<img src="../artwork/summative_16.gif" width="16" height="16" alt="' . $string['displayexamscript'] . '"';
          } elseif ($user_results[$i]['paper_type'] == '3') {
            echo '<img src="../artwork/survey_16.gif" width="16" height="16" alt="' . $string['displaysurvey'] . '"';
          } elseif ($user_results[$i]['paper_type'] == '5') {
            echo '<img src="../artwork/offline_16.gif" width="16" height="16" alt="' . $string['displaypaper'] . '"';
          }
          echo " onclick=\"popMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['paper_type'] . "','$reassign','$late_submissions','" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "',event);" . $onclick . "\" /></td>";
        }
        if ($_GET['sortby'] == 'name') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        if (strpos($user_results[$i]['username'], 'user') === 0) {
          echo "<td class=\"$class$ordered padl tmpacc $role_css\"><span style=\"cursor:hand\" onclick=\"popMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['paper_type'] . "','$reassign','$late_submissions','" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "',event);" . $onclick ."\">" . str_replace('User','Guest Account #',$user_results[$i]['surname']) . "</span>";
        } else {
          echo "<td class=\"$class$ordered padl $role_css\"><span style=\"cursor:hand\" onclick=\"popMenu('" . $user_results[$i]['started'] . "'," . $user_results[$i]['tmp_userID'] . ",'" . $user_results[$i]['paper_type'] . "','$reassign','$late_submissions','" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "',event);" . $onclick . "\">" . $user_results[$i]['title'] . "&nbsp;" . $user_results[$i]['surname'] . ",&nbsp;<span class=\"grey\">" . $user_results[$i]['first_names'] . "</span></span>";
        }
        if (isset($special_needs[$user_results[$i]['tmp_userID']]) and $special_needs[$user_results[$i]['tmp_userID']] == 'y') {
          echo '&nbsp;<img src="../artwork/accessibility_16.png" width="16" height="16" alt="' . $string['alternativearrangements'] . '" />';
        }
        $student_id = $user_results[$i]['username'];
        if ($user_results[$i]['attempt'] > 1) {
          echo '&nbsp;<img src="../artwork/resit.png" width="16" height="16" alt="Resit" />';
        }
        if (isset($notes[$user_results[$i]['tmp_userID']]) and $notes[$user_results[$i]['tmp_userID']] == 'y') {
          echo '&nbsp;<a href="" onclick="viewNote(\'' . $user_results[$i]['tmp_userID'] . '\', event); return false;"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Notes" /></a>';
        }
        echo "</td>";
        if ($_GET['sortby'] == 'student_id') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        if ($user_results[$i]['student_id'] == '') {
          if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
            echo "<td class=\"grey $class$ordered padl $role_css\">&nbsp;</td>";
          } else {
            echo "<td class=\"grey $class$ordered padl $role_css\">" . $string['unknown'] . "</td>";
          }
        } else {
          echo "<td class=\"$class$ordered padl $role_css\">" . $user_results[$i]['student_id'] . "</td>";
        }
        if ($_GET['sortby'] == 'student_grade') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        echo "<td class=\"$class$ordered padl $role_css\">" . $user_results[$i]['student_grade'] . "</td>";
        if ($_GET['sortby'] == 'mark') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        if ($user_results[$i]['adj_percent'] < $pass_mark) {
          echo "<td class=\"mk $class$ordered fail r $role_css\">";
          if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
          echo $user_results[$i]['mark'] . "</td>";
          echo "<td class=\"$class fail r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "%</td><td class=\"$class fail $role_css\">&nbsp;" . $string['fail'] . "</td>";
        } else {
          if ($user_results[$i]['adj_percent'] >= $distinction_mark) {
            echo "<td class=\"mk $class$ordered dist r $role_css\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td class=\"dist $class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "%</td><td class=\"$class dist $role_css\">&nbsp;" . $string['distinction'] . "</td>";
          } else {
            echo "<td class=\"mk $class$ordered r $role_css\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td class=\"$class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . "%</td><td class=\"$class $role_css\">&nbsp;" . $string['pass'] . "</td>";
          }
        }
        // Rank column
        if ($_GET['sortby'] == 'rank') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        echo "<td class=\"$class$ordered r $role_css\">" . $user_results[$i]['rank'] . "</td>";
        // Decile column
        if ($_GET['sortby'] == 'decile') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        echo "<td class=\"$class$ordered r $role_css\">" . $user_results[$i]['decile'] . "</td>";
        // Start Time column
        if ($_GET['sortby'] == 'started') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        echo "<td class=\"$class$ordered padl $role_css\">" . $user_results[$i]['display_started'] . "</td>";
        // Duration column
        if ($_GET['sortby'] == 'duration') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        echo "<td class=\"$class$ordered padl $role_css\">" . formatsec($user_results[$i]['duration']);
        if (isset($log_late[$user_results[$i]['tmp_userID']])) {
          echo '&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" border="0" />';
        }
        echo "</td>";

        if ($_GET['sortby'] == 'ipaddress') {
          $ordered = ' ordered';
        } else {
          $ordered = '';
        }
        echo "<td class=\"$class$ordered padl $role_css\">" . $user_results[$i]['ipaddress'] . "</td>";
        if ($paper_type == 2) {
          if ($_GET['sortby'] == 'room') {
            $ordered = ' ordered';
          } else {
            $ordered = '';
          }
          echo "<td class=\"$class$ordered padl $role_css\">" . $user_results[$i]['room'] . "</td>";
        }

        // Display any associated metadata
        if (count($metadata_cols) > 0) {
          foreach ( $metadata_cols as $type) {
            if ($_GET['sortby'] == $type) {
              $ordered = ' ordered';
            } else {
              $ordered = '';
            }
            echo "<td class=\"$class$ordered $role_css\">&nbsp;" . $user_results[$i][$type] . "</td>";
          }
        }
        echo "</tr>\n";
      }
    }
  }

  $scatter_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID(). '_scatter.dat', 'w');              // Scatter plot data
  fwrite($scatter_file, $scatter_data . "\n");
  fclose($scatter_file);

  $distribution_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_distribution.dat', 'w');   // Distribution data
  fwrite($distribution_file, serialize($distribution) . "\n");
  fclose($distribution_file);

  if ($user_no > 0) {
    //Check for any paper notes
    echo "<tr><td colspan=\"" . ($cols) . "\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"" . ($cols) . "\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"" . ($cols) . "\"><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['papernotes'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    $result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'%d/%m/%Y %H:%i'), note_workstation FROM paper_notes WHERE paper_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($note, $note_date, $note_workstation);
    echo "<tr><td></td><td colspan=\"" . ($cols - 1) . "\">";
    while ($result->fetch()) {
      $lab_name = '';
      $result2 = $mysqli->prepare("SELECT name FROM labs, ip_addresses WHERE labs.id = ip_addresses.lab AND address = ?");
      $result2->bind_param('s', $note_workstation);
      $result2->execute();
      $result2->bind_result($lab_name);
      $result2->fetch();
      $result2->close();
      echo "<div class=\"papernote\"><strong>$note_date</strong><p>$note</p><br /><span style=\"font-size:80%\">$note_workstation";
      if ($lab_name != '') echo " ($lab_name)";
      echo "</span></div>\n";
    }
    echo "</td></tr>";
    $result->close();

    echo "<tr><td colspan=\"" . $cols . "\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"" . $cols . "\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"" . $cols . "\"><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['distributionchart'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";

    echo "<tr><td>&nbsp;</td><td colspan=\"" . ($cols - 1) . "\"><img src=\"draw_distribution_chart.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark&q1=" . $stats['q1'] . "&q2=" . $stats['q2'] . "&q3=" . $stats['q3'] . "\" width=\"830\" height=\"300\" alt=\"Distribution Chart\" /></td></tr>\n";

    echo "<tr><td colspan=\"" . $cols . "\" height=\"9\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"" . $cols . "\"><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['scatterplot'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td colspan=\"" . ($cols - 1) . "\"><img src=\"draw_scatter_plot.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></td></tr>\n";
    echo "</table>\n";
    
    // Display summary -------------------------------------------------------------------------------------
    
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" style=\"font-size:85%; width:100%\">";
    echo "<tr><td class=\"subheading\" style=\"width:50px\">" . $string['summary'] . "</td><td style=\"width:48%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['deciles'] . "</td><td style=\"width:30%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['quartiles'] . "</td><td style=\"width:100%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr>\n";
    echo "<tr><td colspan=\"2\" style=\"width:33%\">";
    
    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\"  style=\"font-size:85%\">\n";
    echo "<tr><td class=\"field\" style=\"width:170px\">" . $string['paper'] . "</td><td colspan=\"3\">$paper</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['cohortsize'];
    if ($_GET['percent'] < 100) {
      if ($ordering == 'desc') {
        echo ' ('.$string['top'].' ' . $_GET['percent'] . '%)';
      } else {
        echo ' ('.$string['bottom'].' ' . $_GET['percent'] . '%)';
      }
    }
    
    $size_msg = ($cohort_size < $user_no) ? $cohort_size . $string['of'] . $user_no : $user_no;
    echo "</td><td class=\"r\" style=\"width:60px\">$size_msg</td>";
    if (($stats['completed_no'] + $stats['out_of_range']) < $user_no) {
      echo '<td>(' . ($user_no - $stats['completed_no'] - $stats['out_of_range']). ' ' . $string['candidatenotcomplete'] . ')</td>';
    } else {
      echo '<td>';
      if ($absent_no == 1) {
        echo "<span style=\"color:#C00000\">($absent_no " . $string['candidateabsent'] . ")</span>";
      } elseif ($absent_no > 1) {
        echo "<span style=\"color:#C00000\">($absent_no " . $string['candidatesabsent'] . ")</span>";
      }
      echo '</td><td>&nbsp;</td>';
    }
    echo "</tr>\n";

    if ($cohort_size > 0) {
      $percent_failures = round(($stats['failures'] / $cohort_size) * 100);
      $percent_passes = round(($stats['passes'] / $cohort_size) * 100);
      $percent_honours = round(($stats['honours'] / $cohort_size) * 100);
    } else {
      $percent_failures = 0;
      $percent_passes = 0;
      $percent_honours = 0;
    }

    echo "<tr><td class=\"field\">" . $string['failureno'] . "</td><td class=\"r\">" . $stats['failures'] . "</td><td>(" . $percent_failures . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['passno'] . "</td><td class=\"r\">" . $stats['passes'] . "</td><td>(" . $percent_passes . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['distinctionno'] . "</td><td class=\"r\"> " . $stats['honours'] . "</td><td>(" . $percent_honours . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";

    echo "<tr><td class=\"field\">" . $string['totalmarks'] . "</td><td class=\"r\">";
    if ($paper_buffer['total_marks'] < $paper_buffer['orig_total_marks']) echo "<span class=\"exclude\">" . $paper_buffer['orig_total_marks'] . "</span>&nbsp;&nbsp;";
    echo $paper_buffer['total_marks'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['passmark'] . "</td><td class=\"r\">$pass_mark%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    if ($marking == '1') {
      echo "<tr><td class=\"field\">" . $string['randommark'] . "</td><td class=\"r\">" . number_format($paper_buffer['total_random_mark'], 2, '.', ',') . "</td><td>&nbsp;</td></tr>\n";
      if ($stats['completed_no'] > 0) {
        if ($paper_buffer['total_marks'] > 0) {
          echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
        } else {
          echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } elseif ($marking == '0') {
      if ($stats['completed_no'] > 0) {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } else {
      echo "<tr><td class=\"field\">" . $string['ss'] .  "</td><td class=\"r\">" . MathsUtils::formatNumber($ss_pass, $percent_decimals) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ($ss_hon > 0) echo "<tr><td class=\"field\">" . $string['ssdistinction'] . "</td><td class=\"r\">" . MathsUtils::formatNumber($ss_hon, $percent_decimals) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ($stats['completed_no'] > 0) {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    }
    $mid_point = round($cohort_size / 2) - 1;
    echo "<tr><td class=\"field\">" . $string['medianmark'] . "</td><td class=\"r\">" . round($stats['median_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['median_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
    if ($stats['completed_no'] == 0) {
      echo "<tr><td class=\"field\">" . $string['stdevmark'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['stdevmark'] . "</td><td class=\"r\">" . number_format($stats['stddev_mark'], 2, '.', ',') . "</td><td>(" . MathsUtils::formatNumber($stats['stddev_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
    }
    echo "<tr><td class=\"field\">" . $string['maxmark'] . "</td><td class=\"r\">" . $stats['max_mark'] . "</td><td>(" . MathsUtils::formatNumber($stats['max_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['minmark'] . "</td><td class=\"r\">" . $stats['min_mark'] . "</td><td>(" . MathsUtils::formatNumber($stats['min_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['range'] . "</td><td class=\"r\">" . $stats['range'] . "</td><td>(" . MathsUtils::formatNumber($stats['range_percent'], $percent_decimals) . "%)</td><td>&nbsp;</td></tr>\n";

    if ($stats['completed_no'] <= 1) {
      echo "<tr><td class=\"field\">" . $string['averagetime'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['averagetime'] . "</td><td class=\"r\">" . formatsec(round($stats['total_time'] / $stats['completed_no'], 0)) . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    }
    if ($exclusions->get_excluded_no() > 0) {
      echo "<tr><td class=\"field\">" . $string['excludedquestions'] . "</td><td colspan=\"3\">" . $paper_buffer['display_excluded'] . "</td></tr>\n";
    }
    if ($paper_buffer['display_experimental'] != '') {
      echo "<tr><td class=\"field\">" . $string['experimantalquestions'] . "</td><td colspan=\"3\">" . $paper_buffer['display_experimental'] . "</td></tr>\n";
    }
    echo "</table></td>\n";
    
    echo "<td></td>";
    
    // Deciles
    $suffix = array('', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th' ,'th');
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\"  style=\"font-size:85%\">\n";
    for ($i=1; $i<10; $i++) {
      echo "<tr><td style=\"width:40px\">" . $i;
			echo ($language == 'en')?$suffix[$i]:'.';
			echo "</td><td>" . MathsUtils::formatNumber($stats["decile$i"], $percent_decimals) . "%</td></tr>\n";
    }
    echo "</table></td>\n";
    
    echo "<td></td>";
    
    // Quartiles
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\"  style=\"font-size:85%\">\n";
    echo "<tr><td style=\"width:40px\">Q1</td><td>" . MathsUtils::formatNumber($stats['q1'], $percent_decimals) . "%</td></tr>\n";
    echo "<tr><td style=\"width:40px\">Q2</td><td>" . MathsUtils::formatNumber($stats['q2'], $percent_decimals) . "%</td></tr>\n";
    echo "<tr><td style=\"width:40px\">Q3</td><td>" . MathsUtils::formatNumber($stats['q3'], $percent_decimals) . "%</td></tr>\n";
    
    echo "</table></td>\n";
    
    echo "</tr></table>\n<br />";
    
    // Email Class -----------------------------------------------------------------------------------------
    if (isset($_POST['emailclass']) and $_POST['emailclass'] == 'yes') {
      // Save the latest template to disk.
      $file = fopen("../email_templates/" . $userObject->get_user_ID(), "w");
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
        $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">\n<html><head>\n<title>$paper</title>\n<style type=\"text/css\">\nbody {font-family: Arial,sans-serif; background-color: white; color:black}</style>\n</head>\n<body>";
        $message .= $_POST['emailtemplate'];
        $message = str_replace("{student-title}", $user_results[$i]['title'], $message);
        $message = str_replace("{student-last-name}", $user_results[$i]['surname'], $message);
        $message = str_replace("{student-mark}", $user_results[$i]['mark'], $message);
        $message = str_replace("{student-percent}", $user_results[$i]['adj_percent'], $message);
        $message = str_replace("{total-paper-mark}", $paper_buffer['total_marks'], $message);
        $message = str_replace("{student-time}", formatsec($user_results[$i]['duration']), $message);
        $message = str_replace("{class-mean-mark}", $stats['mean_mark'], $message);
        $message = str_replace("{class-mean-percent}", $stats['mean_percent'], $message);
        if ($stats['completed_no']-1 == 0) {
          $message = str_replace("{class-stdev}", 0, $message);
        } else {
          $message = str_replace("{class-stdev}", number_format($stats['stddev_mark'], 2, '.', ','), $message);
        }
        $message = str_replace("{class-max-mark}", $stats['max_mark'], $message);
        $message = str_replace("{class-min-mark}", $stats['min_mark'], $message);
        $message = str_replace("{class-mean-time}", formatsec(round($total_time / $stats['completed_no'],0)), $message);
        $message = str_replace("{random-mark}", number_format($paper_buffer['total_random_mark'], 1, '.', ','), $message);
        $message = str_replace("{paper-title}", $paper, $message);

        $to = $user_results[$i]['email'];

        $subject = $_POST['subject'];
        $subject = str_replace("{total-paper-mark}", $paper_buffer['total_marks'], $subject);
        $subject = str_replace("{class-mean-mark}", round($total_mark / $stats['completed_no'], 1), $subject);
        $subject = str_replace("{class-mean-percent}", $stats['mean_percent'], $subject);
        $subject = str_replace("{class-max-mark}", $stats['max_mark'], $subject);
        $subject = str_replace("{class-min-mark}", $stats['min_mark'], $subject);
        $subject = str_replace("{class-mean-time}", formatsec(round($total_time / $stats['completed_no'],0)), $subject);
        $subject = str_replace("{random-mark}", number_format($paper_buffer['total_random_mark'], 1, '.', ','), $subject);
        $subject = str_replace("{paper-title}", $paper, $subject);

        $headers = "From: " . $_POST['from'] . "\n";
        $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
        if ($_POST['ccaddress'] != '') {
          $headers .= "cc: " . $_POST['ccaddress'] . "\n";
        }
        if ($_POST['bccaddress'] != '') {
          $headers .= "bcc: " . $_POST['bccaddress'] . "\n";
        }
        $message .= "</body>\n</html>\n";
        mail ($to, $subject, $message, $headers) or print "<div>Could not send mail to <strong>$to</strong>.</div>";
      }
      echo '<p>' . $string['emailssent'] . '</p>';
    } else {
      if ($paper_type < 2) {
        echo "<div>\n";
        echo "<form name=\"theform\" method=\"post\">\n";
        echo "<input type=\"button\" value=\"" . $string['emailclassmarks'] . "\" onclick=\"popupEmailTemplate();\" />\n";
        echo '<input type="hidden" name="emailclass" value="" />';
        echo '<input type="hidden" name="from" value="" />';
        echo '<input type="hidden" name="emailtemplate" value="" />';
        echo '<input type="hidden" name="ccaddress" value="" />';
        echo '<input type="hidden" name="bccaddress" value="" />';
        echo '<input type="hidden" name="subject" value="" />';
        echo "</form>\n</div>\n";
      }
    }
  } else {
    echo "</table>\n<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left; font-size:85%\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;" . sprintf($string['noattempts'], nicedate($_GET['startdate']), nicedate($_GET['enddate'])) . "</td></tr></table>\n<div>\n</body>\n</html>";
    exit;
  }
  echo "</table>\n";
  $mysqli->close();
?>
<input type="hidden" id="started" value="" /><input type="hidden" id="userID" value="" /><input type="hidden" id="log_type" value="" /><input type="hidden" id="reassign" value="" /><input type="hidden" id="loglate" value="" /><input type="hidden" id="percent" value="" />
</body>
</html>
