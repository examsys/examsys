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

include_once('../../tools/getid3/getid3.php'); // or wherever you actually put the getid3 scripts

if (isset($_POST['addbank']) or isset($_POST['addpaper'])) {
  $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);

  if (isset($_POST['notapplicable'])) {
    $tmp_na = 'true';
  } else {
    $tmp_na = 'false';
  }

  // Insert the Question
  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  $tmp_leadin = clearMSOtags($_POST['leadin']);
  
  $scale_type = $_POST['scale_type'];
  if ($scale_type == 'custom') {
    $scale_size = 0;
    for ($i=1; $i<=10; $i++) {
      if ($_POST["custom$i"] != '') $scale_size = $i;
    }
    $scale_type = '';
    for ($i=1; $i<=$scale_size; $i++) {
      if ($scale_type == '') {
        $scale_type = trim($_POST["custom$i"]);
      } else {
        $scale_type .= '|' . trim($_POST["custom$i"]);
      }
    }
  }

  $format = $scale_type . '|' . $tmp_na;
  setcookie("likert_format", $format, time()+31536000);

  // Insert into Questions
  $question_id = insert_into_questions('likert',$_POST['theme'],$tmp_scenario,$tmp_leadin,'','',$format,$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),'',getTeams(),$_POST['status'],'display order');

  // Insert into Options
  insert_into_options($question_id,'NULL','NULL','NULL','NULL','NULL','NULL','NULL',1);

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
<title>New Likert Question<?php echo " $cfg_install_type"; ?></title>
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
      alert ("Please enter a Lead-in.");
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

  function checkCustom(clickedValue) {
    if (clickedValue.options[clickedValue.selectedIndex].value=='custom') {
      document.getElementById('customtbl').style.display = 'block';
    } else {
      document.getElementById('customtbl').style.display = 'none';
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Likert Scale)</span>
     </td>
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
      <td colspan="2" class="section">General Information</td>
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
      <td><input type="file" size="65" name="q_media" /></td>
    </tr>
    <tr>
      <td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
      <td><?php echo wysiwyg_editor('oEdit2','leadin'); ?></td>
    </tr>
    <?php
    if (isset($_COOKIE['likert_format'])) {
      $current_scale = substr($_COOKIE['likert_format'],0,strrpos($_COOKIE['likert_format'],'|'));
      $na = substr($_COOKIE['likert_format'],strrpos($_COOKIE['likert_format'],'|')+1);
    } else {
      $current_scale = 'Strongly<br />Disagree|Disagree|Uncertain|Agree|Strongly<br />Agree';
      $na = 'false';
    }
    $scale_types = array('line','OSCE Stations Scales','0|1','0, 1','0|1|2','0, 1, 2','Fail|Borderline|Pass','Fail, Borderline, Pass','line','3 Point Scales','Low||High','Low to High','Never||Always','Never to Always','Disagree|Neutral|Agree','Disagree, Neutral, Agree','line','4 Point Scales','Low|||High','Low to High','Never|||Always','Never to Always','Strongly<br />Disagree|Disagree|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Agree, Strongly Agree','line','5 Point Scales','Low||||High','Low to High','Never||||Always','Never to Always','Strongly<br />Disagree|Disagree|Neither Disagree<br />nor Agree|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Neither Disagree nor Agree, Agree, Strongly Agree','Strongly<br />Disagree|Disagree|Uncertain|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Uncertain, Agree, Strongly Agree','Strongly<br />Disagree|Disagree|Neutral|Agree|Strongly<br />Agree','Strongly Disagree, Disagree, Neutral, Agree, Strongly Agree','line','Custom');
    echo "<tr><td class=\"field\">Scale</td><td><select name=\"scale_type\" onchange=\"javascript: checkCustom(this);\">";
    $scale_match = false;
    for ($i=0; $i<count($scale_types); $i+=2) {
      if ($scale_types[$i] == 'line') {
        if ($i > 1) echo "</optgroup>\n";
        echo "<optgroup label=\"" . $scale_types[$i+1] . "\">";
      } else {
        if ($current_scale == $scale_types[$i]) {
          echo "<option value=\"" . $scale_types[$i] . "\" selected>" . $scale_types[$i+1] . "</option>\n";
          $scale_match = true;
        } else {
          echo "<option value=\"" . $scale_types[$i] . "\">" . $scale_types[$i+1] . "</option>\n";
        }
      }
    }
    if ($scale_match == true) {
      echo "<option value=\"custom\">Custom...</option>\n";
    } else {
      echo "<option value=\"custom\" selected>Custom...</option>\n";
      $score_parts = explode('|',$_COOKIE['likert_format']);
    }
    echo "</optgroup>\n</select></td></tr>\n";
    echo "<tr><td class=\"field\">N/A Column</td><td>";
    if ($na == 'true') {
      echo "<input type=\"checkbox\" name=\"notapplicable\" checked /> include 'not applicable' option</td>\n</tr>\n";
    } else {
      echo "<input type=\"checkbox\" name=\"notapplicable\" /> include 'not applicable' option</td>\n</tr>\n";
    }
    if ($scale_match == true) {
      echo "<tr><td></td><td><table id=\"customtbl\" style=\"display: none\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";
    } else {
      echo "<tr><td></td><td><table id=\"customtbl\" style=\"display: block\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";
    }
    for ($i=1; $i<=10; $i++) {
      if ($scale_match == true or $i >= count($score_parts)) {
        echo "<tr><td class=\"field\">$i.</td><td><input type=\"textbox\" size=\"30\" name=\"custom$i\" /></td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">$i.</td><td><input type=\"textbox\" size=\"30\" name=\"custom$i\" value=\"" . $score_parts[$i-1] . "\"/></td></tr>\n";
      }
    }
    echo "</table>\n</td></tr>\n";
    echo echoMetadata('','','',1,$mysqli,false,'','');
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
