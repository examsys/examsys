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

require_once('../../tools/getid3/getid3.php');

if (isset($_POST['submit1'])) {
  $tmp_width = 0;
  $tmp_height = 0;

  // Upload Image (if exists) onto server
  if (isset($_FILES['qmedia']['name'])) {
    $unique_name = unique_filename($_FILES['qmedia']['name']);
    if ($unique_name != 'none' and $unique_name != '') {
      if (!move_uploaded_file($_FILES['qmedia']['tmp_name'], "../../media/$unique_name"))  {
        echo uploadError($_FILES['qmedia']['error']);
        exit;
      } else {
        $tmp_width = 0;
        $tmp_height = 0;
        switch ($_FILES['qmedia']['type']) {
          case 'image/gif':
          case 'image/jpg':
          case 'image/jpeg':
          case 'image/pjpeg':
          case 'image/x-png':
          case 'image/png':
            // Image formats
            $identifier_size = GetImageSize("../../media/$unique_name");
            $tmp_width = $identifier_size[0];
            $tmp_height = $identifier_size[1];
            break;
          default:
            //Not an image!
            echo "<html>\n<head>\n<title>New Labelling Question</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
            echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
            echo "<h1 style=\"margin-left:60px\">File Type Error</h1>\n";
            echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are attempting to upload a file (" . $_FILES['qmedia']['type'] . ") that is not a supported file type.</p>\n<p style=\"margin-left:60px\"><input type=\"button\" onclick=\"history.back();\" name=\"back\" value=\"&lt; Back\" style=\"width:100px\" /></p>\n</body>\n</html>";

            exit();
            break;
        }
      }
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>New Hotspot Question<?php echo " $cfg_install_type"; ?></title>
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
<script language="JavaScript" src="../../javascript/ie_fix.js"></script>
<script language="JavaScript" src="../../javascript/flash_include.js"></script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
</head>

<body>
<form name="add_form" method="post" onsubmit="return checkForm()" action="hotspot2.php" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Image Hotspot)</span>
    </td>
    <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
  <table cellpadding="0" cellspacing="0" border="0" align="center">
    <tr>
    <td colspan="2" align="center">
    <table cellpadding="3" cellspacing="0" border="0">
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td class="field">Theme/Heading</td>
      <td><input type="text" name="theme" value="" size="75" /></td>
    </tr>
    <tr>
      <td class="field">Notes<br /><span class="note">(visible to students)</span></td>
      <td><textarea name="notes" cols="85" rows="2"></textarea></td>
    </tr>
    <tr>
      <td class="field">Scenario<br /><span class="note">(background info)</span></td>
      <td><?php echo wysiwyg_editor('oEdit1','scenario');?></td>
    </tr>
    <tr>
      <td class="field"><span class="mandatory">*</span>&nbsp;Image</td>
      <td>

  <?php
    $plugin_height = $tmp_height + 25;
    if ($plugin_height < 380) $plugin_height = 380;
  ?>
	<div id="flashid" style="width:<?php echo $tmp_width + 306; ?>px; height:<?php echo $plugin_height; ?>px;">
    <script language="JavaScript">
      function swfLoaded1(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $unique_name; ?>', '');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($tmp_width + 306); ?>" height="<?php echo $plugin_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="../add/hotspot_add.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="#F1F5FB" />');
      write_string('<embed src="../add/hotspot_add.swf" quality="high" bgcolor="#F1F5FB" width="<?php echo ($tmp_width + 306); ?>" height="<?php echo $plugin_height; ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
    </script>
    <noscript>Javascript must be enabled to view the Flash movie</noscript>
	</div>
      </td>
    </tr>
    <tr>
      <td valign="top" align="right" class="field">General<br />Feedback</td>
      <td><textarea name="correct_fback" cols="85" rows="6"></textarea></td>
    </tr>
    <?php
      echo echoMetadata('','','',1,$mysqli,true,'','');
    ?>
    <tr>
      <td colspan="2">&nbsp;<?php echo hidden_add_fields(); ?>
        <input type="hidden" id="q1" name="q1" value="" />
        <input type="hidden" id="score_method" name="score_method" value="" />
        <input type="hidden" name="image_name" value="<?php echo $unique_name; ?>" />
        <input type="hidden" name="image_width" value="<?php echo $tmp_width; ?>" />
        <input type="hidden" name="image_height" value="<?php echo $tmp_height; ?>" />
      </td>
    </tr>
    <tr>
      <?php
        if ($_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="2" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank" onmousedown="AddToBank();">&nbsp;<input style="width: 150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="submit" name="cancel" value="Cancel" onclick="formCancel();"/></td>';
        } else {
        echo '<td colspan="2" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width: 100px" type="submit" name="cancel" value="Cancel" onmousedown="formCancel();"/></td>';
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

</body>
</html>
<?php
} elseif (isset($_POST['cancel'])) {
  // Delete the file as the Canel button is pressed.
  unlink("../media/" . $_POST['image_name']);
  redirect($_POST['paperID']);
} else {
  $points = $_POST['q1'];
  $layers = explode('|',$points);
  $i = 0;
  $leadin = '';
  foreach ($layers as $layer) {
    $parts = explode('~',$layer);
    if ($leadin == '') {
      $leadin = chr(65 + $i) . ') ' . $parts[0];
    } else {
      $leadin .= ', ' . chr(65 + $i) . ') ' . $parts[0];
    }
    $i++;
  }
  $marks = $i;

  $tmp_scenario = clearMSOtags($_POST['scenario']);
  if (trim(strip_tags($tmp_scenario)) == '') $tmp_scenario = '';

  // Insert into Questions
  $question_id = insert_into_questions('hotspot',$_POST['theme'],$tmp_scenario,$leadin,$_POST['correct_fback'],'NULL','',$_POST['notes'],$userID,$_POST['image_name'],$_POST['image_width'],$_POST['image_height'],date("YmdHis"),date("YmdHis"),$_POST['bloom'],getTeams(),$_POST['status'],'display order');

  // Insert into Options
  insert_into_options($question_id,NULL,NULL,NULL,NULL,NULL,NULL,$points,$marks);

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
}
?>
