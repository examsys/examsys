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
  $correct_answers = '';
  for ($option_no = 1; $option_no <= 15; $option_no++) {
    if ($_POST["correct$option_no"] != $_POST["old_correct$option_no"]) {
      // Update the 'options' table with the new correct answer.
      $result = $mysqli->prepare("UPDATE options SET correct=? WHERE id_num=?");
      $result->bind_param('si', $_POST["correct$option_no"], $_POST["optionid$option_no"]);
      $result->execute();  
      $result->close();

      // Record the change in 'track_changes'.
      $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'correct #$option_no')");
      $result->bind_param('iss', $q_id, $_POST["old_correct$option_no"], $_POST["correct$option_no"]);
      $result->execute();  
      $result->close();
    }
    if (isset($_POST["optionid$option_no"])) {
      $correct_answers .= $_POST["correct$option_no"];
    }
  }    
  // Remark the student's answers in 'log2'.
  if ($_POST['score_method'] == 'TF_NegativeAbstain' or $_POST['score_method'] == 'YN_NegativeAbstain') {
    $subtract = 1;
  } elseif ($_POST['score_method'] == 'TF_NegativeAbstainHalf') {
    $subtract = 0.5;
  } else {
    $subtract = 0;
  }
  $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
  $result->bind_param('ii', $q_id, $_POST['paperID']);
  $result->execute();  
  $result->store_result();
  $result->bind_result($user_answer);
  while ($row = $result->fetch()) {
    $mark = 0;
    for ($i=0; $i<strlen($correct_answers); $i++) {
      if (substr($correct_answers,$i,1) == substr($user_answer,$i,1)) {
        $mark++;
      } else {
        if (substr($user_answer,$i,1) == 't' or substr($user_answer,$i,1) == 'f') $mark -= $subtract;    // Do not subtract marks for unanswered or abstein.
      }
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
    $part_names = array('theme','scenario','leadin','notes','bloom','general_feedback','score_method','status','option_order');
    foreach($part_names as $section_name) {
      //$$section_name = stripslashes($_POST["$section_name"]);
      $$section_name = $_POST["$section_name"];
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_bloom','old_general_feedback','old_score_method','old_status','old_option_order');
    foreach($part_names as $section_name) {
      //$$section_name = stripslashes($_POST["$section_name"]);
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
    $leadin = clearMSOtags($leadin);
    
    // Upload Image (if exists) onto server
    if ($_FILES['q_media']['name'] != $_POST['old_q_media'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
      if (isset($_POST['old_q_media']) and $_POST['old_q_media'] != '') {
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
  
    // Track Changes
    $changes = false;
    $part_names = array('theme','scenario','leadin','q_media','notes','bloom','general_feedback','score_method','status','option_order');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
    
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    if ($tmp_media_width == '') {
      $tmp_media_width = '0';
      $tmp_media_height = '0';
    }

    $stem_changes = false;
    for ($option_no=1; $option_no<=15; $option_no++) {
      $tmp_option_media = unique_filename($_FILES["option_media$option_no"]['name']);
      if ($_POST["option_text$option_no"] == '' and $tmp_option_media == '' and $_POST["old_option_text$option_no"] != '') {
        // Delete operation.
        $changes = true;
        $temp_id = $_POST["optionid$option_no"];
        $result = $mysqli->prepare("DELETE FROM options WHERE id_num=?");
        $result->bind_param('i', $temp_id);
        $result->execute();  
        $result->close();

        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Deleted Option',?,$userID,?,'',NOW(),'Option #" . $option_no . "')");
        $result->bind_param('is', $q_id, $_POST["old_option_text$option_no"]);
        $result->execute();  
        $result->close();
      } elseif (($_POST["option_text$option_no"] != '' or $tmp_option_media != '') and $_POST["old_option_text$option_no"] == '' and $_POST["old_option_media$option_no"] == '') {
        // Add operation.
        $changes = true;
        $tmp_width = 0;
        $tmp_height = 0;
        if ($tmp_option_media != '') {
          uploadFile("option_media$option_no",$tmp_width,$tmp_height);
        }
        $result = $mysqli->prepare("INSERT INTO options VALUES (?,?,?,?,?,?,?,?, NULL, 1)");
        $result->bind_param('isssssss', $q_id, $_POST["option_text$option_no"], $tmp_option_media, $tmp_width, $tmp_height, $_POST["option_right_fback$option_no"], $_POST["option_wrong_fback$option_no"], $_POST["correct$option_no"]);
        $result->execute();  
        $option_id = $mysqli->insert_id;
        $result->close();

        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'New Option',?,$userID,'',?,NOW(),'Option #" . $option_no . "')");
        $result->bind_param('is', $q_id, $_POST["option_text$option_no"]);
        $result->execute();  
        $result->close();
      } elseif ($_POST["option_text$option_no"] != '' or ( isset($_POST["old_option_media$option_no"]) and html_entity_decode($_FILES["option_media$option_no"]['name']) != html_entity_decode($_POST["old_option_media$option_no"]) ) ) {
        // Edit operation.
        if ($_FILES["option_media$option_no"]['name'] != '' and html_entity_decode($_FILES["option_media$option_no"]['name']) != html_entity_decode($_POST["old_option_media$option_no"])) {
          if (isset($_POST["old_option_media$option_no"]) and $_POST["old_option_media$option_no"] != '') {
            deleteMedia ("../media/" . $_POST["old_option_media$option_no"]);
          }
          $tmp_option_media = uploadFile("option_media$option_no",$tmp_width,$tmp_height);
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Media $option_no',?,$userID,?,?,NOW(),'Media $option_no')");
          $result->bind_param('iss', $_GET['q_id'], $_POST["old_option_media$option_no"], $_FILES["option_media$option_no"]['name']);
          $result->execute();  
          $result->close();
          $stem_changes = true;
        } else {
          $tmp_option_media = $_POST["old_option_media$option_no"];
          $tmp_width = $_POST["old_option_media_width$option_no"];
          $tmp_height = $_POST["old_option_media_height$option_no"];
          if (isset($_POST["delete_media$option_no"]) and $_POST["delete_media$option_no"] == '1') {
            deleteMedia($_POST["old_option_media$option_no"]);
            $tmp_option_media = '';
            $tmp_width = 0;
            $tmp_height = 0;
            $stem_changes = true;
            $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Media $option_no',?,$userID,?,'Deleted',NOW(),'Media $option_no')");
            $result->bind_param('is', $_GET['q_id'], $_POST["old_option_media$option_no"]);
            $result->execute();  
            $result->close();
          }
        }

        $part_names = array('option_text','option_right_fback','option_wrong_fback','correct');
        foreach($part_names as $section_name) {
          $new_section_name = $section_name . "$option_no";
          $new_section_name = html_entity_decode($_POST[$new_section_name]);
          $old_section_name = 'old_' . $section_name . "$option_no";
          $old_section_name = html_entity_decode($_POST[$old_section_name]);
          if ($new_section_name != $old_section_name) {
            $changes = true;
            $stem_changes = true;
            $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'$section_name #$option_no')");
            $result->bind_param('iss', $q_id, $old_section_name, $new_section_name);
            $result->execute();  
            $result->close();
          }
        }
      
        if ($stem_changes == true) {
          $tmp_option_text = $_POST["option_text$option_no"];
          $temp_id = $_POST["optionid$option_no"];
          $result = $mysqli->prepare("UPDATE options SET option_text=?, o_media=?, o_media_width=?, o_media_height=?, feedback_right=?, feedback_wrong=?, correct=? WHERE id_num=?");
          $tmp_option_right_fback = $_POST["option_right_fback$option_no"]; 
          $tmp_option_wrong_fback = $_POST["option_wrong_fback$option_no"];
          $result->bind_param('sssssssi', $tmp_option_text, $tmp_option_media, $tmp_width, $tmp_height, $tmp_option_right_fback, $tmp_option_wrong_fback, $_POST["correct$option_no"], $temp_id);
          $result->execute();  
          $result->close();
        }
      }
    }

    $q_type = 'dichotomous';
    if ($_POST['mcqconvert'] == '1') {  // Convert from Dichotomous to MCQ.
      $q_type = 'mcq';
      $score_method = 'vertical';
     
      $correct_answer = 0;
      for ($i=1; $i<=20; $i++) {
        if ($_POST["correct$i"] == 't') $correct_answer = $i;
      }
      $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
      $result->bind_param('si', $correct_answer, $q_id);
      $result->execute();  
      $result->close();
      $changes = true;
    }

    save_external_responses($mysqli);

    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET q_type=?, theme=?, scenario=?, leadin=?, correct_fback=?, score_method=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=?, q_option_order=? WHERE q_id=?");
      $scenario_plain = trim(strip_tags($scenario));
      $leadin_plain = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssssssssi', $q_type, $theme, $scenario, $leadin, $general_feedback, $score_method, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario_plain, $leadin_plain, $status, $option_order, $q_id);
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
<title>Edit Dichotomous Question<?php echo " $cfg_install_type"; ?></title>
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
      
    var correct_no = 0;
    for (var i=1; i<=15; i++) {
      if (document.getElementById('correct' + i).checked) {
        correct_no++;
      }
    }
    if (correct_no == 1) {
      if (confirm("There is only one correct answer, this would be better as a MCQ question type.\rDo you wish to convert this question to MCQ?")) {
        document.getElementById('mcqconvert').value = 1;
      }
    }
    
    if (edit_form.leadin.value == "") {
      alert ("Please enter a Lead-in.");
      return false;
    }
  }

  function updatelabels(obj) {
    for (i = 1; i < obj.length; i++) {
      if (obj[i].selected == true) {
        if (obj[i].value == 'TF_Negative' || obj[i].value == 'TF_NegativeAbstain' || obj[i].value == 'TF_NegativeAbstainHalf' || obj[i].value == 'TF_Positive' || obj[i].value == 'TF_PositiveAbstain') {
          for (x=1; x<=10; x++) {
            document.getElementById('true' + x).setAttribute('innerHTML','T');
            document.getElementById('false' + x).setAttribute('innerHTML','F');
          }
        } else {
          for (x=1; x<=10; x++) {
            document.getElementById('true' + x).setAttribute('innerHTML','Y');
            document.getElementById('false' + x).setAttribute('innerHTML','N');
          }
        }
      }
    }
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
  $question_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status, q_option_order FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $tmp_bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status, $q_option_order);
  while ($row = $result->fetch()) {
  if ($question_no == 1) {
    echo ("<form name=\"edit_form\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?q_id=" . $_GET['q_id'] ."\" enctype=\"multipart/form-data\">");
?>
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Dichotomous)</span>
      </td>
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
    echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\">\n";
    echo "<tr><td colspan=\"2\"><div class=\"section\">General Information</div></td></tr>\n";
    echo "<tr><td class=\"field\">Theme/Heading</td><td><input type=\"text\" size=\"80\" name=\"theme\" value=\"$theme\" /><input type=\"hidden\" name=\"old_theme\" value=\"" . htmlentities($theme,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
    echo "<tr><td align=\"right\" class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td><textarea name=\"notes\" cols=\"100\" rows=\"2\" style=\"width:700px\">$notes</textarea><input type=\"hidden\"name=\"old_notes\" value=\"" . htmlentities($notes,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
    echo "<tr><td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td><td>\n<textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario,ENT_NOQUOTES,'UTF-8') . "</textarea>";
    echo wysiwyg_editor('oEdit1','scenario',$scenario);
    echo "<input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\"><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\"><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\"></td></tr>\n";
    if ($q_media != '') {
      echo "<tr><td class=\"field\">Current Media</td><td>" . display_media($q_media,$q_media_width,$q_media_height,0) . "</td></tr>\n";
    }
    echo "<tr><td class=\"field\">Change Media</td><td><input type=\"file\" size=\"65\" name=\"q_media\" /></td></tr>\n";
    echo "<tr><td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Leadin<br /><span class=\"note\">(the question)</span></td><td>\n<textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin,ENT_NOQUOTES,'UTF-8') . "</textarea>";
    echo wysiwyg_editor('oEdit2','leadin',$leadin);
    echo "</td></tr>\n";
    echo "<tr><td class=\"field\">General Feedback</td><td><textarea name=\"general_feedback\" rows=\"3\" cols=\"100\" style=\"width:700px\" wrap=\"virtual\">$correct_fback</textarea><input type=\"hidden\"name=\"old_general_feedback\" value=\"" . htmlentities($correct_fback,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
    echo "<tr><td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Scoring method</td><td><input type=\"hidden\" name=\"old_score_method\" value=\"$score_method\" /><select name=\"score_method\" size=\"1\" onchange=\"updatelabels(this)\">\n";
    if ($score_method == 'TF_NegativeAbstain') {
      echo "<option value=\"TF_NegativeAbstain\" style=\"color:red\">True/False/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"TF_NegativeAbstainHalf\" style=\"color:red\">True/False/Abstain (Negative Marking -0.5)</option>\n";
      echo "<option value=\"TF_Positive\">True/False</option>\n";
      echo "<option value=\"YN_NegativeAbstain\" style=\"color:red\">Yes/No/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"YN_Positive\">Yes/No</option>\n";
      $label_true = 'T';
      $label_false = 'F';
    } elseif ($score_method == 'TF_NegativeAbstainHalf') {
      echo "<option value=\"TF_NegativeAbstain\" style=\"color:red\">True/False/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"TF_NegativeAbstainHalf\" style=\"color:red\" selected>True/False/Abstain (Negative Marking -0.5)</option>\n";
      echo "<option value=\"TF_Positive\">True/False</option>\n";
      echo "<option value=\"YN_NegativeAbstain\" style=\"color:red\">Yes/No/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"YN_Positive\">Yes/No</option>\n";
      $label_true = 'T';
      $label_false = 'F';
    } elseif ($score_method == 'TF_Positive') {
      echo "<option value=\"TF_NegativeAbstain\" style=\"color:red\">True/False/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"TF_NegativeAbstainHalf\" style=\"color:red\">True/False/Abstain (Negative Marking -0.5)</option>\n";
      echo "<option value=\"TF_Positive\" selected>True/False</option>\n";
      echo "<option value=\"YN_NegativeAbstain\" style=\"color:red\">Yes/No/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"YN_Positive\">Yes/No</option>\n";
      $label_true = 'T';
      $label_false = 'F';
    } elseif ($score_method == 'YN_NegativeAbstain') {
      echo "<option value=\"TF_NegativeAbstain\" style=\"color:red\">True/False/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"TF_NegativeAbstainHalf\" style=\"color:red\">True/False/Abstain (Negative Marking -0.5)</option>\n";
      echo "<option value=\"TF_Positive\">True/False</option>\n";
      echo "<option value=\"YN_NegativeAbstain\" style=\"color:red\" selected>Yes/No/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"YN_Positive\">Yes/No</option>\n";
      $label_true = 'Y';
      $label_false = 'N';
    } elseif ($score_method == 'YN_Positive') {
      echo "<option value=\"TF_NegativeAbstain\" style=\"color:red\">True/False/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"TF_NegativeAbstainHalf\" style=\"color:red\">True/False/Abstain (Negative Marking -0.5)</option>\n";
      echo "<option value=\"TF_Positive\">True/False</option>\n";
      echo "<option value=\"YN_NegativeAbstain\" style=\"color:red\">Yes/No/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"YN_Positive\" selected>Yes/No</option>\n";
      $label_true = 'Y';
      $label_false = 'N';
    } else {
      echo "<option value=\"TF_NegativeAbstain\" style=\"color:red\">True/False/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"TF_NegativeAbstainHalf\" style=\"color:red\">True/False/Abstain (Negative Marking -0.5)</option>\n";
      echo "<option value=\"TF_Positive\">True/False</option>\n";
      echo "<option value=\"YN_NegativeAbstain\" style=\"color:red\">Yes/No/Abstain (Negative Marking -1)</option>\n";
      echo "<option value=\"YN_Positive\">Yes/No</option>\n";
      $label_true = 'T';
      $label_false = 'F';
    }
    echo "</select>\n</td></tr>\n";
    echo "<tr><td class=\"field\">Option Order</td><td>" . option_order($q_option_order) . "</td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
  }

  echo "<tr><td colspan=\"2\"><div class=\"section\">Stem #" . $question_no . "</div><input type=\"hidden\" name=\"optionid$question_no\" value=\"$id_num\" /></td></tr>\n";
  echo "<tr><td class=\"field\">Question</td>";
  echo "<td><input type=\"text\" name=\"option_text$question_no\" size=\"70\" style=\"width:650px\" value=\"" . htmlentities($option_text,ENT_NOQUOTES,'UTF-8') . "\" /><input type=\"hidden\" name=\"old_option_text$question_no\" value=\"" . htmlentities($option_text,ENT_NOQUOTES,'UTF-8') . "\" />&nbsp;";

  if ($correct == 't') {
    echo "<input type=\"radio\" id=\"correct$question_no\" name=\"correct$question_no\" value=\"t\" checked />&nbsp;<span id=\"true$question_no\" style=\"font-weight:bold; font-size:10pt\">$label_true</span>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"correct$question_no\" value=\"f\" />&nbsp;<span id=\"false$question_no\" style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:10pt\">$label_false</span>";
  } elseif ($correct == 'f') {
    echo "<input type=\"radio\" id=\"correct$question_no\" name=\"correct$question_no\" value=\"t\" />&nbsp;<span id=\"true$question_no\" style=\"font-weight:bold; font-size:90%\">$label_true</span>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"correct$question_no\" value=\"f\" checked />&nbsp;<span id=\"false$question_no\" style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:10pt\">$label_false</span>";
  } else {
    echo "<input type=\"radio\" id=\"correct$question_no\" name=\"correct$question_no\" value=\"t\" />&nbsp;<span id=\"true$question_no\" style=\"font-weight:bold; font-size:90%\">$label_true</span>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"correct$question_no\" value=\"f\" />&nbsp;<span id=\"false$question_no\" style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:10pt\">$label_false</span>";
  }
  echo "<input type=\"hidden\" name=\"old_correct$question_no\" value=\"$correct\" /></td></tr>\n";

  if ($o_media != '') {
    echo "<tr><td  class=\"field\">Current Media</td><td>" . display_media($o_media,$o_media_width,$o_media_height,$question_no) . "</td></tr>\n";
  }
  echo "<tr><td><input type=\"hidden\" name=\"old_option_media" . $question_no . "\" value=\"$o_media\" /><input type=\"hidden\" name=\"old_option_media_width" . $question_no . "\" value=\"$o_media_width\" /><input type=\"hidden\" name=\"old_option_media_height" . $question_no . "\" value=\"$o_media_height\" /></td>\n";
  echo "<tr>\n<td class=\"field\">Change Media</td><td><input type=\"file\" size=\"65\" name=\"option_media" . $question_no . "\" /></td>\n</tr>\n";

  echo "<tr><td class=\"field\">Feedback if Right<br /><span style=\"font-weight:normal; font-size:9pt; color:red\">(default feedback)</span></td>";
  echo "<td><textarea name=\"option_right_fback$question_no\" rows=\"2\" cols=\"100\" style=\"width:700px\" wrap=\"virtual\">" . htmlentities($feedback_right,ENT_NOQUOTES,'UTF-8') . "</textarea><input type=\"hidden\" name=\"old_option_right_fback$question_no\" value=\"" . htmlentities($feedback_right,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";

  echo "<tr><td class=\"field\">Feedback if Wrong<br /><span style=\"font-weight:normal; font-size:9pt; color:#808080\">(leave blank to use default)</span></td>";
  echo "<td><textarea name=\"option_wrong_fback$question_no\" rows=\"2\" cols=\"100\" style=\"width:700px\" wrap=\"virtual\">" . htmlentities($feedback_wrong,ENT_NOQUOTES,'UTF-8') . "</textarea><input type=\"hidden\" name=\"old_option_wrong_fback$question_no\" value=\"" . htmlentities($feedback_wrong,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
  $question_no++;
}

for ($i=$question_no; $i<=15; $i++) {
  $hidden = 'style="display:none"';
  echo "<tr class=\"option\" $hidden><td colspan=\"2\"><div class=\"section\">Stem #" . $i . "</div><input type=\"hidden\" size=\"20\" name=\"stem_id$i\" value=\"\" /></td></tr>\n";
  echo "<tr class=\"option\" $hidden><td class=\"field\">Question</td>";
  echo "<td><input type=\"text\" name=\"option_text$i\" size=\"70\" style=\"width:650px\" value=\"\" /><input type=\"hidden\" name=\"old_option_text$i\" value=\"\" />&nbsp;";

  echo "<input type=\"radio\" id=\"correct$i\" name=\"correct$i\" value=\"t\" />&nbsp;<span id=\"true$i\" style=\"font-weight:bold; font-size:90%\">$label_true</span>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"correct$i\" value=\"f\" />&nbsp;<span id=\"false$i\" style=\"font-family:Arial,sans-serif; font-weight:bold; font-size:10pt\">$label_false</span>";
  echo "</td></tr>\n";

  echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Media</td><td><input type=\"file\" size=\"65\" name=\"option_media" . $i . "\" /></td>\n</tr>\n";

  echo "<tr class=\"option\" $hidden><td class=\"field\">Feedback if Right<br /><span style=\"font-weight:normal; font-size:90%; color:red\">(default feedback)</span></td>";
  echo "<td><textarea name=\"option_right_fback$i\" rows=\"2\" cols=\"100\" style=\"width:700px\" wrap=\"virtual\"></textarea><input type=\"hidden\" name=\"old_option_right_fback$i\" value=\"\" /></td></tr>\n";

  echo "<tr class=\"option\" $hidden><td class=\"field\">Feedback if Wrong<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(leave blank to use default)</span></td>";
  echo "<td><textarea name=\"option_wrong_fback$i\" rows=\"2\" cols=\"100\" style=\"width:700px\" wrap=\"virtual\"></textarea><input type=\"hidden\" name=\"old_option_wrong_fback$i\" value=\"\" /></td></tr>\n";

}
?>
<tr>
<td>&nbsp;</td>
<td><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(5)"/></td>
</tr>
<?php
echo echoMetadata($tmp_bloom, $q_id, $q_group, 1, $mysqli, true, $status, $disabled);
?>
  <tr>
    <td colspan="2"><?php echo hidden_edit_fields(); ?><input type="hidden" name="mcqconvert" id="mcqconvert" value="0" /></td>
  </tr>
  <tr>
    <td colspan="2" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
  </tr>
</table>
</div>
<?php
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
}
?>
</form>
</body>
</html>
