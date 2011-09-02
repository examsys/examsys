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

function replaceLetters($answer) {
  $new_answer = '';
  if ($answer != '') {
    $tmp_array = explode('$',$answer);
    foreach ($tmp_array as $individual_answer) {
      if ($new_answer == '') {
        $new_answer = chr($individual_answer+64);
      } else {
        $new_answer .= ',' . chr($individual_answer+64);
      }
    }
  }
  return $new_answer;
}

if (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
  $changes = false;
  $tmp_answer = '';
  $old_tmp_answer = '';
  for ($qcount=0; $qcount<10; $qcount++) {
    if (trim(strip_tags($_POST["scenario_text$qcount"])) != '' or $_POST["old_media$qcount"] != '') {
      $addr = $_POST["correct_options$qcount"];
      $count = count($addr);
      for ($i=0; $i<$count; $i++) {
        if ($i == 0) {
          $store_answer = ($addr[$i] + 1);
        } else {
          $store_answer .= '$' . ($addr[$i] + 1);
        }
      }
      if ($tmp_answer == '') {
        $tmp_answer = $store_answer;
        $old_tmp_answer = $_POST["old_correct_options$qcount"];
      } else {
        $tmp_answer .= '|' . $store_answer;
        $old_tmp_answer .= '|' . $_POST["old_correct_options$qcount"];
      }
      if ($store_answer != $_POST["old_correct_options$qcount"]) {
        // Record the change in 'track_changes'.
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'Scenario #" . ($qcount + 1) . " Answer')");
        $result->bind_param('iss', $q_id, $_POST["old_correct_options$qcount"], $store_answer);
        $result->execute();  
        $result->close();
        $changes = true;
      }
    }
  }

  if ($changes == true) {
    // Update the 'options' table with the new correct answer.
    $result = $mysqli->prepare("UPDATE options SET correct=\"" . $tmp_answer . "\" WHERE o_id=?");
    $result->bind_param('i', $q_id);
    $result->execute();  
    $result->close();

    // Remark the student's answers in 'log2'.
    $big_answer_parts = explode('|',$tmp_answer);
    $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
    $result->bind_param('ii', $q_id, $_POST['paperID']);
    $result->execute();  
    $result->store_result();
    $result->bind_result($user_answer);
    while ($row = $result->fetch()) {
      $big_user_parts = explode('|',$user_answer);
      $marks = 0;
      for ($i=0; $i<count($big_answer_parts); $i++) {
        if ($big_answer_parts[$i] == $big_user_parts[$i]) $marks++;
      }
      $updateLog = $mysqli->prepare("UPDATE log2 SET mark=? WHERE user_answer=? AND q_id=? AND q_paper=?");
      $updateLog->bind_param('dsii', $marks, $user_answer, $q_id, $_POST['paperID']);
      $updateLog->execute();  
      $updateLog->close();
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
    //Get all the data first into temporay variables.
    $part_names = array('theme','leadin','notes','bloom','status','option_order');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    $part_names = array('old_theme','old_leadin','old_notes','old_bloom','old_status','old_option_order');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    //Get all the data first into temporay variables.
    $tmp_theme = $_POST['theme'];
    $tmp_leadin = $_POST['leadin'];
    $tmp_notes = $_POST['notes'];
    $tmp_scenario = '';
    $tmp_answer = '';
    $tmp_right_feedback = '';
    $tmp_wrong_feedback = '';
    $tmp_media = '';
    $tmp_media_width = '';
    $tmp_media_height = '';

    $media_name = $_FILES['general_media']['name'];
    $media_type = $_FILES['general_media']['type'];
    if ($media_name != '' and $media_name != 'none') {
      // Change a media file.
      deleteMedia ($_POST['old_general_media']);
      $tmp_media = uploadFile("general_media",$tmp_media_width,$tmp_media_height);
    } else {
      $tmp_media = $_POST['old_general_media'];
      $tmp_media_width = $_POST['old_general_media_width'];
      $tmp_media_height = $_POST['old_general_media_height'];
      if (isset($_POST['delete_mediageneral']) and $_POST['delete_mediageneral'] == '1') {
        deleteMedia($_POST["old_general_media"]);
        $tmp_media = '';
        $tmp_media_width = '0';
        $tmp_media_height = '0';
        $changes = true;
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Media deleted',?,$userID,?,'Deleted',NOW(),'General Media')");
        $result->bind_param('is', $_GET['q_id'], $_POST["old_general_media"]);
        $result->execute();  
        $result->close();
      }
    }
  
    $tmp_scenario = '';
    $tmp_answer = '';
    $old_tmp_answer = '';
    $tmp_correct_feedback = '';
    $tmp_old_scenario = '';

    for ($qcount=0; $qcount<10; $qcount++) {
      if (isset($_FILES["media$qcount"]) and $_FILES["media$qcount"]['error'] != 4) {
        $media_name = $_FILES["media$qcount"]['name'];
        $media_type = $_FILES["media$qcount"]['type'];
      } else {
        $media_name = '';
        $media_type = '';
      }
      if (isset($_POST["old_media$qcount"])) {
        $old_media_name = $_POST["old_media$qcount"];
      } else {
        $old_media_name = '';
      }
      $tmp_media_name = '';
      $store_answer = '';
      if ($media_name != '' and $media_name != 'none') {
        // Change a media file.
        deleteMedia($old_media_name);
        $tmp_media_name = uploadFile("media$qcount", $tmp_width, $tmp_height);
        if ($tmp_media_width == '') {
          $tmp_media_width = $tmp_width;
          $tmp_media_height = $tmp_height;
        } else {
          $tmp_media_width .= '|' . $tmp_width;
          $tmp_media_height .= '|' . $tmp_height;
        }
        $tmp_media .= '|' . $tmp_media_name;
      } else {
        if (isset($_POST["delete_media$qcount"]) and $_POST["delete_media$qcount"] == '1') {
          deleteMedia($_POST["old_media$qcount"]);
          $tmp_media .= '|';
          $tmp_media_width .= '|0';
          $tmp_media_height .= '|0';
          $changes = true;
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Media deleted',?,$userID,?,'Deleted',NOW(),'Media " . ($qcount+1) . "')");
          $result->bind_param('is', $_GET['q_id'], $_POST["old_media$qcount"]);
          $result->execute();  
          $result->close();
        } else {
          if(isset($_POST["old_media$qcount"])) {
            $tmp_media .= '|' . $_POST["old_media$qcount"];
            $tmp_media_width .= '|' . $_POST["old_media_width$qcount"];
            $tmp_media_height .= '|' . $_POST["old_media_height$qcount"];
          } else {
            $tmp_media .= '|';
            $tmp_media_width .= '|0';
            $tmp_media_height .= '|0';
          }
        }
      }
      
      $part_names = array('scenario_text','correct_fback');
      foreach($part_names as $section_name) {
        if (isset($_POST["$section_name$qcount"])) {
          $new_section_name = $_POST["$section_name$qcount"];
        } else {
          $new_section_name = '';
        }
        $old_section_name = $_POST['old_' . $section_name . $qcount];
        if ($new_section_name != $old_section_name) {
          $changes = true;
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Scenario',?,$userID,?,?,NOW(),'$section_name" . ($qcount+1) . "')");
          $result->bind_param('iss', $_GET['q_id'],$old_section_name, $new_section_name);
          $result->execute();  
          $result->close();
        }
      }
  
      if (trim(strip_tags(nl2br(str_replace('&nbsp;','',$_POST["scenario_text$qcount"])))) != '' or $media_name != '' or (isset($_POST["old_media$qcount"]) and $_POST["old_media$qcount"] != '') ) {
        $tmp_scenario .= '|' . clearMSOtags($_POST["scenario_text$qcount"]);
        $tmp_old_scenario .= '|' . $_POST["old_scenario_text$qcount"];
        if(isset($_POST["correct_options$qcount"])) {
          $addr = $_POST["correct_options$qcount"];
          $count = count($addr);
          for ($i=0; $i<$count; $i++) {
            if ($i == 0) {
              $store_answer = ($addr[$i] + 1);
            } else {
              $store_answer .= '$' . ($addr[$i] + 1);
            }
          }
        } else {
          $addr = '';
          $store_answer = 0;
        }
        
        $tmp_answer .= '|' . $store_answer;
        $old_tmp_answer .= '|' . $_POST["old_correct_options$qcount"];
        if ($tmp_correct_feedback == '') {
          $tmp_correct_feedback .= $_POST["correct_fback$qcount"];
        } else {
          $tmp_correct_feedback .= '|' . $_POST["correct_fback$qcount"];
        }
      }
  
      if ($_POST["scenario_text$qcount"] != $_POST["old_scenario_text$qcount"]) {
        $changes = true;
      }
    }
    
    $tmp_scenario = substr($tmp_scenario,1);
    $tmp_old_scenario = substr($tmp_old_scenario,1);
    $tmp_answer = substr($tmp_answer,1);
    $old_tmp_answer = substr($old_tmp_answer,1);
    $tmp_correct_feedback = substr($tmp_correct_feedback,1);

    // Have any of the answers changed?
    if ($tmp_answer != $old_tmp_answer) {
      $changes = true;
      $old_answers = explode('|',$old_tmp_answer);    
      $new_answers = explode('|',$tmp_answer);   
      for ($i=0; $i<count($old_answers); $i++) {
        if ($old_answers[$i] != $new_answers[$i]) {
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'Scenario " . ($i+1) . " Answers')");
          $old_answer = replaceLetters($old_answers[$i]);
          $new_answer = replaceLetters($new_answers[$i]);
          $result->bind_param('iss', $_GET['q_id'], $old_answer, $new_answer);
          $result->execute();  
          $result->close();
        }
      }
    }

    $part_names = array('theme','leadin','notes','bloom','status','option_order');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    for ($ocount=0; $ocount<26; $ocount++) {
      if ($_POST["option_text$ocount"] != $_POST["old_option_text$ocount"]) $changes = true;
    }
  
    save_external_responses($mysqli);

    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, correct_fback=?, incorrect_fback=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, q_group=?, bloom=?, scenario_plain=?, leadin_plain=?, last_edited=NOW(), status=?, q_option_order=? WHERE q_id=?");
      $tmp_scenario_plain = trim(strip_tags($tmp_scenario));
      $tmp_leadin_plain = trim(strip_tags($tmp_leadin));
      $result->bind_param('sssssssssssssssi', $tmp_theme, $tmp_scenario, $tmp_leadin, $tmp_correct_feedback, $tmp_incorrect_feedback, $tmp_notes, $tmp_media, $tmp_media_width, $tmp_media_height, $question_teams, $bloom, $tmp_scenario_plain, $tmp_leadin_plain, $status, $option_order, $_GET['q_id']);
      $result->execute();  
      $result->close();
      $question_id = $_GET['q_id'];

      $result = $mysqli->prepare("DELETE FROM options WHERE o_id=?");
      $result->bind_param('i', $_GET['q_id']);
      $result->execute();  
      $result->close();

      for ($ocount=0; $ocount<26; $ocount++) {
        if (isset($_POST["option_text$ocount"]) and $_POST["option_text$ocount"] != '') {
          $result = $mysqli->prepare("INSERT INTO options VALUES (?,?,NULL,NULL,NULL,'','',?,NULL,1)");
          $tmp_option_text = $_POST["option_text$ocount"];
          $result->bind_param('iss', $question_id, $tmp_option_text, $tmp_answer);
          $result->execute();  
          $result->close();
        }
      }
    }
  
    for ($ocount=0; $ocount<26; $ocount++) {
      if (isset($_POST["option_text$ocount"]) and $_POST["option_text$ocount"] != $_POST["old_option_text$ocount"]) {
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'Option " . chr($ocount + 65) . "')");
        $tmp_old_option_text = $_POST["old_option_text$ocount"];
        $result->bind_param('iss', $_GET['q_id'],  $tmp_old_option_text, $_POST["option_text$ocount"]);
        $result->execute();  
        $result->close();
      }
    }
  } else {
    // Limited save.
    do_limitedSave($q_id, $mysqli, $userID);
  }
  redirect();
} elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel') {
  redirect();
} else {
  $matching_scenarios = array();
  $matching_media = array();
  $matching_media_width = array();
  $matching_media_height = array();
  $matching_correct = array();
  $matching_correct_fback = array();
  $matching_incorrect_fback = array();
  $matching_options = array();

  $option_no = 0;
  $question_no = 0;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain,  leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status, q_option_order FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status, $q_option_order);
  while ($row = $result->fetch()) {
    if ($question_no == 0) {
      $matching_scenarios = explode("|", $scenario);
      $matching_scenarios_plain = explode("|", $scenario_plain);
      $matching_media = explode("|", $q_media);
      $matching_media_width = explode("|", $q_media_width);
      $matching_media_height = explode("|", $q_media_height);
      $matching_correct = explode("|", $correct);
      $matching_correct_fback = explode("|", $correct_fback);
      $matching_incorrect_fback = explode("|", $incorrect_fback);
      
      $loop_limit = max(count($matching_scenarios), count($matching_media));
      for($i = 0; $i <= $loop_limit; $i++) {
        if (isset($matching_scenarios[$i]) and trim($matching_scenarios[$i]) != '' or (isset($matching_media[$i + 1]) and trim($matching_media[$i + 1]) != '')) $question_no++;
      }

      $group = $q_group;
    }
    $matching_options[] = $option_text;
    $option_no++;
  }
    
  for ($i=0; $i<=20; $i++) {
    if (!isset($matching_scenarios[$i])) $matching_scenarios[$i] = '';
    if (!isset($matching_scenarios_plain[$i])) $matching_scenarios_plain[$i] = '';
    if (!isset($matching_media[$i])) $matching_media[$i] = '';
    if (!isset($matching_media_width[$i])) $matching_media_width[$i] = '';
    if (!isset($matching_media_height[$i])) $matching_media_height[$i] = '';
    if (!isset($matching_correct[$i])) $matching_correct[$i] = '';
    if (!isset($matching_correct_fback[$i])) $matching_correct_fback[$i] = '';
    if (!isset($matching_incorrect_fback[$i])) $matching_incorrect_fback[$i] = '';
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Extended Matching<?php echo " $cfg_install_type"; ?></title>
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
  
    if (document.getElementById('leadin').value == "" || document.getElementById('leadin').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
      alert ("Please provide Lead In instructions.");
      return false;
    }
  }

  function updateoptions(optionID) {
    labeltext = document.getElementById("option_text" + optionID).value;
    for (i=0; i<10; i++) {
      tempref = "correct_options" + i;
      document.getElementById(tempref).options[optionID].text = String.fromCharCode(optionID + 65) + ". " + labeltext;
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
<form name="add_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . "?q_id=" . $_GET['q_id']; ?>" enctype="multipart/form-data">
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Extended Matching)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab($created, $modified, $locked);
?>
</table>
  <?php
    $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);
  ?>
  <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td class="section" colspan="2">General Details</td>
      <td width="10">&nbsp;</td>
      <td class="section" style="text-align:right; padding-right:6px">Available Options</td>
    </tr>
    <tr>
      <td class="field" style="text-align:right">Theme/Heading</td>
      <td><textarea name="theme" cols="83" rows="2" wrap="virtual" ><?php echo $theme; ?></textarea><textarea style="display:none" name="old_theme" ><?php echo $theme; ?></textarea></td>
      <td width="20">&nbsp;</td>
      <td rowspan="67" valign="top" style="text-align:right; padding-right:6px">
        <?php
          for ($option=0; $option<26; $option++) {
            if ($option < 4) {
              echo '<span class="mandatory">*</span>&nbsp;';
            }
            if ($option < $option_no) {
              echo "<span style=\"font-weight:bold; font-size:90%\">" . chr($option + 65) . ".&nbsp;</span><textarea style=\"width:150px\" rows=\"1\" onblur=\"updateoptions(" . $option . ")\" onchange=\"updateoptions(" . $option . ")\"  id=\"option_text" . $option . "\" name=\"option_text" . $option . "\" >" . $matching_options[$option] . "</textarea><textarea style=\"display:none\" name=\"old_option_text" . $option . "\" >" . $matching_options[$option] . "</textarea><br />\n";
            } else {
              echo "<span style=\"font-weight:bold; font-size:90%\">" . chr($option + 65) . ".&nbsp;</span><textarea style=\"width:150px\" rows=\"1\" onblur=\"updateoptions(" . $option . ")\" onchange=\"updateoptions(" . $option . ")\"  id=\"option_text" . $option . "\" name=\"option_text" . $option . "\" ></textarea><input type=\"hidden\" name=\"old_option_text" . $option . "\" value=\"\"/><br />\n";
            }
          }
        ?>
      </td>
    </tr>
    <tr>
      <td class="field" style="text-align:right">Notes</td>
      <td><textarea name="notes" cols="83" rows="2" wrap="virtual"><?php echo $notes; ?></textarea><textarea name="old_notes" cols="0" rows="0" style="display:none"><?php echo $notes; ?></textarea></td>
