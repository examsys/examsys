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
  $no_options = 0;
  for ($option_no=1; $option_no<=8; $option_no++) {
    if ($_POST["option_min$option_no"] != '' or $_POST["option_max$option_no"] != '') $no_options++;
  }
  if ($no_options == 0) {
    display_error('Error','You have not entered any variables.');
  }

  $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);

  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  $tmp_leadin = clearMSOtags($_POST['leadin']);
  $tmp_score_method = $_POST['answer_decimals'] . ',' . $_POST['tolerance'] . ',' . $_POST['units'];

  // Insert into Questions
  $question_id = insert_into_questions('calculation',$_POST['theme'],$tmp_scenario,$tmp_leadin,$_POST['feedback'],'NULL',$tmp_score_method,$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],'display order');

  // Insert into Options
  for ($option_no=1; $option_no<=8; $option_no++) {
    if ($_POST["option_min$option_no"] != '' and $_POST["option_max$option_no"] != '') {
      $tmp_option_text = $_POST["option_min$option_no"] . ',' . $_POST["option_max$option_no"] . ',' . $_POST["increment$option_no"] . ',' . $_POST["decimals$option_no"];
      insert_into_options($question_id,$tmp_option_text,$unique_name,$tmp_width,$tmp_height,'','', $_POST['formula'],$_POST['marks']);
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

  redirect($paperID);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>New Calculation Question<?php echo " $cfg_install_type"; ?></title>
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
      alert ("Please enter a Lead-in for this question.");
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

  function updateoptions(optionID) {
    labeltext = document.getElementById("option_text" + optionID).value;
    for (i=0; i<10; i++) {
      tempref = "correct_options" + i;
      document.getElementById(tempref).options[optionID].text = String.fromCharCode(optionID + 65) + ". " + labeltext;
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

  function variableLink(elementID, iconID) {
    window.open("/touchstone/question/edit/variable_link.php?paperID=<?php echo $_GET['paperID'] . '&elementID='; ?>" + elementID + "&iconID=" + iconID + "","paper","width=600,height=400,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
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
      <span style="position:absolute; left:80px; top:40px; font-size:12pt; font-weight:bold">(Calculation)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr>
    <td style="text-align: center">
     <table border="0" cellpadding="3" cellspacing="0" align="center">
      <tr>
        <td colspan="7" style="font-size:80%; color:#808080; text-align: center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
      </tr>
      <tr>
        <td colspan="7" class="section">General Information</td>
      </tr>
      <tr>
        <td class="field">Theme/Heading</td>
        <td colspan="6"><input type="text" name="theme" size="80" /></td>
      </tr>
      <tr>
        <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
        <td colspan="6"><textarea name="notes" cols="85" style="width:700px" rows="2" wrap="virtual"></textarea></td>
      </tr>
      <tr>
        <td class="field">Scenario<br /><span class="note">(background info)</span></td>
        <td colspan="6"><?php echo wysiwyg_editor('oEdit1','scenario'); ?></td>
      </tr>
      <tr>
        <td class="field">Media</td>
        <td colspan="6"><input type="file" name="q_media" size="65" /></td>
      </tr>
      <tr>
        <td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
        <td colspan="6"><?php echo wysiwyg_editor('oEdit2','leadin'); ?></td>
      </tr>
      <tr>
        <td colspan="7"><div class="section">Variables</div></td>
      </tr>
      <tr style="font-size: 85%">
        <td></td>
        <td>Min</td>
        <td style="width:50px">&nbsp;</td>
        <td>Max</td>
        <td style="width:50px">&nbsp;</td>
        <td style="width:140px">Decimals</td>
        <td style="width:310px">Increment</td>
      </tr>

      <?php
      for ($option_no=1; $option_no<=8; $option_no++) {
        echo "<tr>\n";
        echo "<td style=\"text-align: right\">";
        echo "<span class=\"field\">$" . chr(64 + $option_no) . "</span></td>";
        echo "<td><input id=\"variable" . chr($option_no+64) . "_min\" type=\"text\" name=\"option_min" . $option_no . "\" style=\"width:100%\" size=\"10\" /></td>";
        echo "<td><img id=\"minicon$option_no\" style=\"cursor:pointer\" onclick=\"variableLink('variable" . chr($option_no+64) . "_min','minicon$option_no')\" src=\"../../artwork/variable_link_off.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" /></td>\n";
        echo "<td><input id=\"variable" . chr($option_no+64) . "_max\" type=\"text\" name=\"option_max" . $option_no . "\" style=\"width:100%\" size=\"10\" /></td>\n";
        echo "<td><img id=\"maxicon$option_no\" style=\"cursor:pointer\" onclick=\"variableLink('variable" . chr($option_no+64) . "_max','minicon$option_no')\" src=\"../../artwork/variable_link_off.png\" width=\"23\" height=\"22\" border=\"0\" alt=\"Link\" /></td>\n";
        echo "<td><select name=\"decimals" . $option_no . "\">\n";
        echo "<option value=\"0\">0</option>\n";
        echo "<option value=\"1\">1</option>\n";
        echo "<option value=\"2\">2</option>\n";
        echo "<option value=\"3\">3</option>\n";
        echo "<option value=\"4\">4</option>\n";
        echo "</select></td>\n";
        echo "<td><select name=\"increment" . $option_no . "\">\n";
        echo "<option value=\"0.0001\">0.0001</option>\n";
        echo "<option value=\"0.001\">0.001</option>\n";
        echo "<option value=\"0.01\">0.01</option>\n";
        echo "<option value=\"0.1\">0.1</option>\n";
        echo "<option value=\"1\" selected>1</option>\n";
        echo "<option value=\"2\">2</option>\n";
        echo "<option value=\"3\">3</option>\n";
        echo "<option value=\"4\">4</option>\n";
        echo "<option value=\"5\">5</option>\n";
        echo "<option value=\"6\">6</option>\n";
        echo "<option value=\"7\">7</option>\n";
        echo "<option value=\"8\">8</option>\n";
        echo "<option value=\"9\">9</option>\n";
        echo "<option value=\"10\">10</option>\n";
        echo "<option value=\"25\">25</option>\n";
        echo "<option value=\"50\">50</option>\n";
        echo "<option value=\"100\">100</option>\n";
        echo "<option value=\"1000\">1000</option>\n";
        echo "</select></td>\n";
        echo "</tr>\n";
      }
      ?>

      <tr>
        <td colspan="7"><div class="section">Answer</div></td>
      </tr>
      <tr>
        <td class="field"><span class="mandatory">*</span>&nbsp;Formula<br /><span style="font-weight:normal"><a href="#" onclick="launchHelp(68); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Online Help" border="0" /></a>&nbsp;<a href="#" onclick="launchHelp(68,'functions'); return false;">supported functions</a></span></td>
        <td colspan="6"><textarea name="formula" cols="100" style="width:700px" rows="2" wrap="virtual"></textarea></td>
      </tr>
      <tr>
        <td class="field">Units</td>
        <td colspan="6"><input type="text" name="units" size="10" />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold; font-size:85%; color:black">Decimals&nbsp;</span>
          <select name="answer_decimals">
            <option value="0">0</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
          </select>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold; font-size:85%; color:black">Tolerance&nbsp;</span><input type="text" name="tolerance" size="10" value="0" />
        </td>
      </tr>
      <tr>
        <td class="field">Feedback</td>
        <td colspan="6"><textarea name="feedback" cols="100" style="width:700px" rows="4" wrap="virtual"></textarea></td>
      </tr>

    <tr><td colspan="7">&nbsp;</td></tr>
    <?php
      echo "<tr>\n<td class=\"field\">Marks</td>\n<td colspan=\"6\">\n<select name=\"marks\">\n";
      for ($i=1; $i<=20; $i++) {
        echo "<option value=\"$i\">$i</option>\n";
      }
      echo "</select>\n</td>\n</tr>\n";
      echo echoMetadata('','','',6,$mysqli,true,'','');
    ?>
    <tr>
      <td colspan="7">&nbsp;<?php echo hidden_add_fields(); ?></td>
    </tr>
    <tr>
    <?php
      if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="7" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width:150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="7" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      }
    ?>
    </tr>
  </table>
 </td></tr>
</table>
</div>
<?php
  if (isset($_GET['paperID'])) {
    $paperID = $_GET['paperID'];
  } else {
    $paperID = '';
  }
  
  echo displayMappingTabAdd($paperID,$mysqli,date('d/m/Y'));
  $mysqli->close();
?>
  <p>&nbsp;</p>
</form>
<?php
}
?>
</body>
</html>