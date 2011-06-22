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

  if (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
    if ($_POST['correct'] != $_POST['old_correct']) {
      // Update the 'options' table with the new correct answer.
      $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
      $result->bind_param('si', $_POST['correct'], $q_id);
      $result->execute();  
      $result->close();

      // Record the change in 'track_changes'.
      $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'Correct Answer')");
      $result->bind_param('iss', $q_id, $_POST['old_correct'], $_POST['correct']);
      $result->execute();  
      $result->close();

      // Remark the student's answers in 'log2'.
      $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
      $result->bind_param('ii', $q_id, $_POST['paperID']);
      $result->execute();  
      $result->store_result();
      $result->bind_result($user_answer);
      while ($row = $result->fetch()) {
        if ($user_answer == $_POST['correct']) {
          $updateLog = $mysqli->prepare("UPDATE log2 SET mark=1 WHERE user_answer=? AND q_id=? AND q_paper=?");
          $updateLog->bind_param('sii', $user_answer, $q_id, $_POST['paperID']);
          $updateLog->execute();  
          $updateLog->close();
        } else {
          $updateLog = $mysqli->prepare("UPDATE log2 SET mark=0 WHERE user_answer=? AND q_id=? AND q_paper=?");
          $updateLog->bind_param('sii', $user_answer, $q_id, $_POST['paperID']);
          $updateLog->execute();  
          $updateLog->close();
        }
      }
      $result->free_result();
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
      $part_names = array('theme','notes','bloom','correct_fback','incorrect_fback','score_method','status','option_order');
      foreach($part_names as $section_name) {
        if(isset($_POST["$section_name"])) {
          $$section_name = $_POST["$section_name"];
        }
      }
      if (trim(strip_tags($scenario)) == '') $scenario = '';
      $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_bloom','old_correct_fback','old_incorrect_fback','old_score_method','old_status','old_option_order');
      foreach($part_names as $section_name) {
        if(isset($_POST["$section_name"])) {
          $$section_name = $_POST["$section_name"];
        }
      }

      // Strip MS Office HTML.
      $scenario = clearMSOtags($scenario);
      $leadin = clearMSOtags($leadin);
   
      // Upload Image (if exists) onto server
      if ($_FILES['q_media']['name'] != $_POST['old_q_media'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
        if ($_POST['old_q_media'] != '') {
          deleteMedia($_POST['old_q_media']);
        }
        $unique_name = uploadFile('q_media',$tmp_media_width,$tmp_media_height);
        $changes = true;
      } else {
        // If the media has not changed set the variables back to the old media settings before the update query.
        $unique_name = $_POST['old_q_media'];
        $tmp_media_width = $_POST['old_q_media_width'];
        $tmp_media_height = $_POST['old_q_media_height'];
        if (isset($_POST['delete_media0']) AND $_POST['delete_media0'] == '1') {
          deleteMedia($_POST['old_q_media']);
          $unique_name = '';
          $tmp_media_width = 0;
          $tmp_media_height = 0;
          $changes = true;
        }
      }
      
      $old_q_media = $_POST['old_q_media'];
      $q_media = $unique_name;

      if ($tmp_media_width == '') {
        $tmp_media_width = 0;
        $tmp_media_height = 0;
      }

      $part_names = array('foo','theme','scenario','leadin','notes','bloom','q_media','correct_fback','incorrect_fback','score_method','status','option_order');
      foreach($part_names as $section_name) {
        $old_section_name = 'old_' . $section_name;
        if(!isset($$old_section_name)) {
          $$old_section_name = '';
        } 
        if(!isset($$section_name)) {
          $$section_name = '';
        } 
        record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
      }
 
      saveKeywords($q_id, $userID, $changes, true, $mysqli);
      
      $question_teams = getTeams();
      record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
     
      for ($option_no=1; $option_no<20; $option_no++) {
        $option_changes = false;
        $old_option_text = html_entity_decode($_POST["old_option_text$option_no"]);
        $old_option_media = $_POST["old_option_media$option_no"];
        $tmp_option_media = $_FILES["new_option_media$option_no"]['name'];
        if ($_POST["new_option_text$option_no"] == '' and $old_option_media == '' and $tmp_option_media == '' and $old_option_text != '') {
          // Delete operation.
          $temp_id = $_POST["optionid$option_no"];
          $result = $mysqli->prepare("DELETE FROM options WHERE id_num=?");
          $result->bind_param('i', $temp_id);
          $result->execute();  
          $result->close();
    
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Deleted Option',?,$userID,?, '',NOW(),'Option #" . $option_no . "')");
          $result->bind_param('is', $q_id, $_POST["old_option_text$option_no"]);
          $result->execute();  
          $result->close();
          $option_changes = true;
        } elseif ($_POST["optionid$option_no"] != '' and ($_POST["new_option_text$option_no"] !== $old_option_text or $_POST["feedback_right$option_no"] != $_POST["old_feedback_right$option_no"] or $_FILES["new_option_media$option_no"]['name'] != $_POST["old_option_media$option_no"])) {
          // Edit operation.
          if ($_FILES["new_option_media$option_no"]['name'] != '' and $_FILES["new_option_media$option_no"]['name'] != $_POST["old_option_media$option_no"]) {
            deleteMedia($_POST["old_option_media$option_no"]);
            if ($tmp_option_media != '') {
              $tmp_option_media = uploadFile("new_option_media$option_no",$tmp_width,$tmp_height);
              $option_changes = true;
            }
          } else {
            $tmp_option_media = $_POST["old_option_media$option_no"];
            $tmp_width = $_POST["old_option_media_width$option_no"];
            $tmp_height = $_POST["old_option_media_height$option_no"];
            if (isset($_POST["delete_media$option_no"]) AND $_POST["delete_media$option_no"] == '1') {
              deleteMedia($_POST["old_option_media$option_no"]);
              $tmp_option_media = '';
              $tmp_width = 0;
              $tmp_height = 0;
            }
            $option_changes = true;
          }
        } elseif (($_POST["new_option_text$option_no"] != '' or $tmp_option_media != '') and $old_option_text == '' and $_POST["old_option_media$option_no"] == '') {
          // Add operation.
          $tmp_width = 0;
          $tmp_height = 0;
          if ($tmp_option_media != '') {
            $tmp_option_media = uploadFile("new_option_media$option_no",$tmp_width,$tmp_height);
          }
          $option_changes = true;
          $result = $mysqli->prepare("INSERT INTO options VALUES (?,?,?, '$tmp_width', '$tmp_height', ?, '',?, NULL, 1)");
          $result->bind_param('issss', $q_id, $_POST["new_option_text$option_no"], $tmp_option_media, $_POST["feedback_right$option_no"],$_POST['correct']);
          $result->execute();  
          $option_id = $mysqli->insert_id;
          $result->close();
    
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'New Option',?,$userID,'',?,NOW(),'Option #" . $option_no . "')");
          $result->bind_param('is', $q_id, $_POST["new_option_text$option_no"]);
          $result->execute();  
          $result->close();
        }
        if($option_changes == true) {
          $temp_id = $_POST["optionid$option_no"];
          $result = $mysqli->prepare("UPDATE options SET option_text=?, o_media=?, o_media_width='$tmp_width', o_media_height='$tmp_height', correct=?, feedback_right=? WHERE id_num=?");
          $tmp_option_text =  $_POST["new_option_text$option_no"];
          $result->bind_param('ssssi',$tmp_option_text, $tmp_option_media, $_POST['correct'], $_POST["feedback_right$option_no"], $temp_id);
          $result->execute();  
          $result->close();
          record_trackChanges('Edit Question', $q_id, $old_option_text, $_POST["new_option_text$option_no"], 'Option #' . $option_no, $userID, $changes);
        }
      }
      
      if ($_POST['correct'] != $_POST['old_correct']) {
        $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
        $result->bind_param('si', $_POST['correct'], $q_id);
        $result->execute();  
        $result->close();
        record_trackChanges('Edit Question', $q_id, $_POST['old_correct'], $_POST['correct'], 'Correct Answer', $userID, $changes);
      }
      
      save_external_responses($mysqli);
   
      if ($changes == true) {
      	$bloom = (empty($bloom)) ? NULL : $bloom;
        $result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, correct_fback=?, incorrect_fback=?, score_method=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=?, q_option_order=? WHERE q_id=?");
        $result->bind_param('ssssssssssssssssi', $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario = trim(strip_tags($scenario)), $leadin = trim(strip_tags($leadin)), $status, $option_order, $q_id);
        $result->execute();  
        $result->close();
      }
    
    } else {
      // Limited save
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
  <title>Edit MCQ Question<?php echo " $cfg_install_type"; ?></title>
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
      var options_used = 0;
      for (i=1; i<=20; i++) {
        if (document.getElementById('new_option_text' + i).value != "" || document.getElementById('old_option_media' + i).value != "") {
          options_used++;
        }
      }
      if (options_used < 2) {
        alert ("Please enter at least one option for this question. Although only one does make the question quite easy!");
        return false;
      }
      
      if (document.getElementById('leadin').value == "" || document.getElementById('leadin').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
        alert ("Please enter a Lead-in for this question.");
        return false;
      }
    }
  </script>
  <script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
  <script language="JavaScript" src="../../javascript/metadata.js"></script>
  <script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
  <?php echo $cfg_editor_javascript; ?>
  <script language="JavaScript" src="../../javascript/staff_help.js"></script>
  </head>

