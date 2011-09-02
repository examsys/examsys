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
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','scale_type','status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_leadin','old_notes','old_scale_type','old_notapplicable','old_status');
    foreach($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }

    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);
    $leadin = clearMSOtags($leadin);

    // Upload Image (if exists) onto server
    if ($_FILES['q_media']['name'] != $_POST['old_q_media'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
      if ($_POST['old_q_media'] != '') {
        unlink("../media/" . $_POST['old_q_media']); 
      }
      $unique_name = uploadFile('q_media',$tmp_media_width,$tmp_media_height);
      $changes = true;
    } else {
      // If the media has not changed set the variables back to the old media settings before the update query.
      $unique_name = $_POST['old_q_media'];
      $tmp_media_width = $_POST['old_q_media_width'];
      $tmp_media_height = $_POST['old_q_media_height'];
      if (isset($_POST['delete_media1']) and $_POST['delete_media1'] == '1') {
        unlink('../../media/' . $_POST['old_q_media']);
        $unique_name = '';
        $tmp_media_width = '0';
        $tmp_media_height = '0';
        $changes = true;
      }
    }

    if (isset($_POST['notapplicable'])) {
      $notapplicable = 'true';
    } else {
      $notapplicable = 'false';
    }

    if ($scale_type == 'custom') {
      $scale_size = 0;
      for ($i=1; $i<=10; $i++) {
        if ($_POST["custom$i"] != '') $scale_size = $i;
      }
      $scale_type = '';
      for ($i=1; $i<=$scale_size; $i++) {
        if ($scale_type == '') {
          $scale_type = trim($_POST["custom$i"]);
        } else {
          $scale_type .= '|' . trim($_POST["custom$i"]);
        }
      }
    }
  
    $part_names = array('theme','scenario','leadin','notes','scale_type','notapplicable','status');
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
      $score_method = $scale_type . '|' . $notapplicable;
      $result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, score_method=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, q_group=?, scenario_plain=?, leadin_plain=?, last_edited=NOW(), status=? WHERE q_id=?");
      $scenario_stripped = trim(strip_tags($scenario));
      $leadin_stripped = trim(strip_tags($leadin));
      $result->bind_param('ssssssssssssi', $theme, $scenario, $leadin, $score_method, $_POST['notes'], $unique_name, $tmp_media_width, $tmp_media_height, $question_teams, $scenario_stripped, $leadin_stripped, $status, $q_id);
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
<title>Edit Likert Question<?php echo " $cfg_install_type"; ?></title>
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
      alert ("Please enter a Leadin.");
      return false;
    }
  }
  function checkCustom(clickedValue) {
    if (clickedValue.options[clickedValue.selectedIndex].value == 'custom') {
      document.getElementById('customtbl').style.display = 'block';
    } else {
      document.getElementById('customtbl').style.display = 'none';
    }
  }
</script>
<script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
</head>

