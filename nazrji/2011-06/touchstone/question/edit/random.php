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

if (isset($_POST['submit']) and $_POST['submit'] == 'Save Changes') {
  if (check_fullSave($q_id,$mysqli)) {
    $changes = false;
    $part_names = array('description','bloom');
    foreach ($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
    $part_names = array('old_description','old_bloom');
    foreach ($part_names as $section_name) {
      $$section_name = $_POST["$section_name"];
    }
  
    $part_names = array('description','bloom');
    foreach($part_names as $section_name) {
      $old_section_name = 'old_' . $section_name;
      record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
    }
   
    saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
    $question_teams = getTeams();
    record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);
  
    // Update the options (random questions).
    $existing = explode(',',$_POST['old_questions']);
    $current = array();
    foreach ($existing as $q) {
      $current[$q] = 1;
    }
    for ($i=0; $i<$_POST['question_no']; $i++) {
      if (isset($_POST["question_text$i"]) and $_POST["question_text$i"] != '') {
        if (!isset($current[$_POST["question_id$i"]])) {  // New question
          insert_into_options($q_id,$_POST["question_id$i"],'','','','','','','');

          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,'',?,NOW(),'Add Question')");
          $result->bind_param('is', $q_id, $_POST["question_id$i"]);
          $result->execute();  
          $result->close();
          $changes = true;
        }
      } else {
        if (isset($_POST["question_id$i"]) and isset($current[$_POST["question_id$i"]])) {  // Delete existing question
          $result = $mysqli->prepare("DELETE FROM options WHERE option_text=? AND o_id=?");
          $result->bind_param('si', $_POST["question_id$i"], $q_id);
          $result->execute();  
          $result->close();

          $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Edit Question',?,$userID,?,'',NOW(),'Delete Question')");
          $result->bind_param('is', $q_id, $_POST["question_id$i"]);
          $result->execute();  
          $result->close();
          $changes = true;
        }
      }
    }
  
    if ($changes == true) {
      $bloom = (empty($bloom)) ? NULL : $bloom;
    	$result = $mysqli->prepare("UPDATE questions SET leadin=?, bloom=?, q_group=?, last_edited=NOW() WHERE q_id=?");
      $result->bind_param('sssi', $description, $bloom, $question_teams,$q_id);
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
<title>Edit Random Question</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
<script language="JavaScript">
  function addQuestion() {
    winH = screen.height - 80
    winW = screen.width - 80
    notice=window.open("../add/add_random_questions_frame.php?q_no=" + document.getElementById('question_no').value + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
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
   <form name="edit_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $q_id; ?>" enctype="multipart/form-data">

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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Random Question Block)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
  <?php
    $sub_questions = '';
    $result = $mysqli->prepare("SELECT leadin, ownerID, creation_date, DATE_FORMAT(creation_date, '%d/%m/%Y') AS creation_display, last_edited, DATE_FORMAT(last_edited, '%d/%m/%Y') AS edited_display, option_text, id_num, bloom, q_group, UNIX_TIMESTAMP(checkout_time) AS checkout_time, checkout_authorID, DATE_FORMAT(locked, '%d/%m/%Y') AS locked, status FROM questions LEFT JOIN options ON questions.q_id=options.o_id WHERE q_id=? ORDER BY q_id, id_num");
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($leadin, $tmp_ownerID, $creation_date, $created, $last_edited, $modified, $option_text, $id_num, $bloom, $q_group, $checkout_time, $checkout_authorID, $locked, $status);
    while ($row = $result->fetch()) {
      if ($sub_questions == '') {
        $sub_questions = $option_text;
      } else {
        $sub_questions .= ',' . $option_text;
      }
    }
    $result->close();
    
    echo displayEditTab($created, $modified, $locked);
    $disabled = check_edit_rights($q_id, $checkout_authorID, $checkout_time, $locked, $mysqli);
  ?>
  <br />
    <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td class="field">Description</td>
      <td><input type="text" name="description" size="60" value="<?php echo $leadin; ?>" /><input type="hidden" name="old_description" value="<?php echo $leadin; ?>" /></div></td>
    </tr>
    <tr>
      <td class="field">Questions</td>
      <td><div id="questionlist" style="width:600px; height:500px; background-color:white; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%">
      <?php
        $question_no = 0;
        if ($sub_questions != '') {
          $result = $mysqli->prepare("SELECT leadin, q_id FROM questions WHERE q_id IN ($sub_questions)");
          $result->execute();
          $result->store_result();
          $result->bind_result($leadin, $q_id);
          while ($row = $result->fetch()) {
            echo "<div style=\"background-color:highlight; color:white\" id=\"divquestion$question_no\"><input type=\"hidden\" name=\"question_id$question_no\" value=\"$q_id\" /><input type=\"checkbox\" onclick=\"toggle('divquestion$question_no'); updateList();\" id=\"question_text$question_no\" name=\"question_text$question_no\" value=\"$question_no\" checked>&nbsp;" . strip_tags($leadin) . "</div>";
            $question_no++;
          }
        }
      ?>      
      </div><input type="hidden" name="old_questions" value="<?php echo $sub_questions; ?>" /></td>
    </tr>
    <tr>
    <td></td><td><input type="button" name="addquestion" value="Add Question(s)" style="width:150px" onclick="addQuestion();" /><input type="hidden" name="question_no" id="question_no" value="<?php echo $question_no; ?>" /></td>
    </tr>
    <?php
      echo echoMetadata($bloom, $q_id, $q_group, 1, $mysqli, true, $status, $disabled);
    ?>
    <tr>
      <td colspan="3">&nbsp;<?php echo hidden_edit_fields(); ?></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center"><?php echo save_buttons($disabled, $locked, $userID, $checkout_author); ?></td>
    </tr>
  </table>
</div>

<?php
  require '../../include/changes_tab.inc';
  require '../../include/comments_tab.inc';
  displayMappingTab($_GET['paperID'],$mysqli, $created, $modified);
}
$mysqli->close();
?>
</form>
</body>
</html>
