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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Create New Paper<?php echo " $cfg_install_type"; ?></title>
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
<style>
  body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:6px; font-size:90%}
  table {font-size:100%}
  textarea, input[type=text], select {font-family: Arail,sans-serif; border: 1px solid #7F9DB9}
</style>
<script language="JavaScript">
  function over(id) {
    if (id != document.getElementById('paper_type').value) {
      document.getElementById(id).src='../artwork/' + id + '_over.png';
    }
  }
  
  function out(id) {
    if (id != document.getElementById('paper_type').value) {
      document.getElementById(id).src='../artwork/' + id + '_off.png';
    }
  }
  
  function activate(id) {
    document.getElementById('formative').src='../artwork/formative_off.png';
    document.getElementById('progress').src='../artwork/progress_off.png';
    document.getElementById('summative').src='../artwork/summative_off.png';
    document.getElementById('survey').src='../artwork/survey_off.png';
    document.getElementById('osce').src='../artwork/osce_off.png';
    document.getElementById('offline').src='../artwork/offline_off.png';
  
    document.getElementById(id).src='../artwork/' + id + '_on.png';
    document.getElementById('paper_type').value = id;
  
  }
  
  function warning() {
    alert("The name '<?php echo $_POST['paper_name']; ?>' is already in use. Please select an alternative paper title.");
  }
</script>
</head>

<body onload="warning();">
<form name="theform" action="new_paper2.php" method="post">
<div style="text-align:center; border:solid 1px #7F9DB9; background-color:white">
<table cellpadding="0" cellspacing="1" style="background-color:white; width:100%">
<tr>
<td colspan="6" style="font-size:120%; font-weight:bold; background-color:#DDE7EE; color:#001687; border-bottom:1px solid #C5C5C5">&nbsp;Paper Type</td>
</tr>
<tr>
<?php
  if ($_POST['paper_type'] == '0') {
    echo "<td onclick=\"activate('0')\" onmouseover=\"over('formative')\" onmouseout=\"out('formative')\"><img id=\"formative\" src=\"../artwork/formative_on.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Formative Self-Assessment\" /></td>\n";
  } else {
    echo "<td onclick=\"activate('0')\" onmouseover=\"over('formative')\" onmouseout=\"out('formative')\"><img id=\"formative\" src=\"../artwork/formative_off.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Formative Self-Assessment\" /></td>\n";
  }
  if ($_POST['paper_type'] == '1') {
    echo "<td onclick=\"activate('1')\" onmouseover=\"over('progress')\" onmouseout=\"out('progress')\"><img id=\"progress\" src=\"../artwork/progress_on.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Progress Test\" /></td>\n";
  } else {
    echo "<td onclick=\"activate('1')\" onmouseover=\"over('progress')\" onmouseout=\"out('progress')\"><img id=\"progress\" src=\"../artwork/progress_off.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Progress Test\" /></td>\n";
  }
  if ($_POST['paper_type'] == '2') {
    echo "<td onclick=\"activate('2')\" onmouseover=\"over('summative')\" onmouseout=\"out('summative')\"><img id=\"summative\" src=\"../artwork/summative_on.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Summative Exam\" /></td>\n";
  } else {
    echo "<td onclick=\"activate('2')\" onmouseover=\"over('summative')\" onmouseout=\"out('summative')\"><img id=\"summative\" src=\"../artwork/summative_off.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Summative Exam\" /></td>\n";
  }
  if ($_POST['paper_type'] == '3') {
    echo "<td onclick=\"activate('3')\" onmouseover=\"over('survey')\" onmouseout=\"out('survey')\"><img id=\"survey\" src=\"../artwork/survey_on.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Survey\" /></td>\n";
  } else {
    echo "<td onclick=\"activate('3')\" onmouseover=\"over('survey')\" onmouseout=\"out('survey')\"><img id=\"survey\" src=\"../artwork/survey_off.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Survey\" /></td>\n";
  }
  if ($_POST['paper_type'] == '4') {
    echo "<td onclick=\"activate('4')\" onmouseover=\"over('osce')\" onmouseout=\"out('osce')\"><img id=\"osce\" src=\"../artwork/osce_on.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"OSCE\" /></td>\n";
  } else {
    echo "<td onclick=\"activate('4')\" onmouseover=\"over('osce')\" onmouseout=\"out('osce')\"><img id=\"osce\" src=\"../artwork/osce_off.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"OSCE\" /></td>\n";
  }
  if ($_POST['paper_type'] == '5') {
    echo "<td onclick=\"activate('5')\" onmouseover=\"over('offline')\" onmouseout=\"out('offline')\"><img id=\"offline\" src=\"../artwork/offline_on.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Offline\" /></td>\n";
  } else {
    echo "<td onclick=\"activate('5')\" onmouseover=\"over('offline')\" onmouseout=\"out('offline')\"><img id=\"offline\" src=\"../artwork/offline_off.png\" width=\"98\" height=\"104\" border=\"0\" alt=\"Offline\" /></td>\n";
  }
?>
</tr>
</table>
</div>
<br />
<span style="font-weight:bold; color:#001687; font-size:120%">Name<span> <input type="text" id="paper_name" name="paper_name" style="background-color:#FFC0C0; color:#800000" value="<?php echo $_POST['paper_name']; ?>" style="width:650px" />
<input type="hidden" id="paper_type" name="paper_type" value="<?php echo $_POST['paper_type']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_POST['folder']; ?>" />
<br />
<br />
<div style="text-align:right"><input onclick="window.close();" type="button" name="cancel" value="Cancel" style="width:100px" />&nbsp;<input type="submit" name="submit" value="Next &gt;" style="width:100px" /></div>
</form>

<?php
} else {
  $paper_types = array('formative'=>0,'progress'=>1,'summative'=>2,'survey'=>3,'osce'=>4,'offline'=>5);
  if ($_POST['paper_type'] == 'summative') {
    $default_rubric = 'This is a closed-book examination and students may <em>not</em> refer to any other source (including dictionaries) or person in taking this paper. No electronic equipment, other than the examination computer, may be used.';
  } else {
    $default_rubric = '';
  }
  
  // Create the new paper.
  if (date('n') < 9) {   // Before September
    $session = (date('Y')-1) . '/' . date('y');
  } else {
    $session = date('Y') . '/' . (date('y') + 1);
  }
  
  if (isset($_POST['folder'])) {
    $folder = $_POST['folder'];
  } else {
    $folder = '';
  }
  
  if (isset($_POST['paper_name'])) {
    $paper_name = stripslashes($_POST['paper_name']);
  } else {
    echo "Error, no paper name.";
    exit;
  }
  
  $result = $mysqli->prepare("INSERT INTO properties VALUES (NULL,?,'20030101090000','20250101090000','Europe/London',?,'','','white','black','#316AC5','#C00000','1','1','1',40,70,?,?,'',?,1,'',NULL,'00000000000000',NOW(),0,0,'1','1','1','1','0',NULL,'$session','',NULL,NULL,'0',0,'')");
  $result->bind_param('sssss', $paper_name, $paper_types[$_POST['paper_type']], $userID, $folder, $default_rubric);
  $result->execute();  
  $property_id = $mysqli->insert_id;
  $result->close();
?>
<style>
  body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:4px; font-size:90%}
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
      alert ("There are no modules selected. Papers must be assigned to at least one module.");
      return false;
    }
  }
