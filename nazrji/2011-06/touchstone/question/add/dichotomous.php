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
require '../../tools/getid3/getid3.php';

if (isset($_POST['addbank']) or isset($_POST['addpaper'])) {
  // Add Question data
  $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);

  $tmp_theme = $_POST['theme'];
  $tmp_theme = clearMSOtags($tmp_theme);

  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  $tmp_leadin = clearMSOtags($_POST['leadin']);

  // Insert into Questions
  $question_id = insert_into_questions('dichotomous',$tmp_theme,$tmp_scenario,$tmp_leadin,$_POST['question_feedback'],'NULL',$_POST['score_method'],$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],$_POST['option_order']);

  // Add Option data
  for ($option_no = 1; $option_no <=15; $option_no++) {
    $tmp_correct = '';
    $tmp_option_correct_fback = '';
    $tmp_option_incorrect_fback = '';
    $tmp_option_abstain_fback = '';
    $tmp_option_text = '';
    $tmp_option_text = html_entity_decode($_POST["option_text$option_no"]);
    $unique_name = uploadFile('omedia' . $option_no,$tmp_width,$tmp_height);
    if ($tmp_option_text != '' or ($unique_name != 'none' and $unique_name != '')) {
      if(isset($_POST['correct' . $option_no])) $tmp_correct = $_POST['correct' . $option_no];
      if(isset($_POST['option_correct_fback' . $option_no])) $tmp_option_correct_fback = $_POST['option_correct_fback' . $option_no];
      if(isset($_POST['option_incorrect_fback' . $option_no])) $tmp_option_incorrect_fback = $_POST['option_incorrect_fback' . $option_no];
      if(isset($_POST['option_abstain_fback' . $option_no])) $tmp_option_abstain_fback = $_POST['option_abstain_fback' . $option_no];
      $tmp_option_text = preg_replace("[\r\n\t]", "", trim($tmp_option_text));

      insert_into_options($question_id,$tmp_option_text,$unique_name,$tmp_width,$tmp_height,$tmp_option_correct_fback,$tmp_option_incorrect_fback,$tmp_correct,1);
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
<title>New Dichotomous Question<?php echo " $cfg_install_type"; ?></title>
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
      alert ("Please enter a Leadin for the question.");
      return false;
    }
    if (document.getElementById('option_text1').value == "") {
      alert ("Please enter at least one option.");
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

  function updatelabels(obj) {
    for (i = 1; i < obj.length; i++) {
      if (obj[i].selected == true) {
        if (obj[i].value == 'TF_Negative' || obj[i].value == 'TF_NegativeAbstain' || obj[i].value == 'TF_Positive' || obj[i].value == 'TF_PositiveAbstain') {
          for (x=1; x<=10; x++) {
            document.getElementById('true' + x).setAttribute('innerHTML','T');
            document.getElementById('false' + x).setAttribute('innerHTML','F');
          }
        } else {
          for (x=1; x<=10; x++) {
            document.getElementById('true' + x).setAttribute('innerHTML','Y');
            document.getElementById('false' + x).setAttribute('innerHTML','N');
          }
        }
      }
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

<body onLoad="document.add_form.theme.focus();">
<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return checkForm()" enctype="multipart/form-data">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Dichotomous)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
<div align="center">
<table border="0" cellpadding="3" cellspacing="0">
<tr><td colspan="2" style="font-size:90%; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td></tr>
<tr><td colspan="2"><div class="section">General Information</div></td></tr>
<tr><td class="field">Theme/Heading</td><td><input type="text" size="75" name="theme" /></td></tr>
<tr><td class="field">Notes<br /><span class="note">(visible to students)</span></td><td><textarea name="notes" rows="2" cols="85" wrap="virtual"></textarea></td></tr>
<tr><td class="field">Scenario<br /><span class="note">(background info)</span></td><td><?php echo wysiwyg_editor('oEdit1','scenario'); ?></td></tr>
<tr><td class="field">Media</td><td><input type="file" size="65" name="q_media" /></td></tr>
<tr><td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td><td><?php echo wysiwyg_editor('oEdit2','leadin'); ?></td></tr>
<tr><td class="field">General Feedback</td><td><textarea name="question_feedback" rows="3" cols="100" style="width:700px" wrap="virtual"></textarea></td></tr>
<tr><td class="field"><span class="mandatory">*</span>&nbsp;Scoring Method</td><td><select name="score_method" size="1" onchange="updatelabels(this)">
  <option value="TF_NegativeAbstain" style="color:red">True/False/Abstain (Negative Marking -1)</option>
  <option value="TF_Positive" selected>True/False</option>
  <option value="YN_NegativeAbstain" style="color:red">Yes/No/Abstain (Negative Marking -1)</option>
  <option value="YN_Positive">Yes/No</option>
</select></td></tr>

<tr><td class="field">Option Order</td><td><?php echo option_order(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<?php
for ($num=1; $num<=15; $num++) {
  if($num > 5) {
    $hidden = 'style="display:none"';
  } else {
	 $hidden = '';
  }
  echo "<tr class=\"option\" $hidden><td colspan=\"2\"><div class=\"section\">Option $num</div></td></tr>\n";
  echo "<tr class=\"option\" $hidden><td class=\"field\">";
  if ($num == 1) {
    echo "<span class=\"mandatory\">*</span>&nbsp;";
  }
  echo "Option Text</td>";
  echo "<td><textarea id=\"option_text$num\" name=\"option_text$num\" cols=\"90\" style=\"width:640px\" rows=\"1\" /></textarea>&nbsp;<input type=\"radio\" id=\"trueradio$num\" name=\"correct$num\" value=\"t\" />&nbsp;<strong><span id=\"true$num\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 10pt\">T</span></strong>&nbsp;&nbsp;&nbsp;<input type=\"radio\" id=\"falseradio$num\" name=\"correct$num\" value=\"f\" />&nbsp;<strong><span id=\"false$num\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 10pt\">F</span></strong></td></tr>\n";
  echo "<tr class=\"option\" $hidden><td class=\"field\">Media</td><td><input type=\"file\" name=\"omedia$num\" size=\"65\" /></td></tr>\n";

  echo "<tr class=\"option\" $hidden><td class=\"field\">Feedback if Right<br /><span style=\"font-weight:normal; font-size:90%; color:red\">(default feedback)</span></td>";
  echo "<td><textarea name=\"option_correct_fback$num\" rows=\"2\" cols=\"100\" style=\"width:700px\"></textarea></td></tr>\n";

  echo "<tr class=\"option\" $hidden><td class=\"field\">Feedback if Wrong<br /><span style=\"font-weight:normal; font-size:90%; color:#808080\">(leave blank to use default)</span></td>";
  echo "<td><textarea name=\"option_incorrect_fback$num\" rows=\"2\" cols=\"100\" style=\"width:700px\"></textarea></td></tr>\n";
}
?>
 <tr>
 <td class="field"></td>
 <td><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(5)"/></td>
 </tr>
<?php
echo echoMetadata('','','',1,$mysqli,true,'','');
    ?>
  <tr>
    <td colspan="2">&nbsp;<?php echo hidden_add_fields(); ?></td>
  </tr>
  <tr>
    <?php
      if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="2" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="2" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      }
    ?>
  </tr>
</table>
</div>
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
