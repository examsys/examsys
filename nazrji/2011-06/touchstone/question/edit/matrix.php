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
  $changes = false;
  $tmp_answer = '';
  $old_tmp_answer = '';
  for ($qcount=0; $qcount<10; $qcount++) {
    if (trim(strip_tags($_POST["stem$qcount"])) != '') {
      if ($tmp_answer == '') {
        $tmp_answer = $_POST["correct$qcount"];
        $old_tmp_answer = $_POST["old_correct$qcount"];
      } else {
        $tmp_answer .= '|' . $_POST["correct$qcount"];
        $old_tmp_answer .= '|' . $_POST["old_correct$qcount"];
      }
      if ($_POST["correct$qcount"] != $_POST["old_correct$qcount"]) {
        // Record the change in 'track_changes'.
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Post Exam Answer change',?,$userID,?,?,NOW(),'Scenario #" . ($qcount + 1) . " Answer')");
        $result->bind_param("iss", $q_id, $_POST["old_correct$qcount"], $_POST["correct$qcount"]);
        $result->execute();  
        $result->close();
        $changes = true;
      }
    }
  }

  if ($changes == true) {
    // Update the 'options' table with the new correct answer.
    $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
    $result->bind_param('si', $tmp_answer, $q_id);
    $result->execute();  
    $result->close();

    // Remark the student's answers in 'log2'.
    $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
    $result->bind_param('ii', $q_id, $_POST['paperID']);
    $result->execute();  
    $result->store_result();
    $result->bind_result($user_answer);
    while ($row = $result->fetch()) {
      $user_parts = explode('|',$user_answer);
      $marks = 0;
      for ($i=0; $i<count($answer_parts); $i++) {
        if ($user_parts[$i] == $answer_parts[$i]) $marks++;
      }
      $updateLog = $mysqli->prepare("UPDATE log2 SET mark=? WHERE user_answer=? AND q_id=? AND q_paper=?");
      $updateLog->bind_param('dsii', $marks, $user_answer, $q_id, $_POST['paperID']);
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
    //Get all the data first into temporay variables.
    $leadin = $_POST['leadin'];
    $part_names = array('theme','notes','bloom','status', 'feedback','option_order');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    $part_names = array('old_theme','old_leadin','old_notes','old_bloom','old_status', 'old_feedback','old_option_order');
    foreach($part_names as $section_name) {
      $$section_name = html_entity_decode($_POST["$section_name"]);
    }

    // Strip MS Office HTML.
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

    if ($tmp_media_width == '') {
      $tmp_media_width = 0;
      $tmp_media_height = 0;
    }
    $tmp_scenario = '';
    for ($qcount=0; $qcount<10; $qcount++) {
      if ($_POST["stem$qcount"] != '') {
        if ($tmp_scenario == '') {
          $tmp_scenario = $_POST["stem$qcount"];
          if(isset($_POST["correct$qcount"])) {
            $tmp_answer = $_POST["correct$qcount"];
          } else {
            $tmp_answer = '';
          }
        } else {
          $tmp_scenario .= '|' . $_POST["stem$qcount"];
          if(isset($_POST["correct$qcount"])) {
            $tmp_answer .= '|' . $_POST["correct$qcount"];
          } else {
            $tmp_answer .= '|';
          }
        }
      }
      if (isset($_POST["correct$qcount"]) and $_POST["correct$qcount"] != $_POST["old_correct$qcount"] and trim(mysql_escape_string($_POST["stem$qcount"])) != '') {
        $changes = true;
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'Stem " . ($qcount + 1) . " Answer')");
        $result->bind_param('iss', $q_id, $_POST["old_correct$qcount"], $_POST["correct$qcount"]);
        $result->execute();  
        $result->close();
      }
    
      $new_stem = html_entity_decode($_POST["stem$qcount"]);
      $old_stem = html_entity_decode($_POST["old_stem$qcount"]);
    
      if ($new_stem != $old_stem) {
        $changes = true;
        if ($new_stem != '' and $old_stem != '') {
          // Edit operation.
          record_trackChanges('Edit Question', $q_id, $old_stem, $new_stem, 'Stem ' . ($qcount + 1), $userID, $changes);
        } elseif ($old_stem == '' and $new_stem != '') {
          // Add operation.    
          record_trackChanges('Edit Question', $q_id, '', $new_stem, 'Add Stem ' . ($qcount + 1), $userID, $changes);
        } elseif ($old_stem != '' and $new_stem == '') {
          // Delete operation.
          record_trackChanges('Edit Question', $q_id, $old_stem, '', 'Delete Stem ' . ($qcount + 1), $userID, $changes);
        }
      }
    }

    $part_names = array('theme','leadin','q_media','notes','bloom','status', 'feedback','option_order');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }

    $result = $mysqli->prepare("DELETE FROM options WHERE o_id=?");
    $result->bind_param('i', $q_id);
    $result->execute();  
    $result->close();

    for ($ocount=0; $ocount<10; $ocount++) {
      if ($_POST["option_text$ocount"] != '') {
        $result = $mysqli->prepare("INSERT INTO options VALUES (?,?,NULL,NULL,NULL,'','',?,NULL,1)");
        $result->bind_param('iss', $q_id, $_POST["option_text$ocount"], $tmp_answer);
        $result->execute();  
        $result->close();
      }

      $new_option_text = html_entity_decode($_POST["option_text$ocount"]);
      $old_option_text = html_entity_decode($_POST["old_option_text$ocount"]);
    
      if ($new_option_text != $old_option_text) {
        $changes = true;
        if ($new_option_text != '' and $old_option_text != '') {
          // Edit operation.
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'Option " . ($ocount + 1) . "')");
          $result->bind_param('iss', $q_id, $old_option_text, $new_option_text);
          $result->execute();  
          $result->close();
        } elseif ($old_option_text == '' and $new_option_text != '') {
          // Add operation.    
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,'',?,NOW(),'Add Option " . ($ocount + 1) . "')");
          $result->bind_param('is', $q_id, $new_option_text);
          $result->execute();  
          $result->close();
        } elseif ($old_option_text != '' and $new_option_text == '') {
          // Delete operation.
          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?, '',NOW(),'Delete Option " . ($ocount + 1) . "')");
          $result->bind_param('is', $q_id, $old_option_text);
          $result->execute();  
          $result->close();
        }
      }
    }

    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    save_external_responses($mysqli);

    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, scenario_plain=?, leadin_plain=?, last_edited=NOW(), status=?, correct_fback=?, q_option_order=? WHERE q_id=?");
      $tmp_scenario = trim(strip_tags($tmp_scenario));
      $leadin = trim(strip_tags($leadin));
      $tmp_scenario = trim(strip_tags($tmp_scenario));
      $leadin = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssssssi', $theme, $tmp_scenario, $leadin, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $tmp_scenario, $leadin, $status, $feedback, $option_order, $_GET['q_id']);
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
  $matching_scenarios = array();
  $matching_correct = array();
  $matching_correct_fback = array();
  $matching_options = array();

  $option_no = 0;
  $question_no = 0;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, leadin, leadin_plain, correct_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status, q_option_order FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $leadin_plain, $correct_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $correct, $id_num, $marks, $bloom, $group, $checkout_time, $checkout_authorID, $locked, $status, $q_option_order);
  while ($row = $result->fetch()) {
    if ($question_no == 0) {
      $matching_scenarios = explode("|", $scenario);
      $matching_media = $q_media;
      $matching_media_width = $q_media_width;
      $matching_media_height = $q_media_height;
      $matching_correct = explode("|", $correct);
      $matching_correct_fback = explode("|", $correct_fback);

      $question_no = substr_count($scenario,'|') + 1;
    }
    $matching_options[] = $option_text;
    $option_no++;
  }

  for ($i=0; $i<=10; $i++) {
    if (!isset($matching_scenarios[$i])) $matching_scenarios[$i] = '';
    if (!isset($matching_scenarios_plain[$i])) $matching_scenarios_plain[$i] = '';
    if (!isset($matching_correct[$i])) $matching_correct[$i] = '';
    if (!isset($matching_correct_fback[$i])) $matching_correct_fback[$i] = '';
    if (!isset($matching_options[$i])) $matching_options[$i] = '';
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Matrix</title>
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
      alert ("Please provide Lead In instructions.");
      return false;
    }
  }

  function updateoptions(optionID) {
    labeltext = document.getElementById("option_text" + optionID).value;
    for (i=0; i<10; i++) {
      tempref = "correct_option" + i;
      document.getElementById(tempref).options[optionID + 1].text = String.fromCharCode(optionID + 65) + ". " + labeltext;
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

<form name="add_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id']; ?>" enctype="multipart/form-data">
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Matrix)</span></td>
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
  ?>
  <table cellpadding="2" cellspacing="0" border="0" align="center">
    <tr><td colspan="2"><div class="section">General Information</div></td></tr>
    <tr>
      <td class="field" style="text-align: right">Theme/Heading</td>
      <td><input type="text" name="theme" size="82" value="<?php echo $theme; ?>" /><input type="hidden" name="old_theme" value="<?php echo htmlentities($theme,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <tr>
      <td class="field" style="text-align: right">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"><?php echo $notes; ?></textarea><input type="hidden" name="old_notes" value="<?php echo htmlentities($notes,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <?php
    if ($matching_media != '') {
      echo "<td class=\"field\">Existing Media</td>\n";
      echo "<td>" . display_media($matching_media,$matching_media_width,$matching_media_height,0) . "</td>\n";
    }
    ?>
    <tr>
      <td class="field">Change Media</td>
      <td><input type="file" name="q_media" size="65" />
      <?php
        echo "<input type=\"hidden\" name=\"old_q_media\" value=\"$matching_media\" />\n";
        echo "<input type=\"hidden\" name=\"old_q_media_width\" value=\"$matching_media_width\" />\n";
        echo "<input type=\"hidden\" name=\"old_q_media_height\" value=\"$matching_media_height\" />\n";
      ?>
</td>
</tr>
<tr>
<td class="field" style="text-align: right"><span class="mandatory">*</span>&nbsp;Lead-in</td>
      <td><textarea style="display:none" name="old_leadin" id="old_leadin"><?php echo htmlentities($leadin,ENT_NOQUOTES,'UTF-8'); ?></textarea>
	  <?php echo wysiwyg_editor('oEdit1','leadin',$leadin); ?>        
      </td>
    </tr>
    <tr>
      <td class="field">Option Order</td><td colspan="2"><?php echo option_order($q_option_order); ?></td>
    </tr>
    
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr><td colspan="2"><div class="section">Options Matrix</div><div class="note">(questions in rows / answers by column)</div></td></tr>
    <tr><td colspan="2">
    <table cellpadding="2" cellspacing="0" border="0" style="border:solid #7F9DB9 1px">
    <?php
    for ($question=0; $question<=10; $question++) {
      if ($question == 1 or $question == 3 or $question == 5 or $question == 7 or $question == 9) {
        echo '<tr style="background-color: #BFCEF3">';
      } else {
        echo '<tr>';
      }
      if ($question <= $question_no) {
        for ($col_no=0; $col_no<=10; $col_no++) {
          if ($question == 0 and $col_no == 0) {
            echo '<td style="border-right:solid #CFCFCF 1px">&nbsp;</td>';
          } elseif ($question == 0 and $col_no > 0) {
            echo '<td style="border-right: solid #CFCFCF 1px"><input type="text" name="option_text' . ($col_no - 1) . '" size="6"  value="' . htmlentities($matching_options[$col_no - 1],ENT_NOQUOTES,'UTF-8') . '" /><input type="hidden" name="old_option_text' . ($col_no - 1) . '" value="' . htmlentities($matching_options[$col_no - 1],ENT_NOQUOTES,'UTF-8') . '" /></td>';
          } elseif ($col_no == 0 and $question > 0) {
            echo '<td style="border-right:solid #CFCFCF 1px; border-top: solid #CFCFCF 1px"><input type="text" name="stem' . ($question - 1) . '" size="6" value="' . htmlentities($matching_scenarios[$question - 1],ENT_NOQUOTES,'UTF-8') . '" /><input type="hidden" name="old_stem' . ($question - 1) . '" value="' . htmlentities($matching_scenarios[$question - 1],ENT_NOQUOTES,'UTF-8') . '" /></td>';
          } else {
            if ($matching_correct[$question - 1] == $col_no) {
              echo '<td style="border-right:solid #CFCFCF 1px; border-top:solid #CFCFCF 1px"><div align="center"><input type="radio" name="correct' . ($question - 1) . '" value="' . $col_no . '" checked /></div></td>';
            } else {
              echo '<td style="border-right:solid #CFCFCF 1px; border-top:solid #CFCFCF 1px"><div align="center"><input type="radio" name="correct' . ($question - 1) . '" value="' . $col_no . '" /></div></td>';
            }
          }
        }
        if ($question > 0) echo "<input type=\"hidden\" name=\"old_correct" . ($question - 1) . "\" value=\"" . $matching_correct[$question - 1] . "\" />";
        echo "</tr>\n";
      } else {
        if ($question == 1 or $question == 3 or $question == 5 or $question == 7 or $question == 9) {
          echo '<tr style="background-color: #BFCEF3">';
        } else {
          echo '<tr>';
        }
        for ($col_no=0; $col_no<=10; $col_no++) {
          if ($question == 0 and $col_no == 0) {
            echo '<td style="border-right:solid #CFCFCF 1px">&nbsp;</td>';
          } elseif ($question == 0 and $col_no > 0) {
            echo '<td style="border-right: solid #CFCFCF 1px"><input type="text" name="option_text' . ($col_no - 1) . '" size="6" /><input type="hidden" name="old_option_text' . ($col_no - 1) . '" /></td>';
          } elseif ($col_no == 0 and $question > 0) {
            echo '<td style="border-right:solid #CFCFCF 1px; border-top:solid #CFCFCF 1px"><input type="text" name="stem' . ($question - 1) . '" size="6" /><input type="hidden" name="old_stem' . ($question - 1) . '" /></td>';
          } else {
            echo '<td style="border-right:solid #CFCFCF 1px; border-top:solid #CFCFCF 1px"><div align="center"><input type="radio" name="correct' . ($question - 1) . '" value="' . $col_no . '" /><input type="hidden" name="old_correct' . ($question - 1) . '" value="" /></div></td>';
          }
        }
        echo "</tr>\n";
      }
    }
    ?>
    </table>
    
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr><td class="field">Feedback</td><td><textarea name="feedback" cols="100" rows="3" style="width:700px"><?php echo $correct_fback; ?></textarea><textarea name="old_feedback" cols="0" rows="0" style="display:none"><?php echo $correct_fback; ?></textarea></td></tr>

    <tr><td colspan="2">&nbsp;</td></tr>
    <?php
      echo echoMetadata($bloom, $q_id, $group, 2, $mysqli, true, $status, $disabled);
    ?>
  <tr><td colspan="2">&nbsp;<?php echo hidden_edit_fields(); ?></td></tr>
    <tr>
      <td colspan="2" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
    </tr>
  </table>
</div>
<?php
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'],$mysqli, $created, $modified);
  $mysqli->close();
}
?>
</form>
</body>
</html>