</script>
<body>
<form name="myform" action="new_paper3.php" method="post" onsubmit="return checkForm()">
<table border="0" cellpadding="1" cellspacing="5" style="width:100%">
<tr>
<td>
<?php
  echo "<table width=\"100%\"><tr><td><span style=\"font-weight:bold; color:#001687; font-size:120%\">Availability<span></td></tr>\n";
  if ($_POST['paper_type'] == 'summative' or $_POST['paper_type'] == 'osce' or $_POST['paper_type'] == 'offline') {
    $next_flag = 1;
    echo "<tr><td align=\"right\" valign=\"top\">Academic Session&nbsp;</td><td>";
    $module_details = $mysqli->prepare("SELECT DISTINCT calendar_year FROM student_modules ORDER BY calendar_year DESC");
    $module_details->execute();
    $module_details->bind_result($calendar_year);
    echo "<select name=\"session\">\n";
    while ($row = $module_details->fetch()) {
      if ($next_flag == 1) {
        $next_session = (substr($calendar_year,0,4) + 1) . '/' . (substr($calendar_year,-2) + 1);
        echo "<option value=\"$next_session\">$next_session</option>\n";
        $next_flag = 0;
      }
      echo "<option value=\"$calendar_year\">$calendar_year</option>\n";
    }
    echo "</select></td></tr>\n";
  } else {
    echo "<input type=\"hidden\" name=\"session\" value=\"null\" />\n";
  }
  
  echo "<tr><td align=\"right\" valign=\"top\">From&nbsp;</td><td>";
  $date_array = getdate();

  // Available from Month
  echo "<select name=\"fmonth\" onchange=\"dateCopy('fmonth')\">\n";
  $current_month = (date('n') + 1);
  if ($current_month > 12) $current_month = 1;
  $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
  for ($i=1; $i<=12; $i++) {
    if ($i < 10) {
      if ($i == $current_month) {
        echo "<option value=\"0$i\" selected>" . $months[$i-1] . "</option>\n";
      } else {
        echo "<option value=\"0$i\">" . $months[$i-1] . "</option>\n";
      }
    } else {
      if ($i == $current_month) {
        echo "<option value=\"$i\" selected>" . $months[$i-1] . "</option>\n";
      } else {
        echo "<option value=\"$i\">" . $months[$i-1] . "</option>\n";
      }
    }    
  }
  echo "</select>\n";
  // Available from Day
  $current_day = date('j');
  echo "<select name=\"fday\" onchange=\"dateCopy('fday')\">\n";
  for ($i=1; $i<=31; $i++) {
    echo '<option value="';
    if ($i < 10) echo '0';
    echo "$i\"";
    if ($i == $current_day) echo ' selected';
    echo ">$i";
    if ($i==1 or $i==21 or $i==31) {
      echo 'st';
    } elseif ($i==2 or $i==22) {
      echo 'nd';
    } elseif ($i==3 or $i==23) {
      echo 'rd';
    } else {
      echo 'th';
    }
    echo "</option>\n";
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
  echo "<td align=\"right\">To&nbsp;</td><td><select name=\"tmonth\" onchange=\"dateCopy('tmonth')\">\n";
  // Available to Month
  $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
  for ($i=1; $i<=12; $i++) {
    if ($i < 10) {
      if ($i == $current_month) {
        echo "<option value=\"0$i\" selected>" . $months[$i-1] . "</option>\n";
      } else {
        echo "<option value=\"0$i\">" . $months[$i-1] . "</option>\n";
      }
    } else {
      if ($i == $current_month) {
        echo "<option value=\"$i\" selected>" . $months[$i-1] . "</option>\n";
      } else {
        echo "<option value=\"$i\">" . $months[$i-1] . "</option>\n";
      }
    }    
  }
  echo "</select>\n";
  // Available to Day
  echo "<select name=\"tday\" onchange=\"dateCopy('tday')\">\n";
  for ($i = 1; $i <= 31; $i++) {
    echo '<option value="';
    if ($i < 10) echo '0';
    echo "$i\"";
    if ($i == $current_day) echo ' selected';
    echo ">$i";
    if ($i==1 or $i==21 or $i==31) {
      echo 'st';
    } elseif ($i==2 or $i==22) {
      echo 'nd';
    } elseif ($i==3 or $i==23) {
      echo 'rd';
    } else {
      echo 'th';
    }
    echo "</option>\n";
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
  $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
  foreach ($times as $key => $value) {
    echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
  }
  echo "</select>\n</td></tr>\n";

  echo "<tr><td align=\"right\">Time Zone</td><td><select name=\"timezone\">";
  $timezone_array = array('*Africa','Dakar','Johannesburg','*America','Anchorage','Denver','Chicago','Halifax','Los_Angeles','New_York','Mexico_City','*Asia','Dubai','Istanbul','Kuala_Lumpur','Shanghai','Singapore','Tokyo','*Australia','Adelaide','Perth','Sydney','Victoria','*Europe','Budapest','London','Moscow','Oslo','Paris','Vienna','*Pacific','Fiji','Auckland');
  $old_prefix = '';
  foreach ($timezone_array as $individual_zone) {
    if (substr($individual_zone,0,1) == '*') {
      if ($old_prefix != '') echo "</optgroup>\n";
      echo "<optgroup label=\"" . substr($individual_zone,1) . "\">\n";
      $old_prefix = substr($individual_zone,1);
    } else {
      if ($individual_zone == 'London') {  // Make UK time the default.
        echo "<option value=\"" . $old_prefix . "/" . $individual_zone . "\" selected>" . str_replace('_',' ',$individual_zone) . "</option>";
      } else {
        echo "<option value=\"" . $old_prefix . "/" . $individual_zone . "\">" . str_replace('_',' ',$individual_zone) . "</option>";
      }
    }
  }
  echo '</optgroup></select></td></tr>';
  
  echo "</table>\n";
  
  echo "<div style=\"font-weight:bold; color:#001687; font-size:120%\">Module(s)</div><div style=\"display:block; background-color:white; height:250px; overflow-y:scroll; border:1px solid #95AEC8; font-size:90%\">";
  $team_sql = implode("','", $teams);
  if ($team_sql != '') $team_sql = "'$team_sql'";
  
  $module_no = 0;
  if (strpos($userroles,'SysAdmin') !== false) {
    $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules, schools ORDER BY moduleID");
  } elseif (strpos($userroles,'Admin') !== false) {
    $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules, schools WHERE modules.school=schools.school AND faculty='$faculty' ORDER BY moduleID");
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($team_sql) ORDER BY moduleID");
  }
  $result->execute();
  $result->bind_result($module_id, $module_name);
  while ($row = $result->fetch()) {
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
<div style="text-align:right"><input type="submit" name="back2" value="&lt Back" style="width:100px" />&nbsp;&nbsp;<input type="submit" name="submit2" value="Finish" style="width:100px" /></div>

</td>
</tr>
</table>
<?php
}
?>

</body>
</html>
