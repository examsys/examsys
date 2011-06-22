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
include_once('../../tools/getid3/getid3.php');

if (isset($_POST['addbank']) or isset($_POST['addpaper'])) {
  $unique_name = uploadFile('qmedia',$tmp_width,$tmp_height);

  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  $tmp_leadin = clearMSOtags($_POST['leadin']);

  $tmp_correct_fback = $_POST['correct_fback'];
  $keywords = getKeywords();
  $score_method = $_POST['columns'] . 'x' . $_POST['rows'];
  
  $question_team = getDefaultTeam();
  // Insert into Questions
  $question_id = insert_into_questions('textbox',$_POST['theme'],$tmp_scenario,$tmp_leadin,$tmp_correct_fback,NULL,$score_method,$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],$question_team,$_POST['status'],'display order');

  // Insert into Options
  insert_into_options($question_id,$_POST['editor'],NULL,NULL,NULL,NULL,NULL,$_POST['terms'],$_POST['marks']);

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>New Textbox Question<?php echo " $cfg_install_type"; ?></title>
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
    if (document.getElementById('leadin').value == "" || document.getElementById('leadin').value == " " || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
      alert ("Please enter a question leadin.");
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
  function showTab(tabID) {
    if (tabID == 'editortab') {
      document.getElementById('editortab').style.display = 'block';
      document.getElementById('mappingtab').style.display = 'none';
    } else if (tabID == 'mappingtab') {
      document.getElementById('editortab').style.display = 'none';
      document.getElementById('mappingtab').style.display = 'block';
    }
  }
</script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
</head>

<body onLoad="document.add_form.theme.focus();">
<form name="add_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Textbox)</span>
    </td>
    <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
    <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td colspan="6">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="6" style="font-size:80%; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
    </tr>
    <tr>
      <td class="field">Theme</td>
      <td colspan="5"><input type="text" name="theme" size="80" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td colspan="5"><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"></textarea></td>
    </tr>
    <tr>
      <td class="field">Scenario<br /><span class="note">(background info)</span></td>
      <td colspan="5"><?php echo wysiwyg_editor('oEdit1','scenario'); ?></td>
    </tr>
    <tr>
      <td class="field">Media</td>
      <td colspan="5"><input type="file" size="65" name="qmedia" /></td>
    </tr>
    <tr>
      <td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
      <td colspan="5"><?php echo wysiwyg_editor('oEdit2','leadin'); ?></td>
    </tr>
    <tr>
      <td class="field">Columns</td>
      <td>
        <select name="columns">
          <option value="10">10 cols</option>
          <option value="20">20 cols</option>
          <option value="30">30 cols</option>
          <option value="40">40 cols</option>
          <option value="50">50 cols</option>
          <option value="60">60 cols</option>
          <option value="70">70 cols</option>
          <option value="80">80 cols</option>
          <option value="90">90 cols</option>
          <option value="100" selected>100 cols</option>
          <option value="110">110 cols</option>
          <option value="120">120 cols</option>
        </select>
      </td>
      <td class="field">Rows</td>
      <td>
        <select name="rows">
          <option value="1">1 row</option>
          <option value="2">2 rows</option>
          <option value="3" selected>3 rows</option>
          <option value="4">4 rows</option>
          <option value="5">5 rows</option>
          <option value="6">6 rows</option>
          <option value="7">7 rows</option>
          <option value="8">8 rows</option>
          <option value="9">9 rows</option>
          <option value="10">10 rows</option>
          <option value="11">11 rows</option>
          <option value="12">12 rows</option>
          <option value="13">13 rows</option>
          <option value="14">14 rows</option>
          <option value="15">15 rows</option>
        </select>
      </td>
      <td class="field">Editor</td>
      <td>
        <select name="editor">
          <option value="plain">Plain Text</option>
          <option value="WYSIWYG">WYSIWYG</option>
        </select>
      </td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    <tr><td colspan="6"><div class="section">Assessment Data</div></td></tr>
    <tr>
      <td class="field">Marks</td>
      <td colspan="5">
        <select name="marks">
        <option value="0"></option>
        <?php
          for ($i=1; $i<=20; $i++) {
            echo "<option value=\"$i\">$i</option>\n";
          }
        ?>
        </select>
      </td>
    </tr>
    <tr>
      <td class="field">Feedback<br /><span class="note">(model answer for assessments)</span></td>
      <td colspan="5"><textarea name="correct_fback" cols="100" style="width:700px" rows="4" wrap="virtual"></textarea></td>
    </tr>
    <tr>
      <td class="field">Terms<br /><span class="note">(separate with semicolons)</td>
      <td colspan="5"><textarea name="terms" cols="100" style="width:700px" rows="2"></textarea></td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    <?php
      echo echoMetadata('','','',5,$mysqli,true,'','');
    ?>
    <tr>
      <td colspan="6">&nbsp;<?php echo hidden_add_fields(); ?></td>
    </tr>
    <tr>
    <?php
      if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="6" style="text-align: center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width:150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="6" style="text-align: center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      }
    ?>
    </tr>
  </table>
  </td>
  </tr>
  </table>
  </div>
  <?php
  if (isset($_GET['paperID'])) {
    $paperID = $_GET['paperID'];
  } else {
    $paperID = '';
  }
  
  echo displayMappingTabAdd($paperID,$mysqli,date('d/m/Y'));
  ?>
</form>
<?php
}
$mysqli->close();
?>
</body>
</html>
