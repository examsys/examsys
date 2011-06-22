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

  $tmp_correct_fback = stripslashes($_POST['correct_fback']);

  $score_method = $_POST['format'] . '|' . $_POST['start_year'] . '|' . $_POST['end_year'];
  // Insert into Questions
  $question_id = insert_into_questions('timedate',$_POST['theme'],$tmp_scenario,$tmp_leadin,$tmp_correct_fback, 'NULL',$score_method,$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],'display order');

  // Insert into Options
  switch ($_POST['format']) {
    case 1:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'] . ' ' . $_POST['answer_hour'] . ':' . $_POST['answer_minute'] . ':' . $_POST['answer_second'];
      break;
    case 2:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'] . ' ' . $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
      break;
    case 3:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'];
      break;
    case 4:
      $correct = $_POST['answer_month'] . '/' . $_POST['answer_day'] . '/' . $_POST['answer_year'];
      break;
    case 5:
      $correct = $_POST['answer_day'] . '/' . $_POST['answer_month'] . '/' . $_POST['answer_year'];
      break;
    case 6:
      $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'] . ':' . $_POST['answer_second'];
      break;
    case 7:
      $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
      break;
    case 8:
      $correct = $_POST['answer_hour'] . ':' . $_POST['answer_minute'];
      break;
  }
  insert_into_options($question_id,'NULL','NULL','NULL','NULL','NULL','NULL',$correct,1);

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
<title>New Time/Date Question<?php echo " $cfg_install_type"; ?></title>
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
    if (document.getElementById('leadin').value == "" || document.getElementById('leadin').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
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
<script language="JavaScript" src="../../javascript/jquery-1.6.1.min.js" type="text/javascript"></script>
<script language="JavaScript" src="../../javascript/mapping_tab.js" type="text/javascript"></script>
<script language="JavaScript" src="../../javascript/metadata.js" type="text/javascript"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/staff_help.js" type="text/javascript"></script>
<script type="text/javascript" src="../../javascript/jquery.formhelpers.js"></script>
</head>

<body onLoad="document.add_form.theme.focus();">

<form class="clearinput" name="add_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Time/Date)</span>
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
      <td colspan="6" style="font-size:9pt; color:#808080; text-align: center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
    </tr>
    <tr>
      <td class="field">Theme/Heading</td>
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
      <td class="field">Start Year</td>
      <td width="90"><input type="text" size="10" name="start_year" value="1906" /></td>
      <td class="field">End Year</td>
      <td><input type="text" size="10" name="end_year" value="2006" /></td>
      <td class="field"></td>
      <td></td>
    </tr>
    <tr>
      <td class="field">Format</td>
      <td colspan="5">
      <select name="format">
        <option value="1">dd/MM/yyyy hh:mm:ss</option>
        <option value="2">dd/MM/yyyy hh:mm</option>
        <option value="3">dd/MM/yyyy</option>
        <option value="4">mm/dd/yyyy</option>
        <option value="5">dd/MMMM/yyyy</option>
        <option value="6">hh:mm:ss</option>
        <option value="7">hh:mm (date)</option>
        <option value="8">hh:mm (duration)</option>
      </select>
      </td>
    </tr>
    <tr>
      <td class="field">Answer</td>
      <td colspan="5">
      <input type="text" size="5" name="answer_day" class="clearinput" title="dd" /> / <input type="text" size="5" name="answer_month" class="clearinput" title="MM" /> / <input type="text" size="5" name="answer_year" class="clearinput" title="yyyy" />&nbsp;&nbsp;&nbsp;<input type="text" size="5" name="answer_hour" class="clearinput" title="hh" /> : <input type="text" size="5" name="answer_minute" class="clearinput" title="mm" /> : <input type="text" size="5" name="answer_second" class="clearinput" title="ss" />
      </td>
    </tr>
    <tr>
      <td class="note" style="text-align: right">(only for assessments)</td>
      <td></td><td colspan="4" class="note">dd/MM/yyyy&nbsp;&nbsp;hh:mm:ss</td>
    </tr>
    <tr>
      <td class="field">Feedback<br /><span class="note">(only for assessments)</span></td>
      <td colspan="5"><textarea name="correct_fback" cols="100" style="width:700px" rows="4" wrap="virtual"></textarea></td>
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
        echo '<td colspan="6" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="6" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      }
    ?>
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
