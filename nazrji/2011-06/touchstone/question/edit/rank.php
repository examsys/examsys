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
  $correct_answers = array();
  for ($option_no = 1; $option_no <= 20; $option_no++) {
    if ($_POST["old_option_text$option_no"] != '') {
      if ($_POST["answer$option_no"] != $_POST["old_answer$option_no"]) {
        $changes = true;
        // Update the 'options' table with the new correct answer.
        $result = $mysqli->prepare("UPDATE options SET correct=? WHERE id_num=?");
        $result->bind_param('si', $_POST["answer$option_no"], $_POST["optionid$option_no"]);
        $result->execute();  
        $result->close();

        // Record the change in 'track_changes'.
        record_trackChanges('Post Exam Answer change', $q_id, $_POST["old_answer$option_no"], $_POST["answer$option_no"], 'Option #' . $option_no, $userID, $changes);
      }
      $correct_answers[$option_no] = $_POST["answer$option_no"];
    }
    if ($_POST['score_method'] != $_POST['old_score_method']) {
      $changes = true;
      $result = $mysqli->prepare("UPDATE questions SET score_method=? WHERE q_id=?");
      $result->bind_param('si', $_POST['score_method'], $_GET['q_id']);
      $result->execute();  
      $result->close();

      record_trackChanges('Post Exam Answer change', $q_id, $_POST['old_score_method'], $_POST['score_method'], 'score_method', $userID, $changes);
    }
  }
  if ($changes == true) {
    // Remark the student's answers in 'log2'.
    $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
    $result->bind_param('ii', $q_id, $_POST['paperID']);
    $result->execute();  
    $result->store_result();
    $result->bind_result($user_answer);
    while ($row = $result->fetch()) {
      $mark = 0;
      $totalpos = 0;
      $correct_rank = true;
      $user_answers = explode(',',$user_answer);
      for ($i=1; $i<=count($correct_answers); $i++) {
        if ($i == 1 and $_POST['score_method'] == 'BonusMark') $totalpos++;
        if ($_POST['score_method'] == 'StrictOrder') {
          if ($user_answers[$i-1] == $correct_answers[$i]) $mark++;
        } elseif ($_POST['score_method'] == 'AllItemsCorrect') {
          if ($i == 1) $totalpos++;
          if ($user_answers[$i-1] <> $correct) $correct_rank = false;
        } elseif ($_POST['score_method'] == 'OrderNeighbours') {
          if ($user_answers[$i-1] != 0 and $user_answers[$i] != 'u') {
            if ($user_answers[$i-1] == $correct_answers[$i]) {
              $mark++;
            } elseif ($user_answers[$i-1] == ($correct_answers[$i] + 1)) {
              $mark = $mark + 0.5;
            } elseif ($user_answers[$i-1] == ($correct_answers[$i] - 1)) {
              $mark = $mark + 0.5;
            }
          }
        } elseif ($_POST['score_method'] == 'BonusMark') {
          if ($user_answers[$i-1] != 0 and $user_answers[$i] != 'u') {
            if ($correct_answers[$i] != 0) $mark++;
            if ($user_answers[$i-1] <> $correct_answers[$i]) $correct_rank = false;
          }
          if ($user_answers[$i-1] == 0 and $correct_answers[$i] != 0) $correct_rank = false;
        }
        if ($correct_answers[$i] != 0 and $_POST['score_method'] != 'AllItemsCorrect') {
          $totalpos++;
        } elseif ($_POST['score_method'] == 'StrictOrder') {
          $totalpos++;
        }
      }
      if ($correct_rank == true and $mark == ($totalpos - 1) and $_POST['score_method'] == 'BonusMark') {
        $mark++;  // Add one mark if the user has all options in the correct order
      } elseif ($correct_rank == true and $_POST['score_method'] == 'AllItemsCorrect') {
        $mark++;  // Add one mark if the user has all options in the correct order
      }
    
      $updateLog = $mysqli->prepare("UPDATE log2 SET mark=?, totalpos=? WHERE user_answer=? AND q_id=? AND q_paper=?");
      $updateLog->bind_param('disii', $mark, $totalpos, $user_answer, $q_id, $_POST['paperID']);
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
    $leadin = $_POST['leadin'];
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','bloom','correct_fback','incorrect_fback','score_method','status','option_order');
    foreach($part_names as $section_name) {
      $$section_name = stripslashes($_POST["$section_name"]);
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_bloom','old_correct_fback','old_incorrect_fback','old_score_method','old_status','old_option_order');
    foreach($part_names as $section_name) {
      $$section_name = stripslashes($_POST["$section_name"]);
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
    $leadin = clearMSOtags($leadin);

    $paperID = $_POST['paperID'];
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
  
    $part_names = array('theme','scenario','leadin','notes','bloom','q_media','correct_fback','incorrect_fback','score_method','status','option_order');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    for ($option_no=1; $option_no<=20; $option_no++) {
      $new_option_text = stripslashes($_POST["new_option_text$option_no"]);
      $old_option_text = stripslashes($_POST["old_option_text$option_no"]);
  
      if ($new_option_text == '' and $old_option_text != '') {
        // Delete operation.
        $changes = true;
        $temp_id = $_POST["optionid$option_no"];
        $result = $mysqli->prepare("DELETE FROM options WHERE id_num=?");
        $result->bind_param('i', $temp_id);
        $result->execute();  
        $result->close();

        record_trackChanges('Edit Question', $q_id, $old_option_text, '', 'Delete Option #' . $option_no, $userID, $changes);
      } elseif ($new_option_text != '' and $old_option_text == '') {
        // Add operation.
        $changes = true;
        $tmp_width = 0;
        $tmp_height = 0;
        $result = $mysqli->prepare("INSERT INTO options VALUES (?,?,'','0','0','','',?,NULL,1)");
        $result->bind_param('iss', $_GET['q_id'], $new_option_text, $_POST["answer$option_no"]);
        $result->execute();
        $option_id = $mysqli->insert_id;
        $result->close();

        record_trackChanges('Edit Question', $q_id, '', $new_option_text, 'New Option #' . $option_no, $userID, $changes);
      } elseif ($new_option_text != '') {
        // Edit operation.
        $temp_id = $_POST["optionid$option_no"];
        if ($old_option_text != $new_option_text) {
          record_trackChanges('Edit Question', $q_id, $old_option_text, $new_option_text, 'Option #' . $option_no, $userID, $changes);
        }
        if ($_POST["old_answer$option_no"] != $_POST["answer$option_no"]) {
          record_trackChanges('Edit Question', $q_id, $_POST["old_answer$option_no"], $_POST["answer$option_no"], 'Edit Option Rank #' . $option_no, $userID, $changes);
        }
        if ($changes == true) {
          $result = $mysqli->prepare("UPDATE options SET option_text=?, correct=? WHERE id_num=?");
          $result->bind_param('ssi', $new_option_text, $_POST["answer$option_no"], $temp_id);
          $result->execute();  
          $result->close();
        }
      }
    }

    save_external_responses($mysqli);
    
    if ($changes == true) {
      // Update Question data
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, correct_fback=?, incorrect_fback=?, score_method=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=?, q_option_order=? WHERE q_id=?");
      $scenario_striped = trim(strip_tags($scenario));
      $leadin_striped = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssssssssi', $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario_striped, $leadin_striped, $status, $option_order, $_GET['q_id']);
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
   <title>Edit Rank Question<?php echo " $cfg_install_type"; ?></title>
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
       alert ("Please enter a Leadin for the question.");
       return false;
     }

    // Check all options with text have a value in the drop-down
    var missingAnswers = [];
    for (var i = 1; i <= 20; i++) {
    	var text;
    	var ddl;

    	text = document.getElementById('new_option_text' + i);
    	if (text != null) {
				ddl = document.getElementById('answer' + i);
				if (ddl != null && ddl.options[ddl.selectedIndex].value == '') {
					missingAnswers[missingAnswers.length] = i;
				}
    	}
    }

    if (missingAnswers.length > 0) {
        var answers = '';
        var plural = (missingAnswers.length > 1) ? 's' : '';
        for (i = 0; i < missingAnswers.length; i++) {
          if (i > 0 && i == missingAnswers.length - 1) answers += ' and ';
          answers += missingAnswers[i];
          if (i < missingAnswers.length - 2) answers += ', ';
        }
        alert('Missing answer' + plural + ' for option' + plural + ' ' + answers + '. Please select a correct answer for all options. Use \'N/A\' for distractors.');
        return false;
    }

    return true;
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
   $result->bind_result($q_id, $q_type, $theme, $scenario,  $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status, $q_option_order);
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Ranking)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab($created, $modified, $locked);
?>
      <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
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
           echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td><input type=\"text\" name=\"theme\" size=\"80\" value=\"$theme\" /><input type=\"hidden\" name=\"old_theme\" value=\"" . htmlentities($theme,ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
           echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\">$notes</textarea><input type=\"hidden\" name=\"old_notes\" value=\"" . htmlentities($notes,ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
           echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td>\n<textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario,ENT_NOQUOTES,'UTF-8') . "</textarea>";
           echo wysiwyg_editor('oEdit1','scenario',$scenario);
           echo "</td>\n</tr>\n";
           echo "<tr>\n<td class=\"field\">Current Media<input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\"><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\"><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\"></td>\n<td>";
           if ($q_media == '') {
             echo "<span style=\"color:#808080\">&lt;no media&gt;</span>";
           } else {
             echo display_media($q_media,$q_media_width,$q_media_height,0);
           }
           echo "</td>\n</tr>\n";
           echo "<tr>\n<td class=\"field\">Change Media</td><td><input type=\"file\" size=\"60\" name=\"q_media\" /></td>\n</tr>\n";
           echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(the question)</span></td>\n<td>\n<textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin,ENT_NOQUOTES,'UTF-8') . "</textarea>";
           echo wysiwyg_editor('oEdit2','leadin',$leadin);
           echo "</td>\n</tr>";
           echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Scoring method</td>\n";
           echo "<td>\n<input type=\"hidden\" name=\"old_score_method\" value=\"$score_method\" /><select name=\"score_method\" size=\"1\">\n";
           if ($score_method == 'StrictOrder') {
             echo "<option value=\"StrictOrder\" selected>Strict Order (mark per option)</option>\n";
           } else {
             echo "<option value=\"StrictOrder\">Strict Order (mark per option)</option>\n";
           }
           if ($score_method == 'AllItemsCorrect') {
             echo "<option value=\"AllItemsCorrect\" selected>All Items Correct (1 mark in total)</option>\n";
           } else {
             echo "<option value=\"AllItemsCorrect\">All Items Correct (1 mark in total)</option>\n";
           }
           if ($score_method == 'OrderNeighbours') {
             echo "<option value=\"OrderNeighbours\" selected>Strict Order with half marks for neighbours</option>\n";
           } else {
             echo "<option value=\"OrderNeighbours\">Strict Order (plus half marks for neighbours)</option>\n";
           }
           if ($score_method == 'BonusMark') {
             echo "<option value=\"BonusMark\" selected>Correct items with bonus for overall order</option>\n";
           } else {
             echo "<option value=\"BonusMark\">Correct items with bonus for overall order</option>\n";
           }
           echo "</select>\n</td>\n</tr>\n";
           echo "<tr><td class=\"field\">Option Order</td><td>" . option_order($q_option_order) . "</td></tr>\n";

           echo "<tr>\n<td colspan=\"2\">&nbsp;</td>\n</tr>\n";
           echo "<tr>\n<td align=\"right\" class=\"field\">Options<br /><span class=\"note\">(Display Order)</span></td><td style=\"text-align: right\" class=\"field\"><strong>Correct<br />Answer</strong></td>\n</tr>\n";
         }

         echo "<tr>\n<td style=\"text-align: right\"><strong>" . $option_no . ".&nbsp;</strong></td>\n";
         echo "<td><input type=\"text\" name=\"new_option_text" . $option_no . "\" id=\"new_option_text" . $option_no . "\" size=\"73\" style=\"width:650px\" value=\"" . htmlentities($option_text,ENT_NOQUOTES,'UTF-8') . "\" /><input type=\"hidden\" name=\"old_option_text" . $option_no . "\" value=\"" . htmlentities($option_text,ENT_NOQUOTES,'UTF-8') . "\" /><input type=\"hidden\" name=\"optionid$option_no\" value=\"" . $id_num . "\" />&nbsp;";
         echo "<input type=\"hidden\" name=\"old_answer" . $option_no . "\" value=\"$correct\" /><select name=\"answer" . $option_no . "\" id=\"answer" . $option_no . "\">\n<option value=\"\"></option>\n";
         if ($correct == '0') {
           echo "<option value=\"0\" selected>N/A</option>\n";
         } else {
           echo "<option value=\"0\">N/A</option>\n";
         }
         for ($possibility=1; $possibility <=20; $possibility++) {
           if ($possibility == $correct) {
             echo "<option value=\"" . $possibility . "\" selected>" . $possibility;
           } else {
             echo "<option value=\"" . $possibility . "\">" . $possibility;
           }
           if ($possibility == 1) {
             echo "st";
           } elseif ($possibility == 2) {
             echo "nd";
           } elseif ($possibility == 3) {
             echo "rd";
           } else {
             echo "th";
           }
           echo "</option>\n";
         }
         echo "</select></td></tr>\n";
         $option_no++;
       }
       for ($i=$option_no; $i<=20;$i++) {
  	    $hidden = 'style="display:none"'; 
        echo "<tr class=\"option\" $hidden><td style=\"text-align:right\"><strong>" . $i . ".&nbsp;</strong></td>\n<td><input type=\"text\" name=\"new_option_text" . $i. "\" size=\"73\" style=\"width:650px\" value=\"\" /><input type=\"hidden\" name=\"old_option_text" . $i. "\" value=\"\" />&nbsp;";
         echo "<select name=\"answer" . $i . "\">\n<option value=\"\"></option>\n";
         echo "<option value=\"0\">N/A</option>\n";
         for ($possibility=1; $possibility <=20; $possibility++) {
           echo "<option value=\"" . $possibility . "\">" . $possibility;
           if ($possibility == 1) {
             echo "st";
           } elseif ($possibility == 2) {
             echo "nd";
           } elseif ($possibility == 3) {
             echo "rd";
           } else {
             echo "th";
           }
           echo "</option>\n";
         }
         echo "</select></td></tr>\n";
       }
       ?>
	   <tr>
	   <td>&nbsp;</td>
	   <td colspan="2"><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(1)"/></td>
	   </tr>
	   <?php
       echo "<tr>\n<td class=\"field\">Feedback if Correct<br /><span style=\"font-weight:normal; font-size:90%; color:red\">(default feedback)</span></td>\n<td><textarea name=\"correct_fback\" cols=\"100\" style=\"width:700px\" rows=\"4\" wrap=\"virtual\">" . $correct_fback . "</textarea><input type=\"hidden\" name=\"old_correct_fback\" value=\"" . htmlentities($correct_fback,ENT_NOQUOTES,'UTF-8') . "\" /></td>\n</tr>\n";
       echo "<tr>\n<td class=\"field\">Feedback if Wrong<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(leave blank to use default)</span></td>\n<td><textarea name=\"incorrect_fback\" cols=\"100\" style=\"width:700px\" rows=\"4\" wrap=\"virtual\">" . $incorrect_fback . "</textarea><input type=\"hidden\" name=\"old_incorrect_fback\" value=\"" . htmlentities($incorrect_fback,ENT_NOQUOTES,'UTF-8') . "\" /><input type=\"hidden\" name=\"options\" value=\"" . $option_no . "\"></td>\n</tr>\n";

       echo echoMetadata($bloom, $q_id, $q_group, 2, $mysqli, true, $status, $disabled);
      ?>
      <tr>
        <td colspan="2"><?php echo hidden_edit_fields(); ?></td>
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
  $result->free_result();
  $result->close();
}
$mysqli->close();
?>
</form>
</body>
</html>
