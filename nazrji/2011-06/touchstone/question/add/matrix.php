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
  // Get all the data first into temporay variables.
  $tmp_theme = $_POST['theme'];
  $tmp_notes = $_POST['notes'];
  $tmp_scenario = '';
  $tmp_answer = '';
  $tmp_right_feedback = '';
  $tmp_wrong_feedback = '';
  $unique_name = '';
  $tmp_media_width = '';
  $tmp_media_height = '';

  $unique_name = uploadFile('general_media',$tmp_media_width,$tmp_media_height);

  for ($qcount=0; $qcount<10; $qcount++) {
    if ($_POST["stem$qcount"] != '' or $unique_name != '') {
      if ($tmp_scenario == '') {
        $tmp_scenario = $_POST["stem$qcount"];
        if(isset($_POST["correct$qcount"])) {
          $tmp_answer = $_POST["correct$qcount"];
        } else {
          $tmp_answer = '';
        }
      } else {
        $tmp_scenario .= '|' . $_POST["stem$qcount"];
        if(isset($_POST["correct$qcount"])) {
          $tmp_answer .= '|' . $_POST["correct$qcount"];
        } else {
          $tmp_answer .= '|';
        }
      }
    }
  }

  $tmp_leadin = clearMSOtags($_POST['leadin']);

  // Insert into Questions
  $question_id = insert_into_questions('matrix',$tmp_theme,$tmp_scenario,$tmp_leadin,'','','',$tmp_notes,$userID,$unique_name,$tmp_media_width,$tmp_media_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],$_POST['option_order']);

  // Insert into Options
  for ($ocount=0; $ocount<=10; $ocount++) {
    if (isset($_POST["option_text$ocount"]) and $_POST["option_text$ocount"] != '') {
      insert_into_options($question_id,$_POST["option_text$ocount"],'NULL','NULL','NULL','','',$tmp_answer,1);
    }
  }

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
<title>New Matrix Question<?php echo " $cfg_install_type"; ?></title>
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
      alert ("Please provide Lead-in instructions.");
      return false;
    }
    if (submit != '') {
      var modules = document.getElementById('modules').value;
      var modulesArray = modules.split(',');
      for(var j = 0; j < modulesArray.length; j++) {
        var objcount = document.getElementById(modulesArray[j] + '_objectiveCount').value;
        for(var i = 0; i < objcount; i++) {
          var cb = document.getElementById(modulesArray[j] + 'obj' + i).checked;
          if(cb == true) {
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

  function updateoptions(optionID) {
    labeltext = document.getElementById("option_text" + optionID).value;
    for (i=0; i<10; i++) {
      tempref = "correct_option" + i;
      document.getElementById(tempref).options[optionID].text = String.fromCharCode(optionID + 64) + ". " + labeltext;
    }
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

<body onload="document.add_form.theme.focus();">

<form name="add_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Matrix)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
  <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td colspan="2" style="font-size:9pt; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr><td colspan="2"><div class="section">General Information</div></td></tr>
    <tr><td class="field">Theme/Heading</td>
    <td><input type="text" size="82" name="theme" /></td>
    </tr>
    <tr>
      <td class="field" style="text-align: right">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"></textarea></td>
    </tr>
    <tr>
      <td class="field">Media</td>
      <td><input type="file" name="general_media" size="65" value="" /></td>
    </tr>
    <tr>
      <td class="field" style="text-align: right"><span class="mandatory">*</span>&nbsp;Lead-in</td>
      <td><?php echo wysiwyg_editor('oEdit1','leadin','For each of the following... (in rows) select a correct... (see columns).'); ?></td>
    </tr>
    <tr>
      <td class="field">Option Order</td><td colspan="2"><?php echo option_order(); ?></td>
    </tr>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr><td colspan="2"><div class="section">Options Matrix</div><div class="note">(questions in rows / answers by column)</div></td></tr>

    <tr><td colspan="2">
    <table cellpadding="2" cellspacing="0" border="1">

    <?php
      for ($row_no=0; $row_no<=10; $row_no++) {
        if ($row_no == 1 or $row_no == 3 or $row_no == 5 or $row_no == 7 or $row_no == 9) {
          echo '<tr style="background-color:#BFCEF3">';
        } else {
          echo '<tr>';
        }
        for ($col_no=0; $col_no<=10; $col_no++) {
          if ($row_no == 0 and $col_no == 0) {
            echo '<td>&nbsp;</td>';
          } elseif ($row_no == 0 and $col_no > 0) {
            echo '<td><input type="text" name="option_text' . ($col_no - 1) . '" size="6" /></td>';
          } elseif ($col_no == 0 and $row_no > 0) {
            echo '<td><input type="text" name="stem' . ($row_no - 1) . '" size="6" /></td>';
          } else {
            echo '<td><div align="center"><input type="radio" name="correct' . ($row_no - 1) . '" value="' . $col_no . '" /></div></td>';
          }
        }
        echo '</tr>';
      }
    ?>
    </table>
    </td></tr>

      <tr><td colspan="2">&nbsp;</td></tr>
    <?php
      echo echoMetadata('','','',1,$mysqli,true,'','');
    ?>
    </table>

    <br />
      <?php
	    echo hidden_add_fields();
        if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
          echo '<div style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></div>';
        } else {
          echo '<div style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></div>';
        }
      ?>
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
