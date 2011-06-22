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

  // Insert into Questions
  $question_id = insert_into_questions('rank',$_POST['theme'],$tmp_scenario,$tmp_leadin,$_POST['correct_fback'],$_POST['incorrect_fback'],$_POST['score_method'],$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],$_POST['option_order']);

  // Insert into Options
  for ($option_no=1; $option_no<=20; $option_no++) {
    $tmp_option_text = $_POST["option_text$option_no"];
    $tmp_answer = $_POST["answer$option_no"];
    if ($tmp_option_text != '') {
      insert_into_options($question_id,$tmp_option_text,'NULL','NULL','NULL','NULL','NULL',$tmp_answer,1);
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
<title>New Ranking Question<?php echo " $cfg_install_type"; ?></title>
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
    if (document.getElementById('leadin').value == "" || document.getElementById('leadin').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
      alert ("Please enter a Lead-in for the question.");
      return false;
    }
    if(submit != '') {
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Ranking)</span>
    </td>
    <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
     <table cellpadding="3" cellspacing="0" border="0" align="center">
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3" class="note" style="text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
      </tr>
      <tr>
        <td colspan="3"><div class="section">General Information</div></td>
      </tr>
      <tr>
        <td class="field" colspan="2">Theme/Heading</td>
        <td><input type="text" name="theme" size="80" /></td>
      </tr>
      <tr>
        <td class="field" colspan="2">Notes<br /><span class="note">(visible to students)</span></td>
        <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"></textarea></td>
      </tr>
      <tr>
        <td class="field" colspan="2">Scenario<br /><span class="note">(background info)</span></td>
        <td><?php echo wysiwyg_editor('oEdit1','scenario'); ?></td>
      </tr>
      <tr>
        <td class="field" colspan="2">Media</td>
        <td><input type="file" size="70" name="qmedia" /></td>
      </tr>
      <tr>
        <td class="field" colspan="2"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
        <td><?php echo wysiwyg_editor('oEdit2','leadin','Please rank the following ... starting with the ... first:'); ?></td>
      </tr>
      <tr>
        <td class="field" colspan="2"><span class="mandatory">*</span>&nbsp;Score Method</td>
        <td>
          <select name="score_method" size="1">
          <option value="StrictOrder">Strict Order (mark per option)</option>
          <option value="AllItemsCorrect">All Items Correct (1 mark in total)</option>
          <option value="OrderNeighbours">Strict Order with half marks for neighbours</option>
          <option value="BonusMark" selected>Correct items with bonus for overall order</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="field" colspan="2">Option Order</td><td><?php echo option_order(); ?></td>
      </tr>
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3"><div class="section">Options</div></td>
      </tr>
      <tr>
        <td colspan="3" style="text-align:right; font-size:90%"><strong>Correct Order</strong></td>
      </tr>

      <?php
      for ($option_no=1; $option_no<=20; $option_no++) {
 	    if($option_no > 6) {
		  $hidden = 'style="display:none"';
		} else {
		  $hidden = '';
		}
		echo "<tr class=\"option\" $hidden>\n";
        if ($option_no == 1) {
          echo "<td class=\"field\" rowspan=\"22\" valign=\"top\">Options:<br /><span class=\"note\">(Display Order)</span></td>";
        }
        echo "<td style=\"text-align:right\">";
        if ($option_no <= 3) {
          echo "<span class=\"mandatory\">*</span>&nbsp;";
        }
        echo "<strong>" . $option_no . ".&nbsp;</strong></td>";
        echo "<td><input type=\"text\" name=\"option_text" . $option_no . "\" size=\"95\" />&nbsp;";

        echo "<select name=\"answer" . $option_no . "\">\n<option value=\"0\"></option>\n";
        echo "<option value=\"0\">N/A</option>\n";
        for ($possibility=1; $possibility <=15; $possibility++) {
          echo "<option value=\"" . $possibility . "\">" . $possibility;
          if ($possibility == 1) {
            echo 'st';
          } elseif ($possibility == 2) {
            echo 'nd';
          } elseif ($possibility == 3) {
            echo 'rd';
          } else {
            echo 'th';
          }
          echo "</option>\n";
        }
        echo "</select></td>\n";
        echo "</tr>\n";
      }
      ?>
	<tr>
	<td>&nbsp;</td>
	<td><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(1)"/></td>
	</tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td class="field" >Feedback if Right<br /><span style="font-weight:normal; font-size:90%; color:red">(default feedback</span></td>
      <td colspan="2"><textarea name="correct_fback" cols="100" style="width:700px" rows="3" wrap="virtual"></textarea></td>
    </tr>
    <tr>
      <td class="field" >Feedback if Wrong<br /><span style="font-weight:normal; font-size:90%; color:#808080">(leave blank to use fefault)</span></td>
      <td colspan="2"><textarea name="incorrect_fback" cols="100" style="width:700px" rows="3" wrap="virtual"></textarea></td>
    </tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    <?php
      echo echoMetadata('','','',2,$mysqli,true,'','');
    ?>
    <tr>
      <td colspan="3">&nbsp;<?php echo hidden_add_fields(); ?></td>
    </tr>
    <tr>
      <?php
        if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
          echo '<td colspan="3" style="text-align:center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
        } else {
          echo '<td colspan="3" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
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
