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
    $changes = false;
    $leadin = $_POST['leadin'];
    $part_names = array('theme','leadin','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    $part_names = array('old_theme','old_leadin','old_status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $leadin = clearMSOtags($leadin);
    $old_leadin = clearMSOtags($old_leadin);

    // Upload Image (if exists) onto server
    if ($_FILES['q_media']['name'] != $_POST['old_q_media'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
      if (isset($_POST['delete_media1']) and $_POST['old_media'] != '') {
        deleteMedia($_POST['old_q_media']); 
      }
      $unique_name = uploadFile('q_media',$tmp_media_width,$tmp_media_height);
      $changes = true;
    } else {
      // If the media has not changed set the variables back to the old media settings before the update query.
      $unique_name = $_POST['old_q_media'];
      $tmp_media_width = $_POST['old_q_media_width'];
      $tmp_media_height = $_POST['old_q_media_height'];
      if (isset($_POST['delete_media1']) and $_POST['delete_media1'] == '1') {
        deleteMedia($_POST['old_q_media']);
        $unique_name = '';
        $tmp_media_width = 0;
        $tmp_media_height = 0;
        $changes = true;
      }
    }

    // Track changes
    $part_names = array('theme','leadin','notes','q_media','bloom','status');
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
  
    if ($changes == true) {
      $result = $mysqli->prepare("UPDATE questions SET theme=?, leadin=?, q_media=?, q_media_width=?, q_media_height=?, q_group=?, leadin_plain=?, last_edited=NOW(), status=? WHERE q_id=?");
      $leadin_stripped = trim(strip_tags($leadin));
      $result->bind_param('ssssssssi', $theme, $leadin, $unique_name, $tmp_media_width, $tmp_media_height, $question_teams, $leadin_stripped, $status, $_GET['q_id']);
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

$result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY q_id, id_num");
$result->bind_param('i', $q_id);
$result->execute();
$result->store_result();
$result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
while ($row = $result->fetch()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Information Block</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">

<script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
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
      alert ("Please enter some information text.");
      return false;
    }
    return true;
  }
</script>
</head>

<body style="background-color:white">

<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
  <?php
    echo "<span style=\"position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt\">Edit Information</span>\n";
  ?>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Information Block)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
    <?php
      echo displayEditTab($created, $modified, $locked);
      $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);
    ?>
    <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td class="field">Theme/Heading&nbsp;</td>
      <td><input type="text" name="theme" value="<?php echo $theme; ?>" size="80" /><input type="hidden" name="old_theme" value="<?php echo htmlentities($theme,ENT_NOQUOTES,'UTF-8'); ?>" /></td>
    </tr>
    <tr>
      <td class="field">Current Media<input type="hidden" name="old_q_media" value="<?php echo $q_media; ?>" /><input type="hidden" name="old_q_media_width" value="<?php echo $q_media_width; ?>" /><input type="hidden" name="old_q_media_height" value="<?php echo $q_media_height; ?>" /></td><td>
      <?php
         if ($q_media != '') {
           echo display_media($q_media,$q_media_width,$q_media_height,1);
         } else {
           echo "<span style=\"color:#808080\">&lt;no media&gt;</span></td>\n";
         }
      ?>
</tr>
<tr>
<td class="field">Change Media</td>
<td><input type="file" size="70" name="q_media" /></td>
</tr>
<tr>
<td valign="top" align="right" class="field">Text</td>
    <td><?php echo wysiwyg_editor('oEdit1','leadin',$leadin,700,250);?>
      <textarea name="old_leadin" style="display:none" id="old_leadin" cols="1" rows="1"><?php echo htmlentities($leadin,ENT_NOQUOTES,'UTF-8'); ?></textarea>
      </td>
    </tr>
    <?php
      echo echoMetadata('', $q_id, $q_group, 1, $mysqli, false, $status, $disabled);
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
$result->free_result();
$result->close();
$mysqli->close();
}
?>
</form>
</body>
</html>
