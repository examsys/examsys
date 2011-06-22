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
  //Get all the data first into temporay variables.
  $tmp_theme = $_POST['theme'];
  $tmp_leadin = clearMSOtags($_POST['leadin']);
  $tmp_notes = $_POST['notes'];
  $tmp_bloom = $_POST['bloom'];
  $tmp_question_team = getTeams();
  $tmp_scenario = '';
  $tmp_answer = '';
  $tmp_right_feedback = '';
  $tmp_wrong_feedback = '';
  $tmp_media = '';
  $tmp_media_width = '';
  $tmp_media_height = '';

  $media_name = $_FILES['general_media']['name'];
  $media_type = $_FILES['general_media']['type'];
  if ($media_name != '' and $media_name != 'none') {
    $tmp_media = uploadFile('general_media',$tmp_media_width,$tmp_media_height);
  } else {
    $tmp_media = '';
    $tmp_media_width .= '0';
    $tmp_media_height .= '0';
  }

  $tmp_correct_feedback = '';
  $tmp_incorrect_feedback = '';
  for ($qcount=0; $qcount<10; $qcount++) {
    $media_name = $_FILES["media$qcount"]['name'];
    $media_type = $_FILES["media$qcount"]['type'];
    $tmp_omedia_name = '';
    if ($_POST["stem$qcount"] != '' or $media_name != '') {
      if ($media_name != '' and $media_name != 'none') {
        $tmp_omedia_name = uploadFile("media$qcount",$opt_media_width,$opt_media_height);
        $tmp_media .= '|' .$tmp_omedia_name;
        $tmp_media_width .= '|' . $opt_media_width;
        $tmp_media_height .= '|' . $opt_media_height;
      } else {
        $tmp_media .= '|';
        $tmp_media_width .= '|0';
        $tmp_media_height .= '|0';
      }
      if (trim(strip_tags($_POST["stem$qcount"])) != '' or $media_name != '') {
        $tmp_scenario .= '|' . clearMSOtags($_POST["stem$qcount"]);
        if(isset($_POST["correct_options$qcount"])) {
          $addr = $_POST["correct_options$qcount"];
        } else {
          $addr = ' ';
        }
        $count = count($addr);
        for ($i=0; $i<$count; $i++) {
          if ($addr[$i] != '') {
            if ($i == 0) {
              $store_answer = ($addr[$i] + 1);
            } else {
              $store_answer .= '$' . ($addr[$i] + 1);
            }
          }
        }
        $tmp_answer .= '|' . $store_answer;
        $tmp_correct_feedback .= '|' . $_POST["correct_fback$qcount"];
      }
    }
  }

  // Strip the first bar off the front.
  $tmp_scenario = substr($tmp_scenario,1);
  $tmp_answer = substr($tmp_answer,1);
  $tmp_correct_feedback = substr($tmp_correct_feedback,1);

  // Insert into Questions
  $question_id = insert_into_questions('extmatch',$tmp_theme,$tmp_scenario,$tmp_leadin,$tmp_correct_feedback,$tmp_incorrect_feedback,'',$tmp_notes,$userID,$tmp_media,$tmp_media_width,$tmp_media_height,date("YmdHis"),date("YmdHis"),$tmp_bloom,getTeams(),$_POST['status'],$_POST['option_order']);

  for ($ocount=0; $ocount<26; $ocount++) {
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
  <title>New Extended Matching Question<?php echo " $cfg_install_type"; ?></title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
  <script language="JavaScript">
  function updateoptions(optionID) {
    labeltext = document.getElementById("option_text" + optionID).value;
    for (i=0; i<10; i++) {
      tempref = "correct_options" + i;
      document.getElementById(tempref).options[optionID].text = String.fromCharCode(optionID + 65) + ". " + labeltext;
    }
  }

  var submit = '';
  function AddToBank() {
    submit = 'AddToBank';
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
    if($cfg_editor_name == 'tinymce') {
      echo "\t tinyMCE.triggerSave();";
    }
    ?>
    if (document.getElementById('leadin').value == "" || document.getElementById('leadin').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
      alert ("Please provide Lead In instructions.");
      submit = '';
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
  </script>
  <script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
  <script language="JavaScript" src="../../javascript/metadata.js"></script>
  <?php echo $cfg_editor_javascript; ?>
  <script language="JavaScript" src="../../javascript/staff_help.js"></script>
  </head>

  <body onload="document.add_form.theme.focus();">
<form name="add_form" method="post" onsubmit="return checkForm(event)" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr height="70" style="background-color:#DFECFF">
<td width="400">
<img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
<span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
<span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Extended Matching)</span></td>
<td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
</td>
</tr>
</table>
<?php
echo displayEditTab();
?>
<table cellpadding="3" cellspacing="0" border="0" align="center">
<tr>
<td>&nbsp;</td>
<td colspan="4" style="font-size:90%; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
</tr>
<tr>
<td class="section" colspan="2">General Information</td>
<td width="10">&nbsp;</td>
<td class="section" style="text-align: right">Available Options</td>
</tr>
<tr>
<td class="field" style="text-align: right">Theme</td>
<td><input type="text" name="theme" size="70"></td>
<td width="20">&nbsp;</td>
<td rowspan="38" valign="top" style="text-align: right">
<?php
  for ($option=0; $option<26; $option++) {
    if ($option < 4) {
      echo '<span class="mandatory">*</span>&nbsp;';
    }
    echo "<span class=\"field\">" . chr($option + 65) . ".&nbsp;</span><input onchange=\"updateoptions($option)\" type=\"text\" id=\"option_text$option\" name=\"option_text$option\" size=\"25\" /><br />\n";
  }
?>
</td>
</tr>
<tr>
<td class="field" style="text-align: right">Notes<br /><span class="note">(visible to students)</span></td>
<td><textarea name="notes" cols="83" rows="2" wrap="virtual"></textarea></td>
<td width="20">&nbsp;</td>
</tr>
<tr>
<td class="field" style="text-align: right"><span class="mandatory">*</span>&nbsp;Lead-in</td>
<td><?php echo wysiwyg_editor('oEditLeadin','leadin');?></td>
<td width="20">&nbsp;</td>
</tr>
<tr>
  <td class="field">Media</td>
  <td><input type="file" name="general_media" size="68" value="" /></td>
  <td width="20">&nbsp;</td>
</tr>
<tr>
  <td class="field">Option Order</td>
  <td><?php echo option_order(); ?></td>
  <td width="20">&nbsp;</td>
</tr>
      <?php
      $roman = array('i','ii','iii','iv','v','vi','vii','viii','ix','x');
      for ($question=0; $question<10; $question++) {
	if ($question > 2) {
	  $hidden = 'style="display:none"';
	} else {
	  $hidden = '';
	}
        echo "<tr class=\"option\" $hidden>\n<td colspan=\"3\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"section\" colspan=\"2\">Scenario " . $roman[$question] . ".</td>\n<td width=\"20\">&nbsp;</td>\n</tr>";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">";
        if ($question < 3) {
          echo "<span class=\"mandatory\">*</span>&nbsp;";
        }
        echo "Stem</td>\n<td>" . wysiwyg_editor("oEdit$question","stem$question") . "</td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Media</td>\n<td><input type=\"file\" name=\"media$question\" size=\"68\" value=\"\" /></td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">Feedback</td>\n<td><textarea name=\"correct_fback" . $question . "\" cols=\"83\" rows=\"3\" wrap=\"virtual\"></textarea></td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        //echo "<tr>\n<td class=\"field\">Feedback if wrong</td>\n<td><textarea name=\"incorrect_fback" . $question . "\" cols=\"75\" rows=\"2\" wrap=\"virtual\"></textarea></td>\n<td width=\"20\">&nbsp;</td>\n</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td class=\"field\">";
        if ($question < 3) {
          echo "<span class=\"mandatory\">*</span>&nbsp;";
        }
        echo "Correct Answers<br /><span style=\"color:#808080; font-size:90%; font-weight:normal\">(Use &lt;ctrl&gt; plus mouse<br />to select several items)</span></td>\n";
        echo "<td><select name=\"correct_options" . $question . "[]\" multiple=\"multiple\" id=\"correct_options$question\" style=\"width:300px\" size=\"10\">\n";
        for ($option=0; $option<26; $option++) {
          echo "<option value=\"" . $option . "\">" . chr($option + 65) . ".</option>\n";
        }
        echo "</select></td>\n</tr>\n";
      }
      echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
  ?>
  <tr>
  <td class="field"></td>
  <td><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(6)"/></td>
  </tr>
  <?php
    echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
    echo echoMetadata('','','',2,$mysqli,true,'','');
  ?>
<tr>
   <td colspan="3">&nbsp;<?php echo hidden_add_fields(); ?></td>
</tr>
<tr>
  <?php
    if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
      echo '<td colspan="3" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width:150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
    } else {
      echo '<td colspan="3" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
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