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

$q_id = $_GET['q_id'];

if (isset($_POST['Corrected']) and $_POST['Corrected'] == 'OK') {
  $points = $_POST['old_points'];

  // Record the change in 'track_changes'.
  $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change', ?, $userID, ?, ?, NOW(), 'Correct Answer')");
  $result->bind_param('iss', $q_id, $_POST['old_points'], $points);
  $result->execute();  
  $result->close();

  // Update log2 with new student marks.
  $student_records = explode(';',$_POST['q1']);
  foreach ($student_records as $student_record) {
    if (strlen($student_record) > 0) {
      $layers = explode('|',$student_record);
      $mark = 0;
      $layer_no = 0;
      foreach ($layers as $layer) {
        $sub_parts = explode(',',$layer);
        if ($layer_no == 0) {
          $database_id = $sub_parts[0];
          $mark += $sub_parts[1];
        } else {
          $mark += $sub_parts[0];
        }
        $layer_no++;
      }
      
      $first_comma = strpos($student_record, ',') + 1;
      $tmp_user_answer = substr($student_record,$first_comma);
      
      $result = $mysqli->prepare("UPDATE log2 SET mark=?, user_answer=? WHERE id=?");
      $result->bind_param('dsi', $mark, $tmp_user_answer, $database_id);
      $result->execute();  
      $result->close();
    }
  }

  // Save the new points into the questions table
  $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
  $result->bind_param('si', $points, $_GET['q_id']);
  $result->execute();  
  $result->close();

  redirect();
} elseif (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
?>
  <html>
  <head>
  <title>Image Hotspot Correction</title>
  <link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
  <script language="JavaScript" src="../../javascript/ie_fix.js"></script>
  <script language="JavaScript" src="../../javascript/flash_include.js"></script>
  </head>
  <body>
  <form action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id']; ?>" method="post" name="correctform">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="600">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">Corrected&nbsp;Answers</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Image Hotspot)</span>
    </td>
  </tr>
  <tr>
  <td colspan="2" style="background-color:#1E3C7B">&nbsp;</td>
  </tr>
</table>
<br />
<div align="center">
  <input type="hidden" name="paperID" value="<?php echo $_POST['paperID']; ?>" />
  <input type="hidden" name="module" value="<?php echo $_POST['module']; ?>" />
  <input type="hidden" name="calling" value="<?php echo $_POST['calling']; ?>" />
  <input type="hidden" name="folder" value="<?php echo $_POST['folder']; ?>" />
  <input type="hidden" name="scrOfY" value="<?php echo $_POST['scrOfY']; ?>" />
  <input type="hidden" name="points" value="<?php if (isset($points)) echo $points; ?>" />
  <input type="hidden" name="old_points" value="<?php echo $_POST['q1']; ?>" />
  <input type="hidden" name="correctedpoints" value="" />
  <input type="hidden" name="checkout_author" value="<?php if (isset($_POST['checkout_author'])) echo $_POST['checkout_author']; ?>" />
<?php
  // Query log2 table for existing student answers.
  $fix_data = '';
  $result = $mysqli->prepare("SELECT id, user_answer FROM log2 WHERE q_id=?");
  $result->bind_param('i', $q_id);
  $result->execute();  
  $result->bind_result($id, $user_answer);
  while ($row = $result->fetch()) {
    if ($user_answer != 'u') {
      $tmp_user_answer = '';
      $layers = explode('|',$user_answer);
      foreach ($layers as $layer) {
        $sub_parts = explode(',',$layer);
        if ($tmp_user_answer == '') {
          $tmp_user_answer = $sub_parts[1] . ',' . $sub_parts[2];
        } else {
          $tmp_user_answer .= '|' . $sub_parts[1] . ',' . $sub_parts[2];
        }
      }
      $fix_data .= ';' . $id . ',' . $tmp_user_answer;
    }
  }
  $result->close();
  $fix_data = substr($fix_data,1);
?>  
  <script language="JavaScript">
    function swfLoaded1(message) {
      var num = message.substring(5,message.length);
      setUpFlash(num, message, '<?php echo $_POST['tmp_media']; ?>', '<?php echo trim($_POST['q1']); ?>', '<?php echo $fix_data; ?>');
    }
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($_POST['tmp_media_width'] + 306); ?>" height="<?php echo ($_POST['tmp_media_height'] + 25); ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="always" />');
    write_string('<param name="movie" value="hotspot_correct.swf" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="#FFFFFF" />');
    write_string('<embed src="hotspot_correct.swf" quality="high" bgcolor="#FFFFFF" width="<?php echo ($_POST['tmp_media_width'] + 306); ?>" height="<?php echo ($_POST['tmp_media_height'] + 25); ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
  </script>

  <br />
  <input type="hidden" name="q1" id="q1" value="" />
  <br />
  <div>The green dots show students ansers which have now been marked as correct.<br />If you need to make further corrections please click 'OK' and then re-edit the question.</div>
  <br />
  <input type="submit" name="Corrected" value="OK" style="width:120px" />
  </div>
<?php
} elseif (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    $points = $_POST['q1'];
    
    $leadin = '';
    $layers = explode('|',$points);
    $i = 0;
    foreach ($layers as $layer) {
      $parts = explode('~',$layer);
      if ($leadin == '') {
        $leadin = chr(65 + $i) . ') ' . $parts[0];
      } else {
        $leadin .= ', ' . chr(65 + $i) . ') ' . $parts[0];
      }
      $i++;
    }
    $marks = $i;
    
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'], $q_id, $mysqli);

    $changes = false;
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','bloom','feedback','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_notes','old_bloom','old_feedback','old_points','old_status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
       
    $part_names = array('theme','scenario','notes','bloom','feedback','points','status');
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
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, notes=?, correct_fback=?, last_edited=NOW(), bloom=?, q_group=?, scenario_plain=?, leadin_plain=?, status=? WHERE q_id=?");
      $scenario_plain = trim(strip_tags($scenario));
      $leadin_plain = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssi', $theme, $scenario, $leadin, $notes, $feedback, $bloom, $question_teams, $scenario_plain, $leadin_plain, $status, $_GET['q_id']);
      $result->execute();  
      $result->close();

      $result = $mysqli->prepare("UPDATE options SET correct=?, marks=? WHERE o_id=?");
      $result->bind_param('sii', $points, $marks, $_GET['q_id']);
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

$result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY q_id, id_num");
$result->bind_param('i', $q_id);
$result->execute();
$result->store_result();
$result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
while ($row = $result->fetch()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Image Hotspot Question<?php echo " $cfg_install_type"; ?></title>
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
      echo "\t tinyMCE.triggerSave();";
    }
    ?>
  }
</script>
<script language="JavaScript" src="../../javascript/ie_fix.js"></script>
<script language="JavaScript" src="../../javascript/flash_include.js"></script>
<script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<?php
echo $cfg_editor_javascript;
$qNo_parm = (isset($_GET['qNo'])) ? '&qNo=' . $_GET['qNo'] : '';
?>
<script src="../../javascript/staff_help.js" type="text/javascript"></script>
</head>

<body style="background-color:white">
<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id'] . $qNo_parm; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%">
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Image Hotspot)</span>
    </td>
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
        <td><textarea name="theme" cols="100" style="width:700px" ><?php echo $theme; ?></textarea><textarea style="display:none" name="old_theme"/><?php echo $theme; ?></textarea><input type="hidden" name="checkout_author" value="<?php echo $checkout_authorID; ?>" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"><?php echo $notes; ?></textarea><textarea style="display:none" name="old_notes" /><?php echo $notes; ?></textarea></td>
    </tr>
    <tr>
    <td class="field">Scenario<br /><span class="note">(background info)</span></td>
        <td><textarea style="display:none" name="old_scenario" id="old_scenario"><?php echo htmlentities($scenario); ?></textarea>
        <?php echo wysiwyg_editor('oEdit1','scenario',$scenario);?>          
    </td>
    </tr>
    <tr>
    <td class="field"><span class="mandatory">*</span>&nbsp;Image</td>
    <td>

