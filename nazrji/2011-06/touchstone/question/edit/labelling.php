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

header('Content-Type: text/html; charset=utf-8');

$q_id = $_GET['q_id'];

if (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'],$q_id,$mysqli);

    $changes = false;
    $leadin = $_POST['leadin'];
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','q1','bloom','feedback','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_points','old_feedback','old_bloom','old_status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
    $leadin = clearMSOtags($leadin);

    // Correct label locations if too far over.
    $first_split = explode(';',$q1);
    $second_split = explode('$',$first_split[8]);
    $tmp_coords = '';
    $a = 0;
    $b = 0;
    foreach ($second_split as $stuff) {
      if ($a == 2 and $stuff < 150 and $b == 0) $stuff = 8;
      if ($a == 2 and $stuff < 150 and $b == 9) $stuff = 110;
      if ($tmp_coords == '') {
        $tmp_coords = $stuff;
      } else {
        $tmp_coords .= '$' . $stuff;
      }
      $a++;
      if ($a == 4) {
        $a = 0;
        $b++;
      }
    }
    $tmp_points = $first_split[0] . ';' . $first_split[1] . ';' . $first_split[2] . ';' . $first_split[3] . ';' . $first_split[4] . ';' . $first_split[5] . ';' . $first_split[6] . ';' . $first_split[7] . ';' . $tmp_coords;
    for ($i=9; $i<count($first_split); $i++) {
      $tmp_points .= ';' . $first_split[$i];
    }
    $points = $tmp_points;

    $part_names = array('theme','scenario','leadin','notes','points','bloom','feedback','status');
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

      if ($points != $old_points) {
        $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
        $result->bind_param('si', $points, $_GET['q_id']);
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

$result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
$result->bind_param('i', $q_id);
$result->execute();
$result->store_result();
$result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
while ($row = $result->fetch()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Edit Labelling Question<?php echo " $cfg_install_type"; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
<script language="JavaScript" src="../../javascript/flash_include.js"></script>
<script language="JavaScript" src="../../javascript/ie_fix.js"></script>
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
    
    if (edit_form.leadin.value == "") {
      alert ("Please enter a Leadin.");
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Labelling)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
    echo displayEditTab($created, $modified, $locked);
    $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);
    ?>
    <div align="center">
    <table cellpadding="3" cellspacing="0" border="0">
    <tr>
      <td class="field">Theme/Heading&nbsp;</td>
        <td><textarea name="theme" cols="100" style="width:700px" ><?php echo $theme; ?></textarea><textarea style="display:none" name="old_theme"/><?php echo $theme; ?></textarea><input type="hidden" name="checkout_author" value="<?php echo $checkout_authorID; ?>" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"><?php echo $notes; ?></textarea><textarea style="display:none" name="old_notes" /><?php echo $notes; ?></textarea></td>
    </tr>
    <tr>
    <td class="field"><span class="mandatory">*</span>&nbsp;Image</td>
    <td>
    <?php
      $tmp_height = $q_media_height;
      if ($tmp_height < 475) $tmp_height = 475;
    ?>
    <script language="JavaScript">
      function swfLoaded1(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $q_media; ?>','<?php echo trim(str_replace('"','&#034;',str_replace("'",'&#039;',str_replace('¬','&#172;',$correct)))); ?>');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($q_media_width + 220); ?>" height="<?php echo ($tmp_height + 25); ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="./label_edit.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="white" />');
      write_string('<embed src="./label_edit.swf" quality="high" bgcolor="white" width="<?php echo ($q_media_width + 220); ?>" height="<?php echo ($tmp_height + 25); ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
    </script>
</td>
</tr>
<tr>
<td class="field">Scenario<br /><span class="note">(background info)</span></td>
    <td><textarea style="display:none" name="old_scenario" id="old_scenario"><?php echo htmlentities($scenario) ?></textarea>
    <?php echo wysiwyg_editor('oEdit1','scenario',$scenario); ?>         
</td>
</tr>
<tr>
<td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
      <td><textarea style="display:none" name="old_leadin" id="old_leadin"><?php echo htmlentities($leadin); ?></textarea>
       <?php echo wysiwyg_editor('oEdit2','leadin',$leadin); ?>         
      </td>
    </tr>
    <tr>
      <td valign="top" align="right" class="field">Feedback</td>
      <td><textarea style="display:none" name="old_feedback"><?php echo $correct_fback; ?></textarea>
      <?php echo wysiwyg_editor('oEdit3','feedback',$correct_fback); ?> 
      </td>
    </tr>
    <?php
     echo echoMetadata($bloom, $q_id, $q_group, 1, $mysqli, true, $status, $disabled);
    ?>
    <tr>
      <td colspan="2"><input type="hidden" name="q1" id="q1" value="<?php echo $correct; ?>" /><input type="hidden" name="old_points" value="<?php echo $correct; ?>" /><?php echo hidden_edit_fields(); ?></td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
    </tr>
  </table>
  </div>
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