<body style="background-color:white">
<?php
  $option_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status, q_option_order FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status, $q_option_order);
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Multiple Choice)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
    <?php
      echo displayEditTab($created, $modified, $locked);
      if ($locked != '') {
        echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" style=\"width:100%; font-size:90%\">\n";
        echo "<tr><td style=\"width:35px; height:32px; text-align:right; background-image:url('../../artwork/locked_gradient.png'); background-repeat:repeat-x\"><img src=\"../../artwork/paper_locked_padlock.png\" width=\"19\" height=\"24\" alt=\"Locked\" />&nbsp;&nbsp;</td><td colspan=\"7\" style=\"height:32px; vertical-align:middle; background-image:url('../../artwork/locked_gradient.png'); background-repeat:repeat-x\"><strong>Question Locked</strong>&nbsp;&nbsp;&nbsp;This question is now locked and cannot be modified. <a style=\"color:black\" href=\"#\" onclick=\"launchHelp(161); return false;\">Click for more details.</a></td></tr>\n";
        echo "</table>\n";
        $disabled = ' disabled';
      } else {
        $disabled = check_edit_rights($tmp_ownerID, $mysqli);
        $checkout_author = check_lock_status($checkout_authorID, $checkout_time, $disabled, $mysqli, $q_id);
      }
      
      echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" align=\"center\" style=\"font-size:100%\">\n";
      echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td colspan=\"2\"><input type=\"text\" name=\"theme\" size=\"80\" value=\"$theme\" /><input type=\"hidden\" name=\"old_theme\" value=\"" . htmlentities($theme,ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"2\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">$notes</textarea><input type=\"hidden\" name=\"old_notes\" value=\"" . htmlentities($notes,ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td colspan=\"2\"><textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario,ENT_NOQUOTES,'UTF-8') . "</textarea>";
      echo wysiwyg_editor('oEdit1','scenario',$scenario);
      echo "</td>\n</tr>\n";
      if ($q_media != '') {
        echo "<tr>\n<td class=\"field\">Current Media</td><td colspan=\"2\">" . display_media($q_media,$q_media_width,$q_media_height,'0');
      }
      echo "<input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Change Media</td><td colspan=\"2\"><input type=\"file\" name=\"q_media\" size=\"65\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(the question)</span></td>\n<td colspan=\"2\"><textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin,ENT_NOQUOTES,'UTF-8') . "</textarea>";
      echo wysiwyg_editor('oEdit2','leadin',$leadin);
	  
      echo "</td>\n</tr>";
      echo "<tr>\n<td class=\"field\">Presentation</td><td colspan=\"2\"><input type=\"hidden\" name=\"old_score_method\" value=\"$score_method\" /><select name=\"score_method\">";
      if ($score_method == 'vertical') {
        echo "<option value=\"vertical\" selected>Vertical Option Button</option>\n";
        echo "<option value=\"vertical_other\">Vertical Option Buttons (with 'other' textbox)</option>\n";
        echo "<option value=\"horizontal\">Horizontal Option Button</option>\n";
        echo "<option value=\"dropdown\">Dropdown List</option>\n";
      } elseif ($score_method == 'vertical_other') {
        echo "<option value=\"vertical\">Vertical Option Button</option>\n";
        echo "<option value=\"vertical_other\" selected>Vertical Option Buttons (with 'other' textbox)</option>\n";
        echo "<option value=\"horizontal\">Horizontal Option Button</option>\n";
        echo "<option value=\"dropdown\">Dropdown List</option>\n";
      } elseif ($score_method == 'horizontal') {
        echo "<option value=\"vertical\">Vertical Option Button</option>\n";
        echo "<option value=\"vertical_other\">Vertical Option Buttons (with 'other' textbox)</option>\n";
        echo "<option value=\"horizontal\" selected>Horizontal Option Button</option>\n";
        echo "<option value=\"dropdown\">Dropdown List</option>\n";
      } else {
        echo "<option value=\"vertical\">Vertical Option Button</option>\n";
        echo "<option value=\"vertical_other\">Vertical Option Buttons (with 'other' textbox)</option>\n";
        echo "<option value=\"horizontal\">Horizontal Option Button</option>\n";
        echo "<option value=\"dropdown\" selected>Dropdown List</option>\n";
      }
      echo "</select></td>\n</tr>\n";
      echo "<tr><td class=\"field\">Option Order</td><td colspan=\"2\">" . option_order($q_option_order) . "</td></tr>\n";
      echo "<tr>\n<td colspan=\"3\">&nbsp;</td>\n</tr>\n";
      echo "<tr>\n<td colspan=\"3\" style=\"text-align:right; font-size:90%\"><strong>Correct<br />Answer</strong></td>\n</tr>\n";
    }

    echo '<tr><td style="text-align:right; font-size:90%">';
    if ($option_no < 3) {
      echo '<span class="mandatory">*</span>&nbsp;';
    }
    echo "<strong>" . $option_no . ".&nbsp;</strong></td>\n";
    //echo "<td colspan=\"2\"><input type=\"text\" name=\"new_option_text" . $option_no . "\" id=\"new_option_text" . $option_no . "\" size=\"105\" value=\"" . $option_text . "\" />&nbsp;<input type=\"radio\" name=\"correct\" value=\"" . $option_no . "\"";
    echo "<td colspan=\"2\"><textarea name=\"new_option_text" . $option_no . "\" id=\"new_option_text" . $option_no . "\" cols=\"105\" rows=\"1\">" . $option_text . "</textarea>&nbsp;<input type=\"radio\" name=\"correct\" value=\"" . $option_no . "\"";
    if ($correct == $option_no) {
      echo 'checked';
      $temp_correct = $correct;
    }
    echo "/><textarea name=\"old_option_text" . $option_no . "\" style=\"display:none\" cols=\"105\" rows=\"1\">" . $option_text . "</textarea><input type=\"hidden\" name=\"optionid" . $option_no . "\" value=\"$id_num\"></td></tr>\n";
    if ($o_media != '') {
      echo "<tr><td>&nbsp;</td><td class=\"field\" colspan=\"2\" style=\"text-align:left\">" . display_media($o_media,$o_media_width,$o_media_height,$option_no) . "</td></tr>\n";
    }
    echo "<tr><td>&nbsp;</td><td class=\"field\" style=\"text-align:left\">Feedback:</td><td><textarea cols=\"85\" rows=\"2\" name=\"feedback_right" . $option_no . "\">$feedback_right</textarea><input type=\"hidden\" name=\"old_feedback_right" . $option_no . "\" value=\"$feedback_right\" /></td>\n</tr>\n";
    echo "<tr><td><input type=\"hidden\" name=\"old_option_media" . $option_no . "\" id=\"old_option_media" . $option_no . "\" value=\"$o_media\" /><input type=\"hidden\" name=\"old_option_media_width" . $option_no . "\" value=\"$o_media_width\" /><input type=\"hidden\" name=\"old_option_media_height" . $option_no . "\" value=\"$o_media_height\" /></td>\n";
    echo "<td class=\"field\" style=\"text-align:left\">Change Media:</td><td><input type=\"file\" name=\"new_option_media" . $option_no . "\" size=\"65\" /></td>\n</tr>\n";
    echo '<tr><td colspan="3">&nbsp;</td></tr>';

    $option_no++;
  }
  for ($i=$option_no; $i<=20; $i++) {
    $hidden = ' style="display:none"';
    echo "<tr class=\"option\"$hidden><td style=\"text-align:right; font-size:90%\"><strong>" . $i . ".&nbsp;</strong></td>\n";
    echo "<td colspan=\"2\"><textarea name=\"new_option_text" . $i . "\" id=\"new_option_text" . $i . "\" cols=\"105\" rows=\"1\"></textarea>&nbsp;<input type=\"radio\" name=\"correct\" value=\"" . $i . "\" /><input type=\"hidden\" name=\"optionid" . $i . "\" value=\"\"></td></tr>\n";
    echo "<tr class=\"option\"$hidden><td>&nbsp;</td><td class=\"field\" style=\"text-align:left\">Feedback:</td><td><textarea cols=\"85\" rows=\"2\" name=\"feedback_right" . $i . "\"></textarea><input type=\"hidden\" name=\"old_feedback_right" . $i . "\" value=\"\" /></td>\n</tr>\n";
    echo "<tr class=\"option\"$hidden><td><textarea name=\"old_option_text" . $i . "\"  style=\"display:none\" cols=\"105\" rows=\"1\"></textarea><input type=\"hidden\" name=\"old_option_media" . $i . "\" id=\"old_option_media" . $option_no . "\" value=\"\" /></td>\n";
    echo "<td class=\"field\" style=\"text-align:left\">Change Media:</td><td><input type=\"file\" name=\"new_option_media" . $i . "\" size=\"65\" /></td>\n</tr>\n";
    echo "<tr class=\"option\"$hidden><td colspan=\"3\">&nbsp;</td></tr>\n";
  }
  ?>
  <tr>
    <td>&nbsp;</td>
    <td colspan="2"><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(4)"/></td>
  </tr>
  <?php
    echo "<tr>\n<td class=\"field\">General Feedback</td>\n<td colspan=\"2\"><textarea name=\"correct_fback\" cols=\"100\" style=\"width:700px\" rows=\"4\" wrap=\"virtual\">" . stripslashes($correct_fback) . "</textarea><input type=\"hidden\" name=\"old_correct_fback\" value=\"" . htmlentities(stripslashes($correct_fback),ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
    echo echoMetadata($bloom, $q_id, $q_group, 3, $mysqli, true, $status, $disabled);
  ?>
  <tr>
    <td colspan="3">&nbsp;<?php echo hidden_edit_fields(); ?><input type="hidden" name="old_correct" value="<?php if(isset($temp_correct)) echo $temp_correct; ?>" /></td>
  </tr>
  <tr>
    <td colspan="3" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
  </tr>
  </table>
</div>
    
<?php    
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'],$mysqli, $created, $modified);
  $result->close();
}

?>
</form>
</body>
</html>
