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
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/media.inc';
require '../../include/metadata.inc';
require '../../include/edit.inc';
require '../../include/mapping_tab.inc';
include_once('../../tools/getid3/getid3.php');

$q_id = $_GET['q_id'];
if (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
  $changes = false;
  // Record the changes in 'track_changes'.
  if ($_POST['formula'] != $_POST['old_formula']) {
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'formula')");
    $result->bind_param('iss', $q_id, $_POST["old_formula"], $_POST["formula"]);
    $result->execute();  
    $result->close();
    $changes = true;
  }
  if ($_POST['answer_decs'] != $_POST['old_answer_decs']) {
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'answer_decs')");
    $result->bind_param('iss', $q_id, $_POST["old_answer_decs"], $_POST["answer_decs"]);
    $result->execute();  
    $result->close();
    $changes = true;
  }
  if ($_POST['tolerance'] != $_POST['old_tolerance']) {
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'tolerance')");
    $result->bind_param('iss', $q_id, $_POST["old_tolerance"], $_POST["tolerance"]);
    $result->execute();  
    $result->close();
    $changes = true;
  }

  if ($changes == true) {
    $score_method = $_POST['answer_decs'] . ',' . $_POST['tolerance'] . ',' . $_POST['old_units'];
    $result = $mysqli->prepare("UPDATE questions SET score_method=? WHERE q_id=?");
    $result->bind_param('si', $score_method, $q_id);
    $result->execute();  
    $result->close();

    $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
    $result->bind_param('si', $_POST['formula'], $q_id);
    $result->execute();  
    $result->close();

    // Remark the student's answers in 'log2'.
    $result = $mysqli->prepare("SELECT user_answer, id FROM log2 WHERE q_id=? AND q_paper=?");
    $result->bind_param('ii', $q_id, $_POST['paperID']);
    $result->execute();
    $result->store_result();
    $result->bind_result($user_answer, $id);
    while ($row = $result->fetch()) {
      // Split up the user answer into its constituent parts.
      $answer_parts = explode('|',$user_answer);
      $variable_array = explode(',',$answer_parts[2]);
      $saved_response = $answer_parts[0];
      $var_no = 1;
      foreach($variable_array as $individual_variable) {
        switch ($var_no) {
          case 1:
            $A = $individual_variable;
            break;
          case 2:
            $B = $individual_variable;
            break;
          case 3:
            $C = $individual_variable;
            break;
          case 4:
            $D = $individual_variable;
            break;
          case 5:
            $E = $individual_variable;
            break;
          case 6:
            $F = $individual_variable;
            break;
          case 7:
            $G = $individual_variable;
            break;
          case 8:
            $H = $individual_variable;
            break;
        }
        $var_no++;
      }
      $mark = 0;
      $answer_equation = $_POST['formula'];
      eval ("\$answer = $answer_equation;");
      $answer = round($answer, $score_array[0]);
      if ($saved_response == $answer) {
        $mark = 1;
      } elseif (abs($saved_response - $answer) <= $_POST['tolerance']) {
        $mark = 1;
      }
      $saved_response .= '|' . $answer . '|' . $answer_parts[2];
    
      $updateLog = $mysqli->prepare("UPDATE log2 SET mark=?, user_answer=? WHERE id=? AND q_paper=?");
      $updateLog->bind_param("dsii", $mark, $saved_response, $id, $_POST['paperID']);
      $updateLog->execute();  
      $updateLog->close();
    }
    $result->close();
  }
  redirect();
} elseif (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'],$q_id,$mysqli);

    $changes = false;
    $leadin = $_POST['leadin'];
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','scenario','leadin','bloom','feedback','formula','units','tolerance','answer_decs','status','formula','marks');
    foreach($part_names as $section_name) {
      if(isset($_POST["$section_name"])) {
        $$section_name = $_POST["$section_name"];
      } else {
        $$section_name = '';
      }
    }
    
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    
    $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_bloom','old_feedback','old_formula','old_units','old_tolerance','old_answer_decs','old_status','old_marks');
    foreach($part_names as $section_name) {
      if(isset($_POST["$section_name"])) {
        $$section_name = $_POST["$section_name"];
      } else {
        $$section_name = '';
      }
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
    $leadin = clearMSOtags($leadin);
  
    // Upload Image (if exists) onto server
    if ($_FILES['q_media']['name'] != $_POST['old_q_media'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
      if (isset($_POST['old_q_media']) and $_POST['old_q_media'] != '') {
        deleteMedia($_POST['old_q_media']); 
      }
      $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);
      $changes = true;
    } else {
      // If the media has not changed set the variables back to the old media settings before the update query.
      $unique_name = $_POST['old_q_media'];
      $tmp_width = $_POST['old_q_media_width'];
      $tmp_height = $_POST['old_q_media_height'];
      if (isset($_POST['delete_media0']) and $_POST['delete_media0'] == '1') {
        deleteMedia($_POST['old_q_media']);
        $unique_name = '';
        $tmp_media_width = 0;
        $tmp_media_height = 0;
        $changes = true;
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'General Media',?,$userID,?,'',NOW(),'q_media')");
        $result->bind_param('is', $_GET['q_id'], $_POST["old_q_media"]);
        $result->execute();  
        $result->close();
      }
    }

    if ($tmp_width == '') {
      $tmp_width = 0;
      $tmp_height = 0;
    }

    $score_method = $answer_decs . ',' . $tolerance . ',' . $units;
    $old_score_method = $old_answer_decs . ',' . $old_tolerance . ',' . $old_units;

    $addressable_options = array('A','B','C','D','E','F','G','H');
    foreach ($addressable_options as $option_no) {
      if(isset($_POST["variableid" . $option_no])) {
        $var_min = trim($_POST["variable" . $option_no . "_min"]);
        $old_min = trim($_POST["old_variable" . $option_no . "_min"]);
        $var_max = trim($_POST["variable" . $option_no . "_max"]);
        $old_max = trim($_POST["old_variable" . $option_no . "_max"]);
        $var_decs = $_POST["variable" . $option_no . "_decs"]; 
        $old_decs = $_POST["old_variable" . $option_no . "_decs"];
        $var_inc = $_POST["variable" . $option_no . "_inc"];
        $old_jump = $_POST["old_variable" . $option_no . "_jump"];
        $temp_id = $_POST["variableid" . $option_no];
      } else {
        $var_min = '';
        $old_min = '';
        $var_max = '';
        $old_max = '';
        $var_decs = '';
        $old_decs = '';
        $var_inc = '';
        $old_jump = '';
        $temp_id = '';
      }
    
      if ($var_min == '' and $old_min != '' and $temp_id != '') {
        // Delete operation.
        $changes = true;
        $result = $mysqli->prepare("DELETE FROM options WHERE id_num=?");
        $result->bind_param('i', $temp_id);
        $result->execute();  
        $result->close();
        record_trackChanges('Deleted Variable', $q_id, $old_min . ',' . $old_max, '', 'Variable $' . $option_no, $userID, $changes);
		
      } elseif ($var_min != ''  and $old_min == '') {
        // Add operation.
        $changes = true;
        $tmp_settings = $var_min . ',' . $var_max . ',' . $var_inc . ',' . $var_decs;
        $result = $mysqli->prepare("INSERT INTO options VALUES (?,?, '', '', '', '', '',?, NULL, 1)");
        $result->bind_param('iss', $q_id, $tmp_settings, $_POST['formula']);
        $result->execute();  
        $option_id = $mysqli->insert_id;
        $result->close();
        record_trackChanges('New Variable', $q_id, '', $var_min . ',' . $var_max, 'Variable $' . $option_no, $userID, $changes);
		
      } elseif ((($var_min != $old_min) or ($var_max != $old_max) or ($var_decs != $old_decs) or ($var_inc != $old_jump)) and $temp_id != '') {
        // Edit operation.
        $changes = true;
        $tmp_settings = $var_min . ',' . $var_max . ',' . $var_inc . ',' . $var_decs;
        $result = $mysqli->prepare("UPDATE options SET option_text=?, correct=? WHERE id_num=?");
        $result->bind_param('ssi', $tmp_settings, $_POST['formula'], $temp_id);
        $result->execute();  
        $result->close();
        record_trackChanges('Edit Minimum', $q_id, $old_min, $var_min, 'Variable ' . $option_no, $userID, $changes);
        record_trackChanges('Edit Maximum', $q_id, $old_max, $var_max, 'Variable ' . $option_no, $userID, $changes);
        record_trackChanges('Edit Decimal Places', $q_id, $old_decs, $var_decs, 'Variable ' . $option_no, $userID, $changes);
        record_trackChanges('Edit Increment', $q_id, $old_jump, $var_inc, 'Variable ' . $option_no, $userID, $changes);
		
      } elseif ($formula != $old_formula and $temp_id != '') {
        //the vars are the same but the awnser has chaged so up date correct
        $changes = true;
        $result = $mysqli->prepare("UPDATE options SET correct=? WHERE id_num=?");
        $result->bind_param('si', $formula, $temp_id);
        $result->execute();  
        $result->close();
      }
    }

    // Track changes
    $part_names = array('theme','scenario','leadin','notes','bloom','feedback','answer_decs','tolerance','units','question_team','status','formula','marks');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      if(isset($$old_section_name)) {
        record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
      }
    }
    
    if ($marks != $old_marks) {
      $result = $mysqli->prepare("UPDATE options SET marks=? WHERE o_id=?");
      $result->bind_param('ii', $marks, $q_id);
      $result->execute();  
      $result->close();
    }

    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    save_external_responses($mysqli);

    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET theme=?,scenario=?,leadin=?,correct_fback=?,score_method=?,notes=?,q_media=?,q_media_width=?,q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=? WHERE q_id=?");
      $scenario_plan = trim(strip_tags($scenario));
      $leadin_plan = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssssssi', $theme, $scenario, $leadin, $feedback, $score_method, $notes, $unique_name, $tmp_width, $tmp_height, $bloom, $question_teams, $scenario_plan, $leadin_plan, $status, $q_id);
      $result->execute();  
      $result->close();
    }
  } else {
    // Limited save.
    do_limitedSave($q_id, $mysqli, $userID);
  }
  redirect();
} elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel') {
  redirect();
} else {

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title>Edit Calculation Question</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="../../css/add_edit.css" type="text/css">

  <script language="JavaScript">
    var cancel = 0;
    function formCancel() {
      cancle = 1;
    }

    function checkForm() {
      if (cancel != 0) {
        return true;
      }    
      
      <?php
      if($cfg_editor_name == 'tinymce') {
        echo "\t tinyMCE.triggerSave();";
      }
      ?>
      
      if (edit_form.leadin.value == "") {
        alert ("Please enter a Lead in for the question.");
        return false;
      }
    }

    function variableLink(elementID, iconID) {
      window.open("variable_link.php?paperID=<?php echo $_GET['paperID'] . '&elementID='; ?>" + elementID + "&q_id=<?php echo $_GET['q_id']; ?>&iconID=" + iconID + "","paper","width=600,height=400,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    }
  </script>
  <script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
  <script language="JavaScript" src="../../javascript/metadata.js"></script>
  <script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
  <?php echo $cfg_editor_javascript; ?>
  <script src="../../javascript/staff_help.js" type="text/javascript"></script>
  </head>

  <body style="background-color:white">
<?php
  $option_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario,  $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $group, $checkout_time, $checkout_authorID, $locked, $status);
  while ($row = $result->fetch()) {
    if ($option_no == 1) {
   ?>
   <form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $q_id; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
  <?php
    if (isset($_GET['qNo'])) {
      echo "<span style=\"position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt\">Edit Question " . $_GET['qNo'] . "</span>\n";
    } else {
      echo "<span style=\"position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt\">Edit Question</span>\n";
    }
  ?>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Calculation)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(68); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab($created, $modified, $locked);
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr>
    <td style="text-align:center">
    <?php
      if ($locked != '') {
        echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" style=\"width:100%; font-size:90%\">\n";
        echo "<tr><td style=\"width:35px; height:32px; text-align:right; background-image:url('../../artwork/locked_gradient.png'); background-repeat:repeat-x\"><img src=\"../../artwork/paper_locked_padlock.png\" width=\"19\" height=\"24\" alt=\"Locked\" />&nbsp;&nbsp;</td><td colspan=\"7\" style=\"height:32px; vertical-align:middle; background-image:url('../../artwork/locked_gradient.png'); background-repeat:repeat-x\"><strong>Question Locked</strong>&nbsp;&nbsp;&nbsp;This question is now locked and cannot be modified. <a style=\"color:black\" href=\"#\" onclick=\"launchHelp(161); return false;\">Click for more details.</a></td></tr>\n";
        echo "</table>\n";
        $disabled = ' disabled';
      } else {
        $disabled = check_edit_rights($tmp_ownerID, $mysqli);
        $checkout_author = check_lock_status($checkout_authorID, $checkout_time, $disabled, $mysqli, $q_id);
      }

      echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" align=\"center\">\n";
      echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td colspan=\"6\"><input type=\"text\" name=\"theme\" size=\"80\" value=\"$theme\" /><input type=\"hidden\" name=\"old_theme\" value=\"" . htmlentities($theme,ENT_NOQUOTES,'UTF-8') . "\" /><input type=\"hidden\" name=\"checkout_author\" value=\"$checkout_authorID\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"6\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">" . htmlentities($notes,ENT_NOQUOTES,'UTF-8') . "</textarea><input type=\"hidden\" name=\"old_notes\" value=\"$notes\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td colspan=\"6\">\n<textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . encodeHTML($scenario) . "</textarea>";
      echo wysiwyg_editor('oEdit1','scenario',$scenario);
	  
      echo "</td>\n</tr>\n";
      if ($q_media != '') {
        echo "<tr>\n<td class=\"field\">Current Media</td><td colspan=\"6\">" . display_media($q_media,$q_media_width,$q_media_height,0) . "</td>\n</tr>\n";
      }
      echo "<tr>\n<td class=\"field\">Change Media</td><td colspan=\"6\"><input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /><input type=\"file\" name=\"q_media\" size=\"65\" accept=\"image/jpg, image/gif\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color: #808080\">(the question)</span></td>\n<td colspan=\"6\">\n<textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . encodeHTML($leadin) . "</textarea>";
      echo wysiwyg_editor('oEdit2','leadin',$leadin);
    ?>
  </td></tr>
  <tr><td colspan="7"><div class="section">Variables</div></td></tr>
  <tr style="font-size: 85%">
    <td></td>
    <td>Min</td>
    <td style="width:50px">&nbsp;</td>
    <td>Max</td>
    <td style="width:50px">&nbsp;</td>
    <td style="width:140px">Decimals</td>
    <td style="width:310px">Increment</td>
  </tr>
  <?php
    $formula = $correct;
  }

  $variable_array = explode(',', $option_text);
  echo "<tr>\n";
  echo "<td style=\"text-align:right\"><span class=\"field\">$" . chr(64 + $option_no) . "</span></td>";
  echo "<td><input id=\"variable" . chr($option_no+64) . "_min\" type=\"text\" name=\"variable" . chr($option_no+64) . "_min\" style=\"width:100%\" size=\"10\" value=\"$variable_array[0]\" /><input type=\"hidden\" name=\"old_variable" . chr($option_no+64) . "_min\" value=\"$variable_array[0]\" /><input type=\"hidden\" name=\"variableid" . chr($option_no+64) . "\" value=\"$id_num\"></td><td>";
  if (strpos($variable_array[0],'var') !== false or strpos($variable_array[0],'ans') !== false) {
    echo "<img id=\"minicon$option_no\" style=\"cursor:pointer\" onclick=\"variableLink('variable" . chr($option_no+64) . "_min','minicon$option_no')\" src=\"../../artwork/variable_link_on.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" />";
  } else {
    echo "<img id=\"minicon$option_no\" style=\"cursor:pointer\" onclick=\"variableLink('variable" . chr($option_no+64) . "_min','minicon$option_no')\" src=\"../../artwork/variable_link_off.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" />";
  }
  echo "</td>";
  echo "<td><input type=\"text\" name=\"variable" . chr($option_no+64) . "_max\" style=\"width:100%\" size=\"10\" value=\"$variable_array[1]\" /><input type=\"hidden\" name=\"old_variable" . chr($option_no+64) . "_max\" value=\"$variable_array[1]\" /></td><td>";
  if (strpos($variable_array[0],'var') !== false or strpos($variable_array[1],'ans') !== false) {
    echo "<img id=\"maxicon$option_no\" style=\"cursor:pointer\" onclick=\"variableLink('variable" . chr($option_no+64) . "_max','maxicon$option_no')\" src=\"../../artwork/variable_link_on.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" />";
  } else {
    echo "<img id=\"maxicon$option_no\" style=\"cursor:pointer\" onclick=\"variableLink('variable" . chr($option_no+64) . "_max','maxicon$option_no')\" src=\"../../artwork/variable_link_off.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" />";
  }
  echo "</td>";
  echo "<td><select name=\"variable" . chr($option_no+64) . "_decs\">\n";
  for ($i=0;$i<=4;$i++) {
    if ($i == $variable_array[3]) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select><input type=\"hidden\" name=\"old_variable" . chr($option_no+64) . "_decs\" value=\"$variable_array[3]\" /></td>\n";
  $inc_array = array(0.0001,0.001,0.02,0.01,0.5,0.2,0.1,1,2,3,4,5,6,7,8,9,10,15,20,25,50,100,1000);
  echo "<td><select name=\"variable" . chr($option_no+64) . "_inc\">\n";
  foreach ($inc_array as $individual_inc) {
    if ($individual_inc == $variable_array[2]) {
      echo "<option value=\"$individual_inc\" selected>$individual_inc</option>\n";
    } else {
      echo "<option value=\"$individual_inc\">$individual_inc</option>\n";
    }
  }
  echo "</select>\n<input type=\"hidden\" name=\"old_variable" . chr($option_no+64) . "_jump\" value=\"$variable_array[2]\" /></td>\n";
  echo "</tr>\n";
  $option_no++;
}
for ($i=$option_no; $i<=8; $i++) {
  echo "<tr>\n";
  echo "<td style=\"text-align: right\">";
  echo "<span class=\"field\">$" . chr(64 + $i) . "</span></td>";
  echo "<td><input type=\"text\" name=\"variable" . chr($i+64) . "_min\" style=\"width:100%\" size=\"10\" /><input type=\"hidden\" name=\"old_variable" . chr($i+64) . "_min\" value=\"\" /></td>\n";
  echo "<td><img id=\"minicon$i\" onclick=\"variableLink('variable" . chr($i+64) . "_min','minicon$i')\" src=\"../../artwork/variable_link_off.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" /></td>";
  echo "<td><input type=\"text\" name=\"variable" . chr($i+64) . "_max\" style=\"width:100%\" size=\"10\" /><input type=\"hidden\" name=\"old_variable" . chr($i+64) . "_max\" value=\"\" /></td>\n";
  echo "<td><img id=\"maxicon$i\" onclick=\"variableLink('variable" . chr($i+64) . "_max','maxicon$i')\" src=\"../../artwork/variable_link_off.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" /></td>";
  echo "<td><select name=\"variable" . chr($i+64) . "_decs\">\n";
  echo "<option value=\"0\"></option>\n";
  echo "<option value=\"0\">0</option>\n";
  echo "<option value=\"1\">1</option>\n";
  echo "<option value=\"2\">2</option>\n";
  echo "<option value=\"3\">3</option>\n";
  echo "<option value=\"4\">4</option>\n";
  echo "</select>\n</td>\n";
  echo "<td><select name=\"variable" . chr($i+64) . "_inc\">\n";
  echo "<option value=\"1\"></option>\n";
  foreach ($inc_array as $individual_inc) {
    echo "<option value=\"$individual_inc\">$individual_inc</option>\n";
  }
  echo "</select>\n</td>\n";
  echo "</tr>\n";
}
$option_no--; // Correct for the actual number
?>
<tr>
  <td colspan="7"><div class="section">Answer</div></td>
</tr>
<?php
  echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Formula<br /><a href=\"#\" onclick=\"launchHelp(68); return false;\"><img src=\"../../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Online Help\" border=\"0\" /></a>&nbsp;<span style=\"font-weight:normal\"><a href=\"#\" onclick=\"launchHelp(68,'functions'); return false;\">supported functions</a></span></td>\n<td colspan=\"6\"><textarea name=\"formula\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">$formula</textarea><input type=\"hidden\" name=\"old_formula\" value=\"$formula\" / ></td>\n</tr>\n";
  $score_array = array();
  $score_array = explode(',', $score_method);
  echo "<tr>\n<td class=\"field\">Units</td>\n<td colspan=\"6\"><input type=\"text\" name=\"units\" size=\"10\" value=\"$score_array[2]\" /><input type=\"hidden\" name=\"old_units\" value=\"$score_array[2]\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"font-weight:bold; font-size:85%; color:black\">Decimals&nbsp;</span>\n";
  echo "<select name=\"answer_decs\">\n";
  for ($i=0;$i<5;$i++) {
    if ($i == $score_array[0]) {
      echo "<option value=\"$i\" selected>$i</option>";
    } else {
      echo "<option value=\"$i\">$i</option>";
    }
  }
  echo "</select>\n<input type=\"hidden\" name=\"old_answer_decs\" value=\"$score_array[0]\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"font-weight:bold; font-size:85%; color:black\">Tolerance&nbsp;</span><input type=\"text\" name=\"tolerance\" size=\"10\" value=\"$score_array[1]\" /><input type=\"hidden\" name=\"old_tolerance\" value=\"$score_array[1]\" />\n</td>\n</tr>\n";
  echo "<tr>\n<td class=\"field\">Feedback</td>\n<td colspan=\"6\"><textarea name=\"feedback\" cols=\"100\" style=\"width:700px\" rows=\"4\" wrap=\"virtual\">$correct_fback</textarea><input type=\"hidden\" name=\"old_feedback\" value=\"" . htmlentities($correct_fback,ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
  echo "<tr>\n<td class=\"field\">Marks</td>\n<td colspan=\"6\">\n<select name=\"marks\">\n";
  for ($i=1; $i<=20; $i++) {
    if ($i == $marks) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select><input type=\"hidden\" name=\"old_marks\" value=\"$marks\" />\n</td>\n</tr>\n";

  echo echoMetadata($bloom, $q_id, $group, 6, $mysqli, true, $status, $disabled);
?>
<tr>
  <td colspan="7">&nbsp;<?php echo hidden_edit_fields(); ?></td>
</tr>
<tr>
  <td colspan="7" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
</tr>
</table>
</td></tr>
</table>
</div>
<?php
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
  $mysqli->close();
}
?>
</form>
</body>
</html>