<td width="20">&nbsp;</td>
</tr>
<tr>
      <td class="field" style="text-align:right"><span class="mandatory">*</span>&nbsp;Lead-in<textarea style="display:none" name="old_leadin" id="old_leadin"><?php echo htmlentities($leadin);?></textarea></td>
      <td><?php echo wysiwyg_editor('oEditLeadin','leadin',$leadin); ?>         
      </td>
      <td width="20">&nbsp;</td>
    </tr>
    <tr>
    <?php
    if ($matching_media[0] != '') {
      echo "<td class=\"field\">Existing Media</td>\n";
      echo "<td>" . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0],'general') . "</td>\n";
      echo "<td width=\"20\">&nbsp;</td>\n";
    
    }
    ?>
    </tr>
    <tr>
      <td class="field">Change Media</td>
      <td><input type="file" name="general_media" size="68" value="" />
      <?php
        echo "<input type=\"hidden\" name=\"old_general_media\" value=\"" . $matching_media[0] . "\" />\n";
        echo "<input type=\"hidden\" name=\"old_general_media_width\" value=\"" . $matching_media_width[0] . "\" />\n";
        echo "<input type=\"hidden\" name=\"old_general_media_height\" value=\"" . $matching_media_height[0] . "\" />\n";
      ?>
      </td>
      <td width="20">&nbsp;</td>
    </tr>
    <tr>
      <td class="field">Option Order</td><td><?php echo option_order($q_option_order); ?></td>
      <td width="20">&nbsp;</td>
    </tr>

    <?php
    $roman = array('i','ii','iii','iv','v','vi','vii','viii','ix','x');
    for ($question=0; $question<10; $question++) {
      if ($question < $question_no) {
        echo "<tr>\n<td colspan=\"3\">&nbsp;</td>\n</tr>\n";
        echo "<tr>\n<td class=\"section\" colspan=\"2\">Scenario " . $roman[$question] . ".</td>\n<td width=\"20\">&nbsp;</td>\n</tr>";
        echo "<tr>\n<td class=\"field\">";
        if ($question < 2) {
          echo '<span class="mandatory">*</span>&nbsp;';
        }
        echo "Stem</td>\n<td><textarea style=\"display:none\" name=\"old_scenario_text$question\" id=\"old_scenario_text$question\">" . htmlentities($matching_scenarios[$question]) . "</textarea>";
        echo wysiwyg_editor("oEdit$question","scenario_text$question",$matching_scenarios[$question]);
        echo "</td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        if ($matching_media[$question + 1] != '') {
          echo "<td class=\"field\">Existing Media</td>\n";
          echo "<td>" . display_media($matching_media[$question + 1],$matching_media_width[$question + 1],$matching_media_height[$question + 1],$question) . "</td>\n";
          echo "<td width=\"20\">&nbsp;</td>\n";
        }
        echo "<tr>\n<td class=\"field\">Change Media</td>\n<td><input type=\"file\" name=\"media$question\" size=\"68\" value=\"\" /></td>\n";
        echo "<td width=\"20\">&nbsp;<input type=\"hidden\" name=\"old_media$question\" value=\"" . $matching_media[$question + 1] . "\" /><input type=\"hidden\" name=\"old_media_width$question\" value=\"" . $matching_media_width[$question + 1] . "\" /><input type=\"hidden\" name=\"old_media_height$question\" value=\"" . $matching_media_height[$question + 1] . "\" /></td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\">Feedback</td>\n<td><textarea name=\"correct_fback" . $question . "\" cols=\"83\" rows=\"3\" wrap=\"virtual\">" . $matching_correct_fback[$question] . "</textarea></td>\n<td width=\"20\">&nbsp;<textarea style=\"display:none\" name=\"old_correct_fback" . $question . "\">" .$matching_correct_fback[$question] . "</textarea></td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\">";
        if ($question < 2) {
          echo '<span class="mandatory">*</span>&nbsp;';
        }
        echo "Correct Answers<br /><span style=\"color:#808080; font-size:9pt; font-weight:normal\">(Use &lt;ctrl&gt; plus mouse<br />to select several items)</span></td>\n";
        echo "<td><select name=\"correct_options" . $question . "[]\" multiple=\"multiple\" id=\"correct_options$question\" style=\"width:300px\" size=\"10\">\n";
        $separate_matching_correct = array();
        $separate_matching_correct = explode('$', $matching_correct[$question]);
        for ($option=0; $option<26; $option++) {
          $tmp_match = 0;
          if ($option < $option_no) {
            foreach ($separate_matching_correct as $individual_matching_correct) {
              if ($individual_matching_correct == ($option + 1)) {
                $tmp_match = 1;
              }
            }
            if ($tmp_match == 1) {
              echo "<option value=\"$option\" selected>" . chr($option + 65) . ". " . $matching_options[$option] . "</option>\n";
            } else {
              echo "<option value=\"$option\">" . chr($option + 65) . ". " . $matching_options[$option] . "</option>\n";
            }
          } else {
            echo "<option value=\"$option\">" . chr($option + 65) . ".</option>\n";
          }
        }
        echo "</select></td><td><textarea style=\"display:none\" name=\"old_correct_options" . $question . "\" >" . $matching_correct[$question] . "</textarea></td>\n</tr>\n";
      } else {
        $hidden = 'style="display:none"';
        echo "<tr class=\"option\" $hidden>\n<td colspan=\"3\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"section\" colspan=\"2\">Scenario " . $roman[$question] . ".</td>\n<td width=\"20\">&nbsp;</td>\n</tr>";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Stem</td>\n<td><textarea style=\"display:none\" name=\"old_scenario_text$question\" id=\"old_scenario_text$question\"></textarea>";
        echo wysiwyg_editor("oEdit$question","scenario_text$question",$matching_scenarios[$question]);
        echo "</td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Change Media</td>\n<td><input type=\"file\" name=\"media$question\" size=\"68\" value=\"\" /></td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Feedback</td>\n<td><textarea name=\"correct_fback" . $question . "\" cols=\"83\" rows=\"3\" wrap=\"virtual\"></textarea></td>\n<td width=\"20\">&nbsp;<input type=\"hidden\" name=\"old_correct_fback" . $question . "\" value=\"\" /></td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Correct Answers<br /><span style=\"color:#808080; font-size:9pt; font-weight:normal\">(Use &lt;ctrl&gt; plus mouse<br />to select several items)</span></td>\n";
        echo "<td><select name=\"correct_options" . $question . "[]\" multiple=\"multiple\" id=\"correct_options$question\" style=\"width:300px\" size=\"10\">\n";
        for ($option=0; $option<26; $option++) {
          if ($option < $option_no) {
            echo "<option value=\"$option\">" . chr($option + 65) . ". " . $matching_options[$option] . "</option>\n";
          } else {
            echo "<option value=\"$option\">" . chr($option + 65) . ".</option>\n";
          }
        }
        echo "</select></td><td><input type=\"hidden\" name=\"old_correct_options" . $question . "\" value=\"\" /></td>\n</tr>\n";
      }
    }
    ?>
    <tr><td colspan="3">&nbsp;</td></tr>
	<tr>
	<td>&nbsp;</td>
	<td colspan="2"><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(6)"/></td>
	</tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    <?php
      echo echoMetadata($bloom, $q_id, $group, 3, $mysqli, true, $status, $disabled);
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
  displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
}
$mysqli->close();
?>
</form>
</body>
</html>