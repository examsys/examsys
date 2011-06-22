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
require '../../include/add.inc';
require '../../include/metadata.inc';
require '../../include/mapping_tab.inc';

if (isset($_POST['addbank']) or isset($_POST['addpaper'])) {
  // Upload the Question SWF file.
  $unique_name = uploadFile('qfile', $tmp_width, $tmp_height);

  // Insert into Questions
  $question_id = insert_into_questions('flash', $_POST['theme'], $_POST['parameters'], $_POST['leadin'], '', '', '', $_POST['notes'], $userID, $unique_name, $tmp_width, $tmp_height, date("YmdHis"), date("YmdHis"), $_POST['bloom'], getTeams(), $_POST['status'], 'display order');
    
  // Upload the Feedback SWF file.
  $unique_name = uploadFile('ffile', $tmp_width, $tmp_height);
  
  // Insert into Options
  insert_into_options($question_id, '', $unique_name, $tmp_width, $tmp_height, '', '', '', $_POST['marks']);
  
  // Save keywords
  $changes = false;
  saveKeywords($question_id, $userID, $changes, false, $mysqli);

  $paperID = $_POST['paperID'];
  // Insert into Papers
  if (isset($_POST['addpaper'])) {
    insert_into_papers($paperID,$question_id);
    saveObjMappings($paperID,$question_id,$mysqli);
  }

  setcookie("default_team", getDefaultTeam(), time()+31536000);
  $mysqli->close();

  redirect($paperID);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>New Flash Question</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
<script language="JavaScript">
  function showTab(tabID) {
    if (tabID == 'editortab') {
      document.getElementById('editortab').style.display = 'block';
      document.getElementById('mappingtab').style.display = 'none';
    } else if (tabID == 'mappingtab') {
      document.getElementById('editortab').style.display = 'none';
      document.getElementById('mappingtab').style.display = 'block';
    } 
  }

  var cancel = 0;
  function formCancel() {
    cancel = 1;
  }

  function checkForm() {

    if (cancel != 0) {
      return true;
    }
    <?php
    if ($cfg_editor_name == 'tinymce') {
      echo "\t tinyMCE.triggerSave();";
    }
    ?>    
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

</script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
</head>

<body onLoad="document.add_form.theme.focus();">
<?php
  echo "<form name=\"add_form\" method=\"post\" onSubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "\" enctype=\"multipart/form-data\">";
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Flash Interface)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
<table border="0" cellpadding="3" cellspacing="0" align="center">
<tr><td colspan="2" style="font-family:Arial,sans-serif; font-size:90%; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td></tr>
<tr><td class="field">Theme/Heading</td><td><input type="text" size="82" name="theme" /></td></tr>
<tr><td class="field">Notes<br /><span class="note">(visible to students)</span></td><td><textarea name="notes" rows="2" cols="85" wrap="virtual"></textarea></td></tr>
<tr><td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td><td><?php echo wysiwyg_editor('oEdit1','leadin'); ?></td></tr>
<tr><td class="field">Parameters<br /><span class="note">(XHTML code)</span></td><td><textarea name="parameters" cols="85" rows="5"></textarea></td></tr>
<tr><td class="field"><span class="mandatory">*</span>&nbsp;Question SWF</td><td><input type="file" size="70" name="qfile" /></td></tr>
<tr><td class="field">Feedback SWF</td><td><input type="file" size="70" name="ffile" /></td></tr>
<tr><td></td><td class="note">(Feedback SWF file required if used in formative assessment mode)</td></tr>
<tr><td class="field">Marks</td><td>
<select name="marks">
<?php
  for ($i=1; $i<=20; $i++) {
    echo "<option value=\"$i\">$i</option>\n";
  }
  echo "</select>\n</td></tr>\n";
  echo echoMetadata('','','',1,$mysqli,true,'','');
?>
<tr>
  <td colspan="2">&nbsp;<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" /><input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" /><input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" /><input type="hidden" name="scrOfY" value="<?php echo $_GET['scrOfY']; ?>" /></td>
</tr>
<tr>
  <?php
    if ($_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
      echo '<td colspan="2" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
    } else {
      echo '<td colspan="2" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
    }
  ?>
</tr>
</table>
</div>
  <?php
    echo displayMappingTabAdd($_GET['paperID'],$mysqli, date('d/m/Y'));
  ?>
</form>
<?php
}
$mysqli->close();
?>
</body>
</html>
