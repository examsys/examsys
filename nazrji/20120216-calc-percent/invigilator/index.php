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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/invigilator_auth.inc';
  require_once '../classes/networkutils.class.php';
  
  function get_students($modules, $session, $paperID, $exam_length) {
    global $string, $mysqli;
    
    // Get any student notes;
    $notes_array = array();
    $notes_results = $mysqli->prepare("SELECT note_id, userID FROM student_notes WHERE paper_id=?");
    $notes_results->bind_param('i', $paperID);
    $notes_results->execute();
    $notes_results->store_result();
    $notes_results->bind_result($note_id, $tmp_userID);
    while ($notes_results->fetch()) {
      $notes_array[$tmp_userID] = true;
    }
    $notes_results->close();

    echo "<div class=\"cohortlist\">\n<table style=\"font-size:100%\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";    
    $results = $mysqli->prepare("SELECT DISTINCT extra_time, student_modules.userID, surname, first_names, title FROM student_modules, users LEFT JOIN special_needs ON users.id=special_needs.userID WHERE moduleid IN ('" . str_replace(",","','",$modules) . "') AND calendar_year=? AND student_modules.userID=users.id ORDER BY surname, initials");
    $results->bind_param('s', $session);
    $results->execute();
    $results->store_result();
    $results->bind_result($extra_time, $tmp_userID, $surname, $first_names, $title);
    while ($results->fetch()) {
      if ($extra_time == '') {
        echo "<tr><td></td><td style=\"cursor:hand\" onclick=\"newStudentNote('$tmp_userID', $paperID, '$title " . addslashes($surname) . "')\">$surname<span style=\"color:#808080\">, $first_names $title</span>";
      } else {
        echo "<tr><td><img src=\"../artwork/accessibility_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['extratime'] . "\" border=\"0\" /></td><td style=\"cursor:hand\" onclick=\"newStudentNote('$tmp_userID', $paperID, '$title " . addslashes($surname) . "')\">$surname<span style=\"color:#808080\">, $first_names $title</span> <span style=\"color:#C00000\">+ " . round(($exam_length/100) * $extra_time) . $string['mins'] . "</span>";
      }
      if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == true) echo ' <img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" border="0" />';
      echo "</td></tr>\n";
    }
    $results->close();

    $results = $mysqli->prepare("SELECT DISTINCT extra_time, log2.userID, surname, first_names, title FROM log2, users LEFT JOIN special_needs ON users.id=special_needs.userID WHERE log2.q_paper=? AND log2.userID=users.id and users.username LIKE 'user%' ORDER BY surname, initials");
    $results->bind_param('i', $paperID);
    $results->execute();
    $results->store_result();
    $results->bind_result($extra_time, $tmp_userID, $surname, $first_names, $title);
    while ($results->fetch()) {
      if ($extra_time == '') {
        echo "<tr><td></td><td style=\"cursor:hand\" onclick=\"newStudentNote('$tmp_userID', $paperID, '$title " . addslashes($surname) . "')\">$surname<span style=\"color:#808080\">, $first_names $title</span>";
      } else {
        echo "<tr><td><img src=\"../artwork/accessibility_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['extratime'] . "\" border=\"0\" /></td><td style=\"cursor:hand\" onclick=\"newStudentNote('$tmp_userID', $paperID, '$title " . addslashes($surname) . "')\">$surname<span style=\"color:#808080\">, $first_names $title</span> <span style=\"color:#C00000\">+ " . round(($exam_length/100) * $extra_time) . $string['mins'] . "</span>";
      }
      if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == true) echo ' <img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" border="0" />';
      echo "</td></tr>\n";
    }
    $results->close();
    echo "</table>\n</div>\n";
  }
  
  function emergencyNumbers($support_numbers) {
    global $string;
  
    echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; margin-left:10px\">\n";
    echo "<tr><td colspan=\"3\" style=\"border-bottom: 1px solid #C0C0C0; font-weight:bold\">" . $string['emergencynumbers'] . "</td></tr>\n";
    foreach ($support_numbers as $number => $contact) {
      echo "<tr><td><img src=\"../artwork/call_icon.png\" width=\"53\" height=\"25\" alt=\"call\" border=\"0\" /></td><td>$number</td><td>$contact</td></tr>\n";
    }
    echo "</table>\n";
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Rogo: <?php echo $string['invigilatoraccess']; ?></title>
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<script language="JavaScript">
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
  
  function newStudentNote(userID, paperID, display_name) {
    studentnote = window.open("new_student_note.php?userID=" + userID + "&paperID=" + paperID + "","studentnote","width=650,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      studentnote.focus();
    }
  }

  function newPaperNote(paperID) {
    papernote = window.open("new_paper_note.php?paperID=" + paperID + "","papernote","width=650,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      papernote.focus();
    }
  }

  function resizeLists() {
    var myHeight = 0;
    if ( typeof( window.innerWidth ) == 'number' ) {
      //Non-IE
      myHeight = window.innerHeight;
    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
      //IE 6+ in 'standards compliant mode'
      myHeight = document.documentElement.clientHeight;
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
      //IE 4 compatible
      myHeight = document.body.clientHeight;
    }
    myHeight = myHeight - 175;

    var mysheet=document.styleSheets[0];
    var totalrules=mysheet.cssRules? mysheet.cssRules.length : mysheet.rules.length
    if (mysheet.deleteRule){ //if Firefox
      mysheet.insertRule(".cohortlist {height:" + myHeight + "px; overflow:auto}", totalrules);
    } else if (mysheet.removeRule){ //else if IE
      document.styleSheets[0].addRule(".cohortlist", "height:" + myHeight + "px; overflow:auto");
    }
  }
  
</script>
<style type="text/css">
body {margin:0px; background-color:white; color:#000040; font-family:Arial,sans-serif}
</style>
</head>

<body onload="StartClock(); resizeLists();" onunload="KillClock()">

<?php
  $current_ip_address = NetworkUtils::get_ipaddress();

  $lab_results = $mysqli->prepare("SELECT lab, name FROM ip_addresses, labs WHERE ip_addresses.lab=labs.id AND address=?");
  $lab_results->bind_param('s', $current_ip_address);
  $lab_results->execute();
  $lab_results->bind_result($lab, $room_name);
  $lab_results->fetch();
  $lab_results->close();

?>
<table class="header">
<tr>
<th><div style="padding-left:10px; font-size:24pt; font-weight:bold">
<?php
  if ($room_name == '') {
    echo NetworkUtils::get_ipaddress() . $string['unknownlab']; 
  } else {
    echo $string['lab'] . ' ' . $room_name; 
  }
?>
</div><div style="padding-left:10px; font-size:10pt; font-weight:bold"><?php echo $string['invigilatoraccess']; ?></div></th>
<th style="text-align:right"><input type="text" style="background-color:transparent; text-align:right; font-size:180%; border:0px; font-weight:bold" id="theTime" />&nbsp;</th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />
<br />
<?php

  $current_lab = '%' . $lab . '%';
  
  $paper_results = $mysqli->prepare("SELECT property_id, paper_title, moduleID, date_format(start_date,'%d/%m/%Y %T'), exam_duration, calendar_year FROM properties WHERE paper_type='2' AND labs LIKE ? AND start_date < DATE_ADD(NOW(), interval 30 minute) AND end_date > NOW() AND deleted IS NULL");
  $paper_results->bind_param('s', $current_lab);
  $paper_results->execute();
  $paper_results->store_result();
  $paper_results->bind_result($property_id, $paper_title, $moduleID, $start_date, $exam_duration, $calendar_year);
  if ($paper_results->num_rows > 0 and $room_name != '') {
    $col_width = round(100 / ($paper_results->num_rows + 1));
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:95%\">\n<tr>\n";
    while ($row = $paper_results->fetch()) {
      echo "<td style=\"vertical-align:top; width:$col_width%\"><div><img src=\"../artwork/summative.png\" align=\"left\" width=\"48\" height=\"48\" alt=\"paper icon\" border=\"0\" /><strong>$paper_title</strong><br />" . $string['start'] . " $start_date<br />" . $string['duration'] . " $exam_duration " . $string['mins'] . " &nbsp;&nbsp;&nbsp;<a href=\"\" onclick=\"newPaperNote($property_id); return false;\" style=\"color:blue\">" . $string['papernote'] . "</a></div><hr style=\"border:0px; height:1px\" noshade=\"noshade\" size=\"1\" />";
      get_students($moduleID, $calendar_year, $property_id, $exam_duration);
      echo "</td>";
    }
    $paper_results->close();
    echo "<td style=\"vertical-align:top; width:$col_width%\">";
    echo sprintf($string['checklist'],'../lang/' . $language . '/invigilator/');
    ?>
    
    <br />
    
    <?php
    emergencyNumbers($emergency_support_numbers);
    echo "</td></tr>\n</table>\n";
  } else {
    echo "<p style=\"font-weight:bold; color:#C00000\">&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" />&nbsp;" . $string['nopapersfound'] . "</p>";
    emergencyNumbers($emergency_support_numbers);
  }

  $mysqli->close();
?>
</body>
</html>
