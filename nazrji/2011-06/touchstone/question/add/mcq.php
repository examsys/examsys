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
  for ($option_no=1; $option_no<=20; $option_no++) {
    if (isset($_POST["option_text$option_no"]) and trim($_POST["option_text$option_no"]) != '') {
      $no_options++;
    } elseif (isset($_FILES['omedia' . $option_no]) and $_FILES['omedia' . $option_no]['name'] != '' and $_FILES['omedia' . $option_no]['name'] != 'none') {
      $no_options++;
    }
  }
  if ($no_options == 0) {
    echo "<html>\n<head>\n<title>Access Denied</title>\n</head>\n<body>\n<div style=\"text-align:center\">\n<table cellpadding=\"10\" cellspacing=\"0\" border=\"0\" style=\"width: 400px; border: 1px solid red; color: red\">\n<tr><td style=\"width: 36px\"><img src=\"../artwork/red_exclamation_icon.gif\" width=\"36\" height=\"34\" alt=\"!\" /></td><td><strong>Error</strong><br />You have not entered any options.</td></tr>\n</table>\n</div>\n</body></html>";
    exit;
  } elseif ($no_options == 1) {
    echo "<html>\n<head>\n<title>Access Denied</title>\n</head>\n<body>\n<div style=\"text-align:center\">\n<table cellpadding=\"10\" cellspacing=\"0\" border=\"0\" style=\"width: 400px; border: 1px solid red; color: red\">\n<tr><td style=\"width: 36px\"><img src=\"../artwork/red_exclamation_icon.gif\" width=\"36\" height=\"34\" alt=\"!\" /></td><td><strong>Error</strong><br />You have only entered one option.</td></tr>\n</table>\n</div>\n</body></html>";
    exit;
  }

  $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);

  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  $tmp_leadin = clearMSOtags($_POST['leadin']);

  // Insert into Questions
  $question_id = insert_into_questions('mcq',$_POST['theme'],$tmp_scenario,$tmp_leadin,$_POST['correct_fback'],'',$_POST['score_method'],$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],$_POST['option_order']);

  // Insert into Options
  for ($option_no=1; $option_no<=20; $option_no++) {
    $tmp_option_text = $_POST["option_text$option_no"];
    
    if(isset($_FILES['omedia' . $option_no])) {
      $unique_name = uploadFile('omedia' . $option_no,$tmp_width,$tmp_height);
    } 
    
    if ($tmp_option_text != '' or ($unique_name != "none" and $unique_name != '')) {
      if (isset($_POST['correct'])) {
        $correct = $_POST['correct'];
      } else {
        $correct = '';
      }
      insert_into_options($question_id,$tmp_option_text,$unique_name,$tmp_width,$tmp_height,$_POST["feedback_right$option_no"],'',$correct,1);
    }
  }

  // Save keywords
  $changes = false;
  saveKeywords($question_id, $userID, $changes, false, $mysqli);

      $paperID = $_POST['paperID'];
  // Insert into Papers
  if (isset($_POST['addpaper'])) {
    insert_into_papers($paperID, $question_id);
    saveObjMappings($paperID, $question_id, $mysqli);
  }

  setcookie("default_team", getDefaultTeam(), time()+31536000);
  $mysqli->close();
  redirect($paperID);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
  <title>New MCQ Question<?php echo " $cfg_install_type"; ?></title>
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
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Multiple Choice)</span>
    </td>
    <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr>
    <td style="text-align:center">
     <table border="0" cellpadding="3" cellspacing="0" align="center">
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3" style="font-size:90%; color:#808080; text-align:center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
      </tr>
      <tr>
        <td colspan="3" class="section">General Information</td>
      </tr>
      <tr>
        <td class="field">Theme/Heading</td>
        <td colspan="2"><input type="text" name="theme" size="80" /></td>
      </tr>
      <tr>
        <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
        <td colspan="2"><textarea name="notes" cols="100" style="width:700px" rows="2" wrap="virtual"></textarea></td>
      </tr>
      <tr>
        <td class="field">Scenario<br /><span class="note">(background info)</span></td>
        <td colspan="2"><?php echo wysiwyg_editor('oEdit1','scenario'); ?></td>
      </tr>
      <tr>
        <td class="field">Media</td>
        <td colspan="2"><input type="file" name="q_media" size="65" /></td>
      </tr>
      <tr>
        <td class="field"><span class="mandatory">*</span>&nbsp;Lead-in<br /><span class="note">(the question)</span></td>
        <td colspan="2"><?php echo wysiwyg_editor('oEdit2','leadin'); ?></td>
      </tr>
      <tr>
        <td class="field">Presentation</td>
        <td colspan="2"><select name="score_method">
          <option value="vertical">Vertical Option Buttons</option>
          <option value="vertical_other">Vertical Option Buttons (with 'other' textbox)</option>
          <option value="horizontal">Horizonal Option Buttons</option>
          <option value="dropdown">Dropdown List</option>
        </td>
      </tr>
      <tr>
        <td class="field">Option Order</td><td colspan="2"><?php echo option_order(); ?></td>
      </tr>
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3"><div class="section">Options</div></td>
      </tr>
      <tr>
        <td colspan="3" class="field">Correct<br />Answer</span></td>
      </tr>

      <?php
      for ($option_no=1; $option_no<=20; $option_no++) {
        if ($option_no > 5) {
          $hidden = 'style="display:none"';
        } else {
          $hidden = '';
        }
        echo "<tr class=\"option\" $hidden>\n";
        echo "<td style=\"text-align:right\">";
        if ($option_no < 3) {
          echo "<span class=\"mandatory\">*</span>&nbsp;";
        }
        echo "<span class=\"field\">" . $option_no . ".&nbsp;</span></td>";
        echo "<td colspan=\"2\"><textarea name=\"option_text" . $option_no . "\" cols=\"100\" style=\"width:700px\" rows=\"1\"></textarea>&nbsp;<input type=\"radio\" name=\"correct\" value=\"" . $option_no . "\" /></td>";
        echo "</tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td>&nbsp;</td><td><span class=\"field\">Feedback</span></td><td><textarea cols=\"95\" rows=\"2\" name=\"feedback_right" . $option_no . "\"></textarea></td></tr>\n";
        echo "<tr class=\"option\" $hidden>\n<td>&nbsp;</td><td><span class=\"field\">Media</span></td><td><input type=\"file\" name=\"omedia$option_no\" size=\"75\" /></td></tr>\n";
        echo "<tr class=\"option\" $hidden ><td colspan=\"3\">&nbsp;</td></tr>\n";
      }
      ?>
    <tr>
      <td colspan="3"><input id="nextOption" type="button" value="Add More Options..." onclick="showNextOption(4)"/></td>
    </tr>
    <tr>
      <td class="field">General Feedback</td>
      <td colspan="2"><textarea name="correct_fback" cols="100" style="width:700px" rows="4" wrap="virtual"></textarea></td>
    </tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    <?php
      echo echoMetadata('','','',3,$mysqli,true,'','');
    ?>
    <tr>
      <td colspan="3">&nbsp;<?php echo hidden_add_fields(); ?></td>
    </tr>
    <tr>
    <?php
      if (isset($_GET['paperID']) and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="3" style="text-align: center"><input style="width: 150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
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
  <p>&nbsp;</p>
</form>

<?php
}
$mysqli->close();
?>
</body>
</html>