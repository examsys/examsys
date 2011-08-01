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

function responseNo($responseID, $tmp_correct) {
  $html = "<select name=\"response_no" . $responseID . "\">";
  for ($i=0; $i<=40; $i++) {
    if ($i == $tmp_correct) {
      $html .= "<option value=\"$i\" selected>$i</option>\n";
    } else {
      $html .= "<option value=\"$i\">$i</option>\n";
    }
  }
  $html .= "</select>\n";
  
  return $html;
}

if (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
  if (check_fullSave($q_id,$mysqli)) {
    // Write out curriculum mapping.
    saveObjMappings($_POST['paperID'],$q_id,$mysqli);

    $changes = false;
    $option_changes = false;
    
    // Work out the highest number of experts
    $max_experts = 1;
    for ($option_no=1; $option_no<=5; $option_no++) {
      if ($_POST['response_no' . $option_no] > $max_experts and $_POST["option_text$option_no"] != '') {
        $max_experts = $_POST['response_no' . $option_no];
      }
      if ($_POST['response_no' . $option_no] != $_POST['old_response_no' . $option_no] or $_POST['feedback_right' . $option_no] != $_POST['old_feedback_right' . $option_no]) {
        $changes = true;
        $option_changes = true;
      }
    }
    
    if ($_POST['scttype'] != $_POST['old_scttype']) {
      $changes = true;
      $option_changes = true;
    }
    
    if ($_POST['leadin1'] != $_POST['old_leadin1']) {   // Hypothesis
      record_trackChanges('Edit Question', $q_id, $_POST['old_leadin1'], $_POST['leadin1'], 'hypothesis', $userID, $changes);
    }
    if ($_POST['leadin2'] != $_POST['old_leadin2']) {   // New information
      record_trackChanges('Edit Question', $q_id, $_POST['old_leadin2'], $_POST['leadin2'], 'new information', $userID, $changes);
    }
  
    $leadin = clearMSOtags($_POST['leadin1']) . '~' . clearMSOtags($_POST['leadin2']);
    $scenario =  $_POST['scenario'];
    $part_names = array('theme','notes','bloom','scttype','status','correct_fback');
    foreach($part_names as $section_name) {
      if(isset($_POST["$section_name"])) {
        $$section_name = $_POST["$section_name"];
      } else {
        $$section_name ='';
      }
    }
    if (trim(strip_tags($scenario)) == '') $scenario = '';
    $part_names = array('old_theme','old_scenario','old_notes','old_bloom','old_scttype','old_status','old_correct_fback');
    foreach($part_names as $section_name) {
      if(isset($_POST["$section_name"])) {
        $$section_name = $_POST["$section_name"];
      } else {
        $$section_name ='';
      }
    }
    
    // Strip MS Office HTML.
    $scenario = clearMSOtags($scenario);

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

    if ($tmp_media_width == '') {
      $tmp_media_width = 0;
      $tmp_media_height = 0;
    }

    $part_names = array('theme','scenario','notes','bloom','q_media','scttype','status','correct_fback');
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
    	$result = $mysqli->prepare("UPDATE questions SET theme=?, scenario=?, leadin=?, score_method=?, notes=?, q_media=?, q_media_width=?, q_media_height=?, bloom=?, q_group=?, last_edited=NOW(), scenario_plain=?, leadin_plain=?, status=?, q_option_order=?, correct_fback=? WHERE q_id=?");
      $scenario_stripped = trim(strip_tags($scenario));
      $leadin_stripped = trim(strip_tags($leadin));
      $result->bind_param('sssssssssssssssi', $theme, $scenario, $leadin, $scttype, $notes, $unique_name, $tmp_media_width, $tmp_media_height, $bloom, $question_teams, $scenario_stripped, $leadin_stripped, $status, $option_order, $correct_fback, $q_id);
      $result->execute();  
      $result->close();
    }
    
    if ($option_changes == true) {
      for ($option_no=1; $option_no<=5; $option_no++) {
        $result = $mysqli->prepare("UPDATE options SET option_text=?, feedback_right=?, correct=?, marks=? WHERE id_num=?");
        $tmp_marks = $_POST['response_no' . $option_no] / $max_experts;
        $result->bind_param('sssdi', $_POST['option_text' . $option_no], $_POST['feedback_right' . $option_no], $_POST['response_no' . $option_no], $tmp_marks, $_POST['optionid' . $option_no]);
        $result->execute();  
        $result->close();
      }
    }
    
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
  <title>Edit Script Concordance Question<?php echo " $cfg_install_type"; ?></title>
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
        echo "\t tinyMCE.triggerSave();\n";
      }
      ?>
      if (document.getElementById('scenario').value == "" || document.getElementById('scenario').value == "&nbsp;" || document.getElementById('scenario').value == "<p>&nbsp;</p>" || document.getElementById('scenario').value == "<div>&nbsp;</div>" || document.getElementById('scenario').value == "<br />") {
        alert ("Please enter a Clinical Vignette for this question.");
        return false;
      }
      if (submit != '') {
        var modules = document.getElementById('modules').value;
        var modulesArray = modules.split(',');
        for (var j = 0; j < modulesArray.length; j++) {
          var objcount = document.getElementById(modulesArray[j] + '_objectiveCount').value;
          for (var i = 0; i < objcount; i++) {
            var cb = document.getElementById(modulesArray[j] + 'obj' + i).checked;
            if (cb == true) {
              submit = '';
              return confirm("WARNING: All mappings will be lost if this question is not added to the paper !");
            }
          }
        }
        submit = '';
      }
      return true;
    }

    var submit = '';
    function AddToBank() {
      submit = 'AddToBank';
    }

    function selectType() {
      typewin = window.open("../add/sct_type.php","type","width=800,height=600,scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no");
      typewin.moveTo(10,10);
      if (window.focus) {
        typewin.focus();
      }
      return false;
    }
    
    function changeType() {
      if (document.getElementById('scttype').options[document.getElementById('scttype').selectedIndex].value == "1") {
        document.getElementById('scttitle').innerHTML = 'Hypothesis';
        document.getElementById('option_text1').value = 'very unlikely';
        document.getElementById('option_text2').value = 'unlikely';
        document.getElementById('option_text3').value = 'neither likely nor unlikely';
        document.getElementById('option_text4').value = 'more likely';
        document.getElementById('option_text5').value = 'very likely';
      } else if (document.getElementById('scttype').options[document.getElementById('scttype').selectedIndex].value == "2") {
        document.getElementById('scttitle').innerHTML = 'Investigation';
        document.getElementById('option_text1').value = 'useless';
        document.getElementById('option_text2').value = 'less useful';
        document.getElementById('option_text3').value = 'neither more or less useful';
        document.getElementById('option_text4').value = 'more useful';
        document.getElementById('option_text5').value = 'very useful';
      } else if (document.getElementById('scttype').options[document.getElementById('scttype').selectedIndex].value == "3") {
        document.getElementById('scttitle').innerHTML = 'Prescription';
        document.getElementById('option_text1').value = 'contra-indicated totally or almost totally';
        document.getElementById('option_text2').value = 'not useful or even detrimental';
        document.getElementById('option_text3').value = 'nor less nor more useful';
        document.getElementById('option_text4').value = 'useful';
        document.getElementById('option_text5').value = 'absolutely necessary';
      } else if (document.getElementById('scttype').options[document.getElementById('scttype').selectedIndex].value == "4") {
        document.getElementById('scttitle').innerHTML = 'Intervention';
        document.getElementById('option_text1').value = 'contraindicated';
        document.getElementById('option_text2').value = 'less indicated';
        document.getElementById('option_text3').value = 'neither more or less indicated';
        document.getElementById('option_text4').value = 'indicated';
        document.getElementById('option_text5').value = 'strongly indicated';
      } else {
        document.getElementById('scttitle').innerHTML = 'Treatment';
        document.getElementById('option_text1').value = 'contraindicated';
        document.getElementById('option_text2').value = 'less indicated';
        document.getElementById('option_text3').value = 'neither more or less indicated';
        document.getElementById('option_text4').value = 'indicated';
        document.getElementById('option_text5').value = 'strongly indicated';
      }
    }
    
  </script>
  <script language="JavaScript" src="../../javascript/edit_tabs.js"></script>
  <script language="JavaScript" src="../../javascript/edit/mapping_tab.js"></script>
  <script language="JavaScript" src="../../javascript/metadata.js"></script>
  <?php echo $cfg_editor_javascript; ?>
  <script language="JavaScript" src="../../javascript/staff_help.js"></script>
  </head>

  <body>
