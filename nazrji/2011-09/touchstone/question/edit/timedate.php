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

require '../../include/staff_auth.inc';
require '../../include/media.inc';
require '../../include/metadata.inc';
require '../../include/edit.inc';
require '../../include/mapping_tab.inc';
include_once('../../tools/getid3/getid3.php');

$q_id = $_GET['q_id'];

function whichFormat($format) {
  switch ($format) {
    case 1:
      $formatString = 'dd/MM/yyyy hh:mm:ss';
      break;
    case 2:
      $formatString = 'dd/MM/yyyy hh:mm';
      break;
    case 3:
      $formatString = 'dd/MM/yyyy';
      break;
    case 4:
      $formatString = 'mm/dd/yyyy';
      break;
    case 5:
      $formatString = 'dd/MMMM/yyyy';
      break;
    case 6:
      $formatString = 'hh:mm:ss';
      break;
    case 7:
      $formatString = 'hh:mm (date)';
      break;
    case 8:
      $formatString = 'hh:mm (duration)';
      break;
  }
  return $formatString;
}

if (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
  $changes = false;
  // Update Option data
  switch ($_POST['format']) {
    case 1:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'] . ' ' . $_POST['answer_hour'] . ':' . $_POST['answer_minute'] . ':' . $_POST['answer_second'];
      if (strlen($correct) == 5) $correct = '';
      break;
    case 2:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'] . ' ' . $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
      if (strlen($correct) == 4) $correct = '';
      break;
    case 3:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'];
      if (strlen($correct) == 2) $correct = '';
      break;
    case 4:
      $correct = $_POST['answer_month'] . '/' . $_POST['answer_day'] . '/' . $_POST['answer_year'];
      if (strlen($correct) == 4) $correct = '';
      break;
    case 5:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'];
      if (strlen($correct) == 2) $correct = '';
      break;
    case 6:
      $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'] . ':' . $_POST['answer_second'];
      if (strlen($correct) == 2) $correct = '';
      break;
    case 7:
    case 8:
      $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
      if (strlen($correct) == 1) $correct = '';
      break;
  }
  
  if ($correct != $_POST['old_correct']) {
    $changes = true;
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Post Exam Answer change',?,$userID,?,?,NOW(),'answer')");
    $result->bind_param('iss', $q_id, $_POST['old_correct'], $correct);
    $result->execute();  
    $result->close();
  }
  if ($_POST['format'] != $_POST['old_format']) {
    $changes = true;
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Post Exam Answer change',?,$userID,?,?,NOW(),'format')");
    $result->bind_param('iss', $q_id, $_POST['old_format'], $_POST['format']);
    $result->execute();  
    $result->close();
  }
  if ($changes == true) {
    $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
    $result->bind_param('si', $correct, $q_id);
    $result->execute();  
    $result->close();

    // Update Question data
    $score_method = $_POST['format'] . '|' . $_POST['old_start_year'] . '|' . $_POST['old_end_year'];
    $result = $mysqli->prepare("UPDATE questions SET score_method=? WHERE q_id=?");
    $result->bind_param('si', $score_method, $q_id);
    $result->execute();  
    $result->close();
  }

  // Remark the student's answers in 'log2'.
  $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
  $result->bind_param('ii', $q_id, $_POST['paperID']);
  $result->execute();  
  $result->store_result();
  $result->bind_result($user_answer);
  while ($row = $result->fetch()) {
    if ($user_answer == $correct) {
      $mark = 1;
    } else {
      $mark = 0;
    }
    $updateLog = $mysqli->prepare("UPDATE log2 SET mark=? WHERE user_answer=? AND q_id=? AND q_paper=?");
    $updateLog->bind_param('dsii', $mark, $user_answer, $q_id, $_POST['paperID']);
    $updateLog->execute();  
    $updateLog->close();
  }
  $result->free_result();
  $result->close();
  
  redirect();
} elseif (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'],$q_id,$mysqli);

    $changes = false;
    $part_names = array('theme','scenario','leadin','feedback','notes','bloom','format','start_year','end_year','terms','answer_day','answer_month','answer_year','answer_hour','answer_minute','answer_second','status','question_terms');
    foreach($part_names as $section_name) {
      if(isset($_POST["$section_name"])) {
        $$section_name = $_POST["$section_name"];
      } else {
        $$section_name = '';
      }
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_feedback','old_correct','old_notes','old_bloom','old_format','old_start_year','old_end_year','old_score_method','old_terms','old_answer_day','old_answer_month','old_answer_year','old_answer_hour','old_answer_minute','old_answer_second','old_status','old_question_terms');
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

    $paperID = $_POST['paperID'];
    // Upload Image (if exists) onto server
    if ($_FILES['q_media']['name'] != $_POST['old_q_media'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
      if (isset($_POST['old_media']) and $_POST['old_media'] != '') {
        deleteMedia($_POST['old_q_media']); 
      }
      $unique_name = uploadFile('q_media',$tmp_media_width,$tmp_media_height);
      $changes = true;
    } else {
      // If the media has not changed set the variables back to the old media settings before the update query.
      $unique_name = $_POST['old_q_media'];
      $tmp_media_width = $_POST['old_q_media_width'];
      $tmp_media_height = $_POST['old_q_media_height'];
      if (isset($_POST['delete_media0']) and $_POST['delete_media0'] == '1') {
        deleteMedia($_POST['old_q_media']);
        $unique_name = '';
        $tmp_media_width = 0;
        $tmp_media_height = 0;
        $changes = true;
      }
    }
    $old_q_media = $_POST['old_q_media'];
    $q_media = $unique_name;

    // Update Option data
    switch ($_POST['format']) {
      case 1:
        $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'] . ' ' . $_POST['answer_hour'] . ':' . $_POST['answer_minute'] . ':' . $_POST['answer_second'];
        if (strlen($correct) == 5) $correct = '';
        break;
      case 2:
        $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'] . ' ' . $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
        if (strlen($correct) == 4) $correct = '';
        break;
      case 3:
        $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'];
        if (strlen($correct) == 2) $correct = '';
        break;
      case 4:
        $correct = $_POST['answer_month'] . '/' . $_POST['answer_day'] . '/' . $_POST['answer_year'];
        if (strlen($correct) == 4) $correct = '';
        break;
      case 5:
        $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'];
        if (strlen($correct) == 2) $correct = '';
        break;
      case 6:
        $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'] . ':' . $_POST['answer_second'];
        if (strlen($correct) == 2) $correct = '';
        break;
      case 7:
      case 8:
        $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
        if (strlen($correct) == 1) $correct = '';
        break;
    }
    if ($correct != $old_correct) {
      $changes = true;
      $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
      $result->bind_param('si', $correct, $q_id);
      $result->execute();  
      $result->close();
    }

    $part_names = array('theme','scenario','leadin','q_media','notes','bloom','format','start_year','end_year','question_terms','answer_day','answer_month','answer_year','answer_hour','answer_minute','answer_second','status');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      if ($$section_name != $$old_section_name) {
        $changes = true;
        if ($section_name == 'format') {
          $tmp_old_section_name = whichFormat($$old_section_name);
          $tmp_section_name = whichFormat($$section_name);
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),?)");
          $result->bind_param('isss', $q_id, $tmp_old_section_name, $tmp_section_name, $section_name);
        } else {
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),?)");
          $result->bind_param('isss', $q_id, $$old_section_name, $$section_name, $section_name);
        }
        $result->execute();  
        $result->close();
      }
    }
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);

    save_external_responses($mysqli);

    if ($changes == true) {
      // Update Question data
      $score_method = $format . '|' . $start_year . '|' . $end_year;
      $bloom = (empty($bloom)) ? NULL : $bloom;
      $result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, score_method=?, correct_fback=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=? WHERE q_id=?");
      $scenario_stripped = trim(strip_tags($scenario));
      $leadin_stripped = trim(strip_tags($leadin));
      
      $result->bind_param('ssssssssssssssi', $theme, $scenario, $leadin, $score_method, $feedback, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario_stripped , $leadin_stripped, $status, $q_id);
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
<title>Edit Time/Date Question<?php echo " $cfg_install_type"; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">

<script language="JavaScript">
var cancel = 0;
function formCancel() {
  cancel = 1;
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
  if (add_form.leadin.value == "") {
    alert ("Please enter a question leadin.");
    return false;
  }
}
</script>
<script language="JavaScript" src="../../javascript/jquery-1.6.1.min.js" type="text/javascript"></script>
<script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
<script type="text/javascript" src="../../javascript/jquery.formhelpers.js"></script>
</head>

<body style="background-color:white">
<?php

  $question_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $temp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
  while ($row = $result->fetch()) {
    if ($question_no == 1) {
?>
  <form class="clearinput" name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $q_id; ?>" enctype="multipart/form-data">
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Time/Date)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
    <?php
      echo displayEditTab($created, $modified, $locked);
      $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);

      echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\">\n";
      echo "<tr><td colspan=\"4\"><div class=\"section\">General Information</div></td></tr>\n";
      echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td colspan=\"6\"><textarea name=\"theme\" cols=\"100\" style=\"width:700px\" >$theme</textarea><textarea style=\"display:none\" name=\"old_theme\"/>$theme</textarea><input type=\"hidden\" name=\"checkout_author\" value=\"$checkout_authorID\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"6\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">" . $notes . "</textarea><textarea style=\"display:none\" name=\"old_notes\" />$notes</textarea></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td colspan=\"3\">\n<textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario) . "</textarea>";
      echo wysiwyg_editor('oEdit1','scenario',$scenario);
      echo "</td>\n</tr>\n";
      if ($q_media != '') {
        echo "<tr><td class=\"field\">Current Media</td><td colspan=\"3\">" . display_media($q_media,$q_media_width,$q_media_height,0) . "</td></tr>\n";
      }
      echo "<tr><td class=\"field\">Change Media</td><td colspan=\"3\"><input type=\"file\" size=\"65\" name=\"q_media\" /><input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td></tr>\n";
      echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:9pt; color:#808080\">(the question)</span></td>\n<td colspan=\"3\">\n<textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin) . "</textarea>";
      echo wysiwyg_editor('oEdit2','leadin',$leadin);
      echo "</td>\n</tr>";
      $format_info = explode("|",$score_method);
      echo "<tr>\n<td class=\"field\">Start Year</td>";
      echo "<td width=\"90\"><input type=\"text\" size=\"10\" name=\"start_year\" value=\"" . $format_info[1] .  "\" /><input type=\"hidden\" name=\"old_start_year\" value=\"" . $format_info[1] .  "\" /></td>";
      echo "<td class=\"field\">End Year</td>";
      echo "<td><input type=\"text\" size=\"10\" name=\"end_year\" value=\"" . $format_info[2] .  "\" /><input type=\"hidden\" name=\"old_end_year\" value=\"" . $format_info[2] .  "\" /></td></tr>\n";
    ?>
    <tr>
      <td class="field">Format</td>
      <td colspan="5"><?php echo '<input type="hidden" name="old_format" value="' . $format_info[0] . '" />'; ?>
      <select name="format">
      <?php
        $answer_day = '';
        $answer_month = '';
        $answer_year = '';
        $answer_hour = '';
        $answer_minute = '';
        $answer_second = '';
      
        switch ($format_info[0]) {
          case 1:
            echo "<option value=\"1\" selected>dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_day = substr($correct,0,2);
            $answer_month = substr($correct,3,2);
            $answer_year = substr($correct,6,4);
            $answer_hour = substr($correct,11,2);
            $answer_minute = substr($correct,14,2);
            $answer_second = substr($correct,17,2);
            break;
          case 2:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\" selected>dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_day = substr($correct,0,2);
            $answer_month = substr($correct,3,2);
            $answer_year = substr($correct,6,4);
            $answer_hour = substr($correct,11,2);
            $answer_minute = substr($correct,14,2);
            break;
          default:
          case 3:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\" selected>dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_day = substr($correct,0,2);
            $answer_month = substr($correct,3,2);
            $answer_year = substr($correct,6,4);
            break;
          case 4:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\" selected>mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_month = substr($correct,0,2);
            $answer_day = substr($correct,3,2);
            $answer_year = substr($correct,6,4);
            break;
          case 5:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\" selected>dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_day = substr($correct,0,2);
            $answer_month = substr($correct,3,2);
            $answer_year = substr($correct,6,4);
            break;
          case 6:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\" selected>hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_hour = substr($correct,0,2);
            $answer_minute = substr($correct,3,2);
            $answer_second = substr($correct,6,2);
            break;
          case 7:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\" selected>hh:mm (date)</option>\n";
            echo "<option value=\"8\">hh:mm (duration)</option>\n";
            $answer_hour = substr($correct,0,2);
            $answer_minute = substr($correct,3,2);
            $answer_second = '';
            break;
          case 8:
            echo "<option value=\"1\">dd/MM/yyyy hh:mm:ss</option>\n";
            echo "<option value=\"2\">dd/MM/yyyy hh:mm</option>\n";
            echo "<option value=\"3\">dd/MM/yyyy</option>\n";
            echo "<option value=\"4\">mm/dd/yyyy</option>\n";
            echo "<option value=\"5\">dd/MMMM/yyyy</option>\n";
            echo "<option value=\"6\">hh:mm:ss</option>\n";
            echo "<option value=\"7\">hh:mm (date)</option>\n";
            echo "<option value=\"8\" selected>hh:mm (duration)</option>\n";
            $answer_hour = substr($correct,0,2);
            $answer_minute = substr($correct,3,2);
            $answer_second = '';
            break;
        }
      ?>
      </select>
      <input type="hidden" name="old_correct" value="<?php echo $correct; ?>" />
      <input type="hidden" name="old_score_method" value="<?php echo $score_method; ?>" />
      </td>
    </tr>
    <tr>
      <td class="field">Answer</td>
      <td colspan="3">
      <?php
        echo "<input type=\"text\" size=\"5\" name=\"answer_day\" value=\"$answer_day\" class=\"clearinput\" title=\"dd\" /> / <input type=\"text\" size=\"5\" name=\"answer_month\" value=\"$answer_month\" class=\"clearinput\" title=\"MM\" /> / <input type=\"text\" size=\"5\" name=\"answer_year\" value=\"$answer_year\" class=\"clearinput\" title=\"yyyy\" />&nbsp;&nbsp;&nbsp;<input type=\"text\" size=\"5\" name=\"answer_hour\" value=\"$answer_hour\" class=\"clearinput\" title=\"hh\" /> : <input type=\"text\" size=\"5\" name=\"answer_minute\" value=\"$answer_minute\" class=\"clearinput\" title=\"mm\" /> : <input type=\"text\" size=\"5\" name=\"answer_second\" value=\"$answer_second\" class=\"clearinput\" title=\"ss\" />\n";
        echo "<input type=\"hidden\" size=\"5\" name=\"old_answer_day\" value=\"$answer_day\" /><input type=\"hidden\" size=\"5\" name=\"old_answer_month\" value=\"$answer_month\" /><input type=\"hidden\" size=\"5\" name=\"old_answer_year\" value=\"$answer_year\" /><input type=\"hidden\" size=\"5\" name=\"old_answer_hour\" value=\"$answer_hour\" /><input type=\"hidden\" size=\"5\" name=\"old_answer_minute\" value=\"$answer_minute\" /><input type=\"hidden\" size=\"5\" name=\"old_answer_second\" value=\"$answer_second\" />\n";
      ?>
      </td>
    </tr>
    <tr>
      <td class="note" style="text-align: right">(only for assessments)</td>
      <td></td><td colspan="4" class="note">dd/MM/yyyy&nbsp;&nbsp;hh:mm:ss</td>
    </tr>
    <tr>
      <td class="field">Feedback<br /><span class="note">(only for assessments)</span></td>
      <td colspan="3"><textarea name="feedback" cols="100" style="width:700px" rows="4" wrap="virtual"><?php echo $correct_fback; ?></textarea><textarea style="display:none" name="old_feedback" ><?php echo $correct_fback; ?></textarea></td>
    </tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    <?php
      echo echoMetadata($bloom, $q_id, $q_group, 4, $mysqli, true, $status, $disabled);
    ?>
    <tr>
      <td colspan="4">&nbsp;<?php echo hidden_edit_fields(); ?></td>
    </tr>
    <tr>
      <td colspan="4" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
    </tr>
  </table>
  </div>
  <?php
      require '../../include/changes_tab.inc';
      require '../../include/comments_tab.inc';
      displayMappingTab($_GET['paperID'],$mysqli, $created, $modified);
    }
  }
  $result->free_result();
  $result->close();
  $mysqli->close();
}

?>
</form>
</body>
</html>
