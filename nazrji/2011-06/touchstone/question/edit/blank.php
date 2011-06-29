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
    $part_names = array('theme','leadin','stem','notes','bloom','correct_fback','score_method','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    $part_names = array('old_theme','old_leadin','old_stem','old_notes','old_bloom','old_correct_fback','old_score_method','old_status');
    foreach($part_names as $section_name) {
      $$section_name = html_entity_decode($_POST["$section_name"]);
    }

    // Strip MS Office HTML.
    $leadin = clearMSOtags($leadin);
    $stem = clearMSOtags($stem);
	
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
  
    // Track changes
    $changes = false;
    $part_names = array('theme','leadin','stem','notes','q_media','bloom','correct_fback','score_method','status');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    save_external_responses($mysqli);

    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, leadin=?, score_method=?, notes=?, correct_fback=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, scenario_plain=?, leadin_plain=?, last_edited=NOW(), status=? WHERE q_id=?");
      $correct_fback = nl2br($correct_fback);
      $scenario = '';
      $leadin = trim(strip_tags($leadin));
      $result->bind_param('sssssssssssssi', $theme, $leadin, $score_method, $notes, $correct_fback, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario, $leadin, $status, $_GET['q_id']);
      $result->execute();  
      $result->close();

      $result = $mysqli->prepare("UPDATE options SET option_text=? WHERE o_id=?");
      $result->bind_param('si', $stem, $_GET['q_id']);
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

$result = $mysqli->prepare("SELECT q_id, q_type, theme, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
$result->bind_param('i', $q_id);
$result->execute();
$result->store_result();
$result->bind_result($q_id, $q_type, $theme, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
while ($row = $result->fetch()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Fill-in-the-Blank Question<?php echo " $cfg_install_type"; ?></title>
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
        echo "\t tinyMCE.triggerSave();\n";
      }
      ?>
    
      if (document.getElementById('stem').value == "" || document.getElementById('stem').value == "&nbsp;" || document.getElementById('stem').value == "<p>&nbsp;</p>" || document.getElementById('stem').value == "<div>&nbsp;</div>" || document.getElementById('stem').value == "<br />") {
        alert ("Please enter a question.");
        return false;
      }
      if (document.getElementById('leadin').value == "" || document.getElementById('stem').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
        alert ("Please enter a Lead-in for the question.");
        return false;
      }
  }
  
  function alterInstructions() {
    if (document.edit_form.score_method.options[document.edit_form.score_method.selectedIndex].value == 'textboxes') {
      document.getElementById('instructions1').style.display = 'none';
      document.getElementById('instructions2').style.display = 'block';
    } else {
      document.getElementById('instructions2').style.display = 'none';
      document.getElementById('instructions1').style.display = 'block';
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

<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id']; ?>" enctype="multipart/form-data">
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Fill-in-the-Blank)</span></td>
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
    <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td class="field">Theme/Heading&nbsp;</td>
      <td><input type="text" name="theme" value="<?php echo $theme; ?>" size="80" /><input type="hidden" name="old_theme" value="<?php echo htmlentities($theme,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"><?php echo $notes; ?></textarea><input type="hidden" name="old_notes" value="<?php echo htmlentities($notes,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
      <?php
         if ($q_media != '') {
           echo "<tr><td class=\"field\">Current Media</td><td>" . display_media($q_media,$q_media_width,$q_media_height,0) . "</td></tr>\n";
         }
       ?>
    
    <tr>
      <td class="field">Change Media</td>
      <td><input type="file" size="70" name="q_media" /><input type="hidden" name="old_q_media" value="<?php echo $q_media; ?>" /><input type="hidden" name="old_q_media_width" value="<?php echo $q_media_width; ?>" /><input type="hidden" name="old_q_media_height" value="<?php echo $q_media_height; ?>" /></td>
</tr>
<tr>
<td class="field"><span class="mandatory">*</span>&nbsp;Lead-in</td>
      <td><textarea name="leadin" id="leadin" cols="100" style="width:700px" rows="2" wrap="virtual"><?php echo $leadin; ?></textarea><input type="hidden" name="old_leadin" value="<?php echo htmlentities($leadin,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <tr>
      <td class="field">Display Mode<input type="hidden" name="old_score_method" value="<?php echo $score_method; ?>" /></td><td><select name="score_method" onchange="alterInstructions()">
      <?php
        if ($score_method == 'textboxes') {
          echo "<option value=\"dropdown\">Dropdown Lists (randomised)</option><option value=\"textboxes\" selected>Blank Textboxes</option></select></td>\n";
        } else {
          echo "<option value=\"dropdown\" selected>Dropdown Lists (randomised)</option><option value=\"textboxes\">Blank Textboxes</option></select></td>\n";
        }
      ?>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>
      <?php
      if ($score_method == 'textboxes') {
        echo '<span class="note" id="instructions1" style="display:none">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />Always put the correct answer as the <strong>first</strong> option, followed by the distractors (all options are randomised automatically).<br />e.g. Tyrannosaurus <span style="color:C00000">[blank]</span>Rex,Roger,Roderick,Ramsey<span style="color:C00000">[/blank]</span> was a large bipedal flesh-eating...</span>';
        echo '<span class="note" id="instructions2">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />Within the [blank] tags add the correct answer and any alternatives also deemed to be correct (separate with commas).<br />e.g. What country are we in <span style="color:C00000">[blank]</span>UK,United Kingdom,Britain,Great Britain,GB<span style="color:C00000">[/blank]</span>?</span>';
      } else {
        echo '<span class="note" id="instructions1">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />Always put the correct answer as the <strong>first</strong> option, followed by the distractors (all options are randomised automatically).<br />e.g. Tyrannosaurus <span style="color:C00000">[blank]</span>Rex,Roger,Roderick,Ramsey<span style="color:C00000">[/blank]</span> was a large bipedal flesh-eating...</span>';
        echo '<span class="note" id="instructions2" style="display:none">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />Within the [blank] tags add the correct answer and any alternatives also deemed to be correct (separate with commas).<br />e.g. What country are we in <span style="color:C00000">[blank]</span>UK,United Kingdom,Britain,Great Britain,GB<span style="color:C00000">[/blank]</span>?</span>';
      }
      ?>
      </td>
    </tr>
    <tr>
      <td valign="top" align="right" class="field"><span class="mandatory">*</span>&nbsp;Question</td>
      <td><?php echo wysiwyg_editor('oEdit1','stem',$option_text,700,250); ?>
      <textarea style="display:none" name="old_stem" id="old_stem" cols="1" rows="1"><?php echo encodeHTML($option_text); ?></textarea>
      </td>
    </tr>
    <tr>
      <td valign="top" align="right" class="field">Feedback</td>
      <?php
        $tmp_correct_fback = str_replace('<br />','',$correct_fback);
        $tmp_correct_fback = htmlentities($tmp_correct_fback,ENT_NOQUOTES,'UTF-8');
      ?>
      <td><textarea name="correct_fback" cols="100" style="width:700px" rows="6" wrap="virtual"><?php echo $tmp_correct_fback; ?></textarea><input type="hidden" name="old_correct_fback" value="<?php echo $tmp_correct_fback; ?>" /></td>
    </tr>
    <?php 
      echo $q_group;
      echo echoMetadata($bloom, $q_id, $q_group, 1, $mysqli, true, $status, $disabled);
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
  }
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
  $mysqli->close();
}
?>
</form>
</body>
</html>