<?php
  $option_no = 1;
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, scenario, scenario_plain, leadin, leadin_plain, correct_fback, score_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, option_text, feedback_right, correct, id_num, marks, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status, q_option_order FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $scenario_plain, $leadin, $leadin_plain, $correct_fback, $score_method, $notes, $tmp_ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $created, $last_edited, $modified, $option_text, $feedback_right, $correct, $id_num, $marks, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status, $q_option_order);
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
       <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Script Concordance)</span>
      </td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
    </tr>
  </table>
    <?php
      echo displayEditTab($created, $modified, $locked);
      $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);

      echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
      echo "<tr>\n<td style=\"text-align:center\">\n";
      echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" align=\"center\">\n";
      echo "<tr>\n<td colspan=\"3\" class=\"section\">General Information</td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Theme/Heading</td>\n<td colspan=\"6\"><textarea name=\"theme\" cols=\"100\" style=\"width:700px\" >$theme</textarea><textarea style=\"display:none\" name=\"old_theme\"/>$theme</textarea><input type=\"hidden\" name=\"checkout_author\" value=\"$checkout_authorID\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Notes<br /><span class=\"note\">(visible to students)</span></td><td colspan=\"6\"><textarea name=\"notes\" cols=\"100\" style=\"width:700px\" rows=\"2\" wrap=\"virtual\">" . $notes . "</textarea><textarea style=\"display:none\" name=\"old_notes\" />$notes</textarea></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\"><span class=\"mandatory\">*</span>&nbsp;Clinical Vignette</td><td colspan=\"2\"><textarea style=\"display:none\" name=\"old_scenario\" id=\"old_scenario\">" . htmlentities($scenario) . "</textarea>";
      echo wysiwyg_editor('oEdit1','scenario',$scenario,740);
      echo "</td></tr>\n";
      if ($q_media != '') {
        echo "<tr>\n<td class=\"field\">Current Media</td><td colspan=\"2\">" . display_media($q_media,$q_media_width,$q_media_height,'0');
      }
      echo "<input type=\"hidden\" name=\"old_q_media\" value=\"$q_media\" /><input type=\"hidden\" name=\"old_q_media_width\" value=\"$q_media_width\" /><input type=\"hidden\" name=\"old_q_media_height\" value=\"$q_media_height\" /></td>\n</tr>\n";
      echo "<tr>\n<td class=\"field\">Change Media</td><td colspan=\"2\"><input type=\"file\" name=\"q_media\" size=\"65\" /></td>\n</tr>\n";
      
      $tmp_parts = explode('~',$leadin);
      echo "<tr>\n<td class=\"field\">Hypothesis</td><td>" . wysiwyg_editor('oEdit2','leadin1',$tmp_parts[0],740) . "<textarea style=\"display:none;\" name=\"old_leadin1\" >" . htmlentities($tmp_parts[0]) . "</textarea></td></tr>\n";
      echo "<tr>\n<td class=\"field\">New Information</td><td>" . wysiwyg_editor('oEdit3','leadin2',$tmp_parts[1],740) . "<textarea style=\"display:none;\" name=\"old_leadin2\" >" . htmlentities($tmp_parts[1]) . "</textarea></td></tr>\n";
      
      echo "<tr>\n<td colspan=\"3\">&nbsp;</td>\n</tr>\n";
      echo "<tr>\n<td colspan=\"3\"><span class=\"section\">Options</span></td>\n</tr>\n";
      $types = array('1'=>'This hypothesis becomes','2'=>'This investigation becomes','3'=>'This prescription becomes','4'=>'This intervention becomes','5'=>'This treatment becomes');
      echo "<tr>\n<td colspan=\"2\">Type <select name=\"scttype\" id=\"scttype\" onchange=\"changeType()\">\n";
      foreach ($types as $type=>$description) {
        if ($type == $score_method) {
          echo "<option value=\"$type\" selected>$description</option>\n";
        } else {
          echo "<option value=\"$type\">$description</option>\n";
        }
      }
      echo "</select></td><td>Experts</td>\n</tr>\n";
    }
    echo "<tr class=\"option\">\n";
    echo "<td style=\"text-align:right\"><span class=\"mandatory\">*</span>&nbsp;<span class=\"field\">" . $option_no . ".&nbsp;</span></td>";
    echo "<td><input type=\"text\" name=\"option_text" . $option_no . "\" id=\"option_text" . $option_no . "\" size=\"90\" style=\"border:none; background-color:#D6DFF7; width:680px\" value=\"$option_text\" /><input type=\"hidden\" name=\"optionid$option_no\" value=\"$id_num\" /></td><td>" . responseNo($option_no, $correct) . "<input type=\"hidden\" name=\"old_response_no$option_no\" value=\"$correct\" /></td>";
    echo "</tr>\n";
    echo "<tr class=\"option\">\n<td>&nbsp;</td><td colspan=\"2\"><span class=\"field\">Feedback</span><textarea cols=\"95\" rows=\"2\" name=\"feedback_right" . $option_no . "\">$feedback_right</textarea><textarea cols=\"1\" rows=\"1\" style=\"display:none\" name=\"old_feedback_right" . $option_no . "\">$feedback_right</textarea></td></tr>\n";
    echo "<tr class=\"option\"><td colspan=\"3\">&nbsp;</td></tr>\n";
    $option_no++;
  }
  echo "<tr>\n<td class=\"field\">General<br />Feedback</span></td>\n<td colspan=\"2\"><textarea name=\"correct_fback\" cols=\"100\" style=\"width:700px\" rows=\"4\" wrap=\"virtual\">$correct_fback</textarea><textarea style=\"display:none\" name=\"old_correct_fback\" >" . $correct_fback . "</textarea></td>\n</tr>\n";
  echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";

  echo echoMetadata($bloom, $q_id, $q_group, 3, $mysqli, true, $status, $disabled);
  ?>
  <tr>
    <td colspan="3">&nbsp;<?php echo hidden_edit_fields(); ?><input type="hidden" name="old_scttype" value="<?php echo $score_method; ?>" /></td>
  </tr>
  <tr>
  <tr>
    <td colspan="3" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
  </tr>
  </table>
</td></tr>
</table>
</div>
    
<?php    
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'], $mysqli, $created, $modified);
}
$mysqli->close();
?>
</form>
</body>
</html>
