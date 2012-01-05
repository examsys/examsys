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
* Allows the properties of a paper to be edited.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/add_edit.inc';  // to clear MS Office tags

function modulo($n,$b) {
  return $n-$b*floor($n/$b);
}

if (isset($_POST['Submit'])) {
  if (isset($_POST['display_correct_answer']) and $_POST['display_correct_answer'] == 1) {
    $display_correct_answer = 1;
  } else {
    $display_correct_answer = 0;
  }
  if (isset($_POST['display_students_response']) and $_POST['display_students_response'] == 1) {
    $display_students_response = 1;
  } else {
    $display_students_response = 0;
  }
  if (isset($_POST['display_question_mark']) and $_POST['display_question_mark'] == 1) {
    $display_question_mark = 1;
  } else {
    $display_question_mark = 0;
  }
  if (isset($_POST['display_feedback']) and $_POST['display_feedback'] == 1) {
    $display_feedback = 1;
  } else {
    $display_feedback = 0;
  }
    
  $tmp_marking = $_POST['marking'];
  if ($tmp_marking == '') $tmp_marking = '0';
  if ($tmp_marking == '2') $tmp_marking = $_POST['std_set'];
        
  $tmp_pass_mark = $_POST['pass_mark'];
  if ($tmp_pass_mark == '') $tmp_pass_mark = 40;

  $tmp_distinction_mark = $_POST['distinction_mark'];
  if ($tmp_distinction_mark == '') $tmp_distinction_mark = 70;

  $editProperties = $mysqli->prepare("UPDATE properties SET marking=?, pass_mark=?, distinction_mark=?, display_correct_answer=?, display_students_response=?, display_question_mark=?, display_feedback=? WHERE property_id=?");
  $editProperties->bind_param('siiiiiii', $tmp_marking, $tmp_pass_mark, $tmp_distinction_mark, $display_correct_answer, $display_students_response, $display_question_mark, $display_feedback, $_POST['paperID']);
  $editProperties->execute();
  $editProperties->close();
  
  // Release objectives-based feedback
  $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id=? AND type='objectives'");
  $editProperties->bind_param('i', $_POST['paperID']);
  $editProperties->execute();
  $editProperties->close();
  if (isset($_POST['objectives_report']) and $_POST['objectives_report'] == 1) {
    $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'objectives')");
    $editProperties->bind_param('i', $_POST['paperID']);
    $editProperties->execute();
    $editProperties->close();
  }

  // Release question-based feedback
  $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id=? AND type='questions'");
  $editProperties->bind_param('i', $_POST['paperID']);
  $editProperties->execute();
  $editProperties->close();
  if (isset($_POST['questions_report']) and $_POST['questions_report'] == 1) {
    $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'questions')");
    $editProperties->bind_param('i', $_POST['paperID']);
    $editProperties->execute();
    $editProperties->close();
  }
  ?>
    <html>
    <head><title>Edit</title>
    <script language="JavaScript">
      function closeWindow() {
        <?php
          if (isset($_POST['noadd']) and $_POST['noadd'] == 'y') {
        ?>
        window.opener.location.reload();
        window.opener.close();
        window.close();
        <?php
          } else {
        ?>
        window.opener.location.reload();
        window.close();
        <?php
          }
        ?>
      }
    </script>
    </head>
    <body onload="closeWindow();">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="closeWindow();" /></div>
    </form>
  <?php
} else {
  $option_no = 1;
  
  $result = $mysqli->prepare("SELECT display_students_response, display_correct_answer, display_question_mark, display_feedback, paper_title, paper_type, start_date, end_date, timezone, bgcolor, fgcolor, themecolor, labelcolor, fullscreen, marking, bidirectional, pass_mark, distinction_mark, folder, labs, rubric, calculator, externals, exam_duration, moduleID, calendar_year, sound_demo, crypt_name FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($display_students_response, $display_correct_answer, $display_question_mark, $display_feedback, $paper_title, $paper_type, $start_date, $end_date, $timezone, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $marking, $bidirectional, $pass_mark, $distinction_mark, $folder, $labs, $rubric, $calculator, $externals, $exam_duration, $moduleID, $calendar_year, $sound_demo, $crypt_name);
  $result->fetch();
  $result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Properties<?php echo " $cfg_install_type"; ?></title>

  <style>
    body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:0px; font-size:100%}
    table {font-size:100%; text-align:left}
    input,textarea {font-family:Arial,sans-serif; color:black}
    .indenton {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
    .indentoff {text-indent:-23px; padding-left:23px; background-color:white}
  </style>

  <script language="JavaScript">
    function launchHelp(pageID) {
      var winheight = screen.height-100;
      if (screen.width == 800) {
        notice=window.open("../help/staff/index.php?id=" + pageID + "","help","width=770,height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        notice.moveTo(10,10);
      } else {
        notice=window.open("../help/staff/index.php?id=" + pageID + "","help","width=950,height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        notice.moveTo(10,10);
      }
    }
  </script>
</head>
<body onload="window.focus();">
<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<table border="0" cellpadding="1" cellspacing="5" style="width:100%; height:100%; font-size:90%">
<tr><td valign="top" style="background-color:white; border:1px solid #7F9DB9; width:120px">

<table cellspacing="0" cellpadding="0" border="0" style="font-size:90%; width:120px">
<tr><td style="background-image:url('../artwork/2007_button_on.png'); height:25px; color:#00156E" valign="middle">&nbsp;<?php echo $string['generaltab']; ?></td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;<?php echo $string['securitytab']; ?></td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;<?php echo $string['reviewerstab']; ?></td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;<?php echo $string['rubrictab']; ?></td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;<?php echo $string['prologuetab']; ?></td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;<?php echo $string['postscripttab']; ?></td></tr>
</table>

</td>

<td style="background-color:white; border:1px solid #7F9DB9" valign="top">

<table id="general" style="height:590px; width:100%; font-size:90%<?php if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') echo ';display:none'; ?>"cellpadding="0" cellspacing="0" border="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/general_heading_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['generalheading']; ?></td></tr>
<td style="text-align:left; vertical-align:top" colspan="2">
   <?php
     echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom: 1px solid #CFDBEB\">&nbsp;" . $string['paperdetails'] . "</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['url'] . "&nbsp;</td><td colspan=\"3\"><a href=\"https://" . $_SERVER['HTTP_HOST'] . "\" target=\"_blank\" style=\"color:blue\">https://" . $_SERVER['HTTP_HOST'] . "</a> " . $string['onlyonexamday'] . "</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['name'] . "&nbsp;</td><td colspan=\"3\"><input type=\"text\" size=\"75\" maxlength=\"255\" value=\"$paper_title\" name=\"paper\" disabled /><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\"></td></tr>\n";
   ?>
     <tr><td align="right" valign="top"><?php echo $string['type']; ?>&nbsp;</td><td>
     <select name="paper_type" disabled>
     <option value="0"<?php if ($paper_type == '0') echo ' selected'; ?> /><?php echo $string['formative self-assessment']; ?></option>
     <option value="1"<?php if ($paper_type == '1') echo ' selected'; ?> /><?php echo $string['progress test']; ?></option>
     <option value="2"<?php if ($paper_type == '2') echo ' selected'; ?> /><?php echo $string['summative exam']; ?></option>
     <option value="3"<?php if ($paper_type == '3') echo ' selected'; ?> /><?php echo $string['survey']; ?></option>
     <option value="4"<?php if ($paper_type == '4') echo ' selected'; ?> /><?php echo $string['osce station']; ?></option>
     <option value="5"<?php if ($paper_type == '5') echo ' selected'; ?> /><?php echo $string['offline paper']; ?></option>
   <?php
     echo "<td align=\"right\" valign=\"top\">" . $string['folder'] . "&nbsp;</td><td valign=\"top\">\n<select style=\"width:210px\" name=\"folderID\" disabled>\n";
     echo "<option value=\"\"></option>";
     $additional = '';
     
     $team_query = $mysqli->prepare("SELECT DISTINCT name FROM teams WHERE memberID=? ORDER BY name");
     $team_query->bind_param('s', $userID);
     $team_query->execute();
     $team_query->store_result();
     $team_query->bind_result($team_name);
     while ($team_query->fetch()) {
       if ($additional == '') {
         $additional = ' OR team_name IN ("' . $team_name . '"';
       } else {
         $additional .= ',"' . $team_name . '"';
       }
     }
     $team_query->close();
     
     if ($additional != '') $additional .= ')';
     if ($folder != '') $additional .= ' OR id=' . $folder;
     
     $folder_details = $mysqli->prepare("SELECT id, name FROM folders WHERE ownerID=? $additional ORDER BY name");
     $folder_details->bind_param('s', $userID);
     $folder_details->execute();
     $folder_details->bind_result($folder_id, $folder_name);
     while ($folder_details->fetch()) {
       $path_parts = substr_count($folder_name,';');
       $folder_array = explode(';',$folder_name);
       $display_name = str_repeat('&nbsp;',$path_parts * 4) . $folder_array[$path_parts];
       if ($folder == $folder_id) {
         echo "<option value=\"" . $folder_id . "\" selected>" . $display_name . "</option>";
       } else {
         echo "<option value=\"" . $folder_id . "\">" . $display_name . "</option>";
       }
     }
     $folder_details->close();
     echo "</select>\n</td></tr>\n";
     
     echo "<tr><td align=\"right\" valign=\"top\">";
     if ($paper_type != '4') echo $string['feedback'] .  '&nbsp';
     echo "</td><td colspan=\"3\">";
     if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
       // Objectives-based Feedback
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id=? AND type='objectives'");
       $feedback_details->bind_param('i', $_GET['paperID']);
       $feedback_details->execute();
       $feedback_details->bind_result($idfeedback_release);
       $feedback_details->fetch();
       if ($idfeedback_release == '') {
         echo "<div><input type=\"checkbox\" value=\"1\" name=\"objectives_report\" />";
       } else {
         echo "<div><input type=\"checkbox\" value=\"1\" name=\"objectives_report\" checked />";
       }
       $feedback_details->close();
     
       echo $string['objectivesreport'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . "/mapping/user_feedback.php?id=$crypt_name\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/mapping/user_feedback.php?id=$crypt_name</a></div>\n";
     
       // Question-based Feedback
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id=? AND type='questions'");
       $feedback_details->bind_param('i', $_GET['paperID']);
       $feedback_details->execute();
       $feedback_details->bind_result($idfeedback_release);
       $feedback_details->fetch();
       if ($idfeedback_release == '') {
         echo "<br /><div><input type=\"checkbox\" value=\"1\" name=\"questions_report\" />";
       } else {
         echo "<br /><div><input type=\"checkbox\" value=\"1\" name=\"questions_report\" checked />";
       }
       $feedback_details->close();
     
       echo $string['questionfeedback'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . "/paper/feedback.php?id=$crypt_name\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/paper/feedback.php?id=$crypt_name</a></div>\n";
     }
     if ($paper_type == '0') {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on">';
     } else {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="display:none">';
     }
     if ($paper_type != '4') {
     ?>
     <tr><td><input type="checkbox" name="display_students_response" value="1"<?php if ($display_students_response == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['ticks_crosses'];?></td><td><input type="checkbox" name="display_question_mark" value="1"<?php if ($display_question_mark == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['question_marks'];?></td></tr>
     <tr><td><input type="checkbox" name="display_correct_answer" value="1"<?php if ($display_correct_answer == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['correctanswerhighlight'];?></td><td><input type="checkbox" name="display_feedback" value="1"<?php if ($display_feedback == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['textfeedback'];?></td></tr>
     </table>
     <?php
     }
     if ($paper_type != '0') {
       echo '<div id="feedback_off">';
     } else {
       echo '<div id="feedback_off" style="display:none">';
     }
     echo "<br />&nbsp;</div>";
     
     echo "<tr>\n";
     
     if ($paper_type == '4') {
       echo '<input type="hidden" name="bgcolor" value="' . $bgcolor . '" />';
       echo '<input type="hidden" name="fgcolor" value="' . $fgcolor . '" />';
       echo '<input type="hidden" name="themecolor" value="' . $themecolor . '" />';
       echo '<input type="hidden" name="labelcolor" value="' . $labelcolor . '" />';
     } else {
       echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA;color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['displayoptions'] ."</td></tr>\n";
       echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
       if ($fullscreen == 0) {
         echo "<tr><td align=\"right\">" . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\" disabled>\n<option value=\"0\" selected>" . $string['windowed'] ."</option><option value=\"1\">" . $string['fullscreen'] ."</option>\n</select></td>";
       } else {
         echo "<tr><td align=\"right\">" . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\" disabled>\n<option value=\"0\">" . $string['windowed'] ."</option><option value=\"1\" selected>" . $string['fullscreen'] ."</option>\n</select></td>";
       }
       if ($bidirectional == 1) {
         echo "<td align=\"right\">" . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\" disabled><option value=\"0\">" . $string['unidirectional'] ."</option><option value=\"1\"selected>" . $string['bidirectional'] ."</option></select></td></tr>\n";
       } else {
         echo "<td align=\"right\">" . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\" disabled><option value=\"0\" selected>" . $string['unidirectional'] ."</option><option value=\"1\">" . $string['bidirectional'] ."</option></select></td></tr>\n";
       }
       
       echo "<tr>\n";
       echo "<td align=\"right\">" . $string['background'] . "&nbsp;</td><td><div onclick=\"showPicker('bgcolor',event)\" id=\"span_bgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$bgcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"bgcolor\" name=\"bgcolor\" value=\"$bgcolor\" /></td>";
       echo "<td align=\"right\">" . $string['foreground'] . "&nbsp;</td><td><div onclick=\"showPicker('fgcolor',event)\" id=\"span_fgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$fgcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"fgcolor\" name=\"fgcolor\" value=\"$fgcolor\" /></td>";
       echo "</tr>\n";
   
       echo "<tr>\n";
       echo "<td align=\"right\">" . $string['theme'] . "&nbsp;</td><td><div onclick=\"showPicker('themecolor',event)\" id=\"span_themecolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$themecolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" /></td>";
       echo "<td align=\"right\">" . $string['labelsnotes'] . "&nbsp;</td><td><div onclick=\"showPicker('labelcolor',event)\" id=\"span_labelcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$labelcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" /></td>";
       echo "</tr>\n";

       if ($calculator == 1) {
         echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"calculator\" checked disabled /> " . $string['displaycalculator'] ."</td>";
       } else {
         echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"calculator\" disabled /> " . $string['displaycalculator'] ."</td>";
       }
       if ($sound_demo == 1) {
         echo "<td align=\"right\">" . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"sound_demo\" checked disabled /> " . $string['demosoundclip'] ."</td></tr>\n";
       } else {
         echo "<td align=\"right\">" . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"sound_demo\" disabled /> " . $string['demosoundclip'] ."</td></tr>\n";
       }
       echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     }
     echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['marking'] . "</td></tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     if ($paper_type == '4') {
       echo "<tr><td align=\"right\" valign=\"top\">Overall&nbsp;Classification&nbsp;</td><td valign=\"top\" colspan=\"3\"><select name=\"marking\">";
     ?>
       <option value="5"<?php if ($marking == '5') echo ' selected'; ?> />&lt;Automatic&gt;</option>
       <option value="3"<?php if ($marking == '3') echo ' selected'; ?> />Clear Fail | Borderline | Clear Pass</option>
       <option value="4"<?php if ($marking == '4') echo ' selected'; ?> />Fail | Borderline fail | Borderline pass | Pass | Good pass</option>
       <option value="6"<?php if ($marking == '6') echo ' selected'; ?> />Clear FAIL | BORDERLINE | Clear PASS | Honours PASS</option>
  <?php
    echo "<tr><td colspan=\"4\">" . wysiwyg_editor('oEdit1','osce_marking_guidance',$paper_prologue,684,230);
  ?>
</td></tr>
     <?php
       echo "</select></td></tr>\n";
     } else {
       echo "<tr><td align=\"right\" valign=\"top\">" . $string['passmark'] . "&nbsp;</td><td valign=\"top\"><select name=\"pass_mark\" id=\"pass_mark\"";
       if ($paper_type == '3') echo ' disabled';
       echo '>';
       for ($i=0; $i<=100; $i++) {
         if ($i == $pass_mark) {
           echo "<option value=\"$i\" selected>$i%</option>\n";
         } else {
           echo "<option value=\"$i\">$i%</option>\n";
         }
       }
       echo "</select></td><td rowspan=\"2\" style=\"text-align:right\" valign=\"top\">" . $string['method'] . "&nbsp;</td><td rowspan=\"2\">";
     ?>
       <input type="radio" id="marking1" name="marking" value="0"<?php if ($marking == '0') echo ' checked'; ?> /><?php echo $string['noadjustment']; ?><br />
       <input type="radio" id="marking2" name="marking" value="1"<?php if ($marking == '1') echo ' checked'; ?> /><?php echo $string['calculatrrandommark']; ?><br />
     <?php
       // Look for any Standard Setting reviews for the paper.
       $std_set_details = $mysqli->prepare("SELECT DISTINCT title, surname, initials, setterID, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, group_review FROM standards_setting, users WHERE standards_setting.setterID=users.id AND paperID=? ORDER BY std_set DESC");
       $std_set_details->bind_param('i', $_GET['paperID']);
       $std_set_details->execute();
       $std_set_details->store_result();
       if ($std_set_details->num_rows > 0) {
         echo "<input type=\"radio\" id=\"marking3\" name=\"marking\" value=\"2\"";
         if (substr($marking,0,1) == '2') echo ' checked';
         echo " />";
         echo 'Std Set <select name="std_set">';
         $std_set_details->bind_result($std_set_title, $std_set_surname, $std_set_initials, $std_set_reviewer, $std_set_display_date, $std_set_date, $group_review);
         while ($row = $std_set_details->fetch()) {
           if ($group_review == 'No') {
             echo "<option value=\"2,$std_set_reviewer,$std_set_date\">$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
           } else {
             echo "<option value=\"2,$std_set_reviewer,$std_set_date\">Group Review - $std_set_display_date</option>";
           }
         }
         echo "</select>\n";
         $std_set_details->close();
       } else {
         echo "<input type=\"radio\" id=\"marking3\" name=\"marking\" value=\"2\" disabled />";
         echo '<span style="color:#808080">Std Set</span>';
       }
     }
     if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
       echo "<tr><td align=\"right\" valign=\"top\">" . $string['distinction'] . "</td><td><select name=\"distinction_mark\">";
       for ($i=0; $i<=100; $i++) {
         if ($i == $distinction_mark) {
           echo "<option value=\"$i\" selected>$i%</option>\n";
         } else {
           echo "<option value=\"$i\">$i%</option>\n";
         }
       }
       echo "</select></td></tr>\n";
     } else {
       echo "<tr><td></td><td></td></tr>\n";
     }
     echo "</table>\n";
   ?>
</td>
</tr>
</table>

</td>
</tr>
<tr><td colspan="2" align="right"><input type="submit" style="width:100px" name="Submit" value="<?php echo $string['ok']; ?>">&nbsp;<input type="button" name="home" style="width:100px" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></td></tr>
</table>

<input type="hidden" name="noadd" value="<?php if (isset($_GET['noadd'])) echo $_GET['noadd']; ?>" />
</form>
<?php
  }
$mysqli->close();
?>
</body>
</html>