<body style="background-color:white">
<?php
  $question_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, incorrect_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $incorrect_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
  while ($row = $result->fetch()) {
    if ($question_no == 1) {
      echo "<form name=\"add_form\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?q_id=$q_id\" enctype=\"multipart/form-data\">";
?>
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Likert Scale)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
    echo displayEditTab($created, $modified, $locked);
    $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);
      
    echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\">\n";
    echo "<tr><td colspan=\"2\" class=\"section\">Question Details</td></tr>\n";
    echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td colspan=\"6\"><textarea name=\"theme\" cols=\"100\" style=\"width:700px\" >$theme</textarea><textarea style=\"display:none\" name=\"old_theme\"/>$theme</textarea><input type=\"hidden\" name=\"checkout_author\" value=\"$checkout_authorID\" /></td>\n</tr>\n";
    echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"6\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">" . $notes . "</textarea><textarea style=\"display:none\" name=\"old_notes\" />$notes</textarea></td>\n</tr>\n";
    echo "<tr>\n<td class=\"field\">Scenario<br /><span class=\"note\">(background info)</span></td>\n<td><textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario) . "</textarea>";
    echo wysiwyg_editor('oEdit1','scenario',$scenario);
    echo "</td>\n</tr>\n";
    echo "<tr><td class=\"field\">Current Media</td><td>";
    if ($q_media == '') {
      echo "<span style=\"color:#808080\">&lt;no media&gt;</span>";
    } else {
      echo display_media($q_media,$q_media_width,$q_media_height,1);
    }
    echo "</td></tr>\n";
    echo "<tr><td class=\"field\">Change Media</td><td><input type=\"file\" size=\"65\" name=\"q_media\" /><input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td></tr>\n";
    echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Lead-in<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(the question)</span></td>\n<td><textarea style=\"display:none\" name=\"old_leadin\" id=\"old_leadin\">" . htmlentities($leadin) . "</textarea>";
    echo wysiwyg_editor('oEdit2','leadin',$leadin);
    echo "</td>\n</tr>";
    $current_scale = substr($score_method,0,strrpos($score_method,'|'));
    $scale_types = array('line','OSCE Stations Scales','0|1','0, 1','0|1|2','0, 1, 2','Fail|Borderline|Pass','Fail, Borderline, Pass','line','3 Point Scales','Low||High','Low to High','Never||Always','Never to Always','Disagree|Neutral|Agree','Disagree, Neutral, Agree','line','4 Point Scales','Low|||High','Low to High','Never|||Always','Never to Always','Strongly<br />Disagree|Disagree|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Agree, Strongly Agree','line','5 Point Scales','Low||||High','Low to High','Never||||Always','Never to Always','Strongly<br />Disagree|Disagree|Neither Disagree<br />nor Agree|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Neither Disagree nor Agree, Agree, Strongly Agree','Strongly<br />Disagree|Disagree|Uncertain|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Uncertain, Agree, Strongly Agree','Strongly<br />Disagree|Disagree|Neutral|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Neutral, Agree, Strongly Agree');
    echo "<tr><td class=\"field\">Scale</td><td><select name=\"scale_type\" onchange=\"javascript: checkCustom(this);\">";
    $scale_match = false;
    for ($i=0; $i<count($scale_types); $i+=2) {
      if ($scale_types[$i] == 'line') {
        if ($i>0) echo "</optgroup>\n";
        echo "<optgroup label=\"" . $scale_types[$i+1] . "\">\n";
      } else {
        if ($current_scale == $scale_types[$i]) {
          echo "<option value=\"" . $scale_types[$i] . "\" selected>" . $scale_types[$i+1] . "</option>\n";
          $scale_match = true;
        } else {
          echo "<option value=\"" . $scale_types[$i] . "\">" . $scale_types[$i+1] . "</option>\n";
        }
      }
    }
    echo "</optgroup>\n<optgroup label=\"Custom\">\n";
    if ($scale_match == true) {
      echo "<option value=\"custom\">Custom...</option>\n";
    } else {
      echo "<option value=\"custom\" selected>Custom...</option>\n";
      $score_parts = explode("|",$score_method);
    }
    echo "</select></optgroup><input type=\"hidden\" name=\"old_scale_type\" value=\"". $current_scale . "\" /></td></tr>\n";
    $na = substr($score_method,strrpos($score_method,'|')+1);
    echo "<tr><td class=\"field\">N/A Column</td><td><input type=\"hidden\" name=\"old_notapplicable\" value=\"$na\" />";
    if ($na == 'true') {
      echo "<input type=\"checkbox\" name=\"notapplicable\" checked /> include 'not applicable' option</td>\n</tr>\n";
    } else {
      echo "<input type=\"checkbox\" name=\"notapplicable\" /> include 'not applicable' option</td>\n</tr>\n";
    }
    if ($scale_match == true) {
      echo "<tr><td></td><td><table id=\"customtbl\" style=\"display: none\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";
    } else {
      echo "<tr><td></td><td><table id=\"customtbl\" style=\"display: block\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";
    }
    for ($i=1; $i<=10; $i++) {
      if ($scale_match == true or $i >= count($score_parts)) {
        echo "<tr><td class=\"field\">$i.</td><td><input type=\"textbox\" size=\"30\" name=\"custom$i\" /></td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">$i.</td><td><input type=\"textbox\" size=\"30\" name=\"custom$i\" value=\"" . $score_parts[$i-1] . "\"/></td></tr>\n";
      }
    }
    echo "</table>\n</td></tr>\n";
    echo echoMetadata('', $q_id, $q_group, 1, $mysqli, false, $status, $disabled);
    ?>
    <tr>
      <td colspan="2">&nbsp;<?php echo hidden_edit_fields(); ?></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
    </tr>
  </table>
</div>
<?php
    }
  }
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  $result->free_result();
  $result->close();
}
$mysqli->close();
?>
</form>
</body>
</html>
