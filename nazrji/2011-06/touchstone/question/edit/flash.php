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

if (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'],$q_id,$mysqli);

    $changes = false;
    $part_names = array('theme','leadin','notes','bloom','marks','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    $part_names = array('old_theme','old_leadin','old_notes','old_bloom','old_marks','old_status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $leadin = clearMSOtags($leadin);

    $paperID = $_POST['paperID'];
    // Upload Flash Question onto server
    if ($_FILES['new_question_swf']['name'] != $_POST['old_q_media'] and ($_FILES['new_question_swf']['name'] != 'none' and $_FILES['new_question_swf']['name'] != '')) {
      if ($_POST['old_q_media'] != '') {
        unlink("../media/" . $_POST['old_q_media']); 
      }
      $unique_question_name = uploadFile('new_question_swf', $tmp_q_width, $tmp_q_height);

      $changes = true;
      $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'Question SWF')");
      $result->bind_param('iss', $q_id, $_POST['old_q_media'], $unique_question_name);
      $result->execute();  
      $result->close();
    } else {
      // If the media has not changed set the variables back to the old media settings before the update query.
      $unique_question_name = $_POST['old_q_media'];
      $tmp_q_width = $_POST['old_q_media_width'];
      $tmp_q_height = $_POST['old_q_media_height'];
      if (isset($_POST['delete_media1']) AND $_POST['delete_media1'] == '1') {
        deleteMedia($_POST['old_q_media']);
        $unique_question_name = '';
        $tmp_q_width = 0;
        $tmp_q_height = 0;
        $changes = true;
      }
    }

    // Upload Flash Answer onto server
    if ($_FILES['new_answer_swf']['name'] != $_POST['old_o_media'] and ($_FILES['new_answer_swf']['name'] != 'none' and $_FILES['new_answer_swf']['name'] != '')) {
      if (isset($_POST['old_o_media']) and $_POST['old_o_media'] != '') {
        deleteMedia($_POST['old_o_media']);
      }
      $unique_answer_name = uploadFile('new_answer_swf', $tmp_o_width, $tmp_o_height);
      $changes = true;
      $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,?,NOW(),'Feedback SWF')");
      $result->bind_param('iss', $q_id, $_POST['old_o_media'], $unique_answer_name);
      $result->execute();  
      $result->close();
    } else {
      // If the media has not changed set the variables back to the old media settings before the update query.
      $unique_answer_name = $_POST['old_o_media'];
      $tmp_o_width = $_POST['old_o_media_width'];
      $tmp_o_height = $_POST['old_o_media_height'];
      if (isset($_POST['delete_media2']) AND $_POST['delete_media2'] == '1') {
        deleteMedia($_POST['old_o_media']);
        $unique_answer_name = '';
        $tmp_o_width = 0;
        $tmp_o_height = 0;
        $changes = true;
      }
    }

    $part_names = array('theme','leadin','notes','bloom','marks','status');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    save_external_responses($mysqli);

    if ($changes == true) {
      // Update Question data
      $bloom = (empty($bloom)) ? NULL : $bloom;
      $tmp_leadin = trim(strip_tags($leadin));
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, leadin=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), leadin_plain=?, status=? WHERE q_id=?");
      $result->bind_param('ssssssssssi', $theme, $leadin, $notes, $unique_question_name, $tmp_q_width, $tmp_q_height, $bloom, $question_teams, $tmp_leadin, $status, $_GET['q_id']);
      $result->execute();  
      $result->close();
  
      // Update Option data
      $result = $mysqli->prepare("UPDATE options SET o_media=?, o_media_width=?, o_media_height=?, marks=? WHERE o_id=?");
      $result->bind_param('sssii', $unique_answer_name, $tmp_o_width, $tmp_o_height, $marks, $_GET['q_id']);
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
<html>
<head>
<title>Edit Flash Question</title>
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
<script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<?php echo $cfg_editor_javascript; ?>
</head>

<body style="background-color:white">
<?php

  $question_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, leadin, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
  while ($row = $result->fetch()) {
    if ($question_no == 1) {
?>
  <form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . "?q_id=" . $q_id; ?>" enctype="multipart/form-data">
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
    <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Flash Interface)</span>
  </tr>
</table>
    <?php
      echo displayEditTab($created, $modified, $locked);
      $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);

      echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\">\n";
      echo "<tr><td class=\"field\">Theme/Heading</td><td colspan=\"3\"><input type=\"text\" name=\"theme\" size=\"75\" value=\"$theme\" /><input type=\"hidden\" name=\"old_theme\" value=\"" . htmlentities($theme,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
      echo "<tr><td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"3\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">$notes</textarea><input type=\"hidden\" name=\"old_notes\" value=\"" . htmlentities($notes,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
      echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(the question)</span></td>\n<td colspan=\"3\">\n<textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin) . "</textarea>";
      echo wysiwyg_editor('oEdit1','leadin',$leadin);
      echo "</td>\n</tr>";
      echo "<tr><td class=\"field\">Current Question SWF</td><td colspan=\"3\">" . display_media($q_media,$q_media_width,$q_media_height,1) . "</td></tr>\n";
      echo "<tr><td class=\"field\">Change Question SWF</td><td colspan=\"3\"><input type=\"file\" size=\"55\" name=\"new_question_swf\" /><input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td></tr>\n";
      if ($o_media != '') {
        echo "<tr><td class=\"field\">Current Answer SWF</td><td colspan=\"3\">" . display_media($o_media,$o_media_width,$o_media_height,2) . "</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">Current Answer SWF</td><td colspan=\"3\"><span style=\"color:#808080\">&lt;no media&gt;</span></td></tr>\n";
      }
      echo "<tr><td class=\"field\">Change Answer SWF</td><td colspan=\"3\"><input type=\"file\" size=\"55\" name=\"new_answer_swf\" /><input type=\"hidden\" name=\"old_o_media\" value=\"$o_media\" /><input type=\"hidden\" name=\"old_o_media_width\" value=\"$o_media_width\" /><input type=\"hidden\" name=\"old_o_media_height\" value=\"$o_media_height\" /></td></tr>\n";
      echo "<tr><td class=\"field\">Marks</td><td colspan=\"3\"><input type=\"hidden\" name=\"old_marks\" value=\"" . $marks . "\" /><select name=\"marks\">\n";
      for ($i=1; $i<=20; $i++) {
        if ($marks == $i) {
          echo "<option value=\"$i\" selected>$i</option>\n";
        } else {
          echo "<option value=\"$i\">$i</option>\n";
        }
      }
      echo "</select></td></tr>\n";

      echo echoMetadata($bloom, $q_id, $q_group, 1, $mysqli, true, $status, $disabled);
    }
  }
?>

<tr><td colspan="5">&nbsp;<?php echo hidden_edit_fields(); ?></td></tr>
<tr>
  <td colspan="5" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
</tr>
</table>
</div>
<?php
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
  $result->free_result();
  $result->close();
?>
</form>
</body>
</html>
<?php
}
$mysqli->close();
?>