<?php
  $plugin_height = $q_media_height + 25;
  if ($plugin_height < 380) $plugin_height = 380;
?>
    <script language="JavaScript">
      function swfLoaded1(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $q_media; ?>', '<?php echo str_replace("'", "\'", trim($correct)); ?>');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($q_media_width + 306); ?>" height="<?php echo $plugin_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="../add/hotspot_add.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="#F1F5FB" />');
      write_string('<embed src="../add/hotspot_add.swf" quality="high" bgcolor="#F1F5FB" width="<?php echo ($q_media_width + 306); ?>" height="<?php echo $plugin_height; ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
    </script>
  </td>
</tr>
    <tr>
      <td valign="top" align="right" class="field">General Feedback</td>
      <td><textarea name="feedback" cols="100" style="width:700px" rows="4" wrap="virtual"><?php echo $correct_fback; ?></textarea><textarea style="display:none" name="old_feedback"><?php echo $correct_fback; ?></textarea></td>
    </tr>
    <?php
      echo echoMetadata($bloom, $q_id, $q_group, 1, $mysqli, true, $status, $disabled);
    ?>
    <tr>
      <td colspan="2"><input type="hidden" cols="80" rows="6" name="q1" id="q1" value="<?php echo $correct; ?>" /><input type="hidden" name="old_points" value="<?php echo $correct; ?>" /><input type="hidden" name="tmp_media" value="<?php echo $q_media; ?>" /><input type="hidden" name="tmp_media_width" value="<?php echo $q_media_width; ?>" /><input type="hidden" name="tmp_media_height" value="<?php echo $q_media_height; ?>" /><?php echo hidden_edit_fields(); ?></td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
    </tr>
  </table>
  </div>
<?php
  }
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  if (isset($_GET['paperID'])) {
    displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
  } else {
    displayMappingTab('', $mysqli, $created, $modified);
  }
  $result->free_result();
  $result->close();
}
$mysqli->close();
?>
</form>
</body>
</html>