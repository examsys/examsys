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
* Allows the properties of a paper to be edited.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/add_edit.inc';  // to clear MS Office tags

function modulo($n,$b) {
  return $n-$b*floor($n/$b);
}

if (isset($_POST['Submit'])) {
  if ($_POST['display_correct_answer'] == 1) {
    $display_correct_answer = 1;
  } else {
    $display_correct_answer = 0;
  }
  if ($_POST['display_students_response'] == 1) {
    $display_students_response = 1;
  } else {
    $display_students_response = 0;
  }
  if ($_POST['display_question_mark'] == 1) {
    $display_question_mark = 1;
  } else {
    $display_question_mark = 0;
  }
  if ($_POST['display_feedback'] == 1) {
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
  
  $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id=?");
  $editProperties->bind_param('i', $_POST['paperID']);
  $editProperties->execute();
  $editProperties->close();
  if ($_POST['objectives_report'] == 1) {
    $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL,?,NOW())");
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
          if ($_POST['noadd'] == 'y') {
        ?>
        window.opener.location = "paper_details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>&folder=<?php echo $_POST['folderID']; ?>&school=";
        window.opener.close();
        window.close();
        <?php
          } else {
        ?>
        window.opener.location = "paper_details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>&folder=<?php echo $_POST['folderID']; ?>&school=";
        window.close();
        <?php
          }
        ?>
      }
      function updateParent() {
        window.opener.parent.location = "paper_details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>";
        window.close();
      }
    </script></head>
    <body onload="closeWindow();">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="updateParent();" /></div>
    </form>
  <?php
} else {
  $option_no = 1;
  
  $result = $mysqli->prepare("SELECT display_students_response, display_correct_answer, display_question_mark, display_feedback, paper_title, paper_type, start_date, end_date, timezone, bgcolor, fgcolor, themecolor, labelcolor, fullscreen, marking, bidirectional, pass_mark, distinction_mark, folder, labs, rubric, calculator, externals, exam_duration, moduleID, calendar_year, sound_demo FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($display_students_response, $display_correct_answer, $display_question_mark, $display_feedback, $paper_title, $paper_type, $start_date, $end_date, $timezone, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $marking, $bidirectional, $pass_mark, $distinction_mark, $folder, $labs, $rubric, $calculator, $externals, $exam_duration, $moduleID, $calendar_year, $sound_demo);
  $result->fetch();
  $result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title>Properties</title>

  <style>
    body {font-family:Arial,sans-serif; color:black; background-color:#ECE9D8; margin:0px; font-size:90%}
    table {font-size:100%}
    input,textarea {font-family:Arial,sans-serif; color:black}
  </style>

  <script language="JavaScript">
    function launchHelp(pageID) {
      var winheight = screen.height-100;
      if (screen.width == 800) {
        notice=window.open("./staff_help/index.php?id=" + pageID + "","help","width=770,height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        notice.moveTo(10,10);
      } else {
        notice=window.open("./staff_help/index.php?id=" + pageID + "","help","width=950,height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        notice.moveTo(10,10);
      }
    }
  </script>
</head>
<body onload="window.focus();">
<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<table border="0" cellpadding="1" cellspacing="5" style="background-color:#ECE9D8; width:100%; height:100%">
<tr><td valign="top" style="background-color:white; border:1px solid #7F9DB9; width:120px">

<table cellspacing="0" cellpadding="2" style="font-size:90%; width:120px">
<tr><td style="background-image:url('./artwork/2007_button_on.png'); height:25px; color:#00156E" valign="middle">&nbsp;General</td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;Security</td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;Reviewers</td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;Exam Rubric</td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;Prologue</td></tr>
<tr><td style="height:25px; color:#808080" valign="middle">&nbsp;Postscript</td></tr>
</table>

</td>

<td style="background-color:white; border:1px solid #7F9DB9" valign="top">

<table id="general" style="height:460px; width:684; font-size:90%<?php if ($_GET['noadd'] == 'y') echo ';display:none'; ?>"cellpadding="0" cellspacing="0" border="0">
<tr><td style="background-image:url('./artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="./artwork/general_heading_icon.png" width="28" height="31" alt="Icon" align="middle" />&nbsp;&nbsp;Paper name, marking and display options</td></tr>
<td style="text-align:left; vertical-align:top" colspan="2">
   <?php
     echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
     echo "<tr><td colspan=\"4\" style=\"background-color:#DDE7EE; color:#00156E; font-weight:bold; border-bottom: 1px solid #C5C5C5\">&nbsp;Paper Details</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">URL&nbsp;</td><td colspan=\"3\"><a href=\"https://" . $_SERVER['HTTP_HOST'] . "\" target=\"_blank\" style=\"color:blue\">https://" . $_SERVER['HTTP_HOST'] . "</a> (only on exam day)</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">Name&nbsp;</td><td colspan=\"3\"><input type=\"text\" size=\"75\" maxlength=\"255\" value=\"$paper_title\" name=\"paper\" disabled /><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\"></td></tr>\n";
   ?>
     <tr><td align="right" valign="top">Type&nbsp;</td><td>
     <select name="paper_type" disabled>
     <option value="0"<?php if ($paper_type == '0') echo ' selected'; ?> />Formative Self-Assessment</option>
     <option value="1"<?php if ($paper_type == '1') echo ' selected'; ?> />Progress Test</option>
     <option value="2"<?php if ($paper_type == '2') echo ' selected'; ?> />Summative Exam</option>
     <option value="3"<?php if ($paper_type == '3') echo ' selected'; ?> />Survey (Questionnaire)</option>
     <option value="4"<?php if ($paper_type == '4') echo ' selected'; ?> />OSCE</option>
     <option value="5"<?php if ($paper_type == '5') echo ' selected'; ?> />Spotter</option>
   <?php
     echo "<td align=\"right\" valign=\"top\">Folder&nbsp;</td><td valign=\"top\">\n<select style=\"width:210px\" name=\"folderID\" disabled>\n";
     echo "<option value=\"\"></option>";
     $additonal = '';
     
     $team_query = $mysqli->prepare("SELECT DISTINCT name FROM teams WHERE memberID=? ORDER BY name");
     $team_query->bind_param('s', $userID);
     $team_query->execute();
     $team_query->store_result();
     $team_query->bind_result($team_name);
     while ($row = $team_query->fetch()) {
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
     while ($row = $folder_details->fetch()) {
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
     if ($paper_type != '4') echo 'Feedback&nbsp';
     echo "</td><td colspan=\"3\">";
     if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id=?");
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
     
       echo "Objectives Report<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . "/touchstone/mapping/user_feedback.php?paperID=" . $_GET['paperID'] . "\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/touchstone/mapping/user_feedback.php?paperID=" . $_GET['paperID'] . "</a></div>\n";
     }
     if ($paper_type == '0') {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on">';
     } else {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="display:none">';
     }
     if ($paper_type != '4') {
     ?>
     <tr><td><input type="checkbox" name="display_students_response" value="1"<?php if ($display_students_response == '1') echo ' checked'; ?> />&nbsp;Ticks/Crosses</td><td><input type="checkbox" name="display_question_mark" value="1"<?php if ($display_question_mark == '1') echo ' checked'; ?> />&nbsp;Question Marks</td></tr>
     <tr><td><input type="checkbox" name="display_correct_answer" value="1"<?php if ($display_correct_answer == '1') echo ' checked'; ?> />&nbsp;Correct Answer Highlight</td><td><input type="checkbox" name="display_feedback" value="1"<?php if ($display_feedback == '1') echo ' checked'; ?> />&nbsp;Text Feedback</td></tr>
     </table>
     <?
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
       echo "<tr><td colspan=\"4\" style=\"background-color:#DDE7EE; color:#00156E; font-weight:bold; border-bottom: 1px solid #C5C5C5\">&nbsp;Display Options</td></tr>\n";
       if ($fullscreen == 0) {
         echo "<tr><td align=\"right\">Display&nbsp;</td><td><select name=\"fullscreen\" disabled>\n<option value=\"0\" selected>Windowed</option><option value=\"1\">Full Screen (IE only)</option>\n</select></td>";
       } else {
         echo "<tr><td align=\"right\">Display&nbsp;</td><td><select name=\"fullscreen\" disabled>\n<option value=\"0\">Windowed</option><option value=\"1\" selected>Full Screen (IE only)</option>\n</select></td>";
       }
       if ($bidirectional == 1) {
         echo "<td align=\"right\">Navigation&nbsp;</td><td><select name=\"bidirectional\" disabled><option value=\"0\">Unidirectional (Linear)</option><option value=\"1\"selected>Bidirectional</option></select></td></tr>\n";
       } else {
         echo "<td align=\"right\">Navigation&nbsp;</td><td><select name=\"bidirectional\" disabled><option value=\"0\" selected>Unidirectional (Linear)</option><option value=\"1\">Bidirectional</option></select></td></tr>\n";
       }
       echo "<tr><td align=\"right\">Background&nbsp;</td><td>";
   ?>
 <script language="JavaScript">
   var oColor1 = new IColorPicker("oColor1");
   oColor1.onPickColor = new Function("document.getElementById('idColor1').style.backgroundColor=oColor1.color;document.getElementById('bgcolor').value=oColor1.color;");
   oColor1.customColors = ["#FFFFFF","#000000","#316AC5","#C00000"];
   oColor1.RENDER();
 </script>

<span id="idColor1" style="width:20px;background-color:<?php echo $bgcolor; ?>;border:black 1px solid">&nbsp;&nbsp;&nbsp;&nbsp;</span>
<input type="button" value="Pick" disabled />
<input type="hidden" value="<?php echo $bgcolor; ?>" name="bgcolor" id="bgcolor" />
   <?php
     echo "</td><td align=\"right\">Foreground&nbsp;</td><td>";
   ?>
 <script language="JavaScript">
   var oColor2 = new IColorPicker("oColor2");
   oColor2.onPickColor = new Function("document.getElementById('idColor2').style.backgroundColor=oColor2.color;document.getElementById('fgcolor').value=oColor2.color;");
   oColor2.customColors = ["#FFFFFF","#000000","#316AC5","#C00000"];
   oColor2.RENDER();
 </script>

<span id="idColor2" style="width:20px;background-color:<?php echo $fgcolor; ?>;border:black 1px solid">&nbsp;&nbsp;&nbsp;&nbsp;</span>
<input type="button" value="Pick" disabled />
<input type="hidden" value="<?php echo $fgcolor; ?>" name="fgcolor" id="fgcolor" />
   <?php
     echo "</td></tr>\n";
     echo "<tr><td align=\"right\">Theme&nbsp;</td><td>";
   ?>
 <script language="JavaScript">
   var oColor3 = new IColorPicker("oColor3");
   oColor3.onPickColor = new Function("document.getElementById('idColor3').style.backgroundColor=oColor3.color;document.getElementById('themecolor').value=oColor3.color;");
   oColor3.customColors = ["#FFFFFF","#000000","#316AC5","#C00000"];
   oColor3.RENDER();
 </script>

<span id="idColor3" style="width:20px;background-color:<?php echo $themecolor; ?>;border:black 1px solid">&nbsp;&nbsp;&nbsp;&nbsp;</span>
<input type="button" value="Pick" disabled />
<input type="hidden" value="<?php echo $themecolor; ?>" name="themecolor" id="themecolor" />
   <?php
     echo "</td><td align=\"right\">Labels/Notes&nbsp;</td><td>";
   ?>
 <script language="JavaScript">
   var oColor4 = new IColorPicker("oColor4");
   oColor4.onPickColor = new Function("document.getElementById('idColor4').style.backgroundColor=oColor4.color;document.getElementById('labelcolor').value=oColor4.color;");
   oColor4.customColors = ["#FFFFFF","#000000","#316AC5","#C00000"];
   oColor4.RENDER();
 </script>

<span id="idColor4" style="width:20px;background-color:<?php echo $labelcolor; ?>;border:black 1px solid">&nbsp;&nbsp;&nbsp;&nbsp;</span>
<input type="button" value="Pick" disabled />
<input type="hidden" value="<?php echo $labelcolor; ?>" name="labelcolor" id="labelcolor" />
   <?php
       echo "</td></tr>\n";
       if ($calculator == 1) {
         echo "<tr><td align=\"right\">Calculator&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"calculator\" checked disabled /> display calculator</td>";
       } else {
         echo "<tr><td align=\"right\">Calculator&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"calculator\" disabled /> display calculator</td>";
       }
       if ($sound_demo == 1) {
         echo "<td align=\"right\">Audio&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"sound_demo\" checked disabled /> demo sound clip</td></tr>\n";
       } else {
         echo "<td align=\"right\">Audio&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"sound_demo\" disabled /> demo sound clip</td></tr>\n";
       }
       echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     }
     echo "<tr><td colspan=\"4\" style=\"background-color:#DDE7EE; color:#00156E; font-weight:bold; border-bottom: 1px solid #C5C5C5\">&nbsp;Marking</td></tr>\n";
     if ($paper_type == '4') {
       echo "<tr><td align=\"right\" valign=\"top\">Overall&nbsp;Classification&nbsp;</td><td valign=\"top\" colspan=\"3\"><select name=\"marking\">";
     ?>
       <option value="5"<?php if ($marking == '5') echo ' selected'; ?> />&lt;Automatic&gt;</option>
       <option value="3"<?php if ($marking == '3') echo ' selected'; ?> />Clear Fail | Borderline | Clear Pass</option>
       <option value="4"<?php if ($marking == '4') echo ' selected'; ?> />Fail | Borderline fail | Borderline pass | Pass | Good pass</option>
       <option value="6"<?php if ($marking == '6') echo ' selected'; ?> />Clear FAIL | BORDERLINE | Clear PASS | Honours PASS</option>
  <?php
    echo "<tr><td colspan=\"4\"><textarea cols=\"80\" rows=\"4\" id=\"osce_marking_guidance\" name=\"osce_marking_guidance\">" . $paper_postscript . "</textarea>";
  ?>
  <script>
    var oEdit4 = new InnovaEditor("oEdit4");
    oEdit4.mode="XHTMLBody";
    oEdit4.useTagSelector=false;
    oEdit4.useBR=false;
    oEdit4.width="100%";
    oEdit4.height="230px";
    oEdit4.features=["Cut","Copy","PasteText","|","Undo","|","Bold","Italic","Underline","|","Superscript","Subscript","|","JustifyLeft","JustifyCenter","JustifyRight","|","Numbering","Bullets","|","Table","Characters","|","XHTMLSource"];
    oEdit4.arrStyle = [["BODY",false,"","background:white; margin:2px; color:black; font-size:90%; font-family:Arial,sans-serif"]];
    oEdit4.btnStyles = true;
    oEdit4.REPLACE("osce_marking_guidance");
  </script>  
</td></tr>
     <?php
       echo "</select></td></tr>\n";
     } else {
       echo "<tr><td align=\"right\" valign=\"top\">Pass&nbsp;Mark&nbsp;</td><td valign=\"top\"><select name=\"pass_mark\" id=\"pass_mark\"";
       if ($paper_type == '3') echo ' disabled';
       echo '>';
       for ($i=0; $i<=100; $i++) {
         if ($i == $pass_mark) {
           echo "<option value=\"$i\" selected>$i%</option>\n";
         } else {
           echo "<option value=\"$i\">$i%</option>\n";
         }
       }
       echo "</select></td><td rowspan=\"2\" style=\"text-align:right\" valign=\"top\">Method&nbsp;</td><td rowspan=\"2\">";
     ?>
       <input type="radio" id="marking1" name="marking" value="0"<?php if ($marking == '0') echo ' checked'; ?> />No Adjustment<br />
       <input type="radio" id="marking2" name="marking" value="1"<?php if ($marking == '1') echo ' checked'; ?> />Calculate Random Mark<br />
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
       echo "<tr><td align=\"right\" valign=\"top\">Distinction</td><td><select name=\"distinction_mark\">";
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
<tr><td colspan="2" align="right"><input type="submit" style="width:100px" name="Submit" value="OK">&nbsp;<input type="button" name="home" style="width:100px" value="Cancel" onclick="javascript:window.close();" /></td></tr>
</table>

<input type="hidden" name="noadd" value="<?php echo $_GET['noadd']; ?>" />
</form>
<?php
  }
$mysqli->close();
?>
</body>
</html>
