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
    $leadin = $_POST['leadin'];
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','correct_fback','columns','rows','notes','editor','bloom','terms','marks','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_correct_fback','old_columns','old_rows','old_notes','old_editor','old_bloom','old_terms','old_marks','old_status');
    foreach($part_names as $section_name) {
      $$section_name = html_entity_decode($_POST["$section_name"]);
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
        deleteMedia('../media/' . $_POST['old_q_media']);
        $unique_name = '';
        $tmp_media_width = 0;
        $tmp_media_height = 0;
        $changes = true;
      }
    }
    $old_q_media = $_POST['old_q_media'];
    $q_media = $unique_name;

    // Update Option data
    if ($terms != $old_terms) {
      $changes = true;
      $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
      $result->bind_param('si', $terms, $_GET['q_id']);
      $result->execute();  
      $result->close();
    }

    if ($editor != $old_editor) {
      $changes = true;
      $result = $mysqli->prepare("UPDATE options SET option_text=? WHERE o_id=?");
      $result->bind_param('si', $editor, $_GET['q_id']);
      $result->execute();  
      $result->close();
    }

    $part_names = array('theme','scenario','leadin','correct_fback','q_media','notes','editor','bloom','rows','columns','terms','marks','status');
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
      $score_method = $columns . 'x' . $rows;
      $bloom = (empty($bloom)) ? NULL : $bloom;
      $result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, score_method=?, correct_fback=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=? WHERE q_id=?");
      $scenario_stripped = trim(strip_tags($scenario));
      $leadin_stripped = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssssssi', $theme, $scenario, $leadin, $score_method, $correct_fback, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario_stripped, $leadin_stripped, $status, $_GET['q_id']);
      $result->execute();  
      $result->close();

      if ($old_marks != $marks) {
        $result = $mysqli->prepare("UPDATE options SET marks=? WHERE o_id=?");
        $result->bind_param('di', $marks, $_GET['q_id']);
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Textbox Question<?php echo " $cfg_install_type"; ?></title>
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
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
</head>

<body style="background-color:white">
<?php

  $result = $mysqli->prepare("SELECT q_id, theme, scenario, scenario_plain, leadin, leadin_plain, score_method, option_text, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, correct, correct_fback, bloom, q_group, marks, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY q_id, id_num LIMIT 1");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $score_method, $option_text, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $correct, $correct_fback, $bloom, $q_group, $marks, $checkout_time, $checkout_authorID, $locked, $status);
  while ($row = $result->fetch()) {
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Textbox)</span></td>
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
  <table cellpadding="0" cellspacing="0" border="0" align="center">
    <tr>
    <td colspan="2" align="center">

    <?php
      echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";
      echo "<tr><td colspan=\"6\"><div class=\"section\">General Information</div></td></tr>\n";
      echo "<tr><td class=\"field\">Theme/Heading</td><td colspan=\"5\"><input type=\"text\" name=\"theme\" size=\"80\" value=\"$theme\" /><input type=\"hidden\" name=\"old_theme\" value=\"" . htmlentities($theme,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
      echo "<tr><td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"5\"><textarea name=\"notes\" cols=\"100\" rows=\"2\" style=\"width:700px\" wrap=\"virtual\">$notes</textarea><input type=\"hidden\" name=\"old_notes\" value=\"" . htmlentities($notes,ENT_NOQUOTES,'UTF-8') . "\" /></td></tr>\n";
      echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td colspan=\"5\">\n<textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario,ENT_NOQUOTES,'UTF-8') . "</textarea>";
      echo wysiwyg_editor('oEdit1','scenario',$scenario);
      echo "</td>\n</tr>\n";
      if ($q_media != '') {
        echo "<tr><td class=\"field\">Current Media</td><td colspan=\"5\">" . display_media($q_media,$q_media_width,$q_media_height,0) . "</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">Current Media</td><td colspan=\"5\"><span style=\"color:#808080\">&lt;no media&gt;</span></td></tr>\n";
      }
      echo "<tr><td class=\"field\">Change Media</td><td colspan=\"5\"><input type=\"file\" size=\"65\" name=\"q_media\" /><input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td></tr>\n";
      echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(the question)</span></td>\n<td colspan=\"5\"><textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin,ENT_NOQUOTES,'UTF-8') . "</textarea>";
      echo wysiwyg_editor('oEdit2','leadin',$leadin);
      echo "</td>\n</tr>";
      $dimensions = explode('x',$score_method);
      echo "<tr><td class=\"field\">Columns</td><td>\n<input type=\"hidden\" name=\"old_columns\" value=\"$dimensions[0]\" /><select name=\"columns\">";
      for ($col=10; $col<=120; $col+=10) {
        if ($dimensions[0] == $col) {
          echo "<option value=\"$col\" selected>$col cols</option>\n";
        } else {
          echo "<option value=\"$col\">$col cols</option>\n";
        }
      }
      echo "</select>\n</td>";
      echo "<td class=\"field\">Rows</td><td>\n<input type=\"hidden\" name=\"old_rows\" value=\"$dimensions[1]\" /><select name=\"rows\">\n";
      if ($dimensions[1] == 1) {
        echo "<option value=\"1\" selected>1 row</option>\n";
      } else {
        echo "<option value=\"1\">1 row</option>\n";
      }
      for ($row_no=2; $row_no<=15; $row_no++) {
        if ($dimensions[1] == $row_no) {
          echo "<option value=\"$row_no\" selected>$row_no rows</option>\n";
        } else {
          echo "<option value=\"$row_no\">$row_no rows</option>\n";
        }
      }
      echo "</select>\n</td>";
      echo "<td class=\"field\">Editor</td><td><select name=\"editor\">\n";
      if ($option_text == 'WYSIWYG') {
        echo "<option value=\"plain\">Plain Text</option>\n";
        echo "<option value=\"WYSIWYG\" selected>WYWIWYG</option>\n";
      } else {
        echo "<option value=\"plain\" selected>Plain Text</option>\n";
        echo "<option value=\"WYSIWYG\">WYWIWYG</option>\n";
      }
      echo "</select><input type=\"hidden\" name=\"old_editor\" value=\"$option_text\" /></tr>";
    ?>
    <tr><td colspan="6">&nbsp;</td></tr>
    <tr><td colspan="6"><div class="section">Assessment Data</div></td></tr>
    <tr>
      <td class="field">Marks</td>
      <td colspan="5"><input type="hidden" name="old_marks" value="<?php echo $marks; ?>" />
        <select name="marks">
        <option value="0"></option>
        <?php
          for ($i=1; $i<=20; $i++) {
            if ($marks == $i) {
              echo "<option value=\"$i\" selected>$i</option>\n";
            } else {
              echo "<option value=\"$i\">$i</option>\n";
            }
          }
        ?>
        </select>
      </td>
    </tr>
    <tr>
      <td class="field">Feedback<br /><span class="note">(model answer for assessments)</span></td>
      <td colspan="5"><textarea name="correct_fback" cols="100" rows="4" style="width:700px" wrap="virtual"><?php echo $correct_fback; ?></textarea><input type="hidden" name="old_correct_fback" value="<?php echo htmlentities($correct_fback,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <tr>
      <td class="field">Terms<br /><span class="note">(separate with semicolons)</span></td>
      <td colspan="5"><textarea name="terms" cols="100" rows="2" style="width:700px" wrap="virtual"><?php echo $correct; ?></textarea><input type="hidden" name="old_terms" value="<?php echo htmlentities($correct,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    <?php
      echo echoMetadata($bloom, $q_id, $q_group, 6, $mysqli, true, $status, $disabled);
    }
?>
<tr>
  <td colspan="6">&nbsp;<?php echo hidden_edit_fields(); ?></td>
</tr>
<tr>
  <td colspan="6" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
</tr>
</table>
</td></tr>
</table>
</div>
<?php
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'],$mysqli, $created, $modified);
  $mysqli->close();
?>
</form>
<?php
 }
?>
</body>
</html>
