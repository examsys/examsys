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
  $totalpos = 0;
  for ($option_no = 1; $option_no <= 20; $option_no++) {
    if ((isset($_POST["option_text$option_no"]) and $_POST["option_text$option_no"] != '') or (isset($_POST["old_option_media$option_no"]) and $_POST["old_option_media$option_no"] != '')) {
      if (isset($_POST["checkbox$option_no"]) and $_POST["checkbox$option_no"] == 'y') {
        $correct = 'y';
        $totalpos++;
      } else {
        $correct = 'n';
      }
      $old_correct = $_POST["old_checkbox$option_no"];
      if ($correct != $old_correct) {
        // Update the 'options' table with the new correct answer.
        $result = $mysqli->prepare("UPDATE options SET correct=? WHERE id_num=?");
        $result->bind_param('si', $correct, $_POST["optionid$option_no"]);
        $result->execute();
        $result->close();

        // Record the change in 'track_changes'.
        $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Post Exam Answer change',?,$userID,?,?, NOW(),'correct #$option_no')");
        $result->bind_param('iss', $q_id, $old_correct, $correct);
        $result->execute();
        $result->close();
      }
      $correct_answers .= $correct;
    }
  }
  // Remark the student's answers in 'log2'.
  $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
  $result->bind_param('ii', $q_id, $_POST['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($user_answer);
  while ($row = $result->fetch()) {
    $mark = 0;
    $all_correct = true;
    for ($i=0; $i<strlen($correct_answers); $i++) {
      if (substr($correct_answers,$i,1) == substr($user_answer,$i,1)) {
        if ($_POST['score_method'] == 'AllNegative') {
          $mark++;
        } else {
          if (substr($correct_answers,$i,1) == 'y') $mark++;
        }
      } else {
        if ($_POST['score_method'] == 'AllNegative') {
          $mark--;
        }
        $all_correct = false;
      }
    }
    if ($_POST['score_method'] == 'AllItemsCorrect' and $all_correct == false) $mark = 0;

    // Recalculate total possible marks if 'all correct' or 'negative'.
    if ($_POST['score_method'] == 'AllItemsCorrect') {
      $totalpos = 1;
    } elseif ($_POST['score_method'] == 'AllNegative') {
      $totalpos = strlen($correct_answers);
    }

    $updateLog = $mysqli->prepare("UPDATE log2 SET mark=?, totalpos=? WHERE user_answer=? AND q_id=? AND q_paper=?");
    $updateLog->bind_param('disii', $mark, $totalpos, $user_answer, $q_id, $_POST['paperID']);
    $updateLog->execute();
    $updateLog->close();
  }
  $result->close();

  redirect();
} elseif (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'],$q_id,$mysqli);

    $changes = false;
    $leadin = $_POST['leadin'];
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','bloom','correct_fback','score_method','status','option_order');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_bloom','old_correct_fback','old_score_method','old_status','old_option_order');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
    $leadin = clearMSOtags($leadin);
    
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

    if (isset($_POST['other']) and $_POST['other'] == 1) $score_method = 'other';

    $part_names = array('theme','scenario','leadin','notes','q_media','bloom','correct_fback','score_method','status','option_order');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    for ($option_no=1; $option_no<=20; $option_no++) {
      if (isset($_POST['checkbox' . $option_no]) and $_POST['checkbox' . $option_no] == 1) {
        $tmp_correct = 'y';
      } else {
        $tmp_correct = 'n';
      }
      
      $old_media_deleted = false;
      if (isset($_POST["delete_media$option_no"]) and $_POST["delete_media$option_no"] == '1') {
        deleteMedia($_POST["old_option_media$option_no"]);
        $tmp_option_media = '';
        $tmp_width = 0;
        $tmp_height = 0;
        $old_media_deleted = true;
      }
      $tmp_option_media = '';
      if(isset($_FILES["new_option_media$option_no"])) $tmp_option_media = $_FILES["new_option_media$option_no"]['name'];
      if ($_POST["optionid$option_no"] != '' and isset($_POST["option_text$option_no"]) and $_POST["option_text$option_no"] == '' and $tmp_option_media == '' and ($_POST["old_option_media$option_no"] == '' or $old_media_deleted)) {
        // Delete operation.
        $changes = true;
        $temp_id = $_POST["optionid$option_no"];
        if (!$old_media_deleted and isset($_POST["old_option_media$option_no"]) and $_POST["old_option_media$option_no"] != '') {
          deleteMedia($_POST["old_option_media$option_no"]);
        }
        $result = $mysqli->prepare("DELETE FROM options WHERE id_num=?");
        $result->bind_param('i', $temp_id);
        $result->execute();
        $result->close();

        record_trackChanges('Edit Question', $q_id, $_POST["old_option_text$option_no"], '', 'Delete Option #' . $option_no, $userID, $changes);
      } elseif ( isset($_POST["option_text$option_no"]) and ($_POST["option_text$option_no"] != '' or $tmp_option_media != '') and $_POST["old_option_text$option_no"] == '' and $_POST["old_option_media$option_no"] == '') {
        // Add operation.
        $changes = true;
        $tmp_option_media = uploadFile("new_option_media$option_no", $tmp_width, $tmp_height);
        
        $fb_correct = (isset($_POST["option_right_fback$option_no"])) ? $_POST["option_right_fback$option_no"] : '';
        $fb_incorrect = (isset($_POST["option_wrong_fback$option_no"])) ? $_POST["option_wrong_fback$option_no"] : '';
        $result = $mysqli->prepare("INSERT INTO options VALUES (?,?,?,?,?,?,?,?,NULL,1)");
        $result->bind_param('isssssss', $q_id, $_POST["option_text$option_no"], $tmp_option_media, $tmp_width, $tmp_height, $fb_correct, $fb_incorrect, $tmp_correct);
        $result->execute();
        $option_id = $mysqli->insert_id;
        $result->close();

        record_trackChanges('Edit Question', $q_id, '', $_POST["option_text$option_no"], 'New Option #' . $option_no, $userID, $changes);
      } elseif ( (isset($_POST["option_text$option_no"]) and $_POST["option_text$option_no"] != '') or (isset($_POST["old_option_media$option_no"]) and $tmp_option_media != $_POST["old_option_media$option_no"]) ) {
        // Edit operation.
        $stem_changes = false;
        if ($tmp_option_media != '' and $tmp_option_media != $_POST["old_option_media$option_no"]) {
          if (!$old_media_deleted and isset($_POST["old_option_media$option_no"]) and $_POST["old_option_media$option_no"] != '') {
            deleteMedia($_POST["old_option_media$option_no"]);
          }
          $tmp_option_media = uploadFile("new_option_media$option_no", $tmp_width, $tmp_height);
          
          $stem_changes = true;
        } else {
          $tmp_option_media = $_POST["old_option_media$option_no"];
          $tmp_width = $_POST["old_option_media_width$option_no"];
          $tmp_height = $_POST["old_option_media_height$option_no"];
          if ($old_media_deleted) {
            $stem_changes = true;
            record_trackChanges('Edit Question', $q_id, $_POST["old_option_media$option_no"], '', 'Delete Media #' . $option_no, $userID, $changes);
          }
        }
        $option_media = $tmp_option_media;

        $temp_id = $_POST["optionid$option_no"];
        $part_names = array('option_text','option_media','option_right_fback','option_wrong_fback','correct');
        foreach($part_names as $section_name) {
          $new_section_name = $section_name . "$option_no";
          if(isset($_POST[$new_section_name])) {
            $new_section_name = $_POST[$new_section_name];
          } else {
            $new_section_name = '';
          }
          $old_section_name = 'old_' . $section_name . "$option_no";
          if(isset($_POST[$old_section_name])) {
            $old_section_name = $_POST[$old_section_name];
          }  else {
            $old_section_name = '';
          }
          if ($new_section_name != $old_section_name) {
            $changes = true;
            $stem_changes = true;
            record_trackChanges('Edit Question', $q_id, $old_section_name, $new_section_name, $section_name . '#' . $option_no, $userID, $changes);
          }
        }

        if (isset($_POST["old_checkbox$option_no"]) and $_POST["old_checkbox$option_no"] == 'y') {
          $old_checkbox = 'y';
        } else {
          $old_checkbox = 'n';
        }
        if (isset($_POST["checkbox$option_no"]) and $_POST["checkbox$option_no"] == 'y') {
          $new_checkbox = 'y';
        } else {
          $new_checkbox = 'n';
        }
        if ($old_checkbox != $new_checkbox) {
          $changes = true;
          $stem_changes = true;
          record_trackChanges('Edit Question', $q_id, $old_checkbox, $new_checkbox, 'Option #' . $option_no . ' Answer', $userID, $changes);
        }
        if ($stem_changes == true) {
          $temp_id = $_POST["optionid$option_no"];
          $tmp_option = $_POST["option_text$option_no"];
          $result = $mysqli->prepare("UPDATE options SET option_text=?, o_media=?, o_media_width=?, o_media_height=?, feedback_right=?, feedback_wrong=?, correct=? WHERE id_num=?");
          $tmp_option_right_fback = $_POST["option_right_fback$option_no"];
          $tmp_option_wrong_fback =  $_POST["option_wrong_fback$option_no"];
          $result->bind_param('sssssssi', $tmp_option, $option_media, $tmp_width, $tmp_height, $tmp_option_right_fback, $tmp_option_wrong_fback, $new_checkbox, $temp_id);
          $result->execute();
          $result->close();
        }
      }
    }

    $q_type = 'mrq';
    if ($_POST['mcqconvert'] == '1') {  // Convert from MRQ to MCQ.
      $q_type = 'mcq';
      $score_method = 'vertical';

      $correct_answer = 0;
      for ($i=1; $i<=20; $i++) {
        if ($_POST["checkbox$i"] == 'y') $correct_answer = $i;
      }
      $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
      $result->bind_param("si", $correct_answer, $q_id);
      $result->execute();
      $result->close();
      $changes = true;
    }

    save_external_responses($mysqli);

    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET q_type=?, theme=?, scenario=?, leadin=?, correct_fback=?, incorrect_fback=?, score_method=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=?, q_option_order=? WHERE q_id=?");
      $scenario_stripped = trim(strip_tags($scenario));
      $leadin_stripped = trim(strip_tags($leadin));
      $result->bind_param('sssssssssssssssssi', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario_stripped, $leadin_stripped, $status, $option_order, $q_id);
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
  <title>Edit MRQ Question<?php echo " $cfg_install_type"; ?></title>

  <link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
   <script language="JavaScript" src="../../javascript/metadata.js"></script>
   <script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
   <?php echo $cfg_editor_javascript; ?>
   <script language="JavaScript" src="../../javascript/staff_help.js"></script>
  <script language="JavaScript" src="../../javascript/jquery-1.6.1.min.js"></script>
<script language="JavaScript">

$(function() { $('#edit_form').submit(checkForm); });

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
  if ($('#leadin').val() == "" || $('#leadin').val() == "&nbsp;" || $('#leadin').val() == "<p>&nbsp;</p>" || $('#leadin').val() == "<div>&nbsp;</div>" || $('#leadin').val() == "<br />") {
    alert ("Please enter a Leadin for the question.");
    return false;
  }
  var correct_no = 0;
  var options_used = 0;
  for (var i=1; i<=20; i++) {
    if ($('#checkbox' + i).prop('checked') == true) {
    	correct_no++;
    }
    if ($('#option_text' + i).val() != "" || $('#old_option_media' + i).val() != "" || $('#new_option_media' + i).val() != "") {
	    options_used++;
    }
  }

  if (options_used < 2) {
    alert ("Please enter at least one option for this question. Although only one does make the question quite easy!");
    return false;
  }
  if (correct_no == 1) {
    if (confirm("There is only one correct answer, this would be better as a MCQ question type.\rDo you wish to convert this question to MCQ?")) {
	    $('#mcqconvert').val(1);
    }
  }
  
  if ((options_used / 2) < correct_no) {
    alert ("WARNING: You have " + options_used + " options of which " + correct_no + " are correct.\nThe examinee will automatically gain " + (correct_no - (options_used - correct_no))  + " point(s).\n\nDecrease the number of correct answers or add more\ndistractors.");
    return false;
  }

  return true;
}
</script>
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
           <form id="edit_form" name="edit_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id='.$q_id; ?>" enctype="multipart/form-data">
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Multiple Response)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
        echo displayEditTab($created, $modified, $locked);
        $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);

        echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" align=\"center\" style=\"font-size:100%\">\n";
        echo "<tr>\n<td colspan=\"2\" class=\"section\">Question Details</td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td colspan=\"6\"><textarea name=\"theme\" cols=\"100\" style=\"width:700px\" >$theme</textarea><textarea style=\"display:none\" name=\"old_theme\"/>$theme</textarea><input type=\"hidden\" name=\"checkout_author\" value=\"$checkout_authorID\" /></td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"6\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">" . $notes . "</textarea><textarea style=\"display:none\" name=\"old_notes\" />$notes</textarea></td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td>\n<textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario) . "</textarea>";
        echo wysiwyg_editor('oEdit1','scenario',$scenario);
        echo "<input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td>\n</tr>\n";
        if ($q_media != '') {
          echo "<tr>\n<td class=\"field\">Current Media</td>\n<td>" . display_media($q_media,$q_media_width,$q_media_height,0) . "</td>\n</tr>\n";
        }
        echo "<tr>\n<td class=\"field\">Change Media</td>\n<td><input type=\"file\" size=\"65\" name=\"q_media\" /></td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(the question)</span></td>\n<td><textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin) . "</textarea>";
        echo wysiwyg_editor('oEdit2','leadin',$leadin);
        echo "</td>\n</tr>";
        echo "<tr><td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Scoring method</td><td><select name=\"score_method\" size=\"1\">\n";
        if ($score_method == 'AllNegative') {
          echo '<option value="AllNegative" selected>1 Mark per Option (with Negative Marking)</option>';
          echo '<option value="AllItemsCorrect">All Options must be Correct (1 mark in total)</option>';
          echo '<option value="SelectedPositive">1 Mark per True Option</option>';
        } elseif ($score_method == 'AllItemsCorrect') {
          echo '<option value="AllNegative">1 Mark per Option (with Negative Marking)</option>';
          echo '<option value="AllItemsCorrect" selected>All Options must be Correct (1 mark in total)</option>';
          echo '<option value="SelectedPositive">1 Mark per True Option</option>';
        } else {
          echo '<option value="AllNegative">1 Mark per Option (with Negative Marking)</option>';
          echo '<option value="AllItemsCorrect">All Options must be Correct (1 mark in total)</option>';
          echo '<option value="SelectedPositive" selected>1 Mark per True Option</option>';
        }
        echo "</select></td></tr>\n";
        echo "<tr>\n<td class=\"field\">Presentation</td><td style=\"font-size:90%\">";
        if ($score_method == 'other') {
          echo "<input type=\"checkbox\" name=\"other\" value=\"1\" checked />&nbsp;include 'other' textbox <span style=\"color: #808080\">(use with surveys)</span><input type=\"hidden\" name=\"old_score_method\" value=\"other\" />";
        } else {
          echo "<input type=\"checkbox\" name=\"other\" value=\"1\" />&nbsp;include 'other' textbox <span style=\"color:#808080\">(use with surveys)</span><input type=\"hidden\" name=\"old_score_method\" value=\"$score_method\" />";
        }
        echo "</td>\n</tr>\n";
        echo "<tr>\n<td class=\"field\">General Feedback</td>\n<td><textarea name=\"correct_fback\" cols=\"100\" style=\"width:700px\" rows=\"3\" wrap=\"virtual\">$correct_fback</textarea><textarea style=\"display:none\" name=\"old_correct_fback\">" . $correct_fback . "</textarea></td>\n</tr>\n";
        echo "<tr><td class=\"field\">Option Order</td><td>" . option_order($q_option_order) . "</td></tr>\n";
        echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

        $c_fback = $correct_fback;
        $inc_fback = $incorrect_fback;
        $group = $q_group;
      }

      echo "<tr>\n<td colspan=\"2\" class=\"section\">Option " . $option_no . ".<input type=\"hidden\" name=\"optionid" . $option_no . "\" value=\"$id_num\"></td>\n</tr>\n";
      echo "<tr>\n<td colspan=\"2\" style=\"text-align:right; font-size:85%\"><strong>Correct?</strong></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Option Text</td>\n<td><textarea id=\"option_text" . $option_no . "\" name=\"option_text" . $option_no . "\" rows=\"1\" style=\"width:700px\">" . $option_text . "</textarea><textarea style=\"display:none\" name=\"old_option_text" . $option_no . "\" >" . $option_text . "</textarea>\n<input type=\"checkbox\" name=\"checkbox" . $option_no . "\" id=\"checkbox" . $option_no . "\" value=\"y\"";
      if ($correct == 'y') {
        echo ' checked /><input type="hidden" name="old_checkbox' . $option_no . '" value="y" />';
      } else {
        echo ' /><input type="hidden" name="old_checkbox' . $option_no . '" value="n" />';
      }
      echo "</td>\n</tr>\n";
      if ($o_media != '') {
        echo "<tr><td  class=\"field\">Current Media</td><td>" . display_media($o_media,$o_media_width,$o_media_height,$option_no) . "</td></tr>\n";
      }
      echo "<tr><td><input type=\"hidden\" id=\"old_option_media" . $option_no . "\" name=\"old_option_media" . $option_no . "\" value=\"$o_media\" /><input type=\"hidden\" name=\"old_option_media_width" . $option_no . "\" value=\"$o_media_width\" /><input type=\"hidden\" name=\"old_option_media_height" . $option_no . "\" value=\"" . $o_media_height . "\" /></td>\n";
      echo "<tr>\n<td class=\"field\">Change Media</td><td><input type=\"file\" size=\"65\" id=\"new_option_media" . $option_no . "\" name=\"new_option_media" . $option_no . "\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Feedback if Correct<br /><span style=\"font-weight:normal; font-size:90%; color:red\">(default feedback)</span></td><td><textarea name=\"option_right_fback" . $option_no . "\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">$feedback_right</textarea><textarea style=\"display:none\" name=\"old_option_right_fback" . $option_no . "\" >" . $feedback_right . "</textarea></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Feedback if Incorrect<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(leave blank to use default)</span></td><td><textarea name=\"option_wrong_fback" . $option_no . "\" style=\"width:700px\" cols=\"100\" rows=\"2\" wrap=\"virtual\">$feedback_wrong</textarea><textarea style=\"display:none\" name=\"old_option_wrong_fback" . $option_no . "\" >" . $feedback_wrong . "</textarea></td>\n</tr>\n";
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

      $option_no++;
    }

    for ($i=$option_no; $i<=20; $i++) {
      $hidden = 'style="display:none"';
      echo "<tr class=\"option\" $hidden>\n<td colspan=\"2\" class=\"section\">Option " . $i . ".<input type=\"hidden\" name=\"optionid" . $i . "\" value=\"\"></td>\n</tr>\n";
      echo "<tr class=\"option\" $hidden>\n<td colspan=\"2\" style=\"text-align:right; font-size:85%\"><strong>Correct?</strong></td>\n</tr>\n";
      echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Option Text</td>\n<td><textarea id=\"option_text" . $i . "\" name=\"option_text" . $i . "\" style=\"width:700px\" rows=\"1\"></textarea><textarea style=\"display:none\" name=\"old_option_text" . $i . "\"></textarea>\n<input type=\"checkbox\" name=\"checkbox$i\" id=\"checkbox$i\" value=\"y\" /><input type=\"hidden\" name=\"old_checkbox$i\" value=\"n\" /></td>\n</tr>\n";
      echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Media</td><td><input type=\"file\" size=\"65\" id=\"new_option_media" . $i . "\" name=\"new_option_media" . $i . "\" /><input type=\"hidden\" id=\"old_option_media" . $i . "\" name=\"old_option_media" . $i . "\" vlaue=\"\" /></td>\n</tr>\n";
      echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Feedback if Correct<br /><span style=\"font-weight:normal; font-size:90%; color:red\">(default feedback)</span></td><td><textarea name=\"option_right_fback" . $i . "\" cols=\"100\" style=\"width:700px\" rows=\"2\"></textarea><input type=\"hidden\" name=\"old_option_right_fback$i\" value=\"\" /></td>\n</tr>\n";
      echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Feedback if Incorrect<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(leave blank to use default)</span></td><td><textarea name=\"option_wrong_fback" . $i . "\" cols=\"100\" style=\"width:700px\" rows=\"2\"></textarea><input type=\"hidden\" name=\"old_option_wrong_fback$i\" value=\"\" /></td>\n</tr>\n";
      echo "<tr class=\"option\" $hidden><td colspan=\"2\">&nbsp;</td></tr>\n";
    }
	?>
	<tr>
	<td>&nbsp;</td>
	<td colspan="2"><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(7)"/></td>
	</tr>
	<?php
    echo echoMetadata($bloom, $q_id, $group, 2, $mysqli, true, $status, $disabled);
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
  $mysqli->close();
}
?>
</form>
</body>
</html>
