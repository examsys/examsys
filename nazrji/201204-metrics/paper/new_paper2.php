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

require '../include/staff_auth.inc';
require '../config/campuses.inc';
require_once '../classes/schoolutils.class.php';
require_once '../classes/dateutils.class.php';
require '../lang/' . $language. '/include/timezones.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['createnewpaper'] . $cfg_install_type; ?></title>
<?php
  // Delete any half completed papers owned by current user.
  $result = $mysqli->prepare("DELETE FROM properties WHERE deleted='0000-00-00 00:00:00' AND paper_ownerID=?");
  $result->bind_param('i', $userID);
  $result->execute();  

  // Check that the new paper name is not already used by any other paper (i.e. unique).
  $result = $mysqli->prepare("SELECT property_id FROM properties WHERE paper_title=? LIMIT 1");
  $result->bind_param('s', $_POST['paper_name']);
  $result->execute();  
  $result->store_result();
  $result->bind_result($tmp_id);
  $rows_found = $result->num_rows;
  $result->free_result();
  $result->close();
  
  if ($rows_found > 0) {
?>
  <style type="text/css">
    body {font-family:Arial,sans-serif; color:black; background-color:#F0F0F0; margin:6px; font-size:90%}
    table {font-size:100%}
    textarea, input[type=text], select {font-family: Arail,sans-serif; border: 1px solid #7F9DB9}
    .icon {color:#001687; padding-top:15px; padding-bottom:15px; padding-left:0px; padding-right:0px; vertical-align:top; width:98px; font-size:8pt}
  </style>
  <script type="text/javascript">
    function over(id) {
      if (id != document.getElementById('paper_type').value) {
        document.getElementById(id).style.backgroundImage = "url('../artwork/over.png')";
      }
      switch (id) {
        case 'formative':
          document.getElementById('description').innerHTML = 'Self-assessment quizzes that be normally be accessed by students at any time.';
          break;
        case 'progress':
          document.getElementById('description').innerHTML = 'Normally used for mid-term tests where feedback is not provided at the end of the assessment.';
          break;
        case 'summative':
          document.getElementById('description').innerHTML = 'High-stakes exams where marks contribute to a student\'s course.';
          break;
        case 'survey':
          document.getElementById('description').innerHTML = 'A questionnaire used for eliciting views and feedback from students.';
          break;
        case 'osce':
          document.getElementById('description').innerHTML = 'Objective Structured Clinical Examination (OSCE) assessment type used for medical and health sciences fields.';
          break;
        case 'offline':
          document.getElementById('description').innerHTML = 'This paper type allows marks from offline papers to be loaded into Rogo.';
          break;
        case 'peer_review':
          document.getElementById('description').innerHTML = 'Generates a form for students to review their peers.';
          break;
      }
    }

    function out(id) {
      if (id != document.getElementById('paper_type').value) {
        document.getElementById(id).style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      }
    }

    function activate(id) {
      document.getElementById('formative').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      document.getElementById('progress').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      document.getElementById('summative').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      document.getElementById('survey').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      document.getElementById('osce').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      document.getElementById('offline').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
      document.getElementById('peer_review').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";

      document.getElementById(id).style.backgroundImage = "url('../artwork/on.png');";
      document.getElementById('paper_type').value = id;
    }

    function warning() {
      alert("<?php printf($string['msg5'], $_POST['paper_name']); ?>");
    }
  </script>
</head>

<body onload="warning();">
<form name="theform" action="new_paper2.php" method="post">
<div style="text-align:center; border:solid 1px #7F9DB9; background-color:white">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:white; width:100%">
<tr>
<td colspan="8" style="text-align:left; font-weight:bold; background-color:#DDE7EE; color:#001687; border-bottom:1px solid #C5C5C5; padding:4px">&nbsp;<?php echo $string['papertype']; ?></td>
</tr>
<tr>
<?php
  if ($_POST['paper_type'] == 'formative') {
    echo "<td class=\"icon\" onclick=\"activate('formative')\" onmouseover=\"over('formative')\" onmouseout=\"out('formative')\" id=\"formative\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/formative.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Formative Self-Assessment\" /><br />" . $string['formative self-assessment'] . "</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('formative')\" onmouseover=\"over('formative')\" onmouseout=\"out('formative')\" id=\"formative\"><img src=\"../artwork/formative.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Formative Self-Assessment\" /><br />" . $string['formative self-assessment'] . "</td>\n";
  }
  if ($_POST['paper_type'] == 'progress') {
    echo "<td class=\"icon\" onclick=\"activate('progress')\" onmouseover=\"over('progress')\" onmouseout=\"out('progress')\" id=\"progress\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/progress.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Progress Test\" /><br />" . $string['progress test'] . "</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('progress')\" onmouseover=\"over('progress')\" onmouseout=\"out('progress')\" id=\"progress\"><img src=\"../artwork/progress.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Progress Test\" /><br />" . $string['progress test'] . "</td>\n";
  }
  if ($_POST['paper_type'] == 'summative') {
    echo "<td class=\"icon\" onclick=\"activate('summative')\" onmouseover=\"over('summative')\" onmouseout=\"out('summative')\" id=\"summative\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/summative.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Summative Exam\" /><br />" . $string['summative exam'] . "</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('summative')\" onmouseover=\"over('summative')\" onmouseout=\"out('summative')\" id=\"summative\"><img src=\"../artwork/summative.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Summative Exam\" /><br />" . $string['summative exam'] . "</td>\n";
  }
  if ($_POST['paper_type'] == 'survey') {
    echo "<td class=\"icon\" onclick=\"activate('survey')\" onmouseover=\"over('survey')\" onmouseout=\"out('survey')\" id=\"survey\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/survey.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Survey\" /><br />" . $string['survey'] . "</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('survey')\" onmouseover=\"over('survey')\" onmouseout=\"out('survey')\" id=\"survey\"><img src=\"../artwork/survey.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Survey\" /><br />" . $string['survey'] . "</td>\n";
  }
  if ($_POST['paper_type'] == 'osce') {
    echo "<td class=\"icon\" onclick=\"activate('osce')\" onmouseover=\"over('osce')\" onmouseout=\"out('osce')\" id=\"osce\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/osce.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"OSCE\" /><br />" . $string['osce station'] . "</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('osce')\" onmouseover=\"over('osce')\" onmouseout=\"out('osce')\" id=\"osce\"><img src=\"../artwork/osce.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"OSCE\" /><br />" . $string['osce station'] . "</td>\n";
  }
  if ($_POST['paper_type'] == 'offline') {
    echo "<td class=\"icon\" onclick=\"activate('offline')\" onmouseover=\"over('offline')\" onmouseout=\"out('offline')\" id=\"offline\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/offline.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Offline\" /><br />" . $string['offline paper'] . "</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('offline')\" onmouseover=\"over('offline')\" onmouseout=\"out('offline')\" id=\"offline\"><img src=\"../artwork/offline.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Offline\" /><br />" . $string['offline paper'] . "</td>\n";
  }
  if ($_POST['paper_type'] == 'peer_review') {
    echo "<td class=\"icon\" onclick=\"activate('peer_review')\" onmouseover=\"over('peer_review')\" onmouseout=\"out('peer_review')\" id=\"peer_review\" style=\"background-image:url('../artwork/on.png')\"><img src=\"../artwork/offline.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Peer Review\" /><br />Peer Review</td>\n";
  } else {
    echo "<td class=\"icon\" onclick=\"activate('peer_review')\" onmouseover=\"over('peer_review')\" onmouseout=\"out('peer_review')\" id=\"peer_review\"><img src=\"../artwork/offline.png\" width=\"48\" height=\"48\" border=\"0\" alt=\"Peer Review\" /><br />Peer Review</td>\n";
  }
?>
<td>&nbsp;</td>
</tr>
</table>
</div>
<br />
<div style="color:#001687"><?php echo $string['name']; ?></div> <input type="text" id="paper_name" name="paper_name" style="width:650px; background-color:#FFC0C0; color:#800000" value="<?php echo $_POST['paper_name']; ?>" style="width:650px" />
<input type="hidden" id="paper_type" name="paper_type" value="<?php echo $_POST['paper_type']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_POST['folder']; ?>" />
<br />
<br />
<div style="text-align:right"><input onclick="window.close();" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" />&nbsp;<input type="submit" name="submit" value="<?php echo $string['next']; ?>" style="width:100px" /></div>
</form>

<?php
} else {
  $paper_types = array('formative'=>0, 'progress'=>1, 'summative'=>2, 'survey'=>3, 'osce'=>4, 'offline'=>5, 'peer_review'=>6);
  if ($_POST['paper_type'] == 'summative') {
    $default_rubric = $string['msg6'];
  } else {
    $default_rubric = '';
  }
  
  // Create the new paper.
  $session = DateUtils::get_current_academic_year();
  
  if (isset($_POST['folder'])) {
    $folder = $_POST['folder'];
  } else {
    $folder = '';
  }
  
  if (isset($_POST['paper_name'])) {
    $paper_name = $_POST['paper_name'];
  } else {
    echo "Error, no paper name.";
    exit;
  }
  
  if ($cfg_summative_mgmt and $_POST['paper_type'] == 'summative') {
    // Summative paper so set null dates
    $result = $mysqli->prepare("INSERT INTO properties VALUES (NULL,?,NULL,NULL,'Europe/London',?,'','','white','black','#316AC5','#C00000','1','1','1',40,70,?,?,'',?,1,'',NULL,'00000000000000',NOW(),0,0,'1','1','1','1','0',NULL,'$session','',NULL,NULL,'0',0,'',NULL,NULL)");
  } else {
    $result = $mysqli->prepare("INSERT INTO properties VALUES (NULL,?,'20100101090000','20250101090000','Europe/London',?,'','','white','black','#316AC5','#C00000','1','1','1',40,70,?,?,'',?,1,'',NULL,'00000000000000',NOW(),0,0,'1','1','1','1','0',NULL,'$session','',NULL,NULL,'0',0,'',NULL,NULL)");
  }
  $result->bind_param('sssss', $paper_name, $paper_types[$_POST['paper_type']], $userID, $folder, $default_rubric);
  $result->execute();  
  $property_id = $mysqli->insert_id;
  $result->close();
?>
<style type="text/css">
  body {font-family:Arial,sans-serif; color:black; background-color:#F0F0F0; margin:4px; font-size:90%}
  table {font-size:100%}
  input,textarea {font-family:Arial,sans-serif; color:black}
</style>

<script language="JavaScript">
  function toggle(objectID) {
    if (document.getElementById(objectID).style.backgroundColor == 'white') {
      document.getElementById(objectID).style.backgroundColor = 'highlight';
      document.getElementById(objectID).style.color = 'white';
    } else {
      document.getElementById(objectID).style.backgroundColor = 'white';
      document.getElementById(objectID).style.color = 'black';
    }
  }
  
  function dateCopy(dropdownID) {
    if (document.getElementById('paper_type').value == 'summative' || document.getElementById('paper_type').value == 'osce' || document.getElementById('paper_type').value == 'offline') {
      switch(dropdownID) {
        case "fday":
          document.myform.tday.value = document.myform.fday.options[document.myform.fday.selectedIndex].value;
          break;
        case "fmonth":
          document.myform.tmonth.value = document.myform.fmonth.options[document.myform.fmonth.selectedIndex].value;
          break;
        case "fyear":
          document.myform.tyear.value = document.myform.fyear.options[document.myform.fyear.selectedIndex].value;
          break;
        case "tday":
          document.myform.fday.value = document.myform.tday.options[document.myform.tday.selectedIndex].value;
          break;
        case "tmonth":
          document.myform.fmonth.value = document.myform.tmonth.options[document.myform.tmonth.selectedIndex].value;
          break;
        case "tyear":
          document.myform.fyear.value = document.myform.tyear.options[document.myform.tyear.selectedIndex].value;
          break;
      }
    }
  }
  
  function checkForm() {
    var module_no = document.getElementById('module_no').value;
    var moduleList = '';
    for (var i = 0; i < module_no; i++) {
      objectID = 'module' + i;        
      if (document.getElementById(objectID).checked == true) {
        if (moduleList == '') {
          moduleList = document.getElementById(objectID).value;
        } else {
          moduleList += ',' + document.getElementById(objectID).value;
        }
      }
    }
    if (moduleList == '') {
      alert ("<?php echo $string['msg4']; ?>");
      return false;
    }
  }
  
  function checkSummativeForm() {
    periodSelect = document.getElementById('period');
    if (periodSelect.options[periodSelect.selectedIndex].text == '') {
      alert ("<?php echo $string['msg7']; ?>");
      return false;
    }
    
    durationSelect = document.getElementById('duration');
    if (durationSelect.options[durationSelect.selectedIndex].text == '') {
      alert (""<?php echo $string['msg8']; ?>"");
      return false;
    }
    
    cohortsizeSelect = document.getElementById('cohort_size');
    if (cohortsizeSelect.options[cohortsizeSelect.selectedIndex].text == '') {
      alert (""<?php echo $string['msg9']; ?>"");
      return false;
    }
    
    var module_no = document.getElementById('module_no').value;
    var moduleList = '';
    for (var i = 0; i < module_no; i++) {
      objectID = 'module' + i;        
      if (document.getElementById(objectID).checked == true) {
        if (moduleList == '') {
          moduleList = document.getElementById(objectID).value;
        } else {
          moduleList += ',' + document.getElementById(objectID).value;
        }
      }
    }
    if (moduleList == '') {
      alert ("<?php echo $string['msg4']; ?>");
      return false;
    }
    
  }
</script>
<body>
<?php
if ($_POST['paper_type'] == 'summative') {
  echo '<form name="myform" action="new_paper3.php" method="post" onsubmit="return checkSummativeForm()">';
} else {
  echo '<form name="myform" action="new_paper3.php" method="post" onsubmit="return checkForm()">';
}
?>
<table border="0" cellpadding="1" cellspacing="5" style="width:100%">
<tr>
<td>
<?php
  echo "<table width=\"100%\" border=\"0\">\n";
  if (!$cfg_summative_mgmt or $_POST['paper_type'] != 'summative') {
    echo "<tr><td><span style=\"font-weight:bold; color:#001687; font-size:120%\">" . $string['availability'] . "<span></td></tr>\n";
  } else {
    echo "<tr><td colspan=\"3\"><span style=\"font-weight:bold; color:#001687; font-size:120%\">Summative Exam Details<span></td></tr>\n";
  }
  if ($_POST['paper_type'] == 'summative' or $_POST['paper_type'] == 'osce' or $_POST['paper_type'] == 'offline') {
    $next_flag = 1;
    echo "<tr><td style=\"width:140px; text-align:right; vertical-align:top\">" . $string['academicsession'] . "</td><td>";
    $module_details = $mysqli->prepare("SELECT DISTINCT calendar_year FROM student_modules ORDER BY calendar_year DESC");
    $module_details->execute();
    $module_details->bind_result($calendar_year);
    echo "<select name=\"session\">\n";
    while ($module_details->fetch()) {
      if ($next_flag == 1) {
        $next_session = (substr($calendar_year,0,4) + 1) . '/' . (substr($calendar_year,-2) + 1);
        $sel = (DateUtils::get_current_academic_year() == $next_session) ? ' selected="selected"' : '';
        echo "<option value=\"$next_session\"$sel>$next_session</option>\n";
        $next_flag = 0;
      }
      $sel = (DateUtils::get_current_academic_year() == $calendar_year) ? ' selected="selected"' : '';
      echo "<option value=\"$calendar_year\"$sel>$calendar_year</option>\n";
    }
    echo "</select></td>\n";
  } else {
    echo "<input type=\"hidden\" name=\"session\" value=\"null\" />\n";
  }
  
  if (!$cfg_summative_mgmt or $_POST['paper_type'] != 'summative') {
    echo "</tr><tr><td align=\"right\" valign=\"top\">" . $string['from'] . "&nbsp;</td><td>";
    $date_array = getdate();

    // Available from Day
    $current_day = date('j');
    echo "<select name=\"fday\" onchange=\"dateCopy('fday')\">\n";
    for ($i=1; $i<=31; $i++) {
      echo '<option value="';
      if ($i < 10) echo '0';
      echo "$i\"";
      if ($i == $current_day) echo ' selected';
      echo '>';
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
    // Available from Month
    echo "<select name=\"fmonth\" onchange=\"dateCopy('fmonth')\">\n";
    $current_month = (date('n') + 1);
    if ($current_month > 12) $current_month = 1;
    $months = array('', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    for ($i=1; $i<=12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if ($i < 10) {
        if ($i == $current_month) {
          echo "<option value=\"0$i\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"0$i\">$trans_month</option>\n";
        }
      } else {
        if ($i == $current_month) {
          echo "<option value=\"$i\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"$i\">$trans_month</option>\n";
        }
      }    
    }
    echo "</select>\n";
    // Available from Year
    echo "<select name=\"fyear\" onchange=\"dateCopy('fyear')\">\n";
    for ($i = $date_array['year']; $i < ($date_array['year']+21); $i++) {
      if ($current_month == 1 and $i == ($date_array['year'] + 1)) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>\n<select name=\"ftime\">\n";
    // Available from Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
    }
    echo "</select>\n</td>";
    echo "<td align=\"right\">" . $string['to'] . "&nbsp;</td><td>";
    // Available from Day
    $current_day = date('j');
    echo "<select name=\"tday\" onchange=\"dateCopy('tday')\">\n";
    for ($i=1; $i<=31; $i++) {
      echo '<option value="';
      if ($i < 10) echo '0';
      echo "$i\"";
      if ($i == $current_day) echo ' selected';
      echo '>';
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
    // Available to Month
    echo "<select name=\"tmonth\" onchange=\"dateCopy('tmonth')\">\n";
    for ($i=1; $i<=12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if ($i < 10) {
        if ($i == $current_month) {
          echo "<option value=\"0$i\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"0$i\">$trans_month</option>\n";
        }
      } else {
        if ($i == $current_month) {
          echo "<option value=\"$i\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"$i\">$trans_month</option>\n";
        }
      }    
    }
    echo "</select>\n";
    // Available to Year
    if ($_POST['paper_type'] == 'summative' or $_POST['paper_type'] == 'osce' or $_POST['paper_type'] == 'offline') {
      $target_year = $date_array['year'];
    } else {
      $target_year = $date_array['year']+20;
    }
    echo "<select name=\"tyear\" onchange=\"dateCopy('tyear')\">\n";
    for ($i = $date_array['year']; $i < ($date_array['year']+21); $i++) {
      if ($i == $target_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>&nbsp;<select name=\"ttime\">\n";
    // Available to Hour
    foreach ($times as $key => $value) {
      echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
    }
    echo "</select>\n</td></tr>\n";

    echo "<tr><td align=\"right\">" . $string['timezone'] . "</td><td><select name=\"timezone\">";
    foreach ($timezone_array as $individual_zone => $display_zone) {
      if ($individual_zone == $cfg_timezone) {
        echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
      } else {
        echo "<option value=\"$individual_zone\">$display_zone</option>";
      }
    }
    echo '</optgroup></select></td></tr>';
  } else {
    echo '<td style="text-align:right">' . $string['barriersneeded'] . '</td><td><input type="checkbox" name="barriers_needed" value="1" chacked="checked" /><td style="text-align:right">Duration</td><td><select name="duration" id="duration">';
    $minutes = array('15'=>'15','20'=>'20','25'=>'25','30'=>'30','35'=>'35','40'=>'40','45'=>'45','50'=>'50','55'=>'55','60'=>'60','65'=>'65','70'=>'70','75'=>'75','80'=>'80','85'=>'85','90'=>'90','95'=>'95','100'=>'100','110'=>'110','120'=>'120','150'=>'150','180'=>'180');
    echo "<option value=\"\"></option>\n";
    foreach ($minutes as $key => $value) {
      echo "<option value=\"" . $key . "\">$value</option>\n";
    }
    echo '</select> ' . $string['mins'] . '</td></tr>';
    echo '<tr><td style="text-align:right">' . $string['daterequired'] . '</td><td><select name="period" id="period">';
    $months = array('january','february','march','april','may','june','july','august','september','october','november','december');
    echo "<option value=\"\"></option>\n";
    for ($i=0; $i<12; $i++) {
      echo "<option value=\"$i\">" . $string[$months[$i]] . "</option>\n";
    }
    echo '</select></td><td style="text-align:right">' . $string['cohortsize'] . '</td><td><select name="cohort_size" id="cohort_size">';
    echo "<option value=\"\"></option>\n";
    $sizes = array('&lt;whole cohort&gt', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300', '301-400', '401-500');
    foreach ($sizes as $size) {
      echo "<option value=\"$size\">$size</option>\n";
    }
    echo '</select></td><td style="text-align:right">' . $string['sittings'] . '</td><td><select name="sittings">';
    for ($i=1; $i<=6; $i++) {
      echo "<option value=\"$i\">$i</option>";
    }
    echo '</select></td></tr>';
    
    echo '<tr><td style="text-align:right">' . $string['campus'] . '</td><td colspan="5"><select name="campus">';
    foreach ($cfg_campus_list as $campus) {
      if ($campus == $cfg_campus_default) {
        echo "<option value=\"$campus\" selected>$campus</option>";
      } else {
        echo "<option value=\"$campus\">$campus</option>";
      }
    }
    echo '</select></td></tr>';
    echo '<tr><td style="text-align:right">' . $string['notes'] . '</td><td colspan="5"><textarea style="width:100%" cols="40" rows="5" name="notes"></textarea></td></tr>';
  }
    
  echo "</table>\n";
  
  echo "<div style=\"font-weight:bold; color:#001687; font-size:120%\">" . $string['modules'] . "</div><div style=\"display:block; background-color:white; height:230px; overflow-y:scroll; border:1px solid #95AEC8; font-size:90%\">";
  $team_sql = implode("','", $teams);
  if ($team_sql != '') $team_sql = "'$team_sql'";
  
  $module_no = 0;
  if (strpos($userroles,'SysAdmin') !== false) {
    $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules, schools WHERE moduleid != '' ORDER BY moduleID");
  } elseif (strpos($userroles,'Admin') !== false) {
    $schoolIDs = implode(',', SchoolUtils::getAdminSchools($userID, $mysqli));
    $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules WHERE (schoolid IN ($schoolIDs) OR moduleid IN ($team_sql)) AND moduleid != '' ORDER BY moduleID");
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN ($team_sql) AND moduleid != '' ORDER BY moduleID");
  }
  $result->execute();
  $result->bind_result($module_id, $module_name);
  while ($result->fetch()) {
    if (isset($_POST['module']) and $_POST['module'] == $module_id) {
      echo "<div style=\"background-color:#B3C8E8\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module_id . "\" checked />&nbsp;" . $module_id . " - " . substr($module_name,0,60) . "</div>\n";
    } else {
      echo "<div style=\"background-color:white\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module_id . "\" />&nbsp;" . $module_id . " - " . substr($module_name,0,60) . "</div>\n";
    }
    $module_no++;
  }
  $result->close();
  echo "</div>\n";

  echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" />\n";
  echo "<input type=\"hidden\" name=\"paper_type\" id=\"paper_type\" value=\"" . $_POST['paper_type'] . "\" />\n";
  echo "<input type=\"hidden\" name=\"paper_name\" id=\"paper_name\" value=\"" . $_POST['paper_name'] . "\" />\n";
  echo "<input type=\"hidden\" name=\"property_id\" value=\"$property_id\" />\n";
  echo "<input type=\"hidden\" name=\"current_year\" id=\"current_year\" value=\"year1\" />\n";
  echo "<input type=\"hidden\" name=\"folder\" value=\"" . $_POST['folder'] . "\" />\n";
?>
<br />
<div style="text-align:right"><input type="submit" name="submit2" value="<?php echo $string['finish']; ?>" style="width:100px" /></div>

</td>
</tr>
</table>
<?php
}
?>

</body>
</html>
