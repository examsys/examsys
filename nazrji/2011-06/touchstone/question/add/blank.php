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
  
  $unique_name = uploadFile('q_media',$tmp_width,$tmp_height);
  $tmp_stem = clearMSOtags($_POST['stem']);

  // Insert into Questions
  $question_id = insert_into_questions('blank',$_POST['theme'],'',$_POST['leadin'],$_POST['correct_fback'],'',$_POST['score_method'],$_POST['notes'],$userID,$unique_name,$tmp_width,$tmp_height,date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],'display order');
 
  // Insert into Options
  $id_num = insert_into_options($question_id, $tmp_stem,'NULL','NULL','NULL','NULL','NULL','NULL','NULL','NULL');

  // Save keywords
  $changes = false;
  saveKeywords($question_id, $userID, $changes, false, $mysqli);

  $paperID = $_POST['paperID'];
  // Insert into Papers
  if (isset($_POST['addpaper'])) {
    insert_into_papers($paperID,$question_id);
    saveObjMappings($paperID,$question_id,$mysqli);
  }

  if(isset($_POST['team'])) setcookie("default_team", $_POST['team'], time()+31536000);
  
  $mysqli->close();
  redirect($paperID);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>New Fill-in-the-Blank Question<?php echo " $cfg_install_type"; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
<script language="JavaScript">
  function checkForm() {
    <?php
    if($cfg_editor_name == 'tinymce') {
      echo "\t tinyMCE.triggerSave();";
    }
    ?>
    if (document.getElementById('leadin').value == "" || document.getElementById('stem').value == "&nbsp;" || document.getElementById('leadin').value == "<p>&nbsp;</p>" || document.getElementById('leadin').value == "<div>&nbsp;</div>" || document.getElementById('leadin').value == "<br />") {
      alert ("Please enter a Lead-in for the question.");
      return false;
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
<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Fill-in-the-Blank)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
  <br />
  <div align="center">
    <table cellpadding="3" cellspacing="0" border="0">
    <tr>
      <td colspan="2" style="font-size:9pt; color:#808080; text-align: center"><span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</td>
    </tr>
    <tr>
      <td class="field">Theme/Heading</td>
      <td><input type="text" name="theme" value="" size="80" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="100" style="width:700px" rows="2"></textarea></td>
    </tr>
    <tr>
      <td class="field">Media</td>
      <td><input type="file" size="65" name="q_media" /></td>
    </tr>
    <tr>
      <td class="field"><span class="mandatory">*</span>&nbsp;Lead-in</td>
      <td><textarea name="leadin" id="leadin" cols="100" style="width:700px" rows="2"></textarea></td>
    </tr>
    <tr>
      <td valign="top" align="right" class="field">Display Mode</td>
      <td><select name="score_method"><option value="dropdown">Dropdown Lists (randomised)</option><option value="textboxes">Blank Textboxes</option></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><span class="note">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />
      Always put the correct answer as the <strong>first</strong> option, followed by the distractors (if using dropdown lists).<br />
      e.g. Tyrannosaurus <span style="color:C00000">[blank]</span>Rex,Roger,Roderick,Ramsey<span style="color:C00000">[/blank]</span> was a large bipedal flesh-eating...</span></td>
    </tr>
    <tr>
      <td valign="top" align="right" class="field"><span class="mandatory">*</span>&nbsp;Question</td>
      <td>
      <?php echo wysiwyg_editor('oEdit1','stem','',700,250); ?>
      </td>
    </tr>
    <tr>
      <td class="field">Feedback</td>
      <td><textarea name="correct_fback" cols="100" style="width:700px" rows="6" wrap="virtual"></textarea></td>
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
        echo '<td colspan="2" style="text-align: center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank" onclick="return checkForm()" />&nbsp;<input style="width:150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper" onclick="return checkForm()" />&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="2" style="text-align: center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank" onclick="return checkForm()" />&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
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
  $mysqli->close();
?>
  <p>&nbsp;</p>
</form>

<?php
}
?>
</body>
</html>
