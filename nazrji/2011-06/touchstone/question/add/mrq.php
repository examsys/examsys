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
  // Insert the Question
  $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);

  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  $tmp_leadin = clearMSOtags($_POST['leadin']);

  $tmp_score_method = $_POST['score_method'];
  if (isset($_POST['other']) and $_POST['other'] == 1) $tmp_score_method = 'other';

  $q_type = 'mrq';
  if (isset($_POST['mcqconvert']) and $_POST['mcqconvert'] == '1') {  // Convert from MRQ to MCQ.
    $q_type = 'mcq';
    $tmp_score_method = 'vertical';

    $tmp_correct = 0;
    for ($i=1; $i<=20; $i++) {
      if ($_POST["correct$i"] == '1') $tmp_correct = $i;
    }
  }

  // Insert into Questions
  $question_id = insert_into_questions($q_type,$_POST['theme'],$tmp_scenario,$tmp_leadin,$_POST['general_feedback'],'NULL',$tmp_score_method,$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],$_POST['option_order']);

  // Insert into Options
  for ($option_no = 1; $option_no <=20; $option_no++) {
    $tmp_option_text = $_POST["option_text$option_no"];
    $unique_name = uploadFile('omedia' . $option_no, $tmp_width, $tmp_height);
    
    if ($tmp_option_text != '' or ($unique_name != 'none' and $unique_name != '')) {
      if ($_POST['mcqconvert'] == '0') {
        if (isset($_POST['correct' . $option_no]) and $_POST['correct' . $option_no] == 1) {
          $tmp_correct = 'y';
        } else {
          $tmp_correct = 'n';
        }
      }
      $current_correctfb = $_POST["correct_feedback$option_no"];
      $current_incorrectfb = $_POST["incorrect_feedback$option_no"];
      insert_into_options($question_id, $tmp_option_text, $unique_name, $tmp_width, $tmp_height, $current_correctfb, $current_incorrectfb, $tmp_correct,1);
    }
  }

  // Save keywords
  $changes = false;
  saveKeywords($question_id, $userID, $changes, false, $mysqli);

  $paperID = $_POST['paperID'];
  // Insert into Papers
  if (isset($_POST['addpaper'])) {
    insert_into_papers($paperID, $question_id);
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
<title>New Multiple Response Question<?php echo " $cfg_install_type"; ?></title>
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
    if (oEdit2.getXHTMLBody() == "" || oEdit2.getXHTMLBody() == "&nbsp;" || oEdit2.getXHTMLBody() == "<p>&nbsp;</p>" || oEdit2.getXHTMLBody() == "<div>&nbsp;</div>" || oEdit2.getXHTMLBody() == "<br />") {
      alert ("Please enter a Leadin.");
      return false;
    }

    var correct_no = 0;
    for (var i=1; i<=20; i++) {
      if (document.getElementById('correct' + i).checked == true) {
        correct_no++;
      }
    }
    if (correct_no == 1) {
      if (confirm("There is only one correct answer, this would be better as a MCQ question type.\rDo you wish to convert this question to MCQ?")) {
        document.getElementById('mcqconvert').value = 1;
      }
    }

    if (document.add_form.score_method.options[document.add_form.score_method.selectedIndex].value == 1) {
      checkedOptions = 0;
      textOptions = 0;
      for (i=1; i<=20; i++) {
        if (eval("this.add_form.option_text" + i + ".value") != '') {
          textOptions++;
          if (eval("this.add_form.correct" + i + ".checked") == true) {
            checkedOptions++;
          }
        }
      }

      if ((textOptions / 2) < checkedOptions) {
        alert ("WARNING: You have " + textOptions + " options of which " + checkedOptions + " are correct.\nThe examinee will automatically gain " + (checkedOptions - (textOptions - checkedOptions))  + " point(s).\n\nDecrease the number of correct answers or add more\ndistractors.");
        return false;
      }
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

<body onLoad="document.add_form.theme.focus();">
<form name="add_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Multiple Response)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
  <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td colspan="2" style="font-family:Arial,sans-serif; font-size:9pt; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
    </tr>
    <tr>
      <td colspan="2" class="section">Question Details</td>
    </tr>
    <tr>
      <td class="field">Theme/Heading</td>
      <td><input type="text" name="theme" size="80" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"></textarea></td>
    </tr>
    <tr>
      <td class="field">Scenario<br /><span class="note">(background info)</span></td>
      <td><?php echo wysiwyg_editor('oEdit1','scenario'); ?></td>
    </tr>
    <tr>
      <td class="field">Media</td>
      <td><input type="file" size="70" name="q_media" /></td>
    </tr>
    <tr>
      <td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
      <td><?php echo wysiwyg_editor('oEdit2','leadin'); ?></td>
    </tr>
    <tr>
      <td class="field"><span class="mandatory">*</span>&nbsp;Score Method</td>
      <td>
        <select name="score_method" size="1">
          <option value="AllItemsCorrect">All Options must be Correct (1 mark in total)</option>
          <option value="SelectedPositive" selected>1 Mark per True Option</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="field">Presentation</td>
      <td><input type="checkbox" name="other" value="1" />&nbsp;include 'other' textbox <span style="color:#808080">(use with surveys)</span></td>
    </tr>
    <tr>
      <td class="field">General Feedback</td>
      <td><textarea name="general_feedback" cols="85" rows="3" wrap="virtual"></textarea></td>
    </tr>
    <tr>
      <td class="field">Option Order</td>
      <td><?php echo option_order(); ?></td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2" class="note">(Use as many or as few options as required)</td>
    </tr>
    <?php
      for ($option_no=1; $option_no <=20; $option_no++) {
	    if($option_no > 5) {
		  $hidden = 'style="display:none"';
		} else {
		  $hidden = '';
		}
        echo "<tr class=\"option\" $hidden>\n<td colspan=\"2\" class=\"section\">Option $option_no</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td colspan=\"2\" style=\"text-align:right; font-size:90%\"><strong>Correct?</strong></td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">";
        if ($option_no <= 3) echo '<span class="mandatory">*</span>&nbsp;';
        echo "Option Text</td>\n<td><textarea name=\"option_text" . $option_no . "\" cols=\"100\" style=\"width:700px\" rows=\"1\"></textarea>\n<input type=\"checkbox\" name=\"correct" . $option_no . "\" id=\"correct" . $option_no . "\" value=\"1\" />\n</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Media</td><td><input type=\"file\" size=\"70\" name=\"omedia$option_no\" /></td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Feedback if Right</td><td><textarea name=\"correct_feedback" . $option_no . "\" cols=\"100\" style=\"width:700px\" rows=\"2\"></textarea></td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Feedback if Wrong</td><td><textarea name=\"incorrect_feedback" . $option_no . "\" cols=\"100\" style=\"width:700px\" rows=\"2\"></textarea></td>\n</tr>\n";
      }
	  ?>
	  <tr>
      <td class="field"></td>
      <td><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(6)"/></td>
      </tr>
	<?php
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      echo echoMetadata('','','',1,$mysqli,true,'','');
    ?>
    <tr>
      <td colspan="2">&nbsp;<?php echo hidden_add_fields(); ?></td>
    </tr>
    <tr>
    <?php
      if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="2" style="text-align: center"><input style="width:150px" type="submit" name="addbank" onmousedown="AddToBank();" value="Add to Bank">&nbsp;<input style="width:150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /><input type="hidden" name="mcqconvert" id="mcqconvert" value="0" /></td>';
      } else {
        echo '<td colspan="2" style="text-align: center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /><input type="hidden" name="mcqconvert" id="mcqconvert" value="0" /></td>